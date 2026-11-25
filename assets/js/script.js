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

    // 3. Submit Button Loading Lock (Asynchronous lock to ensure form data submits correctly)
    document.querySelectorAll('form[data-loading-submit]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!form.checkValidity()) return;
            const btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                const label = btn.getAttribute('data-loading-text') || 'Memproses...';
                setTimeout(() => {
                    btn.disabled = true;
                    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> ${label}`;
                }, 0);
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
            labelLokasi.innerHTML = 'Lokasi Barang Ditemukan <span class="text-danger">*</span>';
            labelTanggal.innerHTML = 'Tanggal Ditemukan <span class="text-danger">*</span>';
            hintDeskripsi.textContent = 'Jelaskan ciri khusus barang dan sebutkan di mana barang disimpan / dapat diambil saat ini.';
        } else {
            labelLokasi.innerHTML = 'Lokasi Terakhir Terlihat <span class="text-danger">*</span>';
            labelTanggal.innerHTML = 'Tanggal Hilang <span class="text-danger">*</span>';
            hintDeskripsi.textContent = 'Jelaskan ciri khusus, warna, merek, atau isi barang agar mudah dikenali pemiliknya.';
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
            const allowedExts = ['jpg', 'jpeg', 'png'];
            const ext = file.name.split('.').pop().toLowerCase();
            const maxSize = 2 * 1024 * 1024; // 2 MB

            // Izinkan jika MIME type cocok ATAU ekstensi file valid (mengatasi Windows MIME kosong)
            const isValidFormat = allowedTypes.includes(file.type) || allowedExts.includes(ext);

            if (!isValidFormat) {
                inputFoto.value = '';
                previewWrap.classList.add('d-none');
                if (fotoError) {
                    fotoError.textContent = 'Format foto harus berupa JPG, JPEG, atau PNG.';
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
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check2"></i> Nomor Tersalin!';
                btn.classList.remove('btn-rf-primary');
                btn.classList.add('btn-success');
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-rf-primary');
                }, 2000);
            } catch (_) {
                // Fallback copy
                const tempInput = document.createElement('input');
                tempInput.value = text;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                btn.innerHTML = '<i class="bi bi-check2"></i> Tersalin!';
            }
        });
    });

    // 8. Action Confirmation
    document.querySelectorAll('[data-confirm]').forEach((el) => {
        el.addEventListener('click', (e) => {
            const msg = el.getAttribute('data-confirm');
            if (msg && !window.confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // 9. Landing Page "Cara Kerja" Motion (Fade-in + Light Parallax)
    const processSection = document.querySelector('.process-section');
    if (processSection) {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const revealEls = processSection.querySelectorAll('.process-reveal');

        if (!prefersReducedMotion) {
            document.documentElement.classList.add('js-motion-ready');
        }

        if (prefersReducedMotion || !('IntersectionObserver' in window)) {
            revealEls.forEach((el) => el.classList.add('is-visible'));
        } else {
            const revealObserver = new IntersectionObserver(
                (entries, observer) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                },
                { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
            );
            revealEls.forEach((el) => revealObserver.observe(el));
        }

        const parallaxEls = processSection.querySelectorAll('[data-parallax-speed]');
        if (!prefersReducedMotion && parallaxEls.length > 0) {
            let rafId = null;

            const updateParallax = () => {
                rafId = null;
                const rect = processSection.getBoundingClientRect();
                const viewportCenter = window.innerHeight * 0.5;
                if (rect.bottom < -120 || rect.top > window.innerHeight + 120) {
                    return;
                }
                const sectionCenter = rect.top + rect.height * 0.5;
                const delta = viewportCenter - sectionCenter;

                parallaxEls.forEach((el) => {
                    const speed = parseFloat(el.getAttribute('data-parallax-speed') || '0');
                    const rawOffset = delta * speed;
                    const clamped = Math.max(-18, Math.min(18, rawOffset));
                    el.style.setProperty('--parallax-y', `${clamped.toFixed(2)}px`);
                });
            };

            const onScrollOrResize = () => {
                if (rafId === null) {
                    rafId = window.requestAnimationFrame(updateParallax);
                }
            };

            updateParallax();
            window.addEventListener('scroll', onScrollOrResize, { passive: true });
            window.addEventListener('resize', onScrollOrResize, { passive: true });
        }
    }
});
