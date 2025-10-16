{!! Form::open(['route' => 'admin.vehicleimages.store', 'files' => true, 'id' => 'createForm']) !!}

<div class="form-group">
    {!! Form::label('vehicle_id', 'Vehículo *') !!}
    <select name="vehicle_id" id="vehicle_id" class="form-control" required>
        <option value="">Seleccione un vehículo</option>
        @foreach($vehicles as $vehicle)
            <option value="{{ $vehicle->id }}">{{ $vehicle->name ?? 'Vehículo #' . $vehicle->id }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    {!! Form::label('images', 'Imágenes *') !!}
    <div class="custom-file">
        <input type="file" name="images[]" id="images" class="custom-file-input" multiple accept="image/*" required>
        <label class="custom-file-label" for="images">Seleccione una o más imágenes</label>
    </div>
    <small class="form-text text-muted">Formatos permitidos: JPEG, PNG, JPG, GIF. Máx. 2MB por imagen.</small>
</div>

<!-- Vista previa de imágenes -->
<div class="form-group">
    <label>Vista previa:</label>
    <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2" style="min-height: 100px;"></div>
</div>

<!-- Campo oculto para la imagen de perfil -->
<input type="hidden" name="profile_image_index" id="profileImageIndex" value="0">

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> Haz clic en la corona de cualquier imagen para establecerla como imagen de perfil.
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">
        <i class="fas fa-times"></i> Cancelar
    </button>
    <button type="submit" class="btn btn-success" id="btnSubmit">
        <i class="fas fa-save"></i> Guardar Imágenes
    </button>
</div>
{!! Form::close() !!}

<script>
$(document).ready(function() {
    let uploadedFiles = [];

    // Vista previa de imágenes - ACUMULA en lugar de reemplazar
    $('#images').on('change', function() {
        const newFiles = Array.from(this.files);
        
        newFiles.forEach((file) => {
            // Verificar si el archivo ya existe para evitar duplicados
            const isDuplicate = uploadedFiles.some(existingFile => 
                existingFile.name === file.name && existingFile.size === file.size
            );
            
            if (!isDuplicate) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Si es el primer archivo, establecer como perfil
                    const isFirstFile = uploadedFiles.length === 0;
                    
                    uploadedFiles.push({
                        file: file,
                        preview: e.target.result,
                        name: file.name,
                        size: file.size,
                        isProfile: isFirstFile
                    });

                    updatePreview();
                    updateFileInput();
                }
                
                reader.readAsDataURL(file);
            }
        });
        
        // Limpiar el input file para permitir nuevas selecciones
        $(this).val('');
    });

    // Actualizar vista previa
    function updatePreview() {
        const preview = $('#imagePreview');
        preview.empty();
        
        uploadedFiles.forEach((fileData, index) => {
            const previewItem = $(`
                <div class="image-preview-item position-relative" style="width: 120px; height: 120px; margin: 5px;" data-index="${index}">
    <img src="${fileData.preview}" class="img-thumbnail w-100 h-100 ${fileData.isProfile ? 'border-success border-3' : ''}" 
         style="object-fit: cover; cursor: pointer;">
    
    <!-- Corona para imagen de perfil - DENTRO de la imagen -->
    <div class="crown-button position-absolute ${fileData.isProfile ? 'text-warning' : 'text-secondary'}" 
         style="bottom: 8px; left: 8px; cursor: pointer; font-size: 18px; z-index: 5;"
         onclick="setAsProfile(${index})">
        <i class="fas fa-crown"></i>
    </div>
    
    <!-- Botón eliminar - DENTRO de la imagen -->
    <button type="button" class="btn btn-danger btn-sm position-absolute remove-image" 
            style="top: 3px; right: 3px; width: 22px; height: 22px; border-radius: 50%; padding: 0; z-index: 5;"
            onclick="removeImage(${index})">
        <i class="fas fa-times" style="font-size: 10px;"></i>
    </button>
</div>
            `);
            
            preview.append(previewItem);
        });

        updateProfileField();
        updateFileLabel();
    }

    // Establecer imagen como perfil
    window.setAsProfile = function(index) {
        uploadedFiles.forEach((file, i) => {
            file.isProfile = (i === index);
        });
        updatePreview();
    }

    // Eliminar imagen
    window.removeImage = function(index) {
        const wasProfile = uploadedFiles[index].isProfile;
        
        uploadedFiles.splice(index, 1);
        
        // Si eliminamos la imagen de perfil, establecer la primera como perfil
        if (wasProfile && uploadedFiles.length > 0) {
            uploadedFiles[0].isProfile = true;
        }
        
        updatePreview();
        updateFileInput();
    }

    // Actualizar el input file con TODOS los archivos acumulados
    function updateFileInput() {
        const dt = new DataTransfer();
        
        uploadedFiles.forEach(fileData => {
            dt.items.add(fileData.file);
        });
        
        $('#images')[0].files = dt.files;
    }

    // Actualizar campo oculto con el índice de la imagen de perfil
    function updateProfileField() {
        const profileIndex = uploadedFiles.findIndex(file => file.isProfile);
        $('#profileImageIndex').val(profileIndex);
    }

    // Actualizar label del file input
    function updateFileLabel() {
        $('.custom-file-label').text(uploadedFiles.length + ' archivo(s) seleccionado(s)');
    }

    // Validación del formulario
    $('#createForm').on('submit', function(e) {
        if (uploadedFiles.length === 0) {
            e.preventDefault();
            Swal.fire('Error', 'Debe seleccionar al menos una imagen', 'error');
            return false;
        }
        
        // Asegurarse de que hay una imagen de perfil
        const hasProfile = uploadedFiles.some(file => file.isProfile);
        if (!hasProfile && uploadedFiles.length > 0) {
            uploadedFiles[0].isProfile = true;
            updateProfileField();
        }
    });
});
</script>

<style>
.image-preview-item {
    transition: transform 0.2s;
    margin: 5px;
}
.image-preview-item:hover {
    transform: scale(1.02);
}
.crown-button:hover {
    transform: scale(1.2);
    transition: transform 0.2s;
}
.remove-image {
    opacity: 0.8;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #dc3545;
}
.remove-image:hover {
    opacity: 1;
    background: #dc3545;
}
</style>