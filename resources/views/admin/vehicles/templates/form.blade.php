<div class="row">
    <!-- Primera fila: Código y Tipo de Vehículo -->
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('code', 'Código *') !!}
            {!! Form::text('code', null, [
                'class' => 'form-control', 
                'placeholder' => 'Ingrese el código (Ej: VEH-ZXIPO)',
                'required',
                'maxlength' => 100
            ]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('type_id', 'Tipo de Vehículo *') !!}
            {!! Form::select('type_id', $types->pluck('name', 'id'), null, [
                'class' => 'form-control',
                'required',
                'placeholder' => 'Seleccione un tipo',
                'style' => 'width: 100%;',
                'id' => 'type_id'
            ]) !!}
        </div>
    </div>
</div>

<!-- Segunda fila: Nombre del Vehículo y Placa -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('name', 'Nombre del Vehículo *') !!}
            {!! Form::text('name', null, [
                'class' => 'form-control', 
                'placeholder' => 'Ingrese el nombre (EJ: VEHICUL001)',
                'required',
                'maxlength' => 100
            ]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('plate', 'Placa *') !!}
            {!! Form::text('plate', null, [
                'class' => 'form-control', 
                'placeholder' => 'Ingrese la placa (Ej: ABC-123)',
                'required',
                'maxlength' => 20,
                'id' => 'plate'
            ]) !!}
        </div>
    </div>
</div>

<!-- Tercera fila: Año y Color -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('year', 'Año *') !!}
            {!! Form::number('year', null, [
                'class' => 'form-control', 
                'placeholder' => 'Ingrese el año (Ej: 2025)',
                'min' => '1900',
                'max' => date('Y') + 1,
                'required'
            ]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('color_id', 'Color *') !!}
            {!! Form::select('color_id', $colors->pluck('name', 'id'), null, [
                'class' => 'form-control',
                'required',
                'placeholder' => 'Seleccione un color',
                'style' => 'width: 100%;',
                'id' => 'color_id'
            ]) !!}
        </div>
    </div>
</div>

<!-- Cuarta fila: Marca y Modelo -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('brand_id', 'Marca *') !!}
            {!! Form::select('brand_id', $brands->pluck('name', 'id'), null, [
                'class' => 'form-control',
                'required',
                'placeholder' => 'Seleccione una marca',
                'style' => 'width: 100%;',
                'id' => 'brand_id'
            ]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('model_id', 'Modelo *') !!}
            <select name="model_id" id="model_id" class="form-control" required style="width: 100%;" 
                    data-placeholder="Seleccione un modelo">
                <option value="">Seleccione un modelo</option>
                @if(isset($vehicle) && $vehicle->brand_id)
                    @foreach(\App\Models\BrandModel::where('brand_id', $vehicle->brand_id)->get() as $model)
                        <option value="{{ $model->id }}" {{ $vehicle->model_id == $model->id ? 'selected' : '' }}>
                            {{ $model->name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>
</div>

<!-- Quinta fila: Capacidades -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('load_capacity', 'Capacidad de Carga (kg) *') !!}
            {!! Form::number('load_capacity', null, [
                'class' => 'form-control', 
                'placeholder' => 'Ingrese la capacidad de carga (Ej: 9528)',
                'step' => '0.01',
                'min' => '0',
                'required'
            ]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('fuel_capacity', 'Capacidad de Combustible (L) *') !!}
            {!! Form::number('fuel_capacity', null, [
                'class' => 'form-control', 
                'placeholder' => 'Ingrese la capacidad de combustible (Ej: 60)',
                'step' => '0.01',
                'min' => '0',
                'required'
            ]) !!}
        </div>
    </div>
</div>

<!-- Sexta fila: Más capacidades -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('compaction_capacity', 'Capacidad de Compactación (kg) *') !!}
            {!! Form::number('compaction_capacity', null, [
                'class' => 'form-control', 
                'placeholder' => 'Ingrese la capacidad de compactación (Ej: 180)',
                'step' => '0.01',
                'min' => '0',
                'required'
            ]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('people_capacity', 'Capacidad de Personas *') !!}
            {!! Form::number('people_capacity', null, [
                'class' => 'form-control', 
                'placeholder' => 'Ingrese la capacidad de personas (Ej: 3)',
                'min' => '1',
                'required'
            ]) !!}
        </div>
    </div>
</div>

<!-- Descripción -->
<div class="row">
    <div class="col-12">
        <div class="form-group">
            {!! Form::label('description', 'Descripción') !!}
            {!! Form::textarea('description', null, [
                'class' => 'form-control',
                'placeholder' => 'Ingrese la descripción',
                'rows' => 3,
            ]) !!}
        </div>
    </div>
</div>

<!-- Estado CORREGIDO -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('status', 'Estado *') !!}
            {!! Form::select('status', [
                1 => 'Activo',
                0 => 'Inactivo'
            ], isset($vehicle) ? $vehicle->status : null, [
                'class' => 'form-control',
                'required',
                'style' => 'width: 100%;',
                'id' => 'status'
            ]) !!}
        </div>
    </div>
</div>

<style>
.form-group {
    margin-bottom: 1.5rem;
}

.form-control {
    border-radius: 4px;
    border: 1px solid #d2d6de;
    padding: 8px 12px;
    font-size: 14px;
}

.form-control:focus {
    border-color: #3c8dbc;
    box-shadow: 0 0 0 0.2rem rgba(60, 141, 188, 0.25);
}

label {
    font-weight: 600;
    color: #555;
    margin-bottom: 8px;
    display: block;
}

/* Estilos para cuando el select está deshabilitado */
select:disabled {
    background-color: #f8f9fa;
    opacity: 0.7;
    cursor: not-allowed;
}
</style>

<script>
// Función para cargar modelos basados en la marca seleccionada
function loadModelsByBrand(brandId, selectedModelId = null) {
    var modelSelect = $('#model_id');
    
    console.log('Cargando modelos para marca:', brandId, 'Modelo seleccionado:', selectedModelId);
    
    if (brandId) {
        modelSelect.html('<option value="">Cargando modelos...</option>');
        modelSelect.prop('disabled', true);
        
        $.ajax({
            url: '{{ route("admin.vehicles.get-models", ":brandId") }}'.replace(':brandId', brandId),
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
                    modelSelect.html('<option value="">No hay modelos disponibles para esta marca</option>');
                }
                
                modelSelect.prop('disabled', false);
                
                // Seleccionar el modelo si se proporcionó uno
                if (selectedModelId) {
                    setTimeout(function() {
                        modelSelect.val(selectedModelId).trigger('change');
                    }, 100);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en AJAX:', error);
                modelSelect.html('<option value="">Error al cargar modelos</option>');
                modelSelect.prop('disabled', false);
            }
        });
    } else {
        modelSelect.html('<option value="">Seleccione una marca primero</option>');
        modelSelect.prop('disabled', true);
    }
}

// Cargar modelos cuando cambia la marca
$('#brand_id').change(function() {
    var brandId = $(this).val();
    loadModelsByBrand(brandId);
});

// Inicializar cuando el documento esté listo
$(document).ready(function() {
    console.log('Formulario de edición cargado');
    
    // Si estamos en edición, configurar automáticamente la marca y modelo
    @if(isset($vehicle) && $vehicle->brand_id)
        console.log('Editando vehículo - Marca:', '{{ $vehicle->brand_id }}', 'Modelo:', '{{ $vehicle->model_id }}');
        
        // Establecer la marca actual
        $('#brand_id').val('{{ $vehicle->brand_id }}');
        
        // Si ya hay modelos precargados en el select, solo habilitarlo
        // Si no, cargar los modelos via AJAX
        var modelSelect = $('#model_id');
        var hasPreloadedModels = modelSelect.find('option[value!=""]').length > 1; // Más de 1 opción (excluyendo el placeholder)
        
        if (hasPreloadedModels) {
            console.log('Modelos ya precargados, habilitando select');
            modelSelect.prop('disabled', false);
            // Ya debería estar seleccionado el modelo correcto por el blade
        } else {
            console.log('Cargando modelos via AJAX');
            // Cargar los modelos para esta marca específica
            loadModelsByBrand('{{ $vehicle->brand_id }}', '{{ $vehicle->model_id }}');
        }
    @else
        // Para crear nuevo, el modelo empieza deshabilitado
        $('#model_id').prop('disabled', true);
    @endif
    
    // También establecer otros valores que puedan necesitarse
    @if(isset($vehicle))
        // Asegurar que el estado esté seleccionado
        $('#status').val('{{ $vehicle->status }}');
        
        // Asegurar que otros selects tengan sus valores
        $('#type_id').val('{{ $vehicle->type_id }}');
        $('#color_id').val('{{ $vehicle->color_id }}');
    @endif
});

// También inicializar cuando se muestre el modal (por si acaso)
$(document).on('shown.bs.modal', '#modal', function() {
    console.log('Modal mostrado - verificando formulario de edición');
    
    @if(isset($vehicle) && $vehicle->brand_id)
        // Verificar que el modelo esté habilitado y tenga el valor correcto
        var modelSelect = $('#model_id');
        if (modelSelect.prop('disabled')) {
            console.log('Modelo aún deshabilitado, habilitando...');
            modelSelect.prop('disabled', false);
        }
        
        // Verificar que el valor del modelo esté seleccionado
        var currentModelValue = modelSelect.val();
        var expectedModelValue = '{{ $vehicle->model_id }}';
        if (currentModelValue !== expectedModelValue) {
            console.log('Corrigiendo valor del modelo:', currentModelValue, '->', expectedModelValue);
            modelSelect.val(expectedModelValue);
        }
    @endif
});
</script>