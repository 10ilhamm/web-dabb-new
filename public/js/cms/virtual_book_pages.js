let currentPageId = null;

function selectPage(pageId) {
    const url = new URL(window.location.href);
    url.searchParams.set('page_id', pageId);
    window.location.href = url;
}

function showAddForm() {
    document.getElementById('pageDisplay').style.display = 'none';
    document.getElementById('pageForm').style.display = 'block';
    document.getElementById('formTitle').textContent = 'Tambah Halaman Baru';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('virtualBookPageForm').action = pageData.storeUrl;

    document.getElementById('pageTitle').value = '';
    document.getElementById('pageContent').value = '';
    document.getElementById('pageOrder').value = pageData.maxOrder;
    document.getElementById('pageType').value = 'content';
    document.getElementById('imagePreviewContainer').style.display = 'none';
    document.getElementById('imagePreview').src = '';

    currentPageId = null;
}

function editPage(pageId) {
    const page = pageData.pages[pageId];

    if (!page) return;

    document.getElementById('pageDisplay').style.display = 'none';
    document.getElementById('pageForm').style.display = 'block';
    document.getElementById('formTitle').textContent = 'Edit Halaman';
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('virtualBookPageForm').action = pageData.updateUrl.replace('__PAGE_ID__', pageId);

    document.getElementById('pageTitle').value = page.title || '';
    document.getElementById('pageContent').value = page.content || '';
    document.getElementById('pageOrder').value = page.order;

    if (page.is_cover) {
        document.getElementById('pageType').value = 'cover';
    } else if (page.is_back_cover) {
        document.getElementById('pageType').value = 'back_cover';
    } else {
        document.getElementById('pageType').value = 'content';
    }

    if (page.image) {
        document.getElementById('imagePreview').src = '/storage/' + page.image;
        document.getElementById('imagePreviewContainer').style.display = 'block';
    } else {
        document.getElementById('imagePreviewContainer').style.display = 'none';
    }

    currentPageId = pageId;
}

function hideForm() {
    document.getElementById('pageForm').style.display = 'none';
    document.getElementById('pageDisplay').style.display = 'block';

    if (!currentPageId) {
        const url = new URL(window.location.href);
        url.searchParams.delete('page_id');
        window.history.pushState({}, '', url);
    }
}

function togglePageTypeFields() {
    // Additional logic if needed
}

// Image preview
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('pageImageInput');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreviewContainer').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
