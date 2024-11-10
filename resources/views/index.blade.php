@extends('layouts.master')
@section('title', 'index')
@section('content')
    <div class="container">
        <h1>You are logged in</h1>
        <p>Email= {{$email}}</p>
        <p>Name= {{$name}}</p>   
        <a href="{{route('logout')}}">Logout</a>
    </div>
@endsection