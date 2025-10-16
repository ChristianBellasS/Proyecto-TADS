<div class="row">
    <div class="col-8">
        <div class="form-group">
            {!! Form::label('name', 'Nombre') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nombre del vehículo', 'required']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('code', 'Código') !!}
            {!! Form::text('code', null, ['class' => 'form-control', 'placeholder' => 'Código del vehículo', 'required']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('plate', 'Placa') !!}
            {!! Form::text('plate', null, ['class' => 'form-control', 'placeholder' => 'Placa del vehículo', 'required']) !!}
        </div>

        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    {!! Form::label('year', 'Año') !!}
                    {!! Form::number('year', null, [
                        'class' => 'form-control', 
                        'placeholder' => 'Año',
                        'min' => '1900',
                        'max' => date('Y') + 1,
                        'required'
                    ]) !!}
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    {!! Form::label('load_capacity', 'Capacidad de Carga (kg)') !!}
                    {!! Form::number('load_capacity', null, [
                        'class' => 'form-control', 
                        'placeholder' => 'Capacidad',
                        'step' => '0.01',
                        'min' => '0',
                        'required'
                    ]) !!}
                </div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('description', 'Descripción') !!}
            {!! Form::textarea('description', null, [
                'class' => 'form-control',
                'placeholder' => 'Agregue una descripción',
                'rows' => 3,
            ]) !!}
        </div>
    </div>

    <div class="col-4">
        <div class="form-group">
            {!! Form::label('brand_id', 'Marca') !!}
            {!! Form::select('brand_id', $brands->pluck('name', 'id'), null, [
                'class' => 'form-control',
                'required',
                'placeholder' => 'Seleccione una marca'
            ]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('model_id', 'Modelo') !!}
            {!! Form::select('model_id', $models->pluck('name', 'id'), null, [
                'class' => 'form-control',
                'required',
                'placeholder' => 'Seleccione un modelo'
            ]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('type_id', 'Tipo de Vehículo') !!}
            {!! Form::select('type_id', $types->pluck('name', 'id'), null, [
                'class' => 'form-control',
                'required',
                'placeholder' => 'Seleccione un tipo'
            ]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('color_id', 'Color') !!}
            {!! Form::select('color_id', $colors->pluck('name', 'id'), null, [
                'class' => 'form-control',
                'required',
                'placeholder' => 'Seleccione un color'
            ]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('status', 'Estado') !!}
            {!! Form::select('status', [
                1 => 'Activo',
                0 => 'Inactivo'
            ], null, ['class' => 'form-control', 'required']) !!}
        </div>
    </div>
</div>

<script>
// Cuando se carga el formulario, convertir la placa a mayúsculas
document.addEventListener('DOMContentLoaded', function() {
    const plateInput = document.getElementById('plate');
    if (plateInput) {
        plateInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Si estamos editando, asegurar que la placa esté en mayúsculas
        if (plateInput.value) {
            plateInput.value = plateInput.value.toUpperCase();
        }
    }
});
</script>