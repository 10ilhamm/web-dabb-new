// Restore form state if there was a validation error
document.addEventListener('DOMContentLoaded', function () {
    var oldRoleEl = document.getElementById('old-role-value');
    var oldRole = oldRoleEl ? oldRoleEl.value : '';
    if (oldRole) {
        var select = document.getElementById('role-select');
        if (select) select.value = oldRole;
        showForm();
    }
});

function updateFileName(input) {
    if (input.files && input.files[0]) {
        var fileName = input.files[0].name;
        var parent = input.closest('.file-upload-wrapper');
        if (parent) {
            var textSpan = parent.querySelector('.file-upload-text');
            if (textSpan) textSpan.textContent = fileName;
        }
    }
}

function showForm() {
    var selected = document.getElementById('role-select').value;
    var formContainer = document.getElementById('registration-form');
    var hiddenRoleInput = document.getElementById('form-role-input');

    if (!selected) return;

    formContainer.style.display = 'block';
    hiddenRoleInput.value = selected;

    // Show dynamic role profile fields
    var allProfileFields = document.querySelectorAll('.reg-profile-fields');
    for (var m = 0; m < allProfileFields.length; m++) {
        var container = allProfileFields[m];
        if (container.getAttribute('data-reg-role') === selected) {
            // Show and ENABLE selected role's fields
            container.style.display = 'block';
            enableFieldsInContainer(container);
        } else {
            // Hide and DISABLE other roles' fields
            container.style.display = 'none';
            disableFieldsInContainer(container);
        }
    }

    // Update labels based on role
    var isOrgRole = authTranslations.isOrganizationRole && authTranslations.isOrganizationRole.indexOf(selected) !== -1;
    if (isOrgRole) {
        var nameLabel = document.getElementById('label-name');
        if (nameLabel) nameLabel.textContent = authTranslations.institutionName || 'Nama Instansi';
        var nameInput = document.getElementById('name');
        if (nameInput) nameInput.placeholder = authTranslations.institutionName || 'Nama Instansi';
    } else {
        var nameLabel2 = document.getElementById('label-name');
        if (nameLabel2) nameLabel2.textContent = authTranslations.fullName || 'Nama Lengkap';
        var nameInput2 = document.getElementById('name');
        if (nameInput2) nameInput2.placeholder = authTranslations.fullName || 'Nama Lengkap';
    }
}

/**
 * Disable all form fields in a container (EXCEPT file inputs).
 * File inputs are NOT disabled because disabling them clears their value
 * and makes them impossible to re-activate for file selection.
 * Instead, file inputs in hidden containers are excluded from form submission.
 */
function disableFieldsInContainer(container) {
    var fields = container.querySelectorAll('input:not([type="file"]), select, textarea');
    for (var i = 0; i < fields.length; i++) {
        fields[i].disabled = true;
    }

    // For file inputs: remove 'required' and clear value so they don't interfere
    var fileInputs = container.querySelectorAll('input[type="file"]');
    for (var j = 0; j < fileInputs.length; j++) {
        fileInputs[j].removeAttribute('required');
        // Do NOT disable file inputs — disabling clears the file value
        // and makes file selection impossible to re-activate in some browsers
    }
}

/**
 * Enable all form fields in a container (EXCLUDING file inputs from disabling).
 * File inputs remain always-enabled; we only restore their required state here.
 */
function enableFieldsInContainer(container) {
    var fields = container.querySelectorAll('input:not([type="file"]), select, textarea');
    for (var i = 0; i < fields.length; i++) {
        fields[i].disabled = false;
    }

    // Restore 'required' on file inputs so they validate when this role is active
    var fileInputs = container.querySelectorAll('input[type="file"]');
    for (var j = 0; j < fileInputs.length; j++) {
        var wasRequired = fileInputs[j].getAttribute('data-was-required');
        if (wasRequired === 'true') {
            fileInputs[j].setAttribute('required', 'required');
        }
    }
}

/**
 * File upload button handler — scoped to the button's own container
 * so it always targets the correct file input even when IDs are duplicated.
 */
function triggerFileUpload(btn) {
    var wrapper = btn.closest('.reg-profile-fields');
    var fileInput;
    if (wrapper) {
        fileInput = wrapper.querySelector('input[type="file"]');
    }
    if (!fileInput) {
        fileInput = document.getElementById(btn.getAttribute('data-target'));
    }
    if (fileInput) {
        fileInput.click();
    }
}

/**
 * Update file name display after file selection.
 * Called from the file input's onchange attribute.
 */
function onFileSelected(input) {
    var wrapper = input.closest('.reg-profile-fields');
    var textSpan;
    if (wrapper) {
        textSpan = wrapper.querySelector('.file-upload-text');
    }
    if (!textSpan) {
        textSpan = document.getElementById('file-name-' + input.name);
    }
    if (textSpan) {
        textSpan.textContent = input.files && input.files[0]
            ? input.files[0].name
            : (window.authTranslations && window.authTranslations.noFile ? window.authTranslations.noFile : 'Tidak ada file');
    }
}
