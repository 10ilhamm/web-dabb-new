function sectionManager() {
    let _idCounter = 1;
    let _isGlobalSubmitting = false;

    // Data gambar disimpan di plain object, BUKAN di Alpine reactive state
    // Format: { id, preview, x, y, isExisting, path?, file? }
    const _imageStore = { add: [], edit: [] };
    const _files = {};

    function getNextId(prefix) {
        return prefix + '-' + Date.now() + '-' + (_idCounter++);
    }

    function parsePos(pos) {
        if (!pos || pos === 'center') return { x: 50, y: 50 };
        const parts = pos.split(' ');
        if (parts.length < 2) return { x: 50, y: 50 };
        return { x: parseFloat(parts[0]) || 50, y: parseFloat(parts[1]) || 50 };
    }

    // ===== DOM RENDERING =====
    // Render semua gambar preview ke container via vanilla DOM
    // Ini sepenuhnya di luar Alpine — tidak ada x-for, tidak ada x-data nested
    function renderPreviews(mode) {
        const containerId = mode === 'add' ? 'add-image-previews' : 'edit-image-previews';
        const container = document.getElementById(containerId);
        if (!container) return;

        // Kosongkan container
        container.innerHTML = '';

        const images = _imageStore[mode];
        if (!images.length) return;

        // Label
        if (mode === 'edit') {
            const label = document.createElement('label');
            label.className = 'block text-sm font-medium text-gray-700 mb-2';
            label.textContent = 'Gambar (Geser untuk menyesuaikan posisi)';
            container.appendChild(label);
        }

        const cols = Math.min(Math.max(images.length, 1), 4);
        // Guest container in desktop is ~1140px. 
        // With a 10px gap, the guest item width is approximately:
        const guestItemWidth = (1140 - ((cols - 1) * 10)) / cols;
        const guestItemHeight = 180;
        const guestAspectRatio = guestItemWidth / guestItemHeight;

        images.forEach((img) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'relative group mb-4 w-full';
            wrapper.dataset.imgId = img.id;

            // Drag container
            const dragBox = document.createElement('div');
            dragBox.className = 'relative overflow-hidden rounded-lg bg-gray-900 cursor-crosshair w-full flex items-center justify-center';
            // Match the precise aspect ratio computed for guest view on desktop
            dragBox.style.width = '100%';
            dragBox.style.aspectRatio = guestAspectRatio.toString();

            // Image
            const imgEl = document.createElement('img');
            imgEl.src = img.preview;
            imgEl.className = 'absolute pointer-events-none select-none w-full h-full object-cover transition-transform duration-300';
            imgEl.style.objectPosition = img.x + '% ' + img.y + '%';
            dragBox.appendChild(imgEl);

            // Focal point
            const focal = document.createElement('div');
            focal.className = 'absolute w-6 h-6 border-2 border-white rounded-full shadow-lg pointer-events-none flex items-center justify-center';
            focal.style.cssText = 'background-color:rgba(59,130,246,0.6);transform:translate(-50%,-50%); transition: opacity 0.2s;';
            focal.style.left = img.x + '%';
            focal.style.top = img.y + '%';
            const dot = document.createElement('div');
            dot.className = 'w-1.5 h-1.5 bg-white rounded-full';
            focal.appendChild(dot);
            dragBox.appendChild(focal);

            // Drag handler
            dragBox.addEventListener('mousedown', function(e) {
                if (e.target.closest('button')) return;
                e.preventDefault();
                const updatePos = (ev) => {
                    const rect = dragBox.getBoundingClientRect();
                    if (rect.width === 0) return;
                    const px = parseFloat(Math.max(0, Math.min(100, ((ev.clientX - rect.left) / rect.width) * 100)).toFixed(2));
                    const py = parseFloat(Math.max(0, Math.min(100, ((ev.clientY - rect.top) / rect.height) * 100)).toFixed(2));
                    imgEl.style.objectPosition = px + '% ' + py + '%';
                    focal.style.left = px + '%';
                    focal.style.top = py + '%';
                    img.x = px;
                    img.y = py;
                };
                const stop = () => {
                    window.removeEventListener('mousemove', updatePos);
                    window.removeEventListener('mouseup', stop);
                };
                window.addEventListener('mousemove', updatePos);
                window.addEventListener('mouseup', stop);
                updatePos(e);
            });

            wrapper.appendChild(dragBox);

            // Delete button
            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'absolute bg-red-500 text-white rounded-full flex items-center justify-center shadow-md hover:bg-red-600 transition-colors cursor-pointer z-50';
            delBtn.style.cssText = 'width:24px;height:24px;top:-8px;right:-8px;line-height:1';
            delBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
            delBtn.addEventListener('mousedown', (e) => e.stopPropagation());
            delBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                // Hapus dari store
                const idx = _imageStore[mode].findIndex(i => i.id === img.id);
                if (idx !== -1) _imageStore[mode].splice(idx, 1);
                delete _files[img.id];
                
                // Re-render semua untuk menghitung ulang grid cols
                renderPreviews(mode);
            });
            wrapper.appendChild(delBtn);

            container.appendChild(wrapper);
        });
    }

    return {
        addSection: { open: false },
        editSection: { open: false, id: null, title: '', description: '', order: 0 },
        deleteSection: { open: false, id: null, name: '' },
        imageModal: { open: false, src: '' },
        isSubmitting: false,

        openAddSection() {
            _imageStore.add = [];
            Object.keys(_files).forEach(k => delete _files[k]);
            this.addSection.open = true;
            this.$nextTick(() => renderPreviews('add'));
        },

        openEditSection(id, title, description, order, images, positions) {
            this.editSection.id = id;
            this.editSection.title = title;
            this.editSection.description = description;
            this.editSection.order = order;

            _imageStore.edit = [];
            Object.keys(_files).forEach(k => delete _files[k]);

            if (images && images.length) {
                images.forEach((path, i) => {
                    const pos = parsePos(positions ? positions[i] : null);
                    _imageStore.edit.push({
                        id: getNextId('ext'),
                        path: path,
                        preview: (window.StorageUrl || '/storage') + '/' + path,
                        x: pos.x,
                        y: pos.y,
                        isExisting: true
                    });
                });
            }

            this.editSection.open = true;
            this.$nextTick(() => renderPreviews('edit'));
        },

        openDeleteSection(id, name) {
            this.deleteSection.id = id;
            this.deleteSection.name = name;
            this.deleteSection.open = true;
        },

        openImageModal(src) {
            this.imageModal.src = src;
            this.imageModal.open = true;
        },

        async handleFileChange(event, mode) {
            const files = Array.from(event.target.files);

            // Get the original file type
            const getOriginalType = (file) => {
                if (file.type === 'image/png') return 'image/png';
                if (file.type === 'image/gif') return 'image/gif';
                return 'image/jpeg';
            };

            const compressImage = (file) => {
                return new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {
                            const canvas = document.createElement('canvas');
                            let width = img.width;
                            let height = img.height;
                            const MAX_SIZE = 1280;

                            if (width > height && width > MAX_SIZE) {
                                height *= MAX_SIZE / width;
                                width = MAX_SIZE;
                            } else if (height > MAX_SIZE) {
                                width *= MAX_SIZE / height;
                                height = MAX_SIZE;
                            }

                            canvas.width = width;
                            canvas.height = height;
                            const ctx = canvas.getContext('2d');

                            // Fill with white background first (fixes transparency issue)
                            ctx.fillStyle = '#FFFFFF';
                            ctx.fillRect(0, 0, width, height);

                            // Draw the image
                            ctx.drawImage(img, 0, 0, width, height);

                            // Use original format instead of WebP to avoid issues
                            const originalType = getOriginalType(file);
                            const quality = originalType === 'image/png' ? undefined : 0.9;

                            canvas.toBlob((blob) => {
                                const ext = originalType === 'image/png' ? 'png' : 'jpg';
                                const originalName = file.name.replace(/\.[^/.]+$/, "");
                                const newFile = new File([blob], originalName + "." + ext, {
                                    type: originalType,
                                    lastModified: Date.now()
                                });
                                resolve({ file: newFile, preview: e.target.result });
                            }, originalType, quality);
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            };

            for (const file of files) {
                const compressed = await compressImage(file);
                const imgId = getNextId('new');
                _files[imgId] = compressed.file;
                
                _imageStore[mode].push({
                    id: imgId,
                    preview: compressed.preview,
                    x: 50,
                    y: 50,
                    isExisting: false
                });
            }
            
            renderPreviews(mode);
            event.target.value = '';
        },

        submitForm(event, mode) {
            event.preventDefault();
            if (_isGlobalSubmitting) return;
            _isGlobalSubmitting = true;

            const form = event.target;
            const images = _imageStore[mode];

            // Bersihkan input dinamis sebelumnya (jika ada)
            form.querySelectorAll('.dynamic-input-remove').forEach(el => el.remove());

            // Buat DataTransfer untuk memindahkan file BLOB image hasil kompresi ke input[type=file]
            const dt = new DataTransfer();

            images.forEach(img => {
                const posInput = document.createElement('input');
                posInput.type = 'hidden';
                posInput.name = 'image_positions[]';
                posInput.value = img.x + '% ' + img.y + '%';
                posInput.className = 'dynamic-input-remove';
                form.appendChild(posInput);

                if (img.isExisting) {
                    const existInput = document.createElement('input');
                    existInput.type = 'hidden';
                    existInput.name = 'existing_images[]';
                    existInput.value = img.path;
                    existInput.className = 'dynamic-input-remove';
                    form.appendChild(existInput);
                } else if (_files[img.id]) {
                    dt.items.add(_files[img.id]);
                }
            });

            // Tambahkan input file hidden untuk menampung file BLOB
            if (dt.files.length > 0) {
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.name = 'images[]';
                fileInput.multiple = true;
                fileInput.files = dt.files;
                fileInput.className = 'hidden dynamic-input-remove';
                form.appendChild(fileInput);
            }

            // Nonaktifkan file input default gambar pada form agar tidak terkirim dobel
            form.querySelectorAll('input[type="file"]:not(.dynamic-input-remove)').forEach(input => {
                input.disabled = true;
            });

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Menyimpan...';

            // Kirim secara normal ke backend (bukan via AJAX)
            // Ini akan memicu redirect bawaan Laravel yang memuat ulang halaman dengan pesan Alert Flash
            form.submit();
        }
    };
}
