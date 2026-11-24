document.addEventListener('DOMContentLoaded', () => {
    // 1. Image Skeleton Shimmer Removal
    document.querySelectorAll('.img-shimmer-wrap img').forEach((img) => {
        const wrap = img.closest('.img-shimmer-wrap');
        const markLoaded = () => wrap && wrap.classList.add('is-loaded');
        if (img.complete) {
            markLoaded();
        } else {
            img.addEventListener('load', markLoaded);
            img.addEventListener('error', markLoaded);
        }
    });

    // 2. Catalog Search & Filter Skeleton Placeholder
    const searchForm = document.getElementById('form-pencarian-barang');
    const gridBarang = document.getElementById('grid-daftar-barang');
    if (searchForm && gridBarang) {
        searchForm.addEventListener('submit', () => {
            const skeletonCard = `
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card-surface p-3 h-100">
                        <div class="skeleton-shimmer skeleton-img mb-3"></div>
                        <div class="skeleton-shimmer skeleton-line short mb-2"></div>
                        <div class="skeleton-shimmer skeleton-line mb-2"></div>
                        <div class="skeleton-shimmer skeleton-line short"></div>
                    </div>
                </div>`;
            gridBarang.innerHTML = skeletonCard.repeat(6);
        });
    }

    // 3. Submit Button Loading Lock
    document.querySelectorAll('form[data-loading-submit]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!form.checkValidity()) return;
            const btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                const label = btn.getAttribute('data-loading-text') || 'Memproses...';
                btn.innerHTML = `<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> ${label}`;
            }
        });
    });

    // 4. Toggle Password Visibility
    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-toggle-password');
            const input = document.getElementById(targetId);
            const icon = btn.querySelector('i');
            if (!input) return;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            if (icon) {
                icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
            }
        });
    });

    // 5. Dynamic Label Form Laporan (Hilang vs Temuan)
    const jenisRadios = document.querySelectorAll('input[name="jenis_laporan"]');
    const labelLokasi = document.getElementById('label-lokasi');
    const labelTanggal = document.getElementById('label-tanggal');
    const hintDeskripsi = document.getElementById('hint-deskripsi');

    const updateFormLabels = (jenis) => {
        if (!labelLokasi || !labelTanggal || !hintDeskripsi) return;
        if (jenis === 'temuan') {
            labelLokasi.textContent = 'Lokasi Barang Ditemukan';
            labelTanggal.textContent = 'Tanggal Ditemukan';
            hintDeskripsi.textContent = 'Jelaskan ciri barang dan sebutkan tempat barang disimpan saat ini.';
        } else {
            labelLokasi.textContent = 'Lokasi Terakhir Terlihat';
            labelTanggal.textContent = 'Tanggal Hilang';
            hintDeskripsi.textContent = 'Jelaskan ciri khusus, warna, merek, atau isi barang agar mudah dikenali.';
        }
    };

    jenisRadios.forEach((radio) => {
        if (radio.checked) updateFormLabels(radio.value);
        radio.addEventListener('change', (e) => updateFormLabels(e.target.value));
    });

    // 6. Photo Upload Validation & Live Preview (JPG/PNG, Max 2 MB)
    const inputFoto = document.getElementById('input-foto');
    const previewWrap = document.getElementById('preview-foto-wrap');
    const previewImg = document.getElementById('preview-foto-img');
    const fotoError = document.getElementById('foto-error-msg');

    if (inputFoto && previewWrap && previewImg) {
        inputFoto.addEventListener('change', () => {
            if (fotoError) fotoError.classList.add('d-none');
            const file = inputFoto.files[0];
            if (!file) {
                previewWrap.classList.add('d-none');
                return;
            }
            const allowedTypes = ['image/jpeg', 'image/png'];
            const maxSize = 2 * 1024 * 1024; // 2 MB

            if (!allowedTypes.includes(file.type)) {
                inputFoto.value = '';
                previewWrap.classList.add('d-none');
                if (fotoError) {
                    fotoError.textContent = 'Format foto harus JPG atau PNG.';
                    fotoError.classList.remove('d-none');
                }
                return;
            }

            if (file.size > maxSize) {
                inputFoto.value = '';
                previewWrap.classList.add('d-none');
                if (fotoError) {
                    fotoError.textContent = 'Ukuran foto maksimal 2 MB.';
                    fotoError.classList.remove('d-none');
                }
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                previewWrap.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }

    // 7. Copy Contact Helper (Detail Barang)
    document.querySelectorAll('[data-copy-text]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const text = btn.getAttribute('data-copy-text');
            if (!text) return;
            try {
                await navigator.clipboard.writeText(text);
                const original = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check2"></i> Tersalin';
                setTimeout(() => { btn.innerHTML = original; }, 1800);
            } catch (_) {}
        });
    });

    // 8. Admin Action Confirmation
    document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('click', (e) => {
            const msg = el.getAttribute('data-confirm');
            if (msg && !window.confirm(msg)) {
                e.preventDefault();
            }
        });
    });
});
