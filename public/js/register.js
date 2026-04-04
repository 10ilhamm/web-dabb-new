function updateFileName(input) {
    const fileName = input.files[0] ? input.files[0].name : authTranslations.noFile;
    document.getElementById('file-name').textContent = fileName;
}

function showForm() {
    const selected = document.getElementById('role-select').value;
    const formContainer = document.getElementById('registration-form');
    const hiddenRoleInput = document.getElementById('form-role-input');
    const jkGroupElements = document.querySelectorAll('.jk-group');
    const jkInputs = document.querySelectorAll('input[name="jenis_kelamin"]');

    if (!selected) return;

    formContainer.style.display = 'block';

    if (selected === 'umum') {
        hiddenRoleInput.value = 'umum';
        document.getElementById('label-name').textContent = authTranslations.fullName;
        document.getElementById('name').placeholder = authTranslations.fullName;
        document.getElementById('label-kartu-identitas').textContent = authTranslations.identityCardKtp;

        // Show JK
        jkGroupElements.forEach(el => el.style.display = 'contents');
        jkInputs.forEach(el => el.required = true);

    } else if (selected === 'pelajar') {
        hiddenRoleInput.value = 'pelajar_mahasiswa';
        document.getElementById('label-name').textContent = authTranslations.fullName;
        document.getElementById('name').placeholder = authTranslations.fullName;
        document.getElementById('label-kartu-identitas').textContent = authTranslations.identityCardKtm;

        // Show JK
        jkGroupElements.forEach(el => el.style.display = 'contents');
        jkInputs.forEach(el => el.required = true);

    } else if (selected === 'instansi') {
        hiddenRoleInput.value = 'instansi_swasta';
        document.getElementById('label-name').textContent = authTranslations.institutionName;
        document.getElementById('name').placeholder = authTranslations.institutionName;
        document.getElementById('label-kartu-identitas').textContent = authTranslations.identityCardInstansi;

        // Hide JK
        jkGroupElements.forEach(el => el.style.display = 'none');
        jkInputs.forEach(el => el.required = false);
    }
}

// Restore form state if there was a validation error
document.addEventListener('DOMContentLoaded', function () {
    const oldRoleEl = document.getElementById('old-role-value');
    const oldRole = oldRoleEl ? oldRoleEl.value : '';
    if (oldRole) {
        const select = document.getElementById('role-select');
        if (oldRole === 'umum') select.value = 'umum';
        if (oldRole === 'pelajar_mahasiswa') select.value = 'pelajar';
        if (oldRole === 'instansi_swasta') select.value = 'instansi';
        showForm();
    }
});
