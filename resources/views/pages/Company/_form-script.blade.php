{{-- Script form perusahaan: pratinjau kop surat langsung, pratinjau logo, format NPWP. --}}
<script>
    (function() {
        const form = document.getElementById('co-form');
        const kop = document.getElementById('kop-live');
        const q = k => kop.querySelector(`[data-k="${k}"]`);
        const v = id => document.getElementById(id).value.trim();
        let objectUrl = null;

        function render() {
            q('name').textContent = v('name').toUpperCase() || 'NAMA PERUSAHAAN';
            const header = v('header').toUpperCase();
            q('header').textContent = header; q('header').hidden = !header;
            q('address').textContent = v('address').toUpperCase() || 'Alamat perusahaan';
            const website = v('website').replace(/^https?:\/\//i, '').replace(/\/$/, '');
            const contact = [v('email'), website].filter(Boolean).join(' | ');
            q('contact').textContent = contact; q('contact').hidden = !contact;

            const d = v('npwp').replace(/\D/g, '');
            const help = document.getElementById('npwp-help');
            if (!d) help.textContent = 'Titik dan strip boleh, akan dibuang.';
            else if (d.length === 15) help.textContent = 'NPWP 15 digit: ' + d.replace(/^(\d{2})(\d{3})(\d{3})(\d)(\d{3})(\d{3})$/, '$1.$2.$3.$4-$5.$6');
            else if (d.length === 16) help.textContent = 'NPWP 16 digit (format NIK/NPWP baru).';
            else help.textContent = `${d.length} digit, harus 15 atau 16.`;
        }

        document.getElementById('foto').addEventListener('change', function() {
            const f = this.files[0];
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            if (!f) return;
            if (f.size > 1024 * 1024) { Swal.fire({ icon: 'error', title: 'Logo terlalu besar', text: 'Maksimal 1 MB.' }); this.value = ''; return; }
            objectUrl = URL.createObjectURL(f);
            for (const img of [document.getElementById('logo-prev'), q('logo')]) { img.src = objectUrl; img.hidden = false; }
            document.getElementById('logo-none').hidden = true; q('nologo').hidden = true;
        });

        form.addEventListener('input', render);
        form.addEventListener('submit', () => { const b = form.querySelector('button[type=submit]'); b.disabled = true; b.textContent = 'Menyimpan'; });
        render();
    })();
</script>
