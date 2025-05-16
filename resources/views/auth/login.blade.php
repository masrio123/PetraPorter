@extends('layouts.app')


@section('content')
    <div class="container" style="margin-top: 120px">
        <div class="row justify-content-center">
            <div class="col-5">
                <div class="authentication-wrapper authentication-basic container-p-y">
                    <div class="authentication-inner">
                        <!-- Register -->
                        <div class="card px-sm-6 px-0">
                            <div class="card-body">
                                <!-- Logo -->
                                <div class="app-brand justify-content-center">
                                    <a href="index.html" class="app-brand-link gap-2">
                                        <span class="app-brand-text demo text-heading fw-bold">Petra Porter</span>
                                    </a>
                                </div>
                                <!-- /Logo -->
                                <h4 class="mb-1">Selamat Datang 👋</h4>
                                <p class="mb-6">Silahkan masuk untuk memulai pekerjaan hari ini</p>

                                <form id="formAuthentication" class="mb-6" action="" method="POST">
                                    @csrf
                                    <div class="mb-6">
                                        <label for="email" class="form-label">User</label>
                                        <input type="text" class="form-control" id="email" name="email"
                                            placeholder="Masukan User" autofocus />
                                    </div>
                                    <div class="mb-6 form-password-toggle">
                                        <label class="form-label" for="password">Sandi</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" id="password" class="form-control" name="password"
                                                placeholder="Masukan Sandi" aria-describedby="password" />
                                            <span class="input-group-text cursor-pointer"><i
                                                    class="icon-base bx bx-hide"></i></span>
                                        </div>
                                    </div>
                                    <div class="mb-8">
                                        <div class="d-flex justify-content-between">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="remember-me" />
                                                <label class="form-check-label" for="remember-me"> Ingat Saya </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-6">
                                        <button type="submit" class="btn btn-primary d-grid w-100"
                                            type="submit">Masuk</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- /Register -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection