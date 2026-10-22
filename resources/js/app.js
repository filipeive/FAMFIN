import { App } from '@capacitor/app';

// Android Hardware Back Button & Native Integration
document.addEventListener('DOMContentLoaded', () => {
    try {
        App.addListener('backButton', ({ canGoBack }) => {
            // 1. If 3-dots dropdown is open, close it
            const moreDropdown = document.getElementById('topbarMoreMenuDropdown');
            if (moreDropdown && !moreDropdown.classList.contains('hidden')) {
                moreDropdown.classList.add('hidden');
                return;
            }

            // 2. If mobile drawer is open, close it
            const drawer = document.getElementById('mobileDrawer');
            if (drawer && !drawer.classList.contains('-translate-x-full')) {
                if (typeof window.toggleMobileDrawer === 'function') {
                    window.toggleMobileDrawer();
                }
                return;
            }

            // 3. If SweetAlert modal or popup is open, close it
            if (window.Swal && Swal.isVisible()) {
                Swal.close();
                return;
            }

            // 4. Current URL path check
            const path = window.location.pathname.replace(/\/+$/, '');
            const isHomeOrLogin = path === '' || 
                                  path === '/famfin' || 
                                  path.endsWith('/dashboard') || 
                                  path.endsWith('/login');

            // If on main page or no navigation history, ask confirmation before exit
            if (isHomeOrLogin || !canGoBack || window.history.length <= 1) {
                if (window.Swal) {
                    Swal.fire({
                        title: 'Sair da Aplicação?',
                        text: 'Deseja realmente fechar o FAMFIN?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#334155',
                        confirmButtonText: 'Sim, Sair',
                        cancelButtonText: 'Ficar',
                        iconColor: '#38bdf8',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            App.exitApp();
                        }
                    });
                } else {
                    if (confirm('Deseja realmente fechar o FAMFIN?')) {
                        App.exitApp();
                    }
                }
            } else {
                // Navigate back to the previous screen
                window.history.back();
            }
        });
    } catch (e) {
        // Capacitor Native Bridge not active in web browser
    }
});
