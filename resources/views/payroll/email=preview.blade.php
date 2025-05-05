
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Preview Pengiriman Email Slip Gaji</h4>
                    <a href="{{ route('payroll.email.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        @if ($test_mode)
                            <strong>MODE TEST:</strong> Email tidak akan dikirim. Ini hanya simulasi untuk melihat daftar email yang akan dikirim.
                        @else
                            <strong>SIAP KIRIM:</strong> {{ $total }} email akan dikirim setelah konfirmasi.
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <strong>Periode:</strong> {{ $period }}
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Karyawan</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($emails as $index => $email)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $email['employee'] }}</td>
                                    <td>{{ $email['email'] }}</td>
                                    <td>
                                        @if (empty($email['email']))
                                            <span class="badge bg-danger">Email tidak tersedia</span>
                                        @elseif (!$email['has_attachment'])
                                            <span class="badge bg-warning text-dark">Tidak ada lampiran</span>
                                        @else
                                            <span class="badge bg-success">Siap kirim</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if ($test_mode)
                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ route('payroll.email.index') }}" class="btn btn-secondary">Kembali</a>
                            <form method="POST" action="{{ route('payroll.email.send') }}">
                                @csrf
                                <input type="hidden" name="period" value="{{ request('period') }}">
                                @if (request('specific_employees'))
                                    @foreach (request('specific_employees') as $employeeId)
                                        <input type="hidden" name="specific_employees[]" value="{{ $employeeId }}">
                                    @endforeach
                                @endif
                                <button type="submit" class="btn btn-primary">Kirim Email Sungguhan</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection