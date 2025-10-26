{!! Form::open(['route' => 'admin.employeegroups.store', 'files' => false, 'id' => 'employeeGroupForm']) !!}

@include('admin.employeegroups.templates.form', [
    'zones' => $zones,
    'shifts' => $shifts,
    'vehicles' => $vehicles,
    'isEdit' => false
])

<div class="row mt-4">
    <div class="col-12 text-right">
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Guardar
        </button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">
            <i class="fas fa-window-close"></i> Cancelar
        </button>
    </div>
</div>

{!! Form::close() !!}