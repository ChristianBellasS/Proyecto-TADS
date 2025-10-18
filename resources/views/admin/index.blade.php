@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <p>Welcome to this beautiful admin panel.</p>
@stop

@section('css')
    <style>
        .nav-sidebar .nav-treeview {
            margin-left: 20px;
        }

        .nav-sidebar .nav-treeview>.nav-item {
            margin-left: 10px;
        }
    </style>
@stop

@section('js')
    <script>
        console.log("Hi, I'm using the Laravel-AdminLTE package!");
    </script>
@stop
