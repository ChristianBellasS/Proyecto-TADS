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
                ->orderBy('attendance_date', 'desc')
                ->orderBy('created_at', 'desc');

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
                ->addColumn('period', function ($attendance) {
                    return $attendance->period;
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

    public function create()
    {
        return view('admin.attendances.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'attendance_time' => 'required|date_format:H:i', // Nuevo campo para la hora
            'type' => 'required|in:ENTRADA,SALIDA',
            'period' => 'required|integer|min:1|max:4',
            'status' => 'required|integer|min:1|max:4',
            'notes' => 'nullable|string'
        ]);

        // Combinar fecha y hora
        $attendanceDateTime = $validated['attendance_date'] . ' ' . $validated['attendance_time'];
        $validated['attendance_date'] = Carbon::createFromFormat('Y-m-d H:i', $attendanceDateTime);

        // Verificar si ya existe un registro para este empleado en la misma fecha, tipo y período
        $existingAttendance = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->where('type', $validated['type'])
            ->where('period', $validated['period'])
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un registro de asistencia para este empleado en la fecha, tipo y período seleccionados.'
            ], 422);
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
            'period' => 'required|integer|min:1|max:4',
            'status' => 'required|integer|min:1|max:4',
            'notes' => 'nullable|string'
        ]);

        // Combinar fecha y hora
        $attendanceDateTime = $validated['attendance_date'] . ' ' . $validated['attendance_time'];
        $validated['attendance_date'] = Carbon::createFromFormat('Y-m-d H:i', $attendanceDateTime);

        // Verificar si ya existe otro registro para este empleado en la misma fecha, tipo y período
        $existingAttendance = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->where('type', $validated['type'])
            ->where('period', $validated['period'])
            ->where('id', '!=', $attendance->id)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe otro registro de asistencia para este empleado en la fecha, tipo y período seleccionados.'
            ], 422);
        }

        $attendance->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Asistencia actualizada correctamente.'
        ]);
    }

    public function destroy(Attendance $attendance)
    {
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

    // Método para obtener las opciones de período
    public function getPeriodOptions()
    {
        return response()->json([
            'data' => [
                ['id' => 1, 'name' => 'Mañana'],
                ['id' => 2, 'name' => 'Tarde'],
                ['id' => 3, 'name' => 'Noche'],
                ['id' => 4, 'name' => 'Día completo']
            ]
        ]);
    }

    // Método para obtener las opciones de estado
    public function getStatusOptions()
    {
        return response()->json([
            'data' => [
                ['id' => 1, 'name' => 'Presente'],
                ['id' => 2, 'name' => 'Ausente'],
                ['id' => 3, 'name' => 'Tarde'],
                ['id' => 4, 'name' => 'Permiso']
            ]
        ]);
    }
}