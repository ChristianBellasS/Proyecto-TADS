{!! Form::model($vehicle, ['route' => ['admin.vehicles.update', $vehicle->id], 'method' => 'PUT', 'files' => false, 'id' => 'vehicleForm']) !!}
@include('admin.vehicles.templates.form')
<div class="text-right mt-4 pt-3 border-top">
    <button type="submit" class="btn btn-success btn-md">
        <i class="fas fa-save mr-1"></i> Guardar
    </button>
    <button type="button" class="btn btn-danger btn-md" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Cancelar
    </button>
</div>
{!! Form::close() !!}