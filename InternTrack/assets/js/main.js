// assets/js/main.js - Interactive Scripts for InternTrack

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Sidebar Toggle
    const sidebarToggleBtn = document.getElementById('sidebar-toggle');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    const mobileOverlay = document.getElementById('mobile-overlay');

    if (sidebarToggleBtn && mobileSidebar) {
        sidebarToggleBtn.addEventListener('click', function () {
            mobileSidebar.classList.toggle('-translate-x-full');
            if (mobileOverlay) mobileOverlay.classList.toggle('hidden');
        });
    }

    if (mobileOverlay && mobileSidebar) {
        mobileOverlay.addEventListener('click', function () {
            mobileSidebar.classList.add('-translate-x-full');
            mobileOverlay.classList.add('hidden');
        });
    }

    // 2. Auto Dismiss Flash Messages
    const flashAlert = document.getElementById('flash-alert');
    if (flashAlert) {
        setTimeout(function () {
            flashAlert.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
            flashAlert.style.opacity = '0';
            flashAlert.style.transform = 'translateY(-10px)';
            setTimeout(() => flashAlert.remove(), 500);
        }, 5000);
    }
});

// Confirmation helper for destructive actions
function confirmAction(message, redirectUrl) {
    if (confirm(message || 'Apakah Anda yakin ingin melakukan tindakan ini?')) {
        window.location.href = redirectUrl;
    }
    return false;
}

// Quick demo login credential filler
function fillLogin(username, password) {
    const uInput = document.getElementById('username');
    const pInput = document.getElementById('password');
    if (uInput && pInput) {
        uInput.value = username;
        pInput.value = password;
        uInput.focus();
    }
}
