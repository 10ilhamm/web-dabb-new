// public/js/alert.js

document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast-message');
    
    toasts.forEach(function(toast) {
        // Show after a slight delay
        setTimeout(function() {
            toast.classList.add('show');
        }, 50);
        
        // Auto hide after 5 seconds
        let hideTimeout = setTimeout(function() {
            closeToast(toast);
        }, 5000);

        // Bind close button
        const closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                clearTimeout(hideTimeout);
                closeToast(toast);
            });
        }
    });

    function closeToast(element) {
        element.classList.remove('show');
        element.classList.add('hide');
        setTimeout(function() {
            element.remove();
        }, 300); // Wait for transition to finish
    }
});
