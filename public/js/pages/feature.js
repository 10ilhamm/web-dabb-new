function filterSections() {
    const query = document.getElementById('sectionSearch').value.toLowerCase();
    const sections = document.querySelectorAll('.feature-section');
    sections.forEach(function(section) {
        const textContent = section.textContent.toLowerCase();
        section.style.display = textContent.includes(query) ? '' : 'none';
    });
}
function openImageModal(src) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImg');
    modal.style.display = 'flex';
    modalImg.src = src;
    document.body.style.overflow = 'hidden';
}
function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}
