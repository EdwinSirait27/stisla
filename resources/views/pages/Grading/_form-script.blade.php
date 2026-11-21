{{-- Script form grading: format rupiah dan pratinjau posisi di jenjang (siapa atasan otomatisnya). --}}
<script>
    (function() {
        const form = document.getElementById('gr-form');
        const LADDER = @json($ladder);
        const SELF = @json($grading?->id);
        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const money = document.getElementById('meal_allowance');

        money.addEventListener('input', function() {
            const d = this.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
            this.value = d ? Number(d).toLocaleString('id-ID') : '';
        });
        form.addEventListener('submit', () => {
            money.value = money.value.replace(/\D/g, '');
            const b = form.querySelector('button[type=submit]'); b.disabled = true; b.textContent = 'Menyimpan';
        });

        function render() {
            const lv = Number((form.querySelector('input[name=level]:checked') || {}).value || 0);
            const name = document.getElementById('grading_name').value.trim() || 'Grading ini';
            const code = document.getElementById('grading_code').value.trim().toUpperCase();
            const others = LADDER.filter(g => g.id !== SELF);
            const rows = others.map(g => ({ ...g, me: false }));
            if (lv) rows.push({ grading_name: name, grading_code: code, level: lv, employees: null, me: true });
            rows.sort((a, b) => (a.level ?? 99) - (b.level ?? 99) || (a.me ? -1 : b.me ? 1 : 0));

            document.getElementById('gr-mini').innerHTML = rows.map(r => `
                <li class="${r.me ? 'is-me' : ''}"><span class="gr-mini-lv">${r.level ?? '-'}</span>
                    <span class="gr-mini-name">${esc(r.grading_name)}${r.grading_code ? ` <small>${esc(r.grading_code)}</small>` : ''}</span>
                    <span class="gr-mini-n">${r.me ? 'ini' : (r.employees ? r.employees + ' org' : '')}</span></li>`).join('');

            const chain = document.getElementById('gr-chain');
            if (!lv) { chain.textContent = 'Pilih level untuk melihat posisinya.'; return; }
            const above = others.filter(g => g.level !== null && g.level < lv).sort((a, b) => b.level - a.level)[0];
            const same = others.filter(g => g.level === lv).map(g => g.grading_name);
            const below = others.filter(g => g.level !== null && g.level > lv).sort((a, b) => a.level - b.level)[0];
            let t = above ? `Atasan otomatis dicari dari level ${above.level} ke atas, mulai ${above.grading_name}.` : 'Level tertinggi: tidak punya atasan otomatis.';
            if (same.length) t += ` Setara dengan ${same.join(', ')}, jadi tidak saling menjadi atasan.`;
            if (below) t += ` Menjadi atasan bagi ${below.grading_name} (level ${below.level}) dan di bawahnya.`;
            chain.textContent = t;
        }
        form.addEventListener('input', render);
        form.addEventListener('change', render);
        render();
    })();
</script>
