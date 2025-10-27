@extends('adminlte::page')

@section('title', 'Registro de Programación')

@section('content_header')
    <div class="d-flex justify-content-end align-items-center">
        <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registrar programación</h3>
        </div>
        <div class="card-body">
            @include('admin.scheduling.templates.form')
        </div>
    </div>
@stop