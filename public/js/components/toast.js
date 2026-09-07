// public/js/components/toast.js

export class Toast {
    static show(message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.role = 'alert';
        toast.textContent = message;

        container.appendChild(toast);

        // Remove after 4 seconds
        setTimeout(() => {
            toast.remove();
        }, 4000);
    }
}
window.Toast = Toast;
