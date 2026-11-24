@extends('errors.layout')
@section('title', 'Page expired')
@section('code', 'Page expired')
@section('heading', 'That page was open for a while.')
@section('message', 'For your security the form expired. Go back, refresh the page and try again. Nothing was sent.')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn-primary">Go back</a>
@endsection
