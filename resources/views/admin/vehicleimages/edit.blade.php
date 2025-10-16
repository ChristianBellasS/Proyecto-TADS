{!! Form::model($vehicle, ['route' => ['admin.vehicleimages.update', $vehicle->id], 'method' => 'PUT', 'id' => 'editForm', 'files' => true]) !!}

<div class="form-group">
    {!! Form::label('vehicle_id', 'Vehículo') !!}
    <input type="text" class="form-control" value="{{ $vehicle->name ?? 'Vehículo #' . $vehicle->id }}" readonly>
    <small class="form-text text-muted">No se puede cambiar el vehículo para mantener la integridad de los datos.</small>
</div>

<!-- Imágenes existentes y nuevas -->
<div class="form-group">
    <label>Imágenes del vehículo ({{ $vehicle->vehicleImages->count() }} existentes):</label>
    
    @if($vehicle->vehicleImages->count() > 0)
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            Haz clic en la corona de cualquier imagen para establecerla como imagen de perfil.
        </div>
    @endif
    
    <div id="allImagesContainer" class="d-flex flex-wrap gap-2 mt-3">
        <!-- Imágenes existentes -->
        @foreach($vehicle->vehicleImages as $vehicleImage)
            <div class="image-item position-relative text-center" data-image-id="{{ $vehicleImage->id }}" data-type="existing" style="width: 100px;">
                <img src="{{ asset('storage/' . $vehicleImage->image) }}" 
                     class="img-thumbnail {{ $vehicleImage->profile ? 'border-success border-3' : '' }}" 
                     style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;">
                
                <!-- Corona para imagen de perfil -->
                <div class="crown-button position-absolute {{ $vehicleImage->profile ? 'text-warning' : 'text-secondary' }}" 
                     style="bottom: 5px; left: 5px; cursor: pointer; font-size: 16px; z-index: 5;"
                     onclick="setExistingAsProfile({{ $vehicleImage->id }})">
                    <i class="fas fa-crown"></i>
                </div>
                
                <!-- Botón eliminar (solo marca para eliminar al guardar) -->
                <button type="button" class="btn btn-danger btn-sm position-absolute remove-existing-image" 
                        style="top: 2px; right: 2px; width: 20px; height: 20px; border-radius: 50%; padding: 0; z-index: 5; background: rgba(255, 255, 255, 0.9); border: 1px solid #dc3545;"
                        data-image-id="{{ $vehicleImage->id }}"
                        onclick="markForDeletion({{ $vehicleImage->id }})"
                        title="Marcar para eliminar al guardar">
                    <i class="fas fa-times" style="font-size: 10px; color: #dc3545;"></i>
                </button>
                
                <div class="mt-1">
                    <small class="text-muted">Existente</small>
                </div>
            </div>
        @endforeach
        
        <!-- Aquí se agregarán las nuevas imágenes -->
        <div id="newImagesContainer" class="d-flex flex-wrap gap-2"></div>
        
        @if($vehicle->vehicleImages->isEmpty())
            <div class="text-center py-4 w-100" id="noImagesMessage">
                <i class="fas fa-image fa-3x text-muted mb-2"></i>
                <p class="text-muted">No hay imágenes para este vehículo</p>
                <small class="text-info">Se usará "no_logo.png" automáticamente</small>
            </div>
        @endif
    </div>
</div>

<!-- Agregar nuevas imágenes -->
<div class="form-group">
    <label>Agregar nuevas imágenes:</label>
    <div class="custom-file">
        <input type="file" name="new_images[]" id="new_images" class="custom-file-input" multiple accept="image/*">
        <label class="custom-file-label" for="new_images">Seleccione imágenes adicionales</label>
    </div>
    <small class="form-text text-muted">Formatos permitidos: JPEG, PNG, JPG, GIF. Máx. 2MB por imagen.</small>
</div>

