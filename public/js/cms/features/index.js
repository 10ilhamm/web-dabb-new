function featureManager() {
    return {
        editModal: { open: false, id: null, name: '', type: 'link', path: '', order: 0, pageType: 'none' },
        addModal: { open: false, type: 'link', pageType: 'none' },
        deleteModal: { open: false, id: null, name: '' },

        openEditModal(id, name, type, path, order, pageType = 'none') {
            this.editModal = { open: true, id, name, type, path, order, pageType };
        },
        openAddModal() {
            this.addModal = { open: true, type: 'link', pageType: 'none' };
        },
        openDeleteModal(id, name) {
            this.deleteModal = { open: true, id, name };
        }
    }
}
