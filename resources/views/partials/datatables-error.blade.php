{{-- Pesan error AJAX DataTables yang jelas (status + penyebab), menggantikan alert "DataTables warning" generik.
     Pakai setelah jquery.dataTables & sweetalert2 dimuat: @include('partials.datatables-error', ['permission' => 'payroll.view']) --}}
<script>
    $.fn.dataTable.ext.errMode = 'none';
    $(document).on('error.dt', function(e, settings, techNote, message) {
        const xhr = settings.jqXHR;
        const status = xhr?.status;
        const server = xhr?.responseJSON?.message || xhr?.responseJSON?.error || '';
        const perm = @json($permission ?? 'yang dibutuhkan');
        const reasons = {
            401: 'Sesi login habis. Muat ulang halaman lalu login lagi.',
            403: server && !/unauthori[sz]ed|right permissions/i.test(server) ? server
                : `Akun Anda tidak punya permission ${perm} untuk data ini. Minta admin menambahkannya di menu Role, lalu muat ulang halaman.`,
            419: 'Sesi halaman kedaluwarsa. Muat ulang halaman.',
            429: 'Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.',
        };
        const detail = reasons[status]
            || (status >= 500 ? 'Terjadi kesalahan di server' + (server ? ': ' + server : '.') + ' Cek storage/logs/laravel.log.' : '')
            || (message || '').replace(/^DataTables warning: table id=[\w-]+ - /, '')
            || 'Tidak bisa memuat data.';
        Swal.fire({ icon: 'error', title: 'Gagal memuat data' + (status ? ` (HTTP ${status})` : ''), text: detail });
        console.error('[DataTables]', settings.nTable?.id, status, server || message, xhr?.responseText?.slice(0, 500));
    });
</script>
