<script>
    $(function() {
        const withUser = @json(!$feedUser);
        const columns = [
            { data: 'time_fmt', name: 'time_fmt', searchable: false },
            ...(withUser ? [{ data: 'causer_fmt', name: 'causer_fmt', orderable: false }] : []),
            { data: 'module_fmt', name: 'module_fmt', searchable: false },
            { data: 'event_fmt', name: 'event_fmt', searchable: false },
            { data: 'detail_fmt', name: 'detail_fmt', orderable: false },
        ];
        const feed = $('#feed-table').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            dom: 'rt<"pr-dt-bottom"<"pr-dt-left"li>p>',
            ajax: {
                url: '{{ route('activity.data') }}',
                data: d => {
                    const r = $('#feed-range').val();
                    d.range = r === 'custom' ? '' : r;
                    if (r === 'custom') { d.date_from = $('#feed-from').val(); d.date_to = $('#feed-to').val(); }
                    d.log_name = $('#feed-log').val();
                    d.event = $('#feed-event').val();
                    d.causer = $('#feed-causer').val() || '';
                    @if ($feedUser) d.user = @json($feedUser); @endif
                }
            },
            order: [[0, 'desc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            language: {
                processing: 'Memuat', lengthMenu: 'Tampilkan _MENU_', info: '_START_ sampai _END_ dari _TOTAL_',
                infoEmpty: 'Tidak ada data', infoFiltered: '', zeroRecords: 'Tidak ada aktivitas yang cocok dengan filter ini.', emptyTable: 'Belum ada aktivitas.',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
            },
            columns,
        });
        feed.on('xhr', () => { const j = feed.ajax.json(); if (j) $('#feed-count').text(`${Number(j.recordsFiltered).toLocaleString('id-ID')} aktivitas`); });

        const sel = '#feed-range, #feed-log, #feed-event, #feed-causer, #feed-from, #feed-to';
        const syncReset = () => {
            const active = $('#feed-range').val() !== '30d' || $('#feed-log').val() || $('#feed-event').val() || $('#feed-causer').val() || $('#feed-search').val();
            $('#feed-reset').prop('hidden', !active);
            $('.js-custom').prop('hidden', $('#feed-range').val() !== 'custom');
        };
        $(sel).on('change', () => { syncReset(); feed.ajax.reload(); });
        let t;
        $('#feed-search').on('input', function() { clearTimeout(t); const v = this.value; t = setTimeout(() => { feed.search(v).draw(); syncReset(); }, 350); });
        $('#feed-reset').on('click', () => {
            $('#feed-range').val('30d'); $('#feed-log, #feed-event, #feed-causer, #feed-from, #feed-to, #feed-search').val('');
            feed.search(''); syncReset(); feed.ajax.reload();
        });
        // Tombol pintas dari ringkasan / grafik
        $(document).on('click', '.js-quick', function() {
            if (this.dataset.range) $('#feed-range').val(this.dataset.range);
            $('#feed-log').val(this.dataset.log ?? '');
            $('#feed-event').val(this.dataset.event ?? '');
            syncReset(); feed.ajax.reload();
            document.getElementById('feed-title').scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
</script>
