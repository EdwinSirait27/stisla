{{-- Helper JS bersama halaman TOIL: escape, label status, format jam, bahasa DataTables --}}
<script>
    window.TOIL = (function() {
        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const hours = v => {
            const n = Number(v || 0);
            return (Math.round(n * 100) / 100).toLocaleString('id-ID', { maximumFractionDigits: 2 });
        };
        const BAL = {
            active: ['Aktif', 'is-approved'], fully_used: ['Habis dipakai', 'is-draft'], expired: ['Hangus', 'is-rejected'],
            cancelled: ['Dibatalkan', 'is-draft'], paid: ['Dibayar', 'is-open'],
        };
        const OT = {
            'Approved': ['Disetujui', 'is-approved'], 'Approved HR': ['Masuk payroll', 'is-open'], 'Rejected': ['Dibatalkan', 'is-draft'],
            'Pending': ['Menunggu', 'is-pending'], 'Cancelled': ['Dibatalkan', 'is-draft'],
        };
        const badge = (map, v) => { const [l, c] = map[v] ?? [v ?? '-', 'is-draft']; return `<span class="status-badge ${c}">${esc(l)}</span>`; };
        const type = v => v === 'Cash' ? '<span class="tl-type is-cash">Cash</span>' : (v === 'Toil' ? '<span class="tl-type is-toil">TOIL</span>' : '<span class="pr-muted">-</span>');
        const lang = (empty) => ({
            processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_', infoEmpty: 'Tidak ada data',
            infoFiltered: '', zeroRecords: 'Tidak ada data yang cocok dengan filter ini.', emptyTable: empty,
            paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }, search: '', searchPlaceholder: 'Cari',
        });
        // Tanggal lokal (WITA), bukan UTC
        const todayLocal = () => { const d = new Date(); d.setMinutes(d.getMinutes() - d.getTimezoneOffset()); return d.toISOString().slice(0, 10); };
        const daysLeft = d => {
            if (d === null || d === undefined) return '<span class="pr-muted">-</span>';
            const cls = d <= 7 ? 'is-danger' : (d <= 14 ? 'is-warn' : '');
            return `<span class="tl-days ${cls}">${d === 0 ? 'hari ini terakhir' : d + ' hari lagi'}</span>`;
        };
        const errMsg = (xhr, fallback) => xhr.responseJSON?.message
            || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('\n') : fallback);
        return { esc, hours, BAL, OT, badge, type, lang, todayLocal, daysLeft, errMsg };
    })();
</script>
