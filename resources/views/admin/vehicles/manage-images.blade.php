{!! Form::open([
    'route' => ['admin.vehicles.store-images', $vehicle->id],
    'method' => 'POST',
    'files' => true,
    'id' => 'imagesForm',
]) !!}

<!-- Header con información del vehículo -->
<div class="vehicle-info-card mb-4">
    <div class="d-flex align-items-center">
        <div class="vehicle-icon mr-3">
            <i class="fas fa-car-side fa-2x text-white"></i>
        </div>
        <div class="vehicle-details">
            <h4 class="mb-2 text-white font-weight-bold">{{ $vehicle->name }}</h4>
            <div class="vehicle-meta">
                <span class="vehicle-badge badge-light mr-2">
                    <i class="fas fa-barcode mr-1"></i>{{ $vehicle->code }}
                </span>
                <span class="vehicle-badge badge-light mr-2">
                    <i class="fas fa-tag mr-1"></i>{{ $vehicle->plate }}
                </span>
                <span class="vehicle-badge badge-light">
                    <i class="fas fa-calendar mr-1"></i>{{ $vehicle->year }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Subir Nuevas Imágenes -->
<div class="upload-section mb-4">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-primary text-white py-3">
            <h5 class="mb-0 font-weight-bold">
                <i class="fas fa-cloud-upload-alt mr-2"></i>
                Agregar Nuevas Imágenes
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="upload-area" id="uploadArea">
                <div class="upload-placeholder text-center py-5">
                    <i class="fas fa-images fa-4x text-primary mb-3"></i>
                    <h4 class="text-dark font-weight-bold">Arrastra y suelta imágenes aquí</h4>
                    <p class="text-muted mb-3">o haz clic para seleccionar archivos</p>
                    <div class="upload-info">
                        <span class="badge badge-info badge-lg">Formatos: JPEG, PNG, JPG, GIF</span>
                        <span class="badge badge-warning badge-lg ml-2">Máx. 2MB por imagen</span>
                    </div>
                </div>
                <input type="file" name="images[]" id="images" class="d-none" multiple accept="image/*">
            </div>

            <!-- Vista Previa de Nuevas Imágenes -->
            <div id="imagePreview" class="preview-grid mt-4" style="display: none;">
                <h5 class="section-title mb-3">
                    <i class="fas fa-eye mr-2 text-primary"></i>Vista Previa de Nuevas Imágenes
                    <span class="badge badge-success badge-lg ml-2" id="newImagesCount">0</span>
                </h5>
                <div class="preview-container" id="previewContainer"></div>
            </div>
        </div>
    </div>
</div>

<!-- Sección de Imágenes Existentes -->
<div class="existing-images-section">
    <div class="card border-0 shadow-lg">
        <div class="card-header bg-gradient-info text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold">
                <i class="fas fa-photo-video mr-2"></i>
                Imágenes Existentes
                <span class="badge badge-light badge-lg ml-2">{{ $vehicle->vehicleImages->count() }}</span>
            </h5>
            <div class="actions">
                <span class="action-hint mr-3">
                    <i class="fas fa-crown text-warning mr-1"></i>Click en corona = Principal
                </span>
                <span class="action-hint">
                    <i class="fas fa-trash text-danger mr-1"></i>X = Eliminar
                </span>
            </div>
        </div>
        <div class="card-body p-4">
            @if ($vehicle->vehicleImages->count() > 0)
                <div class="existing-images-grid">
                    @foreach ($vehicle->vehicleImages as $image)
                        <div class="image-card-existing" data-image-id="{{ $image->id }}">
                            <div class="image-container">
                                <img src="{{ asset('storage/' . $image->image) }}"
                                    class="image-preview {{ $image->profile ? 'profile-active' : 'profile-inactive' }}"
                                    onclick="setExistingAsProfile({{ $image->id }})"
                                    alt="Imagen del vehículo {{ $vehicle->name }}">

                                <!-- Overlay de acciones -->
                                <div class="image-overlay">
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-danger btn-sm delete-btn"
                                            onclick="markForDeletion({{ $image->id }})" title="Eliminar imagen">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Badge de perfil -->
                                @if ($image->profile)
                                    <div class="profile-badge">
                                        <i class="fas fa-crown"></i>
                                        <span>Principal</span>
                                    </div>
                                @else
                                    <div class="profile-crown" onclick="setExistingAsProfile({{ $image->id }})"
                                        title="Establecer como imagen principal">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-images text-center py-5">
                    <i class="fas fa-image fa-5x text-light mb-4"></i>
                    <h4 class="text-muted font-weight-bold">No hay imágenes para este vehículo</h4>
                    <p class="text-muted">Agrega imágenes usando la sección de arriba</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Campos Ocultos -->
<input type="hidden" name="profile_image_id" id="profileImageId"
    value="{{ $vehicle->vehicleImages->where('profile', 1)->first()->id ?? '' }}">
<input type="hidden" name="images_to_delete" id="imagesToDelete" value="">

<!-- Footer -->
<div class="text-right mt-4 pt-3 border-top">
    <button type="submit" class="btn btn-success btn-md">
        <i class="fas fa-save mr-1"></i> Guardar
    </button>
    <button type="button" class="btn btn-danger btn-md" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Cancelar
    </button>
</div>
{!! Form::close() !!}

<style>
    /* ===== ESTILOS MEJORADOS ===== */
    :root {
        --primary-color: #3498db;
        --secondary-color: #2c3e50;
        --success-color: #27ae60;
        --danger-color: #e74c3c;
        --warning-color: #f39c12;
        --info-color: #17a2b8;
        --light-color: #ecf0f1;
        --dark-color: #2c3e50;
    }

    /* Tarjeta de información del vehículo MEJORADA */
    .vehicle-info-card {
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        color: white;
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        border-left: 6px solid var(--warning-color);
    }

    .vehicle-icon {
        background: rgba(255, 255, 255, 0.15);
        padding: 20px;
        border-radius: 15px;
        backdrop-filter: blur(10px);
        margin-right: 1.5rem !important;
    }

    .vehicle-badge {
        background: rgba(255, 255, 255, 0.95) !important;
        color: var(--dark-color) !important;
        font-size: 0.85rem !important;
        padding: 0.5rem 1rem !important;
        border-radius: 25px !important;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Área de upload MEJORADA */
    .upload-area {
        border: 3px dashed #bdc3c7;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.4s ease;
        background: var(--light-color);
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .upload-area:hover,
    .upload-area.drag-over {
        border-color: var(--primary-color);
        background: rgba(52, 152, 219, 0.05);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(52, 152, 219, 0.15);
    }

    .upload-placeholder h4 {
        color: var(--dark-color) !important;
    }

    .badge-lg {
        font-size: 0.9rem !important;
        padding: 0.6rem 1rem !important;
        border-radius: 20px !important;
    }

    /* Grid de preview MEJORADO */
    .preview-grid {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        border: 2px solid var(--light-color);
    }

    .section-title {
        color: var(--dark-color);
        font-weight: 700;
        border-bottom: 3px solid var(--primary-color);
        padding-bottom: 0.75rem;
    }

    .preview-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1.5rem;
    }

    .preview-item {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.4s ease;
        border: 3px solid transparent;
    }

    .preview-item:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        border-color: var(--primary-color);
    }

    .preview-image-container {
        position: relative;
        overflow: hidden;
        aspect-ratio: 1;
    }

    .preview-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.4s ease;
    }

    .preview-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.4s ease;
    }

    .preview-item:hover .preview-overlay {
        opacity: 1;
    }

    .preview-actions {
        display: flex;
        gap: 0.75rem;
    }

    .profile-indicator {
        position: absolute;
        top: 10px;
        left: 10px;
        background: linear-gradient(135deg, var(--success-color), #219653);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    }

    .preview-info {
        padding: 1rem;
        background: var(--light-color);
        border-top: 1px solid #e0e0e0;
    }

    .file-name {
        display: block;
        font-weight: 600;
        color: var(--dark-color);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .file-size {
        color: #7f8c8d;
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* Grid de imágenes existentes MEJORADO */
    .existing-images-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 2rem;
    }

    .image-card-existing {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.4s ease;
        border: 3px solid transparent;
    }

    .image-card-existing:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .image-card-existing.marked-for-deletion {
        border-color: var(--danger-color);
        opacity: 0.7;
        transform: scale(0.95);
    }

    .image-container {
        position: relative;
        overflow: hidden;
        aspect-ratio: 1;
    }

    .image-preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.4s ease;
        cursor: pointer;
    }

    .image-preview.profile-active {
        border: 4px solid var(--success-color);
    }

    .image-preview.profile-inactive {
        border: 3px solid transparent;
    }

    .image-preview.profile-inactive:hover {
        border-color: var(--warning-color);
    }

    .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.85);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: all 0.4s ease;
    }

    .image-card-existing:hover .image-overlay {
        opacity: 1;
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
    }

    .profile-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: linear-gradient(135deg, var(--warning-color), #e67e22);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        z-index: 10;
    }

    .profile-crown {
        position: absolute;
        top: 12px;
        left: 12px;
        background: rgba(255, 255, 255, 0.95);
        color: #7f8c8d;
        padding: 0.6rem;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.4s ease;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
        z-index: 10;
    }

    .profile-crown:hover {
        background: var(--warning-color);
        color: white;
        transform: scale(1.2);
        box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4);
    }

    /* Botones MEJORADOS */
    .btn-gradient {
        background: linear-gradient(135deg, var(--success-color), #219653);
        border: none;
        border-radius: 30px;
        padding: 0.875rem 2.5rem;
        font-weight: 700;
        font-size: 1.1rem;
        transition: all 0.4s ease;
        box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
    }

    .btn-gradient:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4);
    }

    .btn-outline-secondary {
        border-radius: 30px;
        padding: 0.875rem 2.5rem;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .action-hint {
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* Estados de carga */
    .btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none !important;
    }

    /* Responsive MEJORADO */
    @media (max-width: 768px) {
        .preview-container {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }

        .existing-images-grid {
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1.5rem;
        }

        .vehicle-info-card {
            padding: 1.5rem;
        }

        .actions {
            flex-direction: column;
            gap: 0.5rem;
        }

        .action-hint {
            font-size: 0.8rem;
        }
    }

    /* Animaciones MEJORADAS */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .preview-item,
    .image-card-existing {
        animation: fadeInUp 0.6s ease;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(52, 152, 219, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(52, 152, 219, 0);
        }
    }

    .upload-area.drag-over {
        animation: pulse 1.5s infinite;
    }
</style>

<script>
    // El JavaScript permanece igual que en la versión anterior
    // Solo cambian los estilos para mejorar la apariencia
    $(document).ready(function() {
        let uploadedFiles = [];
        let imagesToDelete = [];

        // ========== CONFIGURACIÓN DE DRAG & DROP ==========
        const uploadArea = $('#uploadArea');
        const fileInput = $('#images');

        // Click en área de upload
        uploadArea.on('click', function(e) {
            if (!$(e.target).closest('.preview-item').length) {
                fileInput.click();
            }
        });

        // Drag & Drop
        uploadArea.on('dragover', function(e) {
            e.preventDefault();
            uploadArea.addClass('drag-over');
        });

        uploadArea.on('dragleave', function(e) {
            e.preventDefault();
            uploadArea.removeClass('drag-over');
        });

        uploadArea.on('drop', function(e) {
            e.preventDefault();
            uploadArea.removeClass('drag-over');
            const files = e.originalEvent.dataTransfer.files;
            handleFiles(files);
        });

        // Cambio en input file
        fileInput.on('change', function(e) {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            const validFiles = Array.from(files).filter(file => {
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire('Error', `La imagen "${file.name}" excede 2MB`, 'error');
                    return false;
                }
                if (!file.type.startsWith('image/')) {
                    Swal.fire('Error', `"${file.name}" no es una imagen válida`, 'error');
                    return false;
                }
                return true;
            });

            if (validFiles.length > 0) {
                processFiles(validFiles);
            }
        }

        function processFiles(files) {
            files.forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const isFirstNewImage = uploadedFiles.length === 0;

                    uploadedFiles.unshift({
                        file: file,
                        preview: e.target.result,
                        name: file.name,
                        size: file.size,
                        isProfile: isFirstNewImage
                    });

                    updatePreview();
                    updateFileInput();
                };

                reader.readAsDataURL(file);
            });

            fileInput.val('');
        }

        // ========== ACTUALIZAR VISTA PREVIA ==========
        function updatePreview() {
            const previewContainer = $('#previewContainer');
            const imagePreviewSection = $('#imagePreview');

            previewContainer.empty();

            if (uploadedFiles.length > 0) {
                imagePreviewSection.show();
                $('#newImagesCount').text(uploadedFiles.length);

                uploadedFiles.forEach((fileData, index) => {
                    const previewItem = $(`
                    <div class="preview-item" data-index="${index}">
                        <div class="preview-image-container">
                            <img src="${fileData.preview}" class="preview-image ${fileData.isProfile ? 'profile-active' : ''}">
                            
                            <div class="preview-overlay">
                                <div class="preview-actions">
                                    <button type="button" class="btn btn-warning btn-sm profile-btn"
                                            onclick="setNewAsProfile(${index})"
                                            title="${fileData.isProfile ? 'Imagen principal' : 'Establecer como principal'}">
                                        <i class="fas fa-crown"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm remove-btn"
                                            onclick="removeNewImage(${index})"
                                            title="Quitar imagen">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            ${fileData.isProfile ? 
                                '<div class="profile-indicator"><i class="fas fa-crown"></i> Principal</div>' : 
                                ''}
                        </div>
                        <div class="preview-info">
                            <small class="file-name">${fileData.name}</small>
                            <small class="file-size">${formatFileSize(fileData.size)}</small>
                        </div>
                    </div>
                `);

                    previewContainer.prepend(previewItem);
                });
            } else {
                imagePreviewSection.hide();
            }

            updateProfileField();
        }

        // ========== FUNCIONES DE GESTIÓN ==========
        window.setExistingAsProfile = function(imageId) {
            $('#profileImageId').val(imageId);

            $('.image-card-existing').each(function() {
                const card = $(this);
                const isTarget = card.data('image-id') == imageId;

                card.find('.image-preview')
                    .toggleClass('profile-active', isTarget)
                    .toggleClass('profile-inactive', !isTarget);

                if (isTarget) {
                    card.find('.profile-crown').html(
                        '<i class="fas fa-crown"></i><span>Principal</span>').addClass(
                        'profile-badge');
                    card.find('.profile-badge').show();
                } else {
                    card.find('.profile-crown').html('<i class="fas fa-crown"></i>').removeClass(
                        'profile-badge');
                    card.find('.profile-badge').hide();
                }
            });
        }

        window.markForDeletion = function(imageId) {
            const imageCard = $(`.image-card-existing[data-image-id="${imageId}"]`);

            Swal.fire({
                title: '¿Eliminar imagen?',
                text: 'Esta imagen se eliminará al guardar los cambios',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#3498db',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (!imagesToDelete.includes(imageId)) {
                        imagesToDelete.push(imageId);

                        imageCard.addClass('marked-for-deletion');
                        imageCard.find('.delete-btn').addClass('btn-dark').html(
                            '<i class="fas fa-check"></i>');

                        $('#imagesToDelete').val(imagesToDelete.join(','));

                        if ($('#profileImageId').val() == imageId) {
                            const remainingImages = $('.image-card-existing').not(
                                '.marked-for-deletion');
                            if (remainingImages.length > 0) {
                                const newProfileId = remainingImages.first().data('image-id');
                                setExistingAsProfile(newProfileId);
                            } else {
                                $('#profileImageId').val('');
                            }
                        }

                        Swal.fire('Marcada', 'La imagen se eliminará al guardar', 'info');
                    }
                }
            });
        }

        window.setNewAsProfile = function(index) {
            $('#profileImageId').val('');

            uploadedFiles.forEach((file, i) => {
                file.isProfile = (i === index);
            });

            updatePreview();

            $('.image-card-existing').each(function() {
                $(this).find('.image-preview').removeClass('profile-active').addClass(
                    'profile-inactive');
                $(this).find('.profile-badge').hide();
                $(this).find('.profile-crown').show().html('<i class="fas fa-crown"></i>');
            });
        }

        window.removeNewImage = function(index) {
            Swal.fire({
                title: '¿Quitar imagen?',
                text: 'Esta imagen no se guardará',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#3498db',
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const wasProfile = uploadedFiles[index].isProfile;
                    uploadedFiles.splice(index, 1);

                    if (wasProfile && uploadedFiles.length > 0) {
                        uploadedFiles[0].isProfile = true;
                    }

                    updatePreview();
                    updateFileInput();

                    Swal.fire('Quitada', 'La imagen no se guardará', 'info');
                }
            });
        }

        // ========== FUNCIONES AUXILIARES ==========
        function updateFileInput() {
            const dt = new DataTransfer();
            uploadedFiles.forEach(fileData => {
                dt.items.add(fileData.file);
            });
            fileInput[0].files = dt.files;
        }

        function updateProfileField() {
            // Lógica para actualizar campo de perfil si es necesario
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // ========== INICIALIZACIÓN ==========
        @if ($vehicle->vehicleImages->where('profile', 1)->first())
            setExistingAsProfile({{ $vehicle->vehicleImages->where('profile', 1)->first()->id }});
        @elseif ($vehicle->vehicleImages->count() > 0)
            setExistingAsProfile({{ $vehicle->vehicleImages->first()->id }});
        @endif
    });

    // Validación antes de enviar
    $(document).on('submit', '#imagesForm', function(e) {
        const existingCount = $('.image-card-existing').not('.marked-for-deletion').length;
        const newCount = uploadedFiles.length;

        if (existingCount === 0 && newCount === 0) {
            e.preventDefault();
            Swal.fire('Error', 'Debe haber al menos una imagen para el vehículo', 'error');
            return false;
        }

        $('#btnSubmit').html('<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...').prop('disabled', true);
    });
</script>