<!-- Campos ocultos -->
<input type="hidden" name="profile_image_id" id="profileImageId" value="{{ $vehicle->vehicleImages->where('profile', 1)->first()->id ?? '' }}">
<input type="hidden" name="images_to_delete" id="imagesToDelete" value="">

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancelar
    </button>
    <button type="submit" class="btn btn-success">
        <i class="fas fa-save"></i> Guardar Cambios
    </button>
</div>
{!! Form::close() !!}

<script>
// Función para inicializar el estado del modal - CORREGIDA
function initializeEditModalState() {
    console.log('🔄 Inicializando estado del modal EDIT');
    
    // Resetear arrays
    window.newUploadedFiles = [];
    window.imagesToDelete = [];
    
    // Limpiar vista previa
    $('#newImagesContainer').empty();
    
    // Resetear campos
    $('#imagesToDelete').val('');
    $('.custom-file-label').text('Seleccione imágenes adicionales');
    
    // Resetear selección de eliminación
    $('.image-item').removeClass('to-be-deleted');
    $('.image-item img').removeClass('opacity-50');
    
    // Resetear input file
    $('#new_images').val('');
    
    console.log('✅ Estado del modal inicializado correctamente');
}

// Establecer imagen existente como perfil
function setExistingAsProfile(imageId) {
    $('#profileImageId').val(imageId);
    
    // Actualizar visualmente todas las imágenes existentes
    $('.image-item[data-type="existing"]').each(function() {
        const item = $(this);
        const crown = item.find('.crown-button');
        const img = item.find('img');
        
        if (item.data('image-id') == imageId) {
            crown.removeClass('text-secondary').addClass('text-warning');
            img.addClass('border-success border-3');
        } else {
            crown.removeClass('text-warning').addClass('text-secondary');
            img.removeClass('border-success border-3');
        }
    });
    
    // Quitar selección de nuevas imágenes
    $('.image-item[data-type="new"]').each(function() {
        const crown = $(this).find('.crown-button');
        const img = $(this).find('img');
        crown.removeClass('text-warning').addClass('text-secondary');
        img.removeClass('border-success border-3');
    });
}

// Marcar imagen existente para eliminación
function markForDeletion(imageId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta imagen se eliminará al guardar los cambios',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, marcar para eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            if (!window.imagesToDelete.includes(imageId)) {
                window.imagesToDelete.push(imageId);
                
                // Marcar visualmente la imagen para eliminación
                $(`.image-item[data-image-id="${imageId}"]`).addClass('to-be-deleted');
                $(`.image-item[data-image-id="${imageId}"] img`).addClass('opacity-50');
                
                // Actualizar campo oculto
                $('#imagesToDelete').val(window.imagesToDelete.join(','));
                
                Swal.fire('Marcada', 'La imagen se eliminará al guardar los cambios', 'info');
            }
        }
    });
}

// Vista previa de nuevas imágenes - CORREGIDA
$(document).on('change', '#new_images', function() {
    const newFiles = Array.from(this.files);
    console.log('📁 Archivos seleccionados:', newFiles.length);
    
    if (newFiles.length > 0) {
        // Ocultar mensaje de no imágenes
        $('#noImagesMessage').hide();
    }
    
    let filesProcessed = 0;
    const validFiles = [];
    
    newFiles.forEach((file) => {
        // Verificar si el archivo ya existe para evitar duplicados
        const isDuplicate = window.newUploadedFiles?.some(existingFile => 
            existingFile.name === file.name && existingFile.size === file.size
        );
        
        if (!isDuplicate && file.size <= 2 * 1024 * 1024) {
            validFiles.push(file);
        } else if (file.size > 2 * 1024 * 1024) {
            Swal.fire('Error', `La imagen "${file.name}" excede el tamaño máximo de 2MB`, 'error');
        }
    });
    
    if (validFiles.length === 0) return;
    
    validFiles.forEach((file) => {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            console.log('✅ Imagen cargada:', file.name);
            
            // Asegurarse de que el array existe
            if (!window.newUploadedFiles) {
                window.newUploadedFiles = [];
            }
            
            // Agregar al array de nuevas imágenes
            window.newUploadedFiles.push({
                file: file,
                preview: e.target.result,
                name: file.name,
                size: file.size,
                isProfile: false
            });
            
            filesProcessed++;
            
            // Si es el último archivo, actualizar la vista
            if (filesProcessed === validFiles.length) {
                console.log('🎨 Actualizando vista previa con', window.newUploadedFiles.length, 'imágenes');
                updateNewImagesPreview();
                updateNewFileInput();
                updateFileLabel();
            }
        };
        
        reader.onerror = function(e) {
            console.error('❌ Error al leer archivo:', file.name, e);
            filesProcessed++;
        };
        
        reader.readAsDataURL(file);
    });
    
    // Limpiar el input file para permitir nuevas selecciones
    $(this).val('');
});

