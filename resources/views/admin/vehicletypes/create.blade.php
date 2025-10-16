{!! Form::open(['route' => 'admin.vehicletypes.store', 'files' => false]) !!}
@include('admin.vehicletypes.templates.form')
<button type="submit" class="btn btn-success"> <i class="fas fa-save"></i> Guardar</button>
<button type="button" class="btn btn-danger" data-dismiss="modal"> <i class="fas fa-window-close"></i> Cancelar</button>
{!! Form::close() !!}