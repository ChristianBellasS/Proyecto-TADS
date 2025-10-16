<div class="row">
    <div class="col-12">
        <div class="form-group">
            {!! Form::label('name', 'Nombre del Color') !!}
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Ej: Rojo, Azul, Verde', 'required']) !!}
        </div>
        
        <div class="form-group">
            {!! Form::label('code', 'Código del Color (RGB)') !!}
            <div class="input-group">
                {!! Form::text('code', null, [
                    'class' => 'form-control', 
                    'id' => 'colorCode',
                    'placeholder' => '#FFFFFF',
                    'required'
                ]) !!}
                <div class="input-group-append">
                    <input type="color" id="colorPicker" class="form-control" style="width: 60px; height: 38px; cursor: pointer;">
                </div>
            </div>
            <small class="form-text text-muted">Haz click en el selector de color o ingresa el código hexadecimal</small>
        </div>

        <div class="form-group">
            {!! Form::label('description', 'Descripción') !!}
            {!! Form::textarea('description', null, [
                'class' => 'form-control',
                'placeholder' => 'Agregue una descripción del color',
                'rows' => 3,
            ]) !!}
        </div>

        <!-- Vista previa del color -->
        <div class="form-group">
            <label>Vista Previa del Color:</label>
            <div id="colorPreview" 
                 style="width: 100%; 
                        height: 80px; 
                        border: 2px solid #ddd; 
                        border-radius: 8px; 
                        margin-top: 10px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: bold;
                        font-size: 16px;">
                <span id="previewText">Color seleccionado aparecerá aquí</span>
            </div>
        </div>
    </div>
</div>

<script>
// Función simple y directa para manejar los colores
function initializeColorPicker() {
    const colorPicker = document.getElementById('colorPicker');
    const colorCode = document.getElementById('colorCode');
    const colorPreview = document.getElementById('colorPreview');
    const previewText = document.getElementById('previewText');

    // Función para actualizar la vista previa
    function updatePreview() {
        let colorValue = colorCode.value;
        
        // Asegurarse de que tenga el formato correcto
        if (colorValue && !colorValue.startsWith('#')) {
            colorValue = '#' + colorValue;
            colorCode.value = colorValue;
        }
        
        // Actualizar vista previa
        if (colorValue && /^#[0-9A-F]{6}$/i.test(colorValue)) {
            colorPreview.style.backgroundColor = colorValue;
            
            // Calcular brillo para el color del texto
            const hex = colorValue.replace('#', '');
            const r = parseInt(hex.substr(0, 2), 16);
            const g = parseInt(hex.substr(2, 2), 16);
            const b = parseInt(hex.substr(4, 2), 16);
            const brightness = (r * 299 + g * 587 + b * 114) / 1000;
            
            previewText.style.color = brightness > 128 ? '#000000' : '#FFFFFF';
            previewText.textContent = colorValue.toUpperCase();
        } else {
            colorPreview.style.backgroundColor = '#f8f9fa';
            previewText.style.color = '#6c757d';
            previewText.textContent = 'Ingresa un código hexadecimal válido (ej: #FF0000)';
        }
    }

    // Cuando se selecciona un color con el picker
    colorPicker.addEventListener('input', function() {
        colorCode.value = this.value.toUpperCase();
        updatePreview();
    });

    // Cuando se escribe en el campo de texto
    colorCode.addEventListener('input', function() {
        updatePreview();
    });

    // Cuando se pierde el foco del campo de texto
    colorCode.addEventListener('blur', function() {
        let value = this.value.trim().toUpperCase();
        if (value && !value.startsWith('#')) {
            value = '#' + value;
            this.value = value;
        }
        updatePreview();
    });

    // Inicializar con valores existentes
    @if(isset($color) && $color->code)
        colorCode.value = '{{ $color->code }}'.toUpperCase();
        colorPicker.value = '{{ $color->code }}'.toLowerCase();
    @else
        // Valores por defecto
        colorCode.value = '#000000';
        colorPicker.value = '#000000';
    @endif

    // Actualizar vista previa inicial
    updatePreview();
}

// Ejecutar cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    initializeColorPicker();
});

// También ejecutar cuando el modal se muestre (por si se carga dinámicamente)
$(document).on('shown.bs.modal', '#modal', function() {
    setTimeout(function() {
        initializeColorPicker();
    }, 100);
});
</script>