{!! Form::open(['route' => 'admin.contracts.store', 'method' => 'POST', 'id' => 'contractForm']) !!}
    <div class="card-body">
        @include('admin.contracts.templates.form')
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Guardar
        </button>
    </div>
{!! Form::close() !!}
