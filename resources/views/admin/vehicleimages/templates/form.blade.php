<div class="form-group">
    {!! Form::label('image', 'Imagen') !!}
    {!! Form::file('image', ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('profile', '¿Es una imagen de perfil?') !!}
    {!! Form::select('profile', [0 => 'No', 1 => 'Sí'], $image->profile ?? 0, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('vehicle_id', 'ID del Vehículo') !!}
    {!! Form::number('vehicle_id', isset($image) ? $image->vehicle_id : '', ['class' => 'form-control', 'required']) !!}
</div>
