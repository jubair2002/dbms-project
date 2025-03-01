// Switching between Login, Register, and Forgot Password forms
document.querySelectorAll('.switch-form').forEach(item => {
    item.addEventListener('click', function (event) {
        event.preventDefault();
        const targetForm = this.getAttribute('data-target');

        // Hide all forms
        document.querySelectorAll('.auth-form').forEach(form => {
            form.style.display = 'none';
        });

        // Show the target form
        document.getElementById(targetForm).style.display = 'block';
    });
});

// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(item => {
    item.addEventListener('click', function () {
        const passwordField = this.previousElementSibling;
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
        this.classList.toggle('fa-eye-slash');
    });
});
