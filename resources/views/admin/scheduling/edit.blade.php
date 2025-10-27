@extends('adminlte::page')

@section('title', 'Editar Programación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Editar Programación</h1>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            {!! Form::model($scheduling, ['route' => ['admin.scheduling.update', $scheduling->id], 'method' => 'PUT']) !!}
                @include('admin.scheduling.form')
            {!! Form::close() !!}
        </div>
    </div>
@stop