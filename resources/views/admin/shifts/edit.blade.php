{!! Form::model($shift, ['route' => ['admin.shifts.update', $shift->id], 'method' => 'PUT', 'id' => 'shiftForm']) !!}
    @include('admin.shifts.templates.form')

    <div class="form-group text-right mt-4">
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Guardar
        </button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">
            <i class="fas fa-window-close"></i> Cancelar
        </button>
    </div>
{!! Form::close() !!}
