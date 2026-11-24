@extends('errors.minimal')

@section('title', 'Error del servidor')
@php
    $code = 500;
    $title = 'Error interno del servidor';
    $message = 'Ha ocurrido un error inesperado en el servidor. Estamos trabajando para solucionarlo.';
@endphp
