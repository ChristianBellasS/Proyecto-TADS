{!! Form::model($group, ['route' => ['admin.employeegroups.update', $group->id], 'method' => 'PUT', 'files' => false, 'id' => 'employeeGroupForm']) !!}

@include('admin.employeegroups.templates.form', [
    'zones' => $zones,
    'shifts' => $shifts, 
    'vehicles' => $vehicles,
    'group' => $group,
    'isEdit' => true
])

<div class="row mt-4">
    <div class="col-12 text-right">
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Actualizar
        </button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">
            <i class="fas fa-window-close"></i> Cancelar
        </button>
    </div>
</div>

{!! Form::close() !!}