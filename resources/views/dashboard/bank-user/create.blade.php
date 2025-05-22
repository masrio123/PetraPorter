@extends('layouts.createLayout')

@section('content')
<div class="container">
    <h3>Tambah Bank User</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('dashboard.bank-users.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
        </div>

        <div class="mb-3">
            <label for="account_number" class="form-label">Nomor Rekening</label>
            <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}" required>
        </div>

        <div class="mb-3">
            <label for="bank_id" class="form-label">Nama Bank</label>
            <select name="bank_id" class="form-select" required>
                <option value="" disabled selected>-- Pilih Bank --</option>
                @foreach($banks as $bank)
                    <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                        {{ $bank->bank_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
