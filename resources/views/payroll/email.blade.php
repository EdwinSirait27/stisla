@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Kirim Email Slip Gaji</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('payroll.email.send') }}">
                        @csrf
                        
                        <div class="form-group row mb-3">
                            <label for="period" class="col-md-3 col-form-label">Periode Gaji</label>
                            <div class="col-md-9">
                                <select class="form-control @error('period') is-invalid @enderror" id="period" name="period" required>
                                    <option value="">-- Pilih Periode --</option>
                                    @foreach ($periods as $period)
                                        <option value="{{ $period['month'] }}-{{ $period['year'] }}">
                                            {{ $period['formatted'] }}
                                        </option>
                                    @endforeach
                                </select>
                                
                                @error('period')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group row mb-3">
                            <label class="col-md-3 col-form-label">Penerima Email</label>
                            <div class="col-md-9">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="recipient_type" id="all_employees" value="all" checked>
                                    <label class="form-check-label" for="all_employees">
                                        Semua Karyawan
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="recipient_type" id="specific_employees" value="specific">
                                    <label class="form-check-label" for="specific_employees">
                                        Karyawan Tertentu
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group row mb-3 employee-selection" style="display: none;">
                            <div class="col-md-3"></div>
                            <div class="col-md-9">
                                <select class="form-control" id="employees" name="specific_employees[]" multiple>
                                    @foreach (App\Models\Employee::orderBy('name')->get() as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->email ?: 'No Email' }})</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Tahan tombol Ctrl (atau Command di Mac) untuk memilih beberapa karyawan</small>
                            </div>
                        </div>
                        
                        <div class="form-group row mb-3">
                            <div class="col-md-3"></div>
                            <div class="col-md-9">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="test_mode" id="test_mode" value="1">
                                    <label class="form-check-label" for="test_mode">
                                        <strong>Mode Test</strong> (Hanya simulasi, tidak mengirim email sungguhan)
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group row mb-0">
                            <div class="col-md-9 offset-md-3">
                                <button type="submit" class="btn btn-primary">
                                    Kirim Email
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle employee selection visibility
        const recipientTypeRadios = document.querySelectorAll('input[name="recipient_type"]');
        const employeeSelectionDiv = document.querySelector('.employee-selection');
        
        recipientTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'specific') {
                    employeeSelectionDiv.style.display = 'flex';
                } else {
                    employeeSelectionDiv.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush
@endsection