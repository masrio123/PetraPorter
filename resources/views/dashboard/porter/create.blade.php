@extends('layouts.createLayout')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4"><strong>Tambah Porter</strong></h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('dashboard.porters.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="porter_name" class="form-label">Nama Porter</label>
            <input type="text" name="porter_name" id="porter_name" class="form-control" required
                value="{{ old('porter_name') }}">
        </div>

        <div class="mb-3">
            <label for="porter_nrp" class="form-label">NRP</label>
            <input type="text" name="porter_nrp" id="porter_nrp" class="form-control" required
                value="{{ old('porter_nrp') }}">
        </div>

        <div class="mb-3">
            <label for="department_id" class="form-label">Departemen</label>
            <select name="department_id" id="department_id" class="form-select" required>
                <option disabled {{ old('department_id') ? '' : 'selected' }}>-- Pilih Departemen --</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                        {{ $department->department_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="bank_user_id" class="form-label">Rekening Porter</label>
            <select name="bank_user_id" id="bank_user_id" class="form-select" required>
                <option disabled {{ old('bank_user_id') ? '' : 'selected' }}>-- Pilih Rekening --</option>
                @foreach ($bankUsers as $bankUser)
                    <option value="{{ $bankUser->id }}" {{ old('bank_user_id') == $bankUser->id ? 'selected' : '' }}>
                        {{ $bankUser->account_number }} – {{ $bankUser->bank->bank_name ?? 'Bank Tidak Diketahui' }} – {{ $bankUser->username }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="porter_isOnline" class="form-label">Status Online</label>
            <select name="porter_isOnline" id="porter_isOnline" class="form-select" required>
                <option value="1" {{ old('porter_isOnline') == '1' ? 'selected' : '' }}>Online</option>
                <option value="0" {{ old('porter_isOnline') == '0' ? 'selected' : '' }}>Offline</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Tambah Porter</button>
    </form>
</div>
@endsection
