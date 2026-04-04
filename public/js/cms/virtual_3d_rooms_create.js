/* ============================================================
   virtual_3d_rooms_create.js
   JS for create.blade.php
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

    /* Initialise preview colours */
    updatePreviewColors();

    /* Save button — just submit the form (no html2canvas needed on create) */
    const saveBtn = document.getElementById('saveRoomBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('virtual3d-room-form').submit();
        });
    }
});

/* ── Colour preview ─────────────────────────────────────────── */
function updatePreviewColors() {
    const wc = document.getElementById('wallColorInput').value;
    const fc = document.getElementById('floorColorInput').value;
    const cc = document.getElementById('ceilingColorInput').value;

    document.getElementById('wallColorText').value    = wc;
    document.getElementById('floorColorText').value   = fc;
    document.getElementById('ceilingColorText').value = cc;

    document.getElementById('pv-wall-front').style.backgroundColor = wc;
    document.getElementById('pv-wall-back').style.backgroundColor  = wc;
    document.getElementById('pv-wall-left').style.backgroundColor  = wc;
    document.getElementById('pv-wall-right').style.backgroundColor = wc;
    document.getElementById('pv-floor').style.backgroundColor      = fc;
    document.getElementById('pv-ceiling').style.backgroundColor    = cc;
}

/* ── 3D preview rotation ────────────────────────────────────── */
function rotatePreview(view, btn) {
    const scene = document.getElementById('preview3dScene');
    const rotations = {
        'default': 'translate(-50%, -50%) rotateX(-10deg) rotateY(-25deg)',
        'front':   'translate(-50%, -50%) rotateX(0deg) rotateY(0deg)',
        'back':    'translate(-50%, -50%) rotateX(0deg) rotateY(180deg)',
        'left':    'translate(-50%, -50%) rotateX(0deg) rotateY(90deg)',
        'right':   'translate(-50%, -50%) rotateX(0deg) rotateY(-90deg)',
        'top':     'translate(-50%, -50%) rotateX(-90deg) rotateY(0deg)',
    };
    scene.style.transform = rotations[view] || rotations['default'];
    document.querySelectorAll('.preview-rot-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
}
