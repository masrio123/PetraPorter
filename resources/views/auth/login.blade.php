@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center min-vh-100" style="background-color: #d9d9d9;">
        <div class="w-100" style="max-width: 420px;">
            <div class="text-center mb-4">
                <img src="{{ asset('assets/img/logopcupng.png') }}" alt="Logo Petra" width="200px" class="mb-2">
                <br>
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo Petra" width="120px">
            </div>

            <h3 class="text-center mb-4"><strong>ADMIN PORTAL</strong></h3>

            <div class="card shadow-sm">
                <div class="card-body p-6">
                    <form id="formAuthentication" action="" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">User</label>
                            <input type="text" class="form-control" id="email" name="email"
                                placeholder="Masukan User" autofocus style="background-color: #d9d9d9;">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Sandi</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password"
                                    style="background-color: #d9d9d9;" placeholder="Masukan Sandi">
                                <span class="input-group-text cursor-pointer" style="background-color: #d9d9d9;">
                                    <i class="icon-base bx bx-hide"></i>
                                </span>
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn text-white" style="background-color: #ff7622;">Masuk</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
