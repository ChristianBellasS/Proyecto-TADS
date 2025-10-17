@extends('adminlte::page')

@section('title', 'Imágenes de Vehículos')

@section('content_header')
    <button type="button" class="btn btn-success float-right" id="btnRegistrar">
        <i class="fa fa-plus"></i> Nueva Imagen
    </button>
    <h1>Listado de Imágenes por Vehículo</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table">
                    <thead>
                        <tr>
                            <th>Imagen Perfil</th>
                            <th>Vehículo</th>
                            <th>ID Vehículo</th>
                            <th>Cantidad Imágenes</th>
                            <th>Creado</th>
                            <th>Actualizado</th>
                            <th width="100px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

<!-- Modal -->
<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Formulario de Imágenes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>
</div>

<!-- Modal con carrusel -->
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

        <!-- 🔹 Botones para establecer como perfil y eliminar -->
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

            // Inicialización DataTable
            $('#table').DataTable({
                "ajax": "{{ route('admin.vehicleimages.index') }}",
                "columns": [
                    { "data": "image", "orderable": false, "searchable": false },
                    { "data": "vehicle_name" },
                    { "data": "vehicle_id" },
                    { "data": "images_count", "orderable": false, "searchable": false },
                    { "data": "created_at" },
                    { "data": "updated_at" },
                    { "data": "actions", "orderable": false, "searchable": false }
                ],
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
                }
            });

            // Eliminar TODAS las imágenes del vehículo
            $(document).on('click', '.btnEliminar', function() {
                const vehicleId = $(this).data('id');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Se eliminarán TODAS las imágenes de este vehículo",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar todo'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('admin/vehicleimages') }}/" + vehicleId,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                refreshTable();
                                Swal.fire('Eliminado!', response.message, 'success');
                            },
                            error: function(xhr) {
                                console.error('Error:', xhr);
                                Swal.fire('Error', 'No se pudieron eliminar las imágenes', 'error');
                            }
                        });
                    }
                });
            });

            // Abrir modal para crear nueva imagen
            $('#btnRegistrar').click(function() {
                $.ajax({
                    url: "{{ route('admin.vehicleimages.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modalTitle').text('Subir Imágenes al Vehículo');
                        $('#modal').modal('show');

                        // Manejar el envío del formulario dentro del modal
                        $('#modal form').on('submit', function(e) {
                            e.preventDefault();
                            let form = $(this);
                            let formData = new FormData(this);

                            $.ajax({
                                url: form.attr('action'),
                                type: form.attr('method'),
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(response) {
                                    $('#modal').modal('hide');
                                    refreshTable();
                                    Swal.fire('Éxito!', response.message, 'success');
                                },
                                error: function(xhr) {
                                    let errorMessage = 'Error al guardar las imágenes';
                                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                                        errorMessage = Object.values(xhr.responseJSON.errors).join('<br>');
                                    }
                                    Swal.fire('Error', errorMessage, 'error');
                                }
                            });
                        });
                    }
                });
            });

            // Abrir modal para editar
            $(document).on('click', '.btnEditar', function() {
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ url('admin/vehicleimages') }}/" + id + "/edit",
                    type: 'GET',
                    success: function(response) {
                        $('#modalBody').html(response);
                        $('#modalTitle').text('Gestionar Imágenes del Vehículo');
                        $('#modal').modal('show');

                        // Manejar el envío del formulario dentro del modal
                        $('#modal form').on('submit', function(e) {
                            e.preventDefault();
                            let form = $(this);
                            let formData = new FormData(this);

                            $.ajax({
                                url: form.attr('action'),
                                type: form.attr('method'),
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(response) {
                                    $('#modal').modal('hide');
                                    refreshTable();
                                    Swal.fire('Éxito!', response.message, 'success');
                                },
                                error: function(xhr) {
                                    let errorMessage = 'Error al actualizar las imágenes';
                                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                                        errorMessage = Object.values(xhr.responseJSON.errors).join('<br>');
                                    }
                                    Swal.fire('Error', errorMessage, 'error');
                                }
                            });
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'No se pudo cargar el formulario', 'error');
                    }
                });
            });

            // Refrescar tabla
            function refreshTable() {
                $('#table').DataTable().ajax.reload();
            }
        });
        
        // 🔹 Variable global para almacenar las imágenes
        let currentImages = [];

        // 🔹 Cuando se hace clic en una imagen de perfil → mostrar carrusel
        $(document).on('click', '.img-preview', function() {
            const vehicleId = $(this).data('vehicle');

            $.ajax({
                url: "{{ route('admin.vehicleimages.by-vehicle', '') }}/" + vehicleId,
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
                                '<div class="position-absolute" style="top: 15px; right: 15px;"><i class="fas fa-crown text-warning" style="font-size: 30px;"></i></div>' : '';
                            
                            carouselInner.append(`
                                <div class="carousel-item ${activeClass}" data-image-id="${img.id}">
                                    <div class="position-relative">
                                        ${crownIcon}
                                        <img src="${img.url}" class="d-block w-100 rounded shadow" alt="Imagen del vehículo" style="max-height: 500px; object-fit: contain;">
                                    </div>
                                </div>
                            `);
                        });

                        // Actualizar los botones con el ID de la primera imagen
                        updateProfileButton(0);
                        $('#btnSetProfile').show();
                        $('#btnDeleteImage').show();
                    }

                    $('#imageModal').modal('show');
                },
                error: function(xhr) {
                    console.error('Error al cargar imágenes:', xhr);
                    Swal.fire('Error', 'No se pudieron cargar las imágenes del vehículo', 'error');
                }
            });
        });

        // 🔹 Actualizar el ID de los botones cuando cambia la imagen del carrusel
        $('#vehicleCarousel').on('slid.bs.carousel', function () {
            const activeIndex = $('#vehicleCarousel .carousel-item.active').index();
            updateProfileButton(activeIndex);
        });

        // 🔹 Función para actualizar los botones con el ID de la imagen actual
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

        // 🔹 Establecer imagen como perfil
        $(document).on('click', '#btnSetProfile', function() {
            const imageId = $(this).attr('data-image-id');

            if (!imageId) {
                Swal.fire('Error', 'No se pudo identificar la imagen', 'error');
                return;
            }

            $.ajax({
                url: "{{ url('admin/vehicleimages/set-profile') }}/" + imageId,
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
                    Swal.fire('Error', 'No se pudo establecer la imagen como perfil', 'error');
                }
            });
        });

        // 🔹 Eliminar imagen desde el carrusel
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
                        url: "{{ url('admin/vehicleimages') }}/" + imageId,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire('Eliminado!', response.message, 'success');
                            
                            // Encontrar el índice de la imagen eliminada
                            const activeIndex = $('#vehicleCarousel .carousel-item.active').index();
                            
                            // Eliminar la imagen del array local
                            currentImages.splice(activeIndex, 1);
                            
                            if (currentImages.length === 0) {
                                // Si no quedan imágenes, cerrar el modal
                                $('#imageModal').modal('hide');
                            } else {
                                // Reconstruir el carrusel sin la imagen eliminada
                                const carouselInner = $('#vehicleCarousel .carousel-inner');
                                carouselInner.empty();
                                
                                currentImages.forEach((img, index) => {
                                    const activeClass = index === 0 ? 'active' : '';
                                    const crownIcon = img.is_profile ? 
                                        '<div class="position-absolute" style="top: 15px; right: 15px;"><i class="fas fa-crown text-warning" style="font-size: 30px;"></i></div>' : '';
                                    
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
                            Swal.fire('Error', 'No se pudo eliminar la imagen', 'error');
                        }
                    });
                }
            });
        });

        function refreshTable() {
            $('#table').DataTable().ajax.reload();
        }

    </script>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .carousel-item img {
            max-height: 500px;
            object-fit: contain;
        }
        .position-relative {
            position: relative;
        }
        .position-absolute {
            position: absolute;
        }
    </style>
@stop