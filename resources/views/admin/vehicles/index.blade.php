@extends('adminlte::page')

@section('title', 'Vehículos')

@section('content_header')
    <button type="button" class="btn btn-success float-right" id="btnRegistrar">
        <i class="fa fa-plus"></i> Nuevo Vehículo
    </button>
    <h1>Lista de Vehículos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>Placa</th>
                            <th>Año</th>
                            <th>Capacidad</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Tipo</th>
                            <th>Color</th>
                            <th width="120px">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

<!-- Modal Principal - Versión Minimalista -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <!-- Header elegante -->
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-car mr-2 text-warning" id="modalTitle"></i>Formulario de Vehículos
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="h5 mb-0">&times;</span>
                </button>
            </div>

            <!-- Body limpio -->
            <div class="modal-body p-4" id="modalBody" style="max-height: 80vh; overflow-y: auto;">
                <!-- El contenido se cargará aquí dinámicamente -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Específico para Gestión de Imágenes -->
<div class="modal fade" id="imagesModal" tabindex="-1" role="dialog" aria-labelledby="imagesModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <!-- Header elegante -->
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #035286, #034c7c);">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-images mr-2 text-warning"></i>Gestión de Imágenes
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="h5 mb-0">&times;</span>
                </button>
            </div>

            <!-- Body limpio -->
            <div class="modal-body p-4" id="imagesModalBody" style="max-height: 80vh; overflow-y: auto;">
                <!-- El contenido de imágenes se cargará aquí -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Carrusel de Imágenes -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Imágenes del vehículo</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="vehicleCarousel" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner"></div>

                    <!-- Controles -->
                    <a class="carousel-control-prev" href="#vehicleCarousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </a>
                    <a class="carousel-control-next" href="#vehicleCarousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </a>
                </div>

                <!-- Botones para gestionar imágenes -->
                <div class="text-center mt-3">
                    <button id="btnSetProfile" class="btn btn-primary btn-lg mr-2" data-image-id="">
                        <i class="fas fa-crown"></i> Establecer como perfil
                    </button>
                    <button id="btnDeleteImage" class="btn btn-danger btn-lg" data-image-id="">
                        <i class="fas fa-trash"></i> Eliminar foto
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicialización de DataTable
            $('#table').DataTable({
                "ajax": "{{ route('admin.vehicles.index') }}",
                "columns": [{
                        "data": "images",
                        "orderable": false,
                        "searchable": false,
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "code"
                    },
                    {
                        "data": "plate"
                    },
                    {
                        "data": "year"
                    },
                    {
                        "data": "load_capacity"
                    },
                    {
                        "data": "brand"
                    },
                    {
                        "data": "model"
                    },
                    {
                        "data": "type"
                    },
                    {
                        "data": "color",
                        "orderable": false,
                        "searchable": false,
                    },
                    {
                        "data": "actions",
                        "orderable": false,
                        "searchable": false,
                    }
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                }
            });

            // Eliminar vehículo con confirmación
            $(document).on('click', '.frmDelete', function(event) {
                var form = $(this);
                event.preventDefault();
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡Se eliminará el vehículo y todas sus imágenes!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminarlo!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: form.serialize(),
                            success: function(response) {
                                refreshTable();
                                Swal.fire(
                                    '¡Eliminado!',
                                    'El vehículo ha sido eliminado.',
                                    'success'
                                );
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error',
                                    'Hubo un problema al eliminar el vehículo.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Abrir modal para crear un nuevo vehículo
            $('#btnRegistrar').click(function() {
                $.ajax({
                    url: "{{ route('admin.vehicles.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modal .modal-body').html(response);
                        $('#modal .modal-title').html("Nuevo Vehículo");
                        $('#modal').modal('show');

                        // Manejar el envío del formulario de creación
                        $('#modal form').on('submit', function(e) {
                            e.preventDefault();
                            submitForm($(this));
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr, status, error);
                    }
                });
            });

            // Abrir modal para editar un vehículo existente
            $(document).on('click', '.btnEditar', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/vehicles') }}/" + id + "/edit",
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modal .modal-title').html("Editar Vehículo");
                        $('#modal').modal('show');

                        // Manejar el envío del formulario de edición
                        $('#modal form').on('submit', function(e) {
                            e.preventDefault();
                            submitForm($(this));
                        });
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                        Swal.fire('Error', 'No se pudo cargar el formulario de edición',
                            'error');
                    }
                });
            });

            // Abrir modal para gestionar imágenes
            $(document).on('click', '.btnImages', function() {
                var id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/vehicles') }}/" + id + "/manage-images",
                    type: 'GET',
                    success: function(response) {
                        $('#imagesModalBody').html(response);
                        $('#modalTitle').text('Gestionar Imágenes del Vehículo');
                        $('#imagesModal').modal('show');

                        // Manejar el envío del formulario de imágenes
                        $('#modal form').on('submit', function(e) {
                            e.preventDefault();
                            submitImageForm($(this));
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo cargar el gestor de imágenes', 'error');
                    }
                });
            });

            // Función para enviar formularios de vehículos
            function submitForm(form) {
                var formData = new FormData(form[0]);
                var url = form.attr('action');
                var method = form.attr('method');

                // Mostrar loading
                form.find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Guardando...').prop(
                    'disabled', true);

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#modal').modal('hide');
                        refreshTable();
                        Swal.fire({
                            title: "¡Éxito!",
                            text: response.message,
                            icon: "success",
                            timer: 3000,
                            showConfirmButton: true
                        });
                    },
                    error: function(xhr) {
                        var errorMessage = 'Error en el proceso';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        // Restaurar botón
                        form.find('button[type="submit"]').html('<i class="fas fa-save"></i> Guardar')
                            .prop('disabled', false);

                        Swal.fire({
                            title: "Error!",
                            text: errorMessage,
                            icon: "error",
                            showConfirmButton: true
                        });
                    }
                });
            }

            // Función para enviar formularios de imágenes
            // Función para enviar formularios de imágenes
            $(document).on('submit', '#imagesForm', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = new FormData(this);
                var url = $(this).attr('action');

                // Mostrar loading en el botón
                form.find('button[type="submit"]').html(
                    '<i class="fas fa-spinner fa-spin"></i> Guardando...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#imagesModal').modal('hide');
                        refreshTable();
                        Swal.fire({
                            title: "¡Éxito!",
                            text: response.message,
                            icon: "success",
                            timer: 3000,
                            showConfirmButton: true
                        });
                    },
                    error: function(xhr) {
                        var errorMessage = 'Error al guardar las imágenes';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        // Restaurar botón
                        form.find('button[type="submit"]').html(
                            '<i class="fas fa-save mr-1"></i> Guardar').prop('disabled',
                            false);

                        Swal.fire({
                            title: "Error!",
                            text: errorMessage,
                            icon: "error",
                            showConfirmButton: true
                        });
                    }
                });
            });

            // Función para refrescar la tabla
            function refreshTable() {
                var table = $('#table').DataTable();
                table.ajax.reload(null, false);
            }

            // 🔹 CARRUSEL DE IMÁGENES
            let currentImages = [];

            // CORRECCIÓN EN EL CARRUSEL DE IMÁGENES
            $(document).on('click', '.vehicle-image-preview', function() {
                const vehicleId = $(this).data('vehicle');

                $.ajax({
                    url: "/admin/vehicles/images-by-vehicle/" + vehicleId,
                    type: 'GET',
                    success: function(response) {
                        currentImages = response.images;
                        const carouselInner = $('#vehicleCarousel .carousel-inner');
                        carouselInner.empty();

                        if (!response.images || response.images.length === 0) {
                            carouselInner.append(`
                                <div class="carousel-item active text-center p-5">
                                    <h5>No hay imágenes para este vehículo</h5>
                                </div>
                            `);
                            $('#btnSetProfile').hide();
                            $('#btnDeleteImage').hide();
                        } else {
                            response.images.forEach((img, index) => {
                                const activeClass = index === 0 ? 'active' : '';
                                const crownIcon = img.is_profile ?
                                    '<div class="position-absolute" style="top: 15px; right: 15px;"><i class="fas fa-crown text-warning" style="font-size: 30px;"></i></div>' :
                                    '';

                                carouselInner.append(`
                                    <div class="carousel-item ${activeClass}" data-image-id="${img.id}">
                                        <div class="position-relative">
                                            ${crownIcon}
                                            <img src="${img.url}" class="d-block w-100 rounded shadow" alt="Imagen del vehículo" style="max-height: 500px; object-fit: contain;">
                                        </div>
                                    </div>
                                `);
                            });

                            updateProfileButton(0);
                            $('#btnSetProfile').show();
                            $('#btnDeleteImage').show();
                        }

                        $('#imageModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Error al cargar imágenes:', xhr);
                        Swal.fire('Error', 'No se pudieron cargar las imágenes del vehículo',
                            'error');
                    }
                });
            });

            // Actualizar el ID de los botones cuando cambia la imagen del carrusel
            $('#vehicleCarousel').on('slid.bs.carousel', function() {
                const activeIndex = $('#vehicleCarousel .carousel-item.active').index();
                updateProfileButton(activeIndex);
            });

            // Función para actualizar los botones con el ID de la imagen actual
            function updateProfileButton(index) {
                if (currentImages[index]) {
                    const imageId = currentImages[index].id;
                    const isProfile = currentImages[index].is_profile;

                    $('#btnSetProfile').attr('data-image-id', imageId);
                    $('#btnDeleteImage').attr('data-image-id', imageId);

                    if (isProfile) {
                        $('#btnSetProfile')
                            .removeClass('btn-primary')
                            .addClass('btn-success')
                            .html('<i class="fas fa-crown"></i> Esta es la imagen de perfil')
                            .prop('disabled', true);
                    } else {
                        $('#btnSetProfile')
                            .removeClass('btn-success')
                            .addClass('btn-primary')
                            .html('<i class="fas fa-crown"></i> Establecer como perfil')
                            .prop('disabled', false);
                    }
                }
            }

            // Establecer imagen como perfil
            $(document).on('click', '#btnSetProfile', function() {
                const imageId = $(this).attr('data-image-id');

                if (!imageId) {
                    Swal.fire('Error', 'No se pudo identificar la imagen', 'error');
                    return;
                }

                $.ajax({
                    url: "/admin/vehicles/set-profile/" + imageId,
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        Swal.fire('Éxito!', response.message, 'success');

                        // Actualizar el estado local
                        currentImages.forEach(img => {
                            img.is_profile = (img.id == imageId);
                        });

                        // Actualizar visualmente
                        const activeIndex = $('#vehicleCarousel .carousel-item.active').index();
                        updateProfileButton(activeIndex);

                        // Actualizar coronas en el carrusel
                        $('#vehicleCarousel .carousel-item').each(function(index) {
                            $(this).find('.fa-crown').remove();
                            if (currentImages[index].is_profile) {
                                $(this).find('.position-relative').prepend(
                                    '<div class="position-absolute" style="top: 15px; right: 15px;"><i class="fas fa-crown text-warning" style="font-size: 30px;"></i></div>'
                                );
                            }
                        });

                        // Refrescar la tabla
                        refreshTable();
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        Swal.fire('Error', 'No se pudo establecer la imagen como perfil',
                            'error');
                    }
                });
            });

            // Eliminar imagen desde el carrusel
            $(document).on('click', '#btnDeleteImage', function() {
                const imageId = $(this).attr('data-image-id');

                if (!imageId) {
                    Swal.fire('Error', 'No se pudo identificar la imagen', 'error');
                    return;
                }

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡Esta acción no se puede deshacer!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/admin/vehicles/delete-image/" + imageId,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                Swal.fire('Eliminado!', response.message, 'success');

                                // Encontrar el índice de la imagen eliminada
                                const activeIndex = $(
                                        '#vehicleCarousel .carousel-item.active')
                                    .index();

                                // Eliminar la imagen del array local
                                currentImages.splice(activeIndex, 1);

                                if (currentImages.length === 0) {
                                    // Si no quedan imágenes, cerrar el modal
                                    $('#imageModal').modal('hide');
                                } else {
                                    // Reconstruir el carrusel sin la imagen eliminada
                                    const carouselInner = $(
                                        '#vehicleCarousel .carousel-inner');
                                    carouselInner.empty();

                                    currentImages.forEach((img, index) => {
                                        const activeClass = index === 0 ?
                                            'active' : '';
                                        const crownIcon = img.is_profile ?
                                            '<div class="position-absolute" style="top: 15px; right: 15px;"><i class="fas fa-crown text-warning" style="font-size: 30px;"></i></div>' :
                                            '';

                                        carouselInner.append(`
                                            <div class="carousel-item ${activeClass}" data-image-id="${img.id}">
                                                <div class="position-relative">
                                                    ${crownIcon}
                                                    <img src="${img.url}" class="d-block w-100 rounded shadow" alt="Imagen del vehículo" style="max-height: 500px; object-fit: contain;">
                                                </div>
                                            </div>
                                        `);
                                    });

                                    // Actualizar botones con la nueva imagen activa
                                    updateProfileButton(0);
                                }

                                // Refrescar la tabla
                                refreshTable();
                            },
                            error: function(xhr) {
                                console.error('Error:', xhr);
                                Swal.fire('Error', 'No se pudo eliminar la imagen',
                                    'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
    <style>
        .nav-sidebar .nav-treeview {
            margin-left: 20px;
        }

        .nav-sidebar .nav-treeview>.nav-item {
            margin-left: 10px;
        }
    </style>
@stop