// Actualizar vista previa de nuevas imágenes - CORREGIDA
function updateNewImagesPreview() {
    const container = $('#newImagesContainer');
    container.empty();
    
    if (!window.newUploadedFiles || window.newUploadedFiles.length === 0) {
        console.log('ℹ️ No hay imágenes nuevas para mostrar');
        return;
    }
    
    console.log('🖼️ Mostrando', window.newUploadedFiles.length, 'imágenes nuevas');
    
    window.newUploadedFiles.forEach((fileData, index) => {
        const previewItem = $(`
            <div class="image-item position-relative text-center" data-index="${index}" data-type="new" style="width: 100px;">
                <img src="${fileData.preview}" class="img-thumbnail w-100 h-100 ${fileData.isProfile ? 'border-success border-3' : ''}" 
                     style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;"
                     onerror="console.error('❌ Error cargando imagen:', this.src)">
                
                <!-- Corona para imagen de perfil -->
                <div class="crown-button position-absolute ${fileData.isProfile ? 'text-warning' : 'text-secondary'}" 
                     style="bottom: 5px; left: 5px; cursor: pointer; font-size: 16px; z-index: 5;"
                     onclick="setNewAsProfile(${index})">
                    <i class="fas fa-crown"></i>
                </div>
                
                <!-- Botón eliminar - NUEVAS IMÁGENES -->
                <button type="button" class="btn btn-danger btn-sm position-absolute remove-new-image" 
                        style="top: 2px; right: 2px; width: 20px; height: 20px; border-radius: 50%; padding: 0; z-index: 5; background: rgba(255, 255, 255, 0.9); border: 1px solid #dc3545;"
                        onclick="removeNewImage(${index})"
                        title="Quitar de la vista previa">
                    <i class="fas fa-times" style="font-size: 10px; color: #dc3545;"></i>
                </button>
                
                <div class="mt-1">
                    <small class="text-muted">Nueva</small>
                </div>
            </div>
        `);
        
        container.append(previewItem);
    });

    updateFileLabel();
}

// Establecer nueva imagen como perfil
function setNewAsProfile(index) {
    // Limpiar selección de imagen de perfil existente
    $('#profileImageId').val('');
    
    // Actualizar estado en el array
    window.newUploadedFiles.forEach((file, i) => {
        file.isProfile = (i === index);
    });
    
    // Actualizar visualmente
    updateNewImagesPreview();
    
    // También quitar selección de imágenes existentes
    $('.image-item[data-type="existing"]').each(function() {
        const crown = $(this).find('.crown-button');
        const img = $(this).find('img');
        crown.removeClass('text-warning').addClass('text-secondary');
        img.removeClass('border-success border-3');
    });
}

// Eliminar nueva imagen (solo de la vista previa)
function removeNewImage(index) {
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta imagen se quitará de la vista previa y no se guardará',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, quitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Eliminar del array
            window.newUploadedFiles.splice(index, 1);
            
            // Actualizar la vista
            updateNewImagesPreview();
            updateNewFileInput();
            updateFileLabel();
            
            // Mostrar mensaje si no quedan imágenes
            const remainingExisting = $('.image-item[data-type="existing"]').not('.to-be-deleted').length;
            const remainingNew = window.newUploadedFiles ? window.newUploadedFiles.length : 0;
            
            if (remainingExisting === 0 && remainingNew === 0) {
                $('#noImagesMessage').show();
            }
            
            Swal.fire('Quitada', 'La imagen no se guardará', 'info');
        }
    });
}

