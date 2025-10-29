@extends('adminlte::page')

@section('title', 'Registro de Programación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Registrar Programación</h1>
        <a href="{{ route('admin.scheduling.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Complete los datos de la programación</h3>
        </div>

        <div class="card-body">
            {!! Form::open(['route' => 'admin.scheduling.store', 'method' => 'POST', 'id' => 'schedulingForm', 'data-no-ajax' => 'true']) !!}
                @include('admin.scheduling.templates.form')
            {!! Form::close() !!}
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#schedulingForm').on('submit', function(e) {
                if ($(this).data('no-ajax')) {
                    return true;
                }
            });
        });
    </script>
@stop