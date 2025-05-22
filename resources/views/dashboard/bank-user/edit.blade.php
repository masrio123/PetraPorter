@extends('layouts.createLayout')

@section('content')
<div class="container">
    <h3>Edit Bank User</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('dashboard.bank-users.update', $bank_user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" class="form-control"
                   value="{{ old('username', $bank_user->username) }}" required>
        </div>

        <div class="mb-3">
            <label for="account_number" class="form-label">Nomor Rekening</label>
            <input type="text" name="account_number" class="form-control"
                   value="{{ old('account_number', $bank_user->account_number) }}" required>
        </div>

        <div class="mb-3">
            <label for="bank_id" class="form-label">Nama Bank</label>
            <select name="bank_id" class="form-select" required>
                <option value="" disabled>-- Pilih Bank --</option>
                @foreach($banks as $bank)
                    <option value="{{ $bank->id }}" {{ old('bank_id', $bank_user->bank_id) == $bank->id ? 'selected' : '' }}>
                        {{ $bank->bank_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>
@endsection
