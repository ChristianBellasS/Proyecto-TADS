<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Asistencia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background: linear-gradient(to right, #3b82f6, #6366f1);
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h2, h3 {
            text-align: center;
            margin-bottom: 1rem;
        }
        input {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #2563eb;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #1d4ed8;
        }
        .alert {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bienvenido</h2>
        <h3>Registro de Asistencia</h3>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('attendance.register') }}">
            @csrf
            <input type="text" name="dni" placeholder="Documento de identidad" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Registrar Asistencia</button>
        </form>
    </div>
</body>
</html>