// Actualizar input file para nuevas imágenes
function updateNewFileInput() {
    const dt = new DataTransfer();
    
    if (window.newUploadedFiles) {
        window.newUploadedFiles.forEach(fileData => {
            dt.items.add(fileData.file);
        });
    }
    
    $('#new_images')[0].files = dt.files;
}

// Actualizar label del file input
function updateFileLabel() {
    const count = window.newUploadedFiles ? window.newUploadedFiles.length : 0;
    const label = count > 0 ? 
        count + ' nueva(s) imagen(es) seleccionada(s)' : 
        'Seleccione imágenes adicionales';
    $('.custom-file-label').text(label);
}

// Mostrar botones eliminar al hacer hover
$(document).on('mouseenter', '.image-item', function() {
    $(this).find('.remove-existing-image, .remove-new-image').show();
}).on('mouseleave', '.image-item', function() {
    $(this).find('.remove-existing-image, .remove-new-image').hide();
});

// INICIALIZACIÓN CORREGIDA - Se ejecuta cada vez que se carga el contenido del modal
$(document).ready(function() {
    console.log('🚀 Documento listo, inicializando EDIT modal');
    
    // Inicializar estado
    initializeEditModalState();
    
    // Ocultar botones eliminar inicialmente
    $('.remove-existing-image, .remove-new-image').hide();
    
    // Establecer la primera imagen como perfil por defecto si no hay ninguna seleccionada
    @if($vehicle->vehicleImages->count() > 0 && !$vehicle->vehicleImages->where('profile', 1)->first())
        const firstImageId = {{ $vehicle->vehicleImages->first()->id }};
        setExistingAsProfile(firstImageId);
    @endif
});

// También inicializar cuando el modal se muestra (por si se carga via AJAX)
$(document).on('shown.bs.modal', '#modal', function() {
    console.log('🔍 Modal mostrado, reinicializando estado');
    initializeEditModalState();
});

// Cuando se cierra el modal, no hacer nada (mantener estado para debugging)
$(document).on('hidden.bs.modal', '#modal', function() {
    console.log('📌 Modal cerrado, estado mantenido para debugging');
});

// Cuando se guarda exitosamente, limpiar el estado
$(document).on('ajax:success', '#editForm', function() {
    console.log('✅ Guardado exitoso, limpiando estado');
    initializeEditModalState();
});

// Validación antes de guardar
$('#editForm').on('submit', function(e) {
    const remainingExisting = $('.image-item[data-type="existing"]').not('.to-be-deleted').length;
    const remainingNew = window.newUploadedFiles ? window.newUploadedFiles.length : 0;
    
    if (remainingExisting === 0 && remainingNew === 0) {
        e.preventDefault();
        Swal.fire('Error', 'Debe haber al menos una imagen para el vehículo', 'error');
        return false;
    }
    
    console.log('📤 Enviando formulario con:', {
        existing: remainingExisting,
        new: remainingNew,
        toDelete: window.imagesToDelete ? window.imagesToDelete.length : 0
    });
});
</script>

<style>
.image-item {
    transition: all 0.3s ease;
    margin: 2px;
}
.image-item:hover {
    transform: translateY(-2px);
}
.crown-button:hover {
    transform: scale(1.2);
    transition: transform 0.2s;
}
.remove-existing-image, .remove-new-image {
    opacity: 0.8;
    display: none;
}
.remove-existing-image:hover, .remove-new-image:hover {
    opacity: 1;
}
.to-be-deleted {
    border: 2px dashed #dc3545 !important;
}
.opacity-50 {
    opacity: 0.5 !important;
}
/* Asegurar que todas las imágenes tengan el mismo tamaño */
.image-item img {
    width: 100px !important;
    height: 100px !important;
    object-fit: cover !important;
}
</style>