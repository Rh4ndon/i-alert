
        function showAlert(message, type = 'success') {
            // Remove existing alert if any
            let existingAlert = document.querySelector('.floating-alert');
            if (existingAlert) {
                existingAlert.remove();
            }

            // Create the alert element
            let alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show floating-alert`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `
                <strong>${type === 'success' ? 'Success!' : type === 'warning' ? 'Warning!' : type === 'info' ? 'Info!' : 'Error!'}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

            // Apply floating styles
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '1050';

            // Append to body
            document.body.appendChild(alertDiv);

            // Auto remove after 5 seconds
            setTimeout(() => {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 300);
            }, 5000);
        }

        //show alert on successful logout or index.php?msg=You have been logged out successfully
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('msg')) {
            let message = urlParams.get('msg');
            let type = 'success';
            showAlert(message, type);
        }
        if (urlParams.has('error')) {
            let message = urlParams.get('error');
            let type = 'danger';
            showAlert(message, type);
        }
        if (urlParams.has('warning')) {
            let message = urlParams.get('warning');
            let type = 'warning';
            showAlert(message, type);
        }

       
        if (window.location.search) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        