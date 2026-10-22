<!DOCTYPE html>
<html lang="pt" class="h-full bg-slate-950 text-slate-100">

@include('layouts.partials.head')

<body class="h-full antialiased font-sans flex flex-col md:flex-row bg-slate-950 text-slate-100 selection:bg-emerald-500 selection:text-slate-950">

    <!-- Splash Screen -->
    @include('layouts.partials.splash')

    <!-- Mobile Drawer Overlay Backdrop -->
    <div id="mobileOverlay" 
         class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden transition-opacity duration-300 md:hidden"
         onclick="toggleMobileDrawer()"></div>

    <!-- Mobile Slide-out Drawer -->
    <div id="mobileDrawer" 
         class="fixed inset-y-0 left-0 w-72 max-w-[85vw] z-50 -translate-x-full transition-transform duration-300 ease-in-out md:hidden shadow-2xl flex flex-col">
        @include('layouts.partials.sidebar', ['isMobile' => true])
    </div>

    <!-- Desktop Sidebar (hidden on mobile, fixed width on md+) -->
    <aside class="hidden md:flex md:w-64 flex-shrink-0 flex-col border-r border-slate-800/80">
        @include('layouts.partials.sidebar', ['isMobile' => false])
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-slate-950">
        <!-- Responsive Header (Topbar) -->
        @include('layouts.partials.header', ['header' => $header ?? null])

        <!-- Page Body Content (with generous pb-28 on mobile so bottom-nav never overlaps) -->
        <main class="w-full flex-1 p-4 sm:p-6 md:p-8 space-y-6 md:space-y-8 pb-28 md:pb-8">
            {{ $slot }}
        </main>
    </div>

    <!-- Mobile Bottom Navigation Bar -->
    @include('layouts.partials.bottom-nav')

    <!-- SweetAlert2 Notifications & Helper Scripts -->
    <script>
        // Mobile Drawer Controller
        window.toggleMobileDrawer = function() {
            const drawer = document.getElementById('mobileDrawer');
            const overlay = document.getElementById('mobileOverlay');
            if (!drawer || !overlay) return;

            const isOpen = !drawer.classList.contains('-translate-x-full');
            if (isOpen) {
                drawer.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            } else {
                drawer.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        };

        // Toast configuration
        window.Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        // Flash message toasts
        @if(session('success'))
            window.Toast.fire({
                icon: 'success',
                title: '{{ session('success') }}',
                iconColor: '#10b981'
            });
        @endif

        @if(session('error'))
            window.Toast.fire({
                icon: 'error',
                title: '{{ session('error') }}',
                iconColor: '#f43f5e'
            });
        @endif

        @if(session('info'))
            window.Toast.fire({
                icon: 'info',
                title: '{{ session('info') }}',
                iconColor: '#38bdf8'
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Atenção aos seguintes erros:',
                html: '<div class="text-left text-xs space-y-1 mt-2">@foreach($errors->all() as $error)<p>&bull; {{ addslashes($error) }}</p>@endforeach</div>',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#10b981',
            });
        @endif

        // Global confirmation helpers
        window.confirmAction = function(formIdOrElement, title = 'Tem a certeza?', text = 'Esta ação não poderá ser revertida!', confirmBtn = 'Sim, continuar') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#334155',
                confirmButtonText: confirmBtn,
                cancelButtonText: 'Cancelar',
                iconColor: '#f59e0b',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (typeof formIdOrElement === 'string') {
                        document.getElementById(formIdOrElement).submit();
                    } else if (formIdOrElement instanceof HTMLFormElement) {
                        formIdOrElement.submit();
                    } else if (typeof formIdOrElement === 'function') {
                        formIdOrElement();
                    }
                }
            });
        };

        window.confirmLogout = function(formId = 'topbar-logout-form') {
            Swal.fire({
                title: 'Terminar Sessão',
                text: 'Deseja realmente sair da sua conta FAMFIN?',
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
                    const form = document.getElementById(formId);
                    if (form) form.submit();
                }
            });
        };
    </script>
</body>
</html>
