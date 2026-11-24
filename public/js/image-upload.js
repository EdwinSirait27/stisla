/*
 * Kompres & bersihkan foto di browser sebelum diunggah.
 *
 * Berlaku otomatis untuk setiap <input type="file"> di halaman: saat file dipilih, gambar
 * JPG/PNG/WEBP digambar ulang di canvas (sisi terpanjang maks. 1600 px, JPEG), lalu file di input
 * diganti dengan hasilnya. Menggambar ulang sekaligus membuang EXIF (lokasi GPS, model HP) dan
 * data apa pun yang disisipkan di luar gambar. File lain (PDF, Excel, Word, GIF) tidak disentuh.
 *
 * Atribut opsional di input:
 *   data-max-kb="512"     turunkan kualitas sampai muat (batas server); kalau tetap tidak muat, file dibiarkan
 *                         dan server yang menolak dengan pesannya sendiri.
 *   data-keep-alpha       simpan sebagai PNG supaya latar transparan tetap (tanda tangan, logo).
 *   data-no-compress      lewati input ini.
 *
 * Untuk file yang tidak lewat input (drag & drop): HRXImage.compressFiles(fileList, { maxKb, keepAlpha }).
 *
 * Server tetap mengompres ulang (App\Services\ImageCompressor), jadi ini untuk mempercepat upload
 * dari HP dan supaya foto kamera tidak ditolak batas ukuran. Kalau browser tidak mendukung, file asli dipakai.
 */
