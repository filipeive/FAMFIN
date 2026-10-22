<!-- Animated App Splash Screen (Mobile & Web App Opening) -->
<div id="app-splash-screen" class="fixed inset-0 z-[9999] bg-slate-950 flex flex-col items-center justify-center transition-opacity duration-300 ease-out">
    <div class="relative flex flex-col items-center text-center px-4">
        <div class="w-16 h-16 mb-3 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 p-3 shadow-xl shadow-emerald-500/20 flex items-center justify-center text-slate-950 font-black text-2xl tracking-wider">
            F
        </div>
        <h1 class="text-white text-lg font-black tracking-tight flex items-center">
            FAMFIN
            <span class="ml-1.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">PRO</span>
        </h1>
        <p class="text-emerald-500 text-[10px] font-bold tracking-[0.2em] uppercase mt-0.5">Gestão Financeira Familiar</p>
        <div class="mt-4 flex items-center gap-1.5">
            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></div>
            <span class="text-slate-400 text-[11px] font-medium">A carregar...</span>
        </div>
    </div>
</div>
<script>
    (function() {
        function hideSplash() {
            const splash = document.getElementById('app-splash-screen');
            if (!splash) return;
            splash.style.opacity = '0';
            splash.style.pointerEvents = 'none';
            setTimeout(() => { if (splash && splash.parentNode) splash.parentNode.removeChild(splash); }, 300);
        }
        if (document.readyState === 'complete') {
            setTimeout(hideSplash, 150);
        } else {
            window.addEventListener('load', () => setTimeout(hideSplash, 150));
            setTimeout(hideSplash, 1500);
        }
    })();
</script>
