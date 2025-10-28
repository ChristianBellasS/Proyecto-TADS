<div class="row">
    <div class="col-12">
        <!-- Nombre del Turno -->
        <div class="form-group">
            <label for="name" class="font-weight-bold">Nombre del Turno *</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                </div>
                {!! Form::text('name', null, [
                    'class' => 'form-control',
                    'placeholder' => 'Ingrese el nombre del turno',
                    'required'
                ]) !!}
            </div>
            <small class="form-text text-muted">Ejemplo: Turno Mañana, Turno Tarde, Turno Noche</small>
        </div>

        <!-- Hora de Entrada y Hora de Salida en la misma fila -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="hour_in" class="font-weight-bold">Hora de Entrada *</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                    </div>
                    {!! Form::time('hour_in', null, [
                        'class' => 'form-control',
                        'required'
                    ]) !!}
                </div>
                <small class="form-text text-muted">Formato de 24 horas</small>
            </div>

            <div class="form-group col-md-6">
                <label for="hour_out" class="font-weight-bold">Hora de Salida *</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                    </div>
                    {!! Form::time('hour_out', null, [
                        'class' => 'form-control',
                        'required'
                    ]) !!}
                </div>
                <small class="form-text text-muted">Formato de 24 horas</small>
            </div>
        </div>

        <!-- Descripción -->
        <div class="form-group">
            <label for="description" class="font-weight-bold">Descripción</label>
            {!! Form::textarea('description', null, [
                'class' => 'form-control',
                'placeholder' => 'Ingrese una descripción del turno (opcional)',
                'rows' => 3
            ]) !!}
        </div>

        <!-- Nota informativa -->
        <div class="alert alert-info mt-3">
            <strong>Nota:</strong> Configure los horarios de entrada y salida para este turno.
        </div>
    </div>
</div>
