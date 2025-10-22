<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;


class AttendanceController extends Controller
{
    public function register(Request $request)
    {
        // Validar los campos del formulario
        $request->validate([
            'dni' => 'required|string',
            'password' => 'required|string',
        ]);

        // Obtener las credenciales
        $credentials = $request->only('dni', 'password');

        // Intentar autenticar usando el guard 'employee'
        if (Auth::guard('employee')->attempt($credentials)) {
            $employee = Auth::guard('employee')->user();

            Attendance::create([
                'employee_id'     => $employee->id,
                'attendance_date' => now(), // fecha y hora completa
                'type'            => 'entrada', // puedes cambiar a 'salida' si lo necesitas
                'period'          => 1, // por ejemplo: 1 = mañana, 2 = tarde
                'status'          => 1, // por ejemplo: 1 = registrado, 0 = pendiente
                'notes'           => 'Asistencia registrada automáticamente',
            ]);

            return back()->with('success', 'Asistencia registrada correctamente');
        }


        // Si falla la autenticación
        return back()->withErrors(['dni' => 'Credenciales incorrectas']);
    }
}
