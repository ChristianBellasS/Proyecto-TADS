<div class="row">
    <div class="col-12">
        <div class="form-group">
            {!! Form::label('name', 'Nombre del Modelo *') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Ej: Corolla, Civic, Focus', 'required']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('code', 'Código del Modelo *') !!}
            {!! Form::text('code', null, ['class' => 'form-control', 'placeholder' => 'Ej: COR-2023, CIV-2024', 'required']) !!}
            <small class="form-text text-muted">Código único para identificar el modelo</small>
        </div>

        <div class="form-group">
            {!! Form::label('brand_id', 'Marca *') !!}
            {!! Form::select('brand_id', $brands->pluck('name', 'id'), null, [
                'class' => 'form-control',
                'required',
                'placeholder' => 'Seleccione una marca'
            ]) !!}
        </div>

        <div class="form-group">
            {!! Form::label('description', 'Descripción') !!}
            {!! Form::textarea('description', null, [
                'class' => 'form-control',
                'placeholder' => 'Agregue una descripción del modelo...',
                'rows' => 3,
            ]) !!}
        </div>
    </div>
</div>

<script>
// Convertir código a mayúsculas automáticamente
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.querySelector('input[name="code"]');
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Si estamos editando, asegurar que el código esté en mayúsculas
        if (codeInput.value) {
            codeInput.value = codeInput.value.toUpperCase();
        }
    }
});
</script>