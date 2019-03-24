@extends('layouts.master')

@section('content')
    <div class="container mt--8 pb-5">
        <div class="row justify-content-center"  style="margin-top: 150px">
            <div class="col-lg-5 col-md-7">
                <div class="card bg-secondary shadow border-0">
                    <div class="card-body px-lg-5 py-lg-5">
                        <h4>You have no access unless you are Team Crescendo.</h4>
                        <a href="{{ route('login') }}">Login Page ></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
