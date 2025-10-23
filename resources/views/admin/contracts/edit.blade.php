{!! Form::model($contract, ['route' => ['admin.contracts.update', $contract->id], 'method' => 'PUT', 'id' => 'contractForm']) !!}
    <div class="card-body">
        @include('admin.contracts.templates.form')
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Actualizar
        </button>
    </div>
{!! Form::close() !!}
