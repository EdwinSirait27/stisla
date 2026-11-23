{{-- Script form jenis cuti: tampil/sembunyi syarat, mode durasi, dan hitungan karyawan yang memenuhi syarat. --}}
<script>
    (function() {
        const DATA = @json($eligibility);
        const BLOCKED = @json($blockedStatuses);
        const LABELS = @json($statusOptions);
        const form = document.getElementById('lt-form');
        const special = document.getElementById('is_special');
        const box = document.getElementById('special-box');
        const fmt = v => Number(v || 0).toLocaleString('id-ID');
        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const checked = sel => [...form.querySelectorAll(sel)].filter(i => i.checked).map(i => i.value);
        const mode = () => (form.querySelector('input[name=duration_mode]:checked') || {}).value || 'free';

        function syncDuration() {
            form.querySelectorAll('.lt-dur').forEach(el => { el.hidden = el.dataset.mode !== mode(); });
        }

        function render() {
            const isSpecial = special.checked;
            box.hidden = !isSpecial;
            syncDuration();

            const gender = isSpecial ? (checked('input[name=gender_rule]')[0] || 'all') : 'all';
            const married = isSpecial && form.querySelector('input[name=require_married]:checked') !== null;
            const statuses = isSpecial ? checked('input[name="allowed_status[]"]') : [];

            const total = DATA.reduce((a, r) => a + r.c, 0);
            // Status dicentang: hanya status itu (DW/OJT ikut kalau dicentang). Tidak dicentang: semua kecuali DW/OJT.
            const statusOk = s => statuses.length ? statuses.includes(s) : !BLOCKED.includes(s);
            const blocked = DATA.filter(r => !statusOk(r.s)).reduce((a, r) => a + r.c, 0);
            const ok = DATA.filter(r => statusOk(r.s)
                && (gender === 'all' || r.g === gender)
                && (!married || r.m)).reduce((a, r) => a + r.c, 0);

            document.getElementById('sum-count').textContent = fmt(ok);
            document.getElementById('sum-of').textContent = `dari ${fmt(total)} karyawan aktif`;
            document.getElementById('sum-bar').style.transform = `scaleX(${total ? ok / total : 0})`;

            const rules = [statuses.length ? `${fmt(blocked)} karyawan dengan status lain tidak bisa mengajukan.` : `${fmt(blocked)} karyawan DW atau On Job Training tidak punya hak cuti ini.`];
            if (isSpecial) {
                if (gender !== 'all') rules.push(gender === 'female' ? 'Hanya perempuan.' : 'Hanya laki-laki.');
                if (married) rules.push('Hanya yang sudah menikah.');
                if (statuses.length) rules.push('Hanya status ' + statuses.map(s => LABELS[s] || s).join(', ') + '.');
                const f = document.getElementById('fixed_days').value, m = document.getElementById('max_days').value;
                if (mode() === 'fixed' && f) rules.push(`Durasi tepat ${f} hari kalender, tanggal selesai terkunci.`);
                if (mode() === 'max' && m) rules.push(`Maksimal ${m} hari per pengajuan.`);
                if (form.querySelector('input[name=require_attachment]:checked')) rules.push('Wajib lampiran bukti.');
                const bal = document.getElementById('default_balance').value;
                rules.push(bal ? `Saldo ${bal} hari, dibuat lewat leave:generate-special.` : 'Belum ada jatah saldo: karyawan tidak punya saldo untuk cuti ini.');
                if (ok === 0) rules.push('Tidak ada karyawan aktif yang memenuhi semua syarat ini.');
            } else {
                rules.push('Tanpa syarat khusus: cukup punya saldo cuti ini.');
            }
            document.getElementById('sum-rules').innerHTML = rules.map(r => `<li>${esc(r)}</li>`).join('');
        }

        form.addEventListener('input', render);
        form.addEventListener('change', render);
        form.addEventListener('submit', function() {
            // Isian durasi yang tidak dipilih tidak ikut terkirim.
            const fixedEl = document.getElementById('fixed_days'), maxEl = document.getElementById('max_days');
            if (!special.checked || mode() !== 'fixed') fixedEl.disabled = true;
            if (!special.checked || mode() !== 'max') maxEl.disabled = true;
            const btn = form.querySelector('button[type=submit]');
            btn.disabled = true; btn.textContent = 'Menyimpan';
        });
        render();
    })();
</script>
