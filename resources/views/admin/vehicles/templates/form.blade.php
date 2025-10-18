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
                'class' => 'form-control select2',
                'required',
                'placeholder' => 'Seleccione un tipo',
                'style' => 'width: 100%;'
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
                'class' => 'form-control select2',
                'required',
                'placeholder' => 'Seleccione un color',
                'style' => 'width: 100%;'
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
                'class' => 'form-control select2',
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
            <select name="model_id" id="model_id" class="form-control select2" required style="width: 100%;" 
                    data-placeholder="Primero seleccione una marca" disabled>
                <option value="">Primero seleccione una marca</option>
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

<!-- Estado -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('status', 'Estado *') !!}
            {!! Form::select('status', [
                1 => 'Activo',
                0 => 'Inactivo'
            ], null, [
                'class' => 'form-control select2',
                'required',
                'style' => 'width: 100%;'
            ]) !!}
        </div>
    </div>
</div>

<style>
.select2-container .select2-selection--single {
    height: 38px !important;
    border: 1px solid #d2d6de !important;
    border-radius: 3px !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}

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
</style>

<script>
// Cargar modelos cuando cambia la marca
$('#brand_id').change(function() {
    var brandId = $(this).val();
    var modelSelect = $('#model_id');
    
    console.log('Marca cambiada:', brandId);
    
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
                modelSelect.trigger('change');
                
                // Seleccionar modelo actual en edición (solo si estamos editando)
                @if(isset($vehicle) && $vehicle->model_id)
                    // Solo seleccionar si la marca coincide
                    @if($vehicle->brand_id)
                        if (brandId == '{{ $vehicle->brand_id }}') {
                            setTimeout(function() {
                                modelSelect.val('{{ $vehicle->model_id }}').trigger('change');
                            }, 100);
                        }
                    @endif
                @endif
            },
            error: function(xhr, status, error) {
                console.error('Error en AJAX:', error);
                console.log('Respuesta del servidor:', xhr.responseText);
                modelSelect.html('<option value="">Error al cargar modelos</option>');
                modelSelect.prop('disabled', false);
            }
        });
    } else {
        modelSelect.html('<option value="">Primero seleccione una marca</option>');
        modelSelect.prop('disabled', true);
    }
});
</script>