{!! Form::model($models, ['route' => ['admin.models.update', $models->id], 'method' => 'PUT']) !!}
    @include('admin.models.templates.form')
    <button type="submit" class="btn btn-success"> <i class="fas fa-save"></i> Actualizar</button>
    <button type="button" class="btn btn-danger" data-dismiss="modal"> <i class="fas fa-window-close"></i> Cancelar</button>
{!! Form::close() !!}