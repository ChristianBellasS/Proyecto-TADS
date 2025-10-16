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
$(document).ready(function() {
    // Formatear placa
    $('#plate').on('input', function() {
        $(this).val($(this).val().toUpperCase().replace(/[^A-Z0-9-]/g, ''));
    });

    // Cargar modelos cuando cambia la marca
    $('#brand_id').change(function() {
        var brandId = $(this).val();
        var modelSelect = $('#model_id');
        
        console.log('Marca cambiada:', brandId);
        
        if (brandId) {
            modelSelect.html('<option value="">Cargando modelos...</option>');
            modelSelect.prop('disabled', true);
            
            $.ajax({
                url: '{{ route("admin.vehicles.get-models", ["brandId" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', brandId),
                type: 'GET',
                dataType: 'json',
                success: function(models) {
                    console.log('Modelos recibidos:', models);
                    
                    modelSelect.html('<option value="">Seleccione un modelo</option>');
                    
                    if (models && models.length > 0) {
                        $.each(models, function(index, model) {
                            modelSelect.append($('<option>', {
                                value: model.id,
                                text: model.name
                            }));
                        });
                    } else {
                        modelSelect.html('<option value="">No hay modelos disponibles</option>');
                    }
                    
                    modelSelect.prop('disabled', false);
                    
                    // Seleccionar modelo actual en edición
                    @if(isset($vehicle) && $vehicle->model_id)
                        modelSelect.val('{{ $vehicle->model_id }}');
                    @endif
                },
                error: function(xhr, status, error) {
                    console.error('Error en AJAX:', error);
                    modelSelect.html('<option value="">Error al cargar modelos</option>');
                    modelSelect.prop('disabled', false);
                }
            });
        } else {
            modelSelect.html('<option value="">Primero seleccione una marca</option>');
            modelSelect.prop('disabled', true);
        }
    });

    // Cargar modelos inicial si hay marca seleccionada
    @if(isset($vehicle) && $vehicle->brand_id)
        setTimeout(function() {
            $('#brand_id').val('{{ $vehicle->brand_id }}').trigger('change');
        }, 500);
    @endif
});
</script>