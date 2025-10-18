{!! Form::open(['route' => 'admin.usertypes.store', 'id' => 'usertypeForm']) !!}
@include('admin.usertypes.templates.form')
<button type="submit" class="btn btn-success"> <i class="fas fa-save"></i> Guardar</button>
<button type="button" class="btn btn-danger" data-dismiss="modal"> <i class="fas fa-window-close"></i> Cancelar</button>
{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $('#usertypeForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var formData = new FormData(this);

            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#modal').modal('hide');
                    refreshTable();
                    Swal.fire({
                        title: "Proceso exitoso!",
                        text: response.message,
                        icon: "success"
                    });
                },
            });
        });
    });
</script>
