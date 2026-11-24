@extends('errors.minimal')

@section('title', 'Página no encontrada')
@php
    $code = 404;
    $title = 'Página no encontrada';
    $message = 'La página u obra que buscas no existe o ha sido trasladada.';
@endphp
