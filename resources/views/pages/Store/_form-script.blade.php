{{-- Script form lokasi: peta Leaflet untuk titik & radius geofence, tempel "lat, lng" dari Google Maps. --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
    (function() {
        const form = document.getElementById('st-form');
        const latEl = document.getElementById('latitude'), lngEl = document.getElementById('longitude'), radEl = document.getElementById('geofence_radius');
        const state = document.getElementById('geo-state');
        const DEFAULT = [-8.65, 115.2167]; // Denpasar, hanya titik awal peta
        const num = el => { const v = parseFloat(String(el.value).replace(',', '.')); return Number.isFinite(v) ? v : null; };

        // Tempel "lat, lng" sekaligus
        latEl.addEventListener('paste', e => {
            const t = (e.clipboardData || window.clipboardData).getData('text');
            const m = t.match(/(-?\d{1,2}\.\d+)\s*[,; ]\s*(-?\d{1,3}\.\d+)/);
            if (m) { e.preventDefault(); latEl.value = m[1]; lngEl.value = m[2]; sync(true); }
        });

        if (typeof L === 'undefined') { state.textContent = 'Peta tidak bisa dimuat. Isi latitude dan longitude secara manual.'; return; }

        const start = num(latEl) !== null && num(lngEl) !== null ? [num(latEl), num(lngEl)] : null;
        const map = L.map('st-map', { scrollWheelZoom: false }).setView(start || DEFAULT, start ? 17 : 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
        const accent = getComputedStyle(document.documentElement).getPropertyValue('--pr-accent').trim() || 'currentColor';
        let marker = null, circle = null;

        function place(latlng, pan) {
            if (!marker) {
                marker = L.marker(latlng, { draggable: true, keyboard: true, title: 'Titik lokasi' }).addTo(map);
                marker.on('drag', ev => { write(ev.target.getLatLng()); circle.setLatLng(ev.target.getLatLng()); });
                circle = L.circle(latlng, { radius: Number(radEl.value) || 100, color: accent, weight: 2, fillOpacity: 0.12 }).addTo(map);
            }
            marker.setLatLng(latlng); circle.setLatLng(latlng);
            if (pan) map.setView(latlng, Math.max(map.getZoom(), 17));
            label();
        }
        function write(ll) { latEl.value = ll.lat.toFixed(7); lngEl.value = ll.lng.toFixed(7); label(); }
        function label() {
            const r = Number(radEl.value) || 0;
            state.textContent = marker ? `Titik terpilih, radius ${r.toLocaleString('id-ID')} meter. Karyawan di luar lingkaran ditolak saat absen.` : 'Belum ada titik: karyawan yang wajib absen di lokasi akan selalu ditolak.';
        }
        function sync(pan) {
            const la = num(latEl), lo = num(lngEl);
            if (la !== null && lo !== null && Math.abs(la) <= 90 && Math.abs(lo) <= 180) place([la, lo], pan);
            if (circle) circle.setRadius(Number(radEl.value) || 0);
            label();
        }

        map.on('click', ev => { write(ev.latlng); place(ev.latlng, false); });
        latEl.addEventListener('change', () => sync(true));
        lngEl.addEventListener('change', () => sync(true));
        radEl.addEventListener('input', () => sync(false));
        if (start) place(start, false); else label();

        form.addEventListener('submit', () => { const b = form.querySelector('button[type=submit]'); b.disabled = true; b.textContent = 'Menyimpan'; });
    })();
</script>
