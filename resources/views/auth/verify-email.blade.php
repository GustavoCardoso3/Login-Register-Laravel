@extends('layouts.master')
@section('title','Verify')
@section('content')
    <div class="container">
        @if (session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session()->has('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        <h2>Verify your email</h2>
        <p>Didn't get the email?</p>
        <div class="d-flex justify-content-between">
            <form action="{{route('verification.send')}}" method="post">
                @csrf
                <button class="btn">Send again</button>
            </form>
            <a href="{{route('login')}}">Go back to login</a>
        </div>

    </div>
@endsection