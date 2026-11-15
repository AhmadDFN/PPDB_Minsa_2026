/**
 * PPDB Minsatama — Client-side JavaScript
 * Lightweight, accessible Vanilla JavaScript (No heavyweight frameworks)
 */

document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------------------
    // 1. Public Navbar Mobile Menu Toggle
    // ----------------------------------------------------
    const mobileMenuBtn = document.getElementById('mobile-menu-button');
    const mobileMenuPanel = document.getElementById('mobile-menu-panel');
    const openIcon = document.getElementById('menu-open-icon');
    const closeIcon = document.getElementById('menu-close-icon');

    function setMobileMenuOpen(open) {
        if (!mobileMenuPanel || !mobileMenuBtn) return;

        if (open) {
            mobileMenuPanel.classList.remove('hidden');
            mobileMenuBtn.setAttribute('aria-expanded', 'true');
            if (openIcon) openIcon.classList.add('hidden');
            if (closeIcon) closeIcon.classList.remove('hidden');
        } else {
            mobileMenuPanel.classList.add('hidden');
            mobileMenuBtn.setAttribute('aria-expanded', 'false');
            if (openIcon) openIcon.classList.remove('hidden');
            if (closeIcon) closeIcon.classList.add('hidden');
        }
    }

    if (mobileMenuBtn && mobileMenuPanel) {
        mobileMenuBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const isOpen = !mobileMenuPanel.classList.contains('hidden');
            setMobileMenuOpen(!isOpen);
        });

        // Close when clicking any link inside mobile menu
        mobileMenuPanel.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                setMobileMenuOpen(false);
            });
        });

        // Close when clicking outside of the header/navbar
        document.addEventListener('click', (e) => {
            if (!mobileMenuPanel.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                setMobileMenuOpen(false);
            }
        });
    }

    // ----------------------------------------------------
    // 2. Admin Sidebar Mobile Toggle & Backdrop
    // ----------------------------------------------------
    const adminSidebarToggle = document.getElementById('admin-sidebar-toggle');
    const adminSidebar = document.getElementById('admin-sidebar');
    const adminSidebarBackdrop = document.getElementById('admin-sidebar-backdrop');
    const adminSidebarClose = document.getElementById('admin-sidebar-close');

    function openAdminSidebar() {
        if (adminSidebar && adminSidebarBackdrop) {
            adminSidebar.classList.remove('-translate-x-full');
            adminSidebarBackdrop.classList.remove('hidden');
            document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
        }
    }

    function closeAdminSidebar() {
        if (adminSidebar && adminSidebarBackdrop) {
            adminSidebar.classList.add('-translate-x-full');
            adminSidebarBackdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden', 'lg:overflow-auto');
        }
    }

    if (adminSidebarToggle) {
        adminSidebarToggle.addEventListener('click', openAdminSidebar);
    }

    if (adminSidebarClose) {
        adminSidebarClose.addEventListener('click', closeAdminSidebar);
    }

    if (adminSidebarBackdrop) {
        adminSidebarBackdrop.addEventListener('click', closeAdminSidebar);
    }

    // ----------------------------------------------------
    // 3. Dismissible Alert Close Handler
    // ----------------------------------------------------
    document.querySelectorAll('[data-dismiss="alert"]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            const alert = e.currentTarget.closest('.alert-dismissible');
            if (alert) {
                alert.style.transition = 'opacity 0.2s ease-out, transform 0.2s ease-out';
                alert.style.opacity = '0';
                alert.style.transform = 'scale(0.98)';
                setTimeout(() => alert.remove(), 200);
            }
        });
    });
});