(function () {
    'use strict';

    var MAX_SIDE = 1600;
    var QUALITY = 0.82;
    var MIN_QUALITY = 0.5;
    var IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    var pending = new Set();        // kompresi yang sedang berjalan (submit ditahan sampai selesai)
    var ours = new WeakSet();       // event change yang kita kirim ulang, jangan diproses lagi

    var supported = typeof window.DataTransfer === 'function'
        && typeof HTMLCanvasElement !== 'undefined'
        && typeof HTMLCanvasElement.prototype.toBlob === 'function';

    function isImage(file) {
        return file && IMAGE_TYPES.indexOf(file.type) !== -1;
    }

    /** Nama file aman: tanpa path, tanpa karakter aneh, ekstensi sesuai isi. */
    function safeName(name, ext) {
        var base = String(name || 'foto').split(/[\\/]/).pop().replace(/\.[^.]*$/, '');
        base = base.normalize ? base.normalize('NFKD') : base;
        base = base.replace(/[^\w\- ]+/g, '').replace(/\s+/g, '-').replace(/^[-_.]+/, '').slice(0, 60);
        return (base || 'foto') + '.' + ext;
    }

    function loadImage(file) {
        // createImageBitmap menerapkan orientasi EXIF (foto HP tidak miring)
        if (typeof window.createImageBitmap === 'function') {
            return window.createImageBitmap(file, { imageOrientation: 'from-image' }).catch(function () {
                return window.createImageBitmap(file);
            });
        }
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () { URL.revokeObjectURL(url); resolve(img); };
            img.onerror = function () { URL.revokeObjectURL(url); reject(new Error('Gambar tidak bisa dibaca')); };
            img.src = url;
        });
    }

    function toBlob(canvas, type, quality) {
        return new Promise(function (resolve) { canvas.toBlob(resolve, type, quality); });
    }

    /** Satu file → File baru yang sudah dikompres, atau file asli kalau bukan gambar / gagal / tidak lebih kecil. */
    async function compressFile(file, opts) {
        opts = opts || {};
        if (!supported || !isImage(file)) return file;

        var maxBytes = opts.maxKb ? opts.maxKb * 1024 : 0;
        var keepAlpha = !!opts.keepAlpha && file.type !== 'image/jpeg';

        try {
            var img = await loadImage(file);
            var w = img.width, h = img.height;
            if (!w || !h) return file;

            var scale = Math.min(1, MAX_SIDE / Math.max(w, h));
            var canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(w * scale));
            canvas.height = Math.max(1, Math.round(h * scale));
            var ctx = canvas.getContext('2d');
            if (!keepAlpha) {
                // JPEG tidak punya transparansi: latar putih, sama dengan server
                ctx.fillStyle = '#fff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            if (img.close) img.close();

            var type = keepAlpha ? 'image/png' : 'image/jpeg';
            var blob = await toBlob(canvas, type, QUALITY);

            // Masih di atas batas: turunkan kualitas JPEG, lalu ukuran, sampai muat
            for (var q = QUALITY - 0.1, tries = 0; blob && maxBytes && blob.size > maxBytes && tries < 6; tries++) {
                if (!keepAlpha && q >= MIN_QUALITY) {
                    blob = await toBlob(canvas, type, q);
                    q -= 0.1;
                } else {
                    var c2 = document.createElement('canvas');
                    c2.width = Math.max(1, Math.round(canvas.width * 0.8));
                    c2.height = Math.max(1, Math.round(canvas.height * 0.8));
                    var x2 = c2.getContext('2d');
                    if (!keepAlpha) { x2.fillStyle = '#fff'; x2.fillRect(0, 0, c2.width, c2.height); }
                    x2.drawImage(canvas, 0, 0, c2.width, c2.height);
                    canvas = c2;
                    blob = await toBlob(canvas, type, keepAlpha ? undefined : MIN_QUALITY);
                }
            }

            if (!blob) return file;
            // Hasil lebih besar dari asli (foto kecil yang sudah terkompres): tetap pakai hasil
            // kalau asli melewati batas, kalau tidak pakai yang asli
            if (blob.size >= file.size && scale === 1 && (!maxBytes || file.size <= maxBytes)) return file;

            return new File([blob], safeName(file.name, keepAlpha ? 'png' : 'jpg'), {
                type: type,
                lastModified: Date.now(),
            });
        } catch (e) {
            if (window.console) console.warn('[image-upload] kompresi dilewati:', e);
            return file;
        }
    }

    async function compressFiles(fileList, opts) {
        var files = Array.prototype.slice.call(fileList || []);
        var out = [];
        for (var i = 0; i < files.length; i++) out.push(await compressFile(files[i], opts));
        return out;
    }

    function optionsFor(input) {
        var kb = parseInt(input.getAttribute('data-max-kb'), 10);
        return { maxKb: kb > 0 ? kb : 0, keepAlpha: input.hasAttribute('data-keep-alpha') };
    }

    function track(promise) {
        pending.add(promise);
        promise.then(function () { pending.delete(promise); }, function () { pending.delete(promise); });
        return promise;
    }

    // Tangkap "change" lebih dulu (fase capture), kompres, ganti isi input, lalu kirim ulang "change"
    // supaya pratinjau dan validasi bawaan halaman melihat file yang sudah dikompres.
    document.addEventListener('change', function (e) {
        var input = e.target;
        if (ours.has(e) || !supported) return;
        if (!(input instanceof HTMLInputElement) || input.type !== 'file' || input.hasAttribute('data-no-compress')) return;
        var files = Array.prototype.slice.call(input.files || []);
        if (!files.some(isImage)) return;

        e.stopImmediatePropagation();
        input.setAttribute('aria-busy', 'true');

        track(compressFiles(files, optionsFor(input)).then(function (out) {
            var dt = new DataTransfer();
            out.forEach(function (f) { dt.items.add(f); });
            input.files = dt.files;
        }).finally(function () {
            input.removeAttribute('aria-busy');
            var ev = new Event('change', { bubbles: true });
            ours.add(ev);
            input.dispatchEvent(ev);
            // Handler jQuery inline (onchange="...") ikut terpanggil oleh dispatchEvent
        }));
    }, true);

    // Submit form biasa saat kompresi belum selesai: tahan, lalu kirim setelah selesai
    document.addEventListener('submit', function (e) {
        if (!pending.size) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        var form = e.target, submitter = e.submitter;
        Promise.allSettled(Array.from(pending)).then(function () {
            if (form.requestSubmit) form.requestSubmit(submitter && submitter.form === form ? submitter : undefined);
            else form.submit();
        });
    }, true);

    window.HRXImage = {
        compressFile: compressFile,
        compressFiles: compressFiles,
        /** Tunggu semua kompresi selesai (untuk upload lewat AJAX/FormData). */
        ready: function () { return Promise.allSettled(Array.from(pending)); },
    };
})();
