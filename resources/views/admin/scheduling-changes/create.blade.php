{!! Form::open(['route' => 'admin.scheduling-changes.store', 'files' => true]) !!}
@include('admin.scheduling-changes.templates.form')
<button type="button" class="btn btn-success" id="btn-guardar"> <i class="fas fa-save"></i> Guardar</button>
<button type="button" class="btn btn-danger" data-dismiss="modal"> <i class="fas fa-window-close"></i> Cancelar</button>
{!! Form::close() !!}