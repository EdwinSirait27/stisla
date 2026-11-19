{{--
    Pemilih permission untuk create/edit role.
    Butuh: $groupedPermissions (Collection grup → permissions), $rolePermissions (array id string, kondisi awal)
    Field yang dikirim: permissions[] (id permission)
--}}
@php
    $selected = collect(old('permissions', $rolePermissions))->map(fn($id) => (string) $id)->all();
    $original = collect($rolePermissions)->map(fn($id) => (string) $id)->values()->all();
    $total = $groupedPermissions->flatten()->count();
@endphp
<section class="pr-panel" aria-labelledby="picker-title">
    <div class="pr-panel-head">
        <div>
            <h2 class="pr-panel-title" id="picker-title">Permission</h2>
            <span class="pr-panel-sub"><span id="pk-count">{{ count($selected) }}</span> dari {{ $total }} dipilih</span>
        </div>
        <div class="pk-changes" id="pk-changes" aria-live="polite"></div>
    </div>

    <div class="pr-tablebar">
        <div class="pr-tablebar-group">
            <label class="visually-hidden sr-only" for="pk-search">Cari permission</label>
            <span class="pr-search">
                <i class="fas fa-search" aria-hidden="true"></i>
                <input type="search" id="pk-search" placeholder="Cari permission" autocomplete="off">
            </span>
            <label class="pk-toggle">
                <input type="checkbox" id="pk-only-selected"> Hanya yang dipilih
            </label>
        </div>
        <div class="pr-tablebar-group">
            <button type="button" class="pr-btn pr-btn-sm pr-btn-ghost" id="pk-reset" @disabled(empty($original))>Kembalikan</button>
            <button type="button" class="pr-btn pr-btn-sm pr-btn-ghost" id="pk-clear">Kosongkan</button>
        </div>
    </div>

    @error('permissions')
        <p class="pr-error-text" style="padding: 8px 24px 0;">{{ $message }}</p>
    @enderror

    <div class="pk-groups" id="pk-groups">
        @foreach ($groupedPermissions as $group => $perms)
            @php $groupId = 'pk-g-' . \Illuminate\Support\Str::slug($group); @endphp
            <fieldset class="pk-group" data-group="{{ $group }}">
                <legend class="pk-group-head">
                    <span class="pk-group-name">{{ $group === 'format lama' ? 'Format lama (tanpa titik)' : $group }}</span>
                    <span class="pk-group-count"><span class="js-group-count">0</span>/{{ $perms->count() }}</span>
                    <button type="button" class="pr-link js-group-toggle">Pilih semua</button>
                </legend>
                <div class="pk-grid">
                    @foreach ($perms as $permission)
                        <label class="pk-item" data-name="{{ strtolower($permission->name) }}">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                @checked(in_array((string) $permission->id, $selected, true))>
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
        <p class="pr-empty-note" id="pk-empty" hidden>Tidak ada permission yang cocok.</p>
    </div>
</section>

@push('scripts')
    <script>
        (function() {
            const original = new Set(@json($original));
            const boxes = Array.from(document.querySelectorAll('.pk-item input'));
            const search = document.getElementById('pk-search');
            const onlySel = document.getElementById('pk-only-selected');

            function refresh() {
                const q = search.value.trim().toLowerCase();
                let visibleAny = false;
                document.querySelectorAll('.pk-group').forEach(group => {
                    const items = group.querySelectorAll('.pk-item');
                    let visible = 0, checked = 0;
                    items.forEach(item => {
                        const box = item.querySelector('input');
                        if (box.checked) checked++;
                        const show = (!q || item.dataset.name.includes(q)) && (!onlySel.checked || box.checked);
                        item.hidden = !show;
                        if (show) visible++;
                    });
                    group.querySelector('.js-group-count').textContent = checked;
                    group.querySelector('.js-group-toggle').textContent = checked === items.length ? 'Lepas semua' : 'Pilih semua';
                    group.hidden = visible === 0;
                    if (visible) visibleAny = true;
                });
                document.getElementById('pk-empty').hidden = visibleAny;

                const now = new Set(boxes.filter(b => b.checked).map(b => b.value));
                document.getElementById('pk-count').textContent = now.size;
                const added = [...now].filter(v => !original.has(v)).length;
                const removed = [...original].filter(v => !now.has(v)).length;
                const parts = [];
                if (added) parts.push(`<span class="pk-chip is-add">+${added} ditambah</span>`);
                if (removed) parts.push(`<span class="pk-chip is-remove">&minus;${removed} dihapus</span>`);
                document.getElementById('pk-changes').innerHTML = parts.join('');
                window.pkSummary = { total: now.size, added, removed };
            }

            boxes.forEach(b => b.addEventListener('change', refresh));
            search.addEventListener('input', refresh);
            onlySel.addEventListener('change', refresh);
            document.querySelectorAll('.js-group-toggle').forEach(btn => btn.addEventListener('click', () => {
                const items = Array.from(btn.closest('.pk-group').querySelectorAll('.pk-item:not([hidden]) input'));
                const allOn = items.every(b => b.checked);
                items.forEach(b => b.checked = !allOn);
                refresh();
            }));
            document.getElementById('pk-clear').addEventListener('click', () => { boxes.forEach(b => b.checked = false); refresh(); });
            document.getElementById('pk-reset').addEventListener('click', () => { boxes.forEach(b => b.checked = original.has(b.value)); refresh(); });
            refresh();
        })();
    </script>
@endpush
