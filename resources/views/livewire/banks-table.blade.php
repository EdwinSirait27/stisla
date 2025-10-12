{{-- <div>
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="mb-0">Bank List</h5>
            <button wire:click="loadBanks" class="btn btn-light btn-sm">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="table-dark">
                        <tr class="text-center">
                            <th>Bank Name</th>
                            <th width="15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($banks as $bank)
                            <tr>
                                <td>{{ $bank->name }}</td>
                                <td class="text-center">{!! $bank->action !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">
                                    There is no Banks.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> --}}
{{-- <div>
    <style>
        /* ===== Custom Styles for Banks Table ===== */
        .card.bank-card {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .bank-card .card-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #fff;
            padding: 0.75rem 1rem;
        }

        .bank-card h5 {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .bank-card .btn-refresh {
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        .bank-card .btn-refresh:hover {
            background: #f8f9fa;
            color: #0056b3;
            transform: scale(1.05);
        }

        .bank-card .table {
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        .bank-card .table th {
            background-color: #343a40;
            color: #fff;
            vertical-align: middle;
        }

        .bank-card .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .bank-card .table td,
        .bank-card .table th {
            vertical-align: middle;
            padding: 0.75rem;
        }

        .bank-card .table td.text-center i {
            transition: color 0.2s ease-in-out;
        }

        .bank-card .table td.text-center i:hover {
            color: #007bff;
        }

        .bank-card .no-data {
            color: #999;
            font-style: italic;
        }
    </style>

    <div class="card bank-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-university mr-2"></i>Bank List</h5>
            <button wire:click="loadBanks" class="btn btn-light btn-refresh btn-sm">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr class="text-center">
                            <th>Bank Name</th>
                            <th width="15%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($banks as $bank)
                            <tr>
                                <td>{{ $bank->name }}</td>
                                <td class="text-center">{!! $bank->action !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-3 no-data">
                                    <i class="fas fa-info-circle"></i> There is no Banks.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> --}}
