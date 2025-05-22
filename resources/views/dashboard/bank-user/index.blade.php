@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4"><strong>Bank User Management</strong></h3>

        <a href="{{ route('dashboard.bank-users.create') }}" class="btn btn-primary mb-3">Tambah Bank User</a>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Nomor Rekening</th>
                    <th>Nama Bank</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bank_users as $key => $user)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->account_number }}</td>
                        <td>{{ $user->bank?->bank_name ?? 'Tidak ada bank' }}</td>
                        <td>
                            <a href="{{ route('dashboard.bank-users.edit', $user->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('dashboard.bank-users.destroy', $user->id) }}" method="POST"
                                style="display:inline-block;" onsubmit="return confirm('Yakin hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
