// This script handles the sidebar menu functionality
document.addEventListener('DOMContentLoaded', function() {
    // TOGGLE SIDEBAR
    const menuBar = document.querySelector('#content nav .bx.bx-menu');
    const sidebar = document.getElementById('sidebar');

    if (menuBar) {
        menuBar.addEventListener('click', function () {
            sidebar.classList.toggle('hide');
        });
    }

    // RESPONSIVE BEHAVIOR
    if(window.innerWidth < 768) {
        sidebar.classList.add('hide');
    }

    window.addEventListener('resize', function () {
        if(this.innerWidth < 768) {
            sidebar.classList.add('hide');
        }
    });
});