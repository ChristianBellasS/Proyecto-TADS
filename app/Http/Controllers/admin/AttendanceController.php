<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $attendances = Attendance::with('employee')
                ->when($request->start_date, function ($query) use ($request) {
                    return $query->whereDate('attendance_date', '>=', $request->start_date);
                })
                ->when($request->end_date, function ($query) use ($request) {
                    return $query->whereDate('attendance_date', '<=', $request->end_date);
                })
                ->when($request->type, function ($query) use ($request) {
                    return $query->where('type', $request->type);
                })
                ->when($request->search, function ($query) use ($request) {
                    return $query->where(function ($q) use ($request) {
                        $q->whereHas('employee', function ($employeeQuery) use ($request) {
                            $employeeQuery->where('dni', 'like', '%' . $request->search . '%')
                                ->orWhere('name', 'like', '%' . $request->search . '%')
                                ->orWhere('last_name', 'like', '%' . $request->search . '%');
                        });
                    });
                })
                ->orderBy('attendances.attendance_date', 'desc')
                ->orderBy('attendances.created_at', 'desc');

            return DataTables::of($attendances)
                ->addColumn('dni', function ($attendance) {
                    return $attendance->employee ? $attendance->employee->dni : '-';
                })
                ->addColumn('employee_name', function ($attendance) {
                    return $attendance->employee ? $attendance->employee->name . ' ' . $attendance->employee->last_name : '-';
                })
                ->addColumn('attendance_date', function ($attendance) {
                    return $attendance->attendance_date ? $attendance->attendance_date->format('d/m/Y H:i:s') : '-';
                })
                ->addColumn('type', function ($attendance) {
                    return $attendance->type;
                })
                ->addColumn('status', function ($attendance) {
                    return $attendance->status;
                })
                ->addColumn('edit', function ($attendance) {
                    return '';
                })
                ->addColumn('delete', function ($attendance) {
                    return '';
                })
                ->rawColumns(['edit', 'delete'])
                ->make(true);
        }

        return view('admin.attendances.index');
    }

    public function create(Request $request)
    {

        $employeeId = $request->get('employee_id');
        $attendanceDate = $request->get('attendance_date', now()->format('Y-m-d'));

        $suggestedType = 'ENTRADA';
        $isTypeLocked = false;
        $lastAttendance = null;

        if ($employeeId) {
            // Buscar registros del empleado para la fecha seleccionada
            $existingAttendances = Attendance::where('employee_id', $employeeId)
                ->whereDate('attendance_date', $attendanceDate)
                ->orderBy('attendance_date', 'asc')
                ->get();

            $hasEntry = $existingAttendances->where('type', 'ENTRADA')->count() > 0;
            $hasExit = $existingAttendances->where('type', 'SALIDA')->count() > 0;

            if (!$hasEntry) {
                $suggestedType = 'ENTRADA';
                $isTypeLocked = true;
            } elseif ($hasEntry && !$hasExit) {
                $suggestedType = 'SALIDA';
                $isTypeLocked = true;
            } else {
                $suggestedType = 'ENTRADA';
                $isTypeLocked = false;
            }

            $lastAttendance = $existingAttendances->last();
        }

        return view('admin.attendances.create', compact(
            'suggestedType',
            'isTypeLocked',
            'employeeId',
            'attendanceDate',
            'lastAttendance'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'attendance_time' => 'required|date_format:H:i',
            'type' => 'required|in:ENTRADA,SALIDA',
            'status' => 'required|integer|min:1|max:2',
            'notes' => 'nullable|string',
        ]);

        $attendanceDateTime = $validated['attendance_date'] . ' ' . $validated['attendance_time'];
        $validated['attendance_date'] = Carbon::createFromFormat('Y-m-d H:i', $attendanceDateTime);

        // Verificar si ya existe un registro para este empleado en la misma fecha y tipo
        $existingAttendance = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->where('type', $validated['type'])
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un registro de ' . strtolower($validated['type']) . ' para este empleado en la fecha seleccionada.'
            ], 422);
        }

        // Validar secuencia lógica
        $existingAttendances = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->orderBy('attendance_date', 'asc')
            ->get();

        $hasEntry = $existingAttendances->where('type', 'ENTRADA')->count() > 0;
        $hasExit = $existingAttendances->where('type', 'SALIDA')->count() > 0;

        // Validaciones de secuencia
        if ($validated['type'] === 'SALIDA' && !$hasEntry) {
            return response()->json([
                'success' => false,
                'message' => 'No puede registrar una SALIDA sin tener una ENTRADA registrada primero.'
            ], 422);
        }

        if ($validated['type'] === 'ENTRADA' && $hasExit) {
            // Permitir múltiples entradas/salidas pero validar horarios
            $lastExit = $existingAttendances->where('type', 'SALIDA')->last();
            if ($lastExit && $validated['attendance_date'] <= $lastExit->attendance_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'La nueva ENTRADA debe ser posterior a la última SALIDA registrada.'
                ], 422);
            }
        }

        Attendance::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Asistencia registrada correctamente.'
        ]);
    }

    public function edit(Attendance $attendance)
    {
        // Separar fecha y hora para el formulario
        $attendance->load('employee');
        $attendance->formatted_date = $attendance->attendance_date->format('Y-m-d');
        $attendance->formatted_time = $attendance->attendance_date->format('H:i');

        return view('admin.attendances.edit', compact('attendance'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'attendance_time' => 'required|date_format:H:i',
            'type' => 'required|in:ENTRADA,SALIDA',
            'status' => 'required|integer|min:1|max:2',
            'notes' => 'nullable|string'
        ]);

        // Combinar fecha y hora
        $attendanceDateTime = $validated['attendance_date'] . ' ' . $validated['attendance_time'];
        $validated['attendance_date'] = Carbon::createFromFormat('Y-m-d H:i', $attendanceDateTime);

        // Verificar si ya existe otro registro para este empleado en la misma fecha y tipo
        $existingAttendance = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->where('type', $validated['type'])
            ->where('id', '!=', $attendance->id)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe otro registro de asistencia para este empleado en la fecha y tipo seleccionados.'
            ], 422);
        }

        $attendance->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Asistencia actualizada correctamente.'
        ]);
    }

    public function destroy($id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Asistencia no encontrada.'
            ], 404);
        }

        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Asistencia eliminada correctamente.'
        ]);
    }


    public function searchEmployees(Request $request)
    {
        $search = $request->get('search');

        $employees = Employee::query()
            ->select('id', 'name', 'last_name', 'dni', 'email', 'telefono')
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('last_name', 'like', '%' . $search . '%')
                        ->orWhere('dni', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->activo()
            ->orderBy('name')
            ->orderBy('last_name')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $employees->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name . ' ' . $employee->last_name,
                    'dni' => $employee->dni,
                    'email' => $employee->email,
                    'phone' => $employee->telefono
                ];
            })
        ]);
    }

    // Método para obtener las opciones de tipo (para usar en formularios)
    public function getTypeOptions()
    {
        return response()->json([
            'data' => [
                ['id' => 'ENTRADA', 'name' => 'Entrada'],
                ['id' => 'SALIDA', 'name' => 'Salida']
            ]
        ]);
    }

    // Método para obtener las opciones de estado
    public function getStatusOptions()
    {
        return response()->json([
            'data' => [
                ['id' => 1, 'name' => 'Presente'],
                ['id' => 2, 'name' => 'Tarde']
            ]
        ]);
    }

    public function getDayRecords(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date'
        ]);

        $records = Attendance::where('employee_id', $request->employee_id)
            ->whereDate('attendance_date', $request->date)
            ->orderBy('attendance_date', 'asc')
            ->get()
            ->map(function ($record) {
                return [
                    'type' => $record->type,
                    'time' => $record->attendance_date->format('H:i:s'),
                    'status' => $record->status
                ];
            });

        return response()->json([
            'records' => $records
        ]);
    }

  // Métodos para vistas públicas
    public function __construct()
    {
        // Aplicar middleware solo a métodos de admin, excluyendo los públicos
        $this->middleware('auth:web')->except(['createPublic', 'storePublic', 'searchEmployees', 'getDayRecords']);
    }

    public function createPublic(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $attendanceDate = $request->get('attendance_date', now()->format('Y-m-d'));

        $suggestedType = 'ENTRADA';
        $isTypeLocked = false;
        $lastAttendance = null;

        if ($employeeId) {
            // Buscar registros del empleado para la fecha seleccionada
            $existingAttendances = Attendance::where('employee_id', $employeeId)
                ->whereDate('attendance_date', $attendanceDate)
                ->orderBy('attendance_date', 'asc')
                ->get();

            $hasEntry = $existingAttendances->where('type', 'ENTRADA')->count() > 0;
            $hasExit = $existingAttendances->where('type', 'SALIDA')->count() > 0;

            if (!$hasEntry) {
                $suggestedType = 'ENTRADA';
                $isTypeLocked = true;
            } elseif ($hasEntry && !$hasExit) {
                $suggestedType = 'SALIDA';
                $isTypeLocked = true;
            } else {
                $suggestedType = 'ENTRADA';
                $isTypeLocked = false;
            }

            $lastAttendance = $existingAttendances->last();
        }

        // Usar vista pública
        return view('public.attendances.create', compact(
            'suggestedType',
            'isTypeLocked',
            'employeeId',
            'attendanceDate',
            'lastAttendance'
        ));
    }

    public function storePublic(Request $request)
    {
        // Reutilizar la misma lógica del store normal
        return $this->store($request);
    }
}
