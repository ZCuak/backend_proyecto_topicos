<!-- NAV SUPERIOR -->
<nav class="fixed md:static inset-x-0 top-0 h-16 md:h-0 z-40 bg-white md:bg-transparent border-b md:border-0 border-slate-200 flex items-center md:hidden px-4">
    <button id="menuToggle" class="mr-3 inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-600">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div class="flex items-center gap-2">
        <img src="{{ asset('img/logo_rsu.png') }}" alt="RSU" class="h-8 w-auto">
        <span class="font-bold text-slate-800">RSU Reciclaje</span>
    </div>
    <span class="ml-auto text-xs font-semibold bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-lg">ADMIN</span>
</nav>

<!-- SIDEBAR -->
<aside id="sidebarMenu"
    class="fixed md:static top-16 md:top-0 left-0 h-[calc(100vh-4rem)] md:h-screen w-72 -translate-x-full md:translate-x-0
           transition-transform duration-300 ease-in-out bg-white border-r border-slate-200 shadow-[0_8px_30px_rgb(0,0,0,0.04)] z-50 flex flex-col">

    <!-- Cabecera -->
    <div class="hidden md:flex items-center justify-between h-16 px-4 border-b border-slate-200">
        <div class="flex items-center gap-2">
            <img src="{{ asset('img/logo_rsu.png') }}" alt="RSU" class="h-9 w-auto">
            <span class="font-bold text-slate-800">RSU Reciclaje</span>
        </div>
        <span class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-lg">ADMIN</span>
    </div>

    <!-- Perfil -->
    <div class="px-5 py-4 border-b border-slate-200">
        <p class="text-sm font-semibold text-slate-700">Administrador</p>
    </div>

    <!-- MENÚ PRINCIPAL -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 text-[15px] space-y-3">
        <!-- DASHBOARD -->
        <a href="{{ url('/') }}"
           class="group flex items-center gap-3 px-3 py-2.5 rounded-xl 
                  hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 transition">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 group-hover:bg-emerald-100 group-hover:text-emerald-700">
                <i class="fa-solid fa-leaf"></i>
            </span>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- GESTIÓN DE VEHÍCULOS -->
        <details class="group rounded-xl">
            <summary class="flex items-center justify-between cursor-pointer px-3 py-2.5 rounded-xl hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 transition">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 group-open:bg-emerald-100 group-open:text-emerald-700">
                        <i class="fas fa-car"></i>
                    </span>
                    <span class="font-medium">Gestión de Vehículos</span>
                </div>
                <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="pl-14 mt-1 space-y-1">
                <a href="{{ route('vehiclecolors.index') }}" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-palette mr-2"></i>Color</a>
                <a href="{{ route('brands.index') }}" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-tags mr-2"></i>Marcas</a>
                <a href="{{ route('brand-models.index') }}" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-wrench mr-2"></i>Modelos</a>
                <a href="{{ route('vehicletypes.index') }}" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-car mr-2"></i>Tipo de Vehículo</a>
                <a href="#" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-car-side mr-2"></i>Vehículo</a>
            </div>
        </details>

        <!-- GESTIÓN DE EMPLEADOS -->
        <details class="group rounded-xl">
            <summary class="flex items-center justify-between cursor-pointer px-3 py-2.5 rounded-xl hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 transition">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 group-open:bg-emerald-100 group-open:text-emerald-700">
                        <i class="fas fa-users"></i>
                    </span>
                    <span class="font-medium">Gestión de Personal</span>
                </div>
                <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="pl-14 mt-1 space-y-1">
                <a href="{{ route('usertypes.index') }}" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-user-tie mr-2"></i>Tipo de Personal</a>
                <a href="{{ route('personal.index') }}" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-user mr-2"></i>Personal</a>
                <a href="{{ route('contracts.index') }}" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-file-contract mr-2"></i>Contratos</a>
                <a href="{{ route('attendances.index') }}" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-user-clock mr-2"></i>Asistencia</a>
                <a href="{{ route('vacations.index') }}" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-plane mr-2"></i>Vacaciones</a>
            </div>
        </details>

        <!-- PROGRAMACIÓN -->
        <details class="group rounded-xl">
            <summary class="flex items-center justify-between cursor-pointer px-3 py-2.5 rounded-xl hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 transition">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 group-open:bg-emerald-100 group-open:text-emerald-700">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                    <span class="font-medium">Programación</span>
                </div>
                <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="pl-14 mt-1 space-y-1">
                <a href="{{  route('schedules.index') }}" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-clock mr-2"></i>Turnos</a>
                <a href="{{ route('zones.index') }}" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-map-marker-alt mr-2"></i>Zonas</a>
                <a href="#" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-users mr-2"></i>Grupo de Personal</a>
                <a href="#" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-calendar-check mr-2"></i>Programación</a>
            </div>
        </details>

        <!-- GESTIÓN DE CAMBIOS -->
        <details class="group rounded-xl">
            <summary class="flex items-center justify-between cursor-pointer px-3 py-2.5 rounded-xl hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 transition">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 group-open:bg-emerald-100 group-open:text-emerald-700">
                        <i class="fas fa-exchange-alt"></i>
                    </span>
                    <span class="font-medium">Gestión de Cambios</span>
                </div>
                <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="pl-14 mt-1 space-y-1">
                <a href="#" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-clipboard-list mr-2"></i>Motivos</a>
                <a href="#" class="block text-slate-600 hover:text-emerald-600 transition"><i class="fas fa-retweet mr-2"></i>Cambios</a>
            </div>
        </details>

        <!-- USUARIO -->
        <p class="px-3 mt-6 mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Usuario</p>
        <div class="px-1 space-y-1">
            <button id="userProfileBtnMovil"
                class="w-full flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 transition">
                <i class="fa-solid fa-user text-emerald-600"></i> <span>Mi perfil</span>
            </button>
            <button id="logoutBtn"
                class="w-full flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-red-50 text-red-600 transition">
                <i class="fa-solid fa-right-from-bracket"></i> <span>Cerrar sesión</span>
            </button>
        </div>
    </nav>
</aside>

<!-- OVERLAY -->
<div id="overlay" class="fixed inset-0 bg-black/40 hidden md:hidden z-40"></div>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebarMenu');
    const overlay = document.getElementById('overlay');
    const toggleBtn = document.getElementById('menuToggle');
    const logoutBtn = document.getElementById('logoutBtn');
    const profileBtn = document.getElementById('userProfileBtnMovil');

    const openMenu = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    };
    const closeMenu = () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    };

    toggleBtn?.addEventListener('click', openMenu);
    overlay?.addEventListener('click', closeMenu);

    profileBtn?.addEventListener('click', () => {
        Swal.fire({
            icon: 'info',
            title: 'Perfil del Usuario',
            html: `
                <ul class="text-left leading-7">
                    <li><b>Rol:</b> Administrador</li>
                    <li><b>Nombre:</b> Usuario de Prueba</li>
                    <li><b>Correo:</b> admin@rsu.com</li>
                    <li><b>Dirección:</b> Chiclayo - Perú</li>
                </ul>`,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#10b981'
        });
    });

    logoutBtn?.addEventListener('click', () => {
        Swal.fire({
            icon: 'question',
            title: '¿Deseas cerrar sesión?',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444'
        });
    });
});
</script>
