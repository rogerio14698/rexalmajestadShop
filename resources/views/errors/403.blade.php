@extends('errors.minimal')

@section('title', 'Acceso denegado')
@php
    $code = 403;
    $title = 'Acceso denegado';
    $message = 'No tienes permisos para acceder a esta sección.';
@endphp
