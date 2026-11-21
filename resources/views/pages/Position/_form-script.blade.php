{{-- Script form jabatan: daftar isian (tambah/hapus/Enter), saklar lowongan, dan kelengkapan uraian. --}}
<script>
    (function() {
        const form = document.getElementById('po-form');
        const pub = document.getElementById('publish_career');
        const careerBox = document.getElementById('career-box');
        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const filled = name => [...form.querySelectorAll(`input[name="${name}[]"]`)].filter(i => i.value.trim() !== '').length;

        function renumber(list) {
            [...list.children].forEach((li, i) => {
                li.querySelector('.po-num').textContent = i + 1;
                const input = li.querySelector('input');
                input.setAttribute('aria-label', input.getAttribute('aria-label').replace(/\d+$/, i + 1));
                li.querySelector('.po-remove').setAttribute('aria-label', 'Hapus baris ' + (i + 1));
            });
        }
        function addRow(list, focus) {
            const tpl = list.lastElementChild.cloneNode(true);
            const input = tpl.querySelector('input');
            input.value = ''; input.classList.remove('is-invalid');
            list.appendChild(tpl); renumber(list);
            if (focus) input.focus();
            return input;
        }
        form.addEventListener('click', e => {
            const add = e.target.closest('.po-add');
            if (add) { addRow(form.querySelector(`.po-list[data-list="${add.dataset.target}"]`), true); render(); return; }
            const rm = e.target.closest('.po-remove');
            if (rm) {
                const li = rm.closest('li'), list = li.parentElement;
                if (list.children.length === 1) { li.querySelector('input').value = ''; li.querySelector('input').focus(); }
                else { const next = li.nextElementSibling || li.previousElementSibling; li.remove(); renumber(list); next?.querySelector('input')?.focus(); }
                render();
            }
        });
        // Enter di baris daftar = baris baru (bukan submit form)
        form.addEventListener('keydown', e => {
            if (e.key !== 'Enter' || !e.target.closest('.po-list')) return;
            e.preventDefault();
            const li = e.target.closest('li'), list = li.parentElement;
            if (li.nextElementSibling) li.nextElementSibling.querySelector('input').focus();
            else if (e.target.value.trim() !== '') addRow(list, true);
        });

        function render() {
            careerBox.hidden = !pub.checked;
            form.querySelectorAll('.js-pub-req').forEach(el => el.hidden = !pub.checked);

            const checks = [
                [document.getElementById('role_summary').value.trim() !== '', 'Ringkasan peran'],
                [filled('key_respon') > 0, `Tanggung jawab utama (${filled('key_respon')} baris)`],
                [filled('qualification') > 0, `Kualifikasi (${filled('qualification')} baris)`],
            ];
            const done = checks.filter(c => c[0]).length;
            const items = checks.map(([ok, label]) => [ok, label]);
            if (pub.checked) {
                items.push([!!document.getElementById('vacancy').value, 'Jumlah lowongan']);
                const s = document.getElementById('career_start_date').value, en = document.getElementById('career_end_date').value;
                items.push([!!(s && en && en >= s), s && en && en < s ? 'Tanggal tayang terbalik' : 'Tanggal tayang']);
                items.push([filled('requirement') > 0, `Persyaratan pelamar (${filled('requirement')} baris)`]);
            }
            document.getElementById('sum-score').textContent = done;
            document.getElementById('sum-bar').style.transform = `scaleX(${done / 3})`;
            document.getElementById('sum-checks').innerHTML = items.map(([ok, label]) =>
                `<li class="${ok ? 'is-ok' : ''}"><i class="fas ${ok ? 'fa-circle-check' : 'fa-circle'}" aria-hidden="true"></i><span>${esc(label)}${ok ? '' : ' <small>belum</small>'}</span></li>`).join('');
        }

        form.addEventListener('input', render);
        form.addEventListener('change', render);
        form.addEventListener('submit', () => { const b = form.querySelector('button[type=submit]'); b.disabled = true; b.textContent = 'Menyimpan'; });
        render();
    })();
</script>
