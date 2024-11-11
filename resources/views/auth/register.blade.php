@extends('layouts.master')
@section('title','Register')
@section('content')
<div class="container" style="margin-top: 200px">
    <section class="vh-100">
        <div class="container-fluid h-custom">
          <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-md-9 col-lg-6 col-xl-5">
              <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/draw2.webp"
                class="img-fluid" alt="Sample image">
            </div>
            <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-danger">{{ $error }}</div>
                    @endforeach
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session()->has('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <br>
              <form action="{{route('register.post')}}" method="POST">
                @csrf
                <!-- Email input -->
                <div data-mdb-input-init class="form-outline mb-3">
                    <input name="name" type="name" id="form3Example3" class="form-control form-control-lg"
                      placeholder="Enter your name" />
                  </div>
                <!-- Email input -->
                <div data-mdb-input-init class="form-outline mb-3">
                  <input name="email" type="email" id="form3Example3" class="form-control form-control-lg"
                    placeholder="Enter a valid email address" />
                </div>
      
                <!-- Password input -->
                <div class="form-outline mb-3 position-relative">
                  <input name="password" type="password" id="password" class="form-control form-control-lg" placeholder="Enter password" />
                </div>

                <!-- Confirm Password input -->
                <div  class="form-outline mb-3 position-relative">
                  <input name="confirm-password" type="password" id="password-confirm" class="form-control form-control-lg" placeholder="Confirm password" />
                </div>
                <div class="text-center text-lg-start mt-4 pt-2">
                  <button  type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                    style="padding-left: 2.5rem; padding-right: 2.5rem;">Register</button>
                  <p class="small fw-bold mt-2 pt-1 mb-0">Already have an account? <a href="{{route('login')}}"
                      class="link-danger">Login</a></p>
                </div>
      
              </form>
            </div>
          </div>
        </div>
      </section>
</div>

@endsection