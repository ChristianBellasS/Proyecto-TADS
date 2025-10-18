<div class="row">
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('dni', 'DNI *') !!}
                    {!! Form::text('dni', null, [
                        'class' => 'form-control',
                        'placeholder' => '12345678',
                        'maxlength' => '8',
                        'required',
                    ]) !!}
                    <small class="form-text text-muted">8 dígitos únicos</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('usertype_id', 'Tipo de Empleado *') !!}
                    @if (isset($usertypes) && $usertypes->count() > 0)
                        {!! Form::select('usertype_id', $usertypes->pluck('name', 'id'), null, [
                            'class' => 'form-control',
                            'placeholder' => 'Seleccione un tipo',
                            'required',
                        ]) !!}
                    @else
                        <select class="form-control" name="usertype_id" required>
                            <option value="">No hay tipos de usuario disponibles</option>
                        </select>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('name', 'Nombres *') !!}
                    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Ingrese los nombres', 'required']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('last_name', 'Apellidos *') !!}
                    {!! Form::text('last_name', null, [
                        'class' => 'form-control',
                        'placeholder' => 'Ingrese los apellidos',
                        'required',
                    ]) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('birthdate', 'Fecha de Nacimiento *') !!}
                    {!! Form::date('birthdate', isset($user) && $user->birthdate ? $user->birthdate->format('Y-m-d') : null, [
                        'class' => 'form-control',
                        'required',
                        'max' => date('Y-m-d', strtotime('-18 years')),
                    ]) !!}
                    <small class="form-text text-muted">Mayor de 18 años</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('telefono', 'Teléfono') !!}
                    {!! Form::text('telefono', null, [
                        'class' => 'form-control',
                        'placeholder' => '987654321',
                        'maxlength' => '15',
                    ]) !!}
                    <small class="form-text text-muted">Número de teléfono (opcional)</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('email', 'Email *') !!}
                    {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => 'empleado@ejemplo.com', 'required']) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('estado', 'Estado *') !!}
                    {!! Form::select(
                        'estado',
                        ['activo' => 'Activo', 'inactivo' => 'Inactivo'],
                        isset($user) ? $user->estado : 'activo',
                        ['class' => 'form-control', 'required'],
                    ) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('password', 'Contraseña ' . (isset($user) ? '(dejar en blanco para no cambiar)' : '*')) !!}
                    {!! Form::password('password', [
                        'class' => 'form-control',
                        'placeholder' => 'Mínimo 6 caracteres',
                        isset($user) ? '' : 'required',
                    ]) !!}
                    <small class="form-text text-muted">Mínimo 6 caracteres</small>
                </div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('address', 'Dirección *') !!}
            {!! Form::text('address', null, [
                'class' => 'form-control',
                'placeholder' => 'Av. Principal 123, Distrito, Ciudad',
                'minlength' => '10',
                'required',
            ]) !!}
            <small class="form-text text-muted">Dirección completa (mínimo 10 caracteres)</small>
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Foto de Perfil</label>
            <div id="imageButton" style="width: 100%; text-align:center; padding:10px;">
                <img id="imagePreview"
                    src="{{ isset($user) && $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : asset('storage/profile_photos/no_logo.png') }}"
                    alt="Vista previa de la imagen"
                    style="width: 100%; height: 400px; object-fit: cover; cursor: pointer; border-radius: 8px;">
                <p style="font-size:12px; margin-top: 10px;">Haga click para seleccionar una imagen</p>
            </div>
        </div>
        <div class="form-group">
            {!! Form::file('profile_photo', [
                'class' => 'form-control-file d-none',
                'accept' => 'image/*',
                'id' => 'imageInput',
            ]) !!}
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Manejo de la imagen
        $('#imageInput').change(function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result).show();
                };
                reader.readAsDataURL(file);
            }
        });

        $('#imageButton').click(function() {
            $('#imageInput').click();
        });

        // Validación de formulario
        $('#userForm').off('submit').on('submit', function(e) {
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
                    if (typeof table !== 'undefined') {
                        table.ajax.reload();
                    }
                    Swal.fire({
                        title: "¡Éxito!",
                        text: response.message,
                        icon: "success"
                    });
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = '';

                    if (errors) {
                        $.each(errors, function(key, value) {
                            errorMessage += value[0] + '\n';
                        });
                    } else {
                        errorMessage = xhr.responseJSON.message ||
                            'Hubo un error al procesar la solicitud';
                    }

                    Swal.fire({
                        title: "Error!",
                        text: errorMessage,
                        icon: "error"
                    });
                }
            });
        });
    });
</script>