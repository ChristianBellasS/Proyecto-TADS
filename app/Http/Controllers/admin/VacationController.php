<?php

namespace App\Http\Controllers\admin;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vacation;
use App\Models\Employee;
use Carbon\Carbon;

class VacationController extends Controller
{
    // /**
    //  * Display a listing of the resource.
    //  */
    // public function index()
    // {
    //     //
    // }

    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     //
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     //
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(string $id)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(string $id)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $id)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     //
    // }

    public function index()
    {
        $vacations = Vacation::with('employee')->latest()->get();
        return view('admin.vacations.index', compact('vacations'));
    }

    public function create()
    {
        $employees = Employee::with('employeetype')->active()->get();
        return view('admin.vacations.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date|after_or_equal:today',
            'requested_days' => 'required|integer|min:1|max:30',
            'notes' => 'nullable|string|max:500',
        ]);

        // Validar que el empleado puede solicitar vacaciones
        if (!Vacation::canEmployeeRequestVacation($request->employee_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Este tipo de empleado no puede solicitar vacaciones'
            ], 422);
        }

        // Validar días disponibles
        $availableDays = Vacation::getRemainingVacationDays($request->employee_id);
        if ($request->requested_days > $availableDays) {
            return response()->json([
                'success' => false,
                'message' => "Solo tiene {$availableDays} días de vacaciones disponibles"
            ], 422);
        }

        // Validar que no tenga vacaciones solapadas
        $overlapping = Vacation::where('employee_id', $request->employee_id)
            ->whereIn('status', ['Pending', 'Approved'])
            ->where(function($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })->exists();

        if ($overlapping) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tiene vacaciones programadas para estas fechas'
            ], 422);
        }

        // Calcular fecha de fin
        $startDate = Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addDays($request->requested_days - 1);

        $vacation = Vacation::create([
            'employee_id' => $request->employee_id,
            'request_date' => now(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'requested_days' => $request->requested_days,
            'notes' => $request->notes,
            'status' => 'Pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de vacaciones creada exitosamente'
        ]);
    }

    public function show(Vacation $vacation)
    {
        return response()->json($vacation->load('employee'));
    }

    public function edit(Vacation $vacation)
    {
        $employees = Employee::with('employeetype')->active()->get();
        return view('admin.vacations.edit', compact('vacation', 'employees'));
    }

    public function update(Request $request, Vacation $vacation)
    {
        // Solo permitir edición si está pendiente
        if ($vacation->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar solicitudes pendientes'
            ], 422);
        }

        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'requested_days' => 'required|integer|min:1|max:30',
            'notes' => 'nullable|string|max:500',
        ]);

        // Validar días disponibles (excluyendo los días de esta solicitud)
        $availableDays = Vacation::getRemainingVacationDays($vacation->employee_id) + $vacation->requested_days;
        if ($request->requested_days > $availableDays) {
            return response()->json([
                'success' => false,
                'message' => "Solo tiene {$availableDays} días de vacaciones disponibles"
            ], 422);
        }

        // Validar solapamiento (excluyendo esta solicitud)
        $overlapping = Vacation::where('employee_id', $vacation->employee_id)
            ->where('id', '!=', $vacation->id)
            ->whereIn('status', ['Pending', 'Approved'])
            ->where(function($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })->exists();

        if ($overlapping) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tiene vacaciones programadas para estas fechas'
            ], 422);
        }

        // Calcular fecha de fin
        $startDate = Carbon::parse($request->start_date);
        $endDate = $startDate->copy()->addDays($request->requested_days - 1);

        $vacation->update([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'requested_days' => $request->requested_days,
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de vacaciones actualizada exitosamente'
        ]);
    }

    public function destroy(Vacation $vacation)
    {
        // Solo permitir eliminación si está pendiente
        if ($vacation->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden eliminar solicitudes pendientes'
            ], 422);
        }

        $vacation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de vacaciones eliminada exitosamente'
        ]);
    }

    // Aprobar vacaciones
    public function approve(Vacation $vacation)
    {
        if ($vacation->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden aprobar solicitudes pendientes'
            ], 422);
        }

        // Validar días disponibles
        $availableDays = Vacation::getRemainingVacationDays($vacation->employee_id);
        if ($vacation->requested_days > $availableDays) {
            return response()->json([
                'success' => false,
                'message' => "El empleado no tiene suficientes días disponibles. Disponibles: {$availableDays}"
            ], 422);
        }

        $vacation->update(['status' => 'Approved']);

        return response()->json([
            'success' => true,
            'message' => 'Vacaciones aprobadas exitosamente'
        ]);
    }

    // Rechazar vacaciones
    public function reject(Vacation $vacation)
    {
        if ($vacation->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden rechazar solicitudes pendientes'
            ], 422);
        }

        $vacation->update(['status' => 'Rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Vacaciones rechazadas exitosamente'
        ]);
    }

    // Cancelar vacaciones
    public function cancel(Vacation $vacation)
    {
        if (!in_array($vacation->status, ['Pending', 'Approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden cancelar solicitudes pendientes o aprobadas'
            ], 422);
        }

        $vacation->update(['status' => 'Cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Vacaciones canceladas exitosamente'
        ]);
    }

    // Obtener días disponibles de un empleado
    public function getAvailableDays($employeeId)
    {
        $availableDays = Vacation::getRemainingVacationDays($employeeId);
        $canRequest = Vacation::canEmployeeRequestVacation($employeeId);

        //Imprimir resultado
        \Log::info("Días disponibles para el empleado {$employeeId}: {$availableDays}");
        \Log::info("¿Puede solicitar vacaciones? " . ($canRequest ? 'Sí' : 'No'));

        return response()->json([
            'available_days' => $availableDays,
            'can_request' => $canRequest
        ]);
    }


}
