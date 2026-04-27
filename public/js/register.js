function updateFileName(input) {
    if (input.files && input.files[0]) {
        var fileName = input.files[0].name;
        var textEl = document.getElementById('file-name');
        if (textEl) textEl.textContent = fileName;
        // Also update the specific file-name span
        var parent = input.closest('.file-upload-wrapper');
        if (parent) {
            var textSpan = parent.querySelector('.file-upload-text');
            if (textSpan) textSpan.textContent = fileName;
        }
    }
}

function showForm() {
    const selected = document.getElementById('role-select').value;
    const formContainer = document.getElementById('registration-form');
    const hiddenRoleInput = document.getElementById('form-role-input');
    const jkGroupElements = document.querySelectorAll('.jk-group');
    const jkInputs = document.querySelectorAll('input[name="jenis_kelamin"]');

    if (!selected) return;

    formContainer.style.display = 'block';
    hiddenRoleInput.value = selected;

    // Show dynamic role profile fields
    document.querySelectorAll('.reg-profile-fields').forEach(function(el) {
        el.style.display = 'none';
    });
    const selectedProfileFields = document.querySelector('[data-reg-role="' + selected + '"]');
    if (selectedProfileFields) {
        selectedProfileFields.style.display = 'block';
    }

    // Show/hide keperluan fields based on whether role has them in dynamic columns
    const staticKeperluan = document.getElementById('static-keperluan-fields');
    const profileFields = selectedProfileFields ? selectedProfileFields.querySelectorAll('[name]') : [];
    const hasKeperluan = Array.from(profileFields).some(function(input) {
        return input.name === 'jenis_keperluan' || input.name === 'judul_keperluan';
    });

    if (hasKeperluan && staticKeperluan) {
        staticKeperluan.style.display = 'none';
    } else if (staticKeperluan) {
        staticKeperluan.style.display = 'block';
    }

    // Update labels based on role
    if (authTranslations.isOrganizationRole && authTranslations.isOrganizationRole.includes(selected)) {
        const nameLabel = document.getElementById('label-name');
        if (nameLabel) nameLabel.textContent = authTranslations.institutionName || 'Nama Instansi';
        const nameInput = document.getElementById('name');
        if (nameInput) nameInput.placeholder = authTranslations.institutionName || 'Nama Instansi';

        // Hide JK fields for instance
        jkGroupElements.forEach(function(el) { el.style.display = 'none'; });
        jkInputs.forEach(function(el) { el.required = false; });
    } else {
        const nameLabel = document.getElementById('label-name');
        if (nameLabel) nameLabel.textContent = authTranslations.fullName || 'Nama Lengkap';
        const nameInput = document.getElementById('name');
        if (nameInput) nameInput.placeholder = authTranslations.fullName || 'Nama Lengkap';

        // Show JK fields for umum/pelajar
        jkGroupElements.forEach(function(el) { el.style.display = 'contents'; });
        // Set required for JK only if the field exists in the form
        const jkRadio = document.querySelector('input[name="jenis_kelamin"]');
        if (jkRadio && selectedProfileFields) {
            const hasJk = Array.from(selectedProfileFields.querySelectorAll('[name="jenis_kelamin"]')).length > 0;
            jkInputs.forEach(function(el) {
                el.required = hasJk;
            });
        }
    }
}

// Restore form state if there was a validation error
document.addEventListener('DOMContentLoaded', function () {
    const oldRoleEl = document.getElementById('old-role-value');
    const oldRole = oldRoleEl ? oldRoleEl.value : '';
    if (oldRole) {
        const select = document.getElementById('role-select');
        if (select) select.value = oldRole;
        showForm();
    }
});
