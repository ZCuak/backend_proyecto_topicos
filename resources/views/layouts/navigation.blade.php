@php
    use Illuminate\Support\Facades\Auth;
    $user = Auth::user();

    $activeDashboard = request()->is('/') || request()->routeIs('dashboard');
    $activeVehicles = request()->routeIs('vehiclecolors.*') || request()->routeIs('brands.*') || request()->routeIs('brand-models.*') || request()->routeIs('vehicletypes.*') || request()->routeIs('vehicles.*') || request()->routeIs('maintenances.*') || request()->routeIs('maintenance-schedules.*') || request()->routeIs('maintenance-records.*');
    $activeStaff = request()->routeIs('usertypes.*') || request()->routeIs('personal.*') || request()->routeIs('contracts.*') || request()->routeIs('attendances.*') || request()->routeIs('vacations.*');
    $activeScheduling = request()->routeIs('schedules.*') || request()->routeIs('zones.*') || request()->routeIs('groups.*') || request()->routeIs('schedulings.*');
    $activeChanges = request()->routeIs('history.*');

    $linkBase = 'group flex items-center gap-3 px-3 py-2.5 rounded-2xl border border-white/5 text-slate-200 hover:text-white hover:border-emerald-300/40 hover:bg-white/5 transition';
    $linkActive = 'bg-gradient-to-r from-emerald-500/90 to-emerald-600 text-white shadow-[0_10px_40px_rgba(16,185,129,0.35)] border-emerald-300/70';
    $summaryBase = 'flex items-center justify-between cursor-pointer px-3 py-2.5 rounded-2xl border border-white/5 text-slate-200 hover:text-white hover:border-emerald-300/40 hover:bg-white/5 transition';
    $summaryActive = 'bg-white/5 text-white border-emerald-300/60 shadow-[0_12px_40px_rgba(16,185,129,0.25)]';
    $subLinkBase = 'block px-3 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition';
    $subLinkActive = 'bg-white/10 text-white border border-emerald-300/50';
@endphp



<aside id="sidebarMenu"
    class="fixed md:static top-16 md:top-0 left-0 h-[calc(100vh-4rem)] md:h-screen w-80 -translate-x-full md:translate-x-0
           transition-transform duration-300 ease-in-out bg-gradient-to-b from-slate-950 via-slate-900 to-emerald-950 text-white border-r border-white/10 shadow-[0_30px_120px_rgba(15,23,42,0.4)] z-50 flex flex-col rounded-r-3xl md:rounded-none relative overflow-hidden">

   

    <div class="px-5 py-5 border-b border-white/10 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            @if($user && $user->profile_photo_path)
                <img src="{{ asset('storage/'.$user->profile_photo_path) }}" class="w-12 h-12 rounded-2xl border border-white/10 object-cover shadow-lg shadow-emerald-500/20">
            @else
                <div class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white/5 text-emerald-200 border border-white/10">
                    <i class="fa-solid fa-user"></i>
                </div>
            @endif
            <div class="leading-tight min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ $user->firstname ?? 'Usuario' }} {{ $user->lastname ?? '' }}</p>
                <p class="text-xs text-slate-300 truncate">{{ $user->email ?? '' }}</p>
            </div>
        </div>
        @if($user)
            <span class="shrink-0 text-[11px] font-semibold bg-white/10 border border-emerald-300/40 text-emerald-100 px-3 py-1 rounded-full uppercase tracking-wide">
                {{ $user->usertype->name ?? 'Usuario' }}
            </span>
        @endif
    </div>

    <nav class="flex-1 overflow-y-auto px-4 py-5 text-[15px] space-y-4">
        <a href="{{ url('/') }}"
           class="{{ $linkBase }} {{ $activeDashboard ? $linkActive : '' }}">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-emerald-200 border border-white/10 group-hover:border-emerald-300/50">
                <i class="fa-solid fa-chart-line"></i>
            </span>
            <span class="font-semibold">Dashboard</span>
        </a>

        <details class="group" @if($activeVehicles) open @endif>
            <summary class="{{ $summaryBase }} {{ $activeVehicles ? $summaryActive : '' }}">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-emerald-200 border border-white/10 group-open:border-emerald-300/50">
                        <i class="fas fa-car"></i>
                    </span>
                    <span class="font-semibold">Gestion de Vehiculos</span>
                </div>
                <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="pl-14 mt-2 space-y-1">
                <a href="{{ route('vehiclecolors.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('vehiclecolors.*') ? $subLinkActive : '' }}"><i class="fas fa-palette mr-2"></i>Color</a>
                <a href="{{ route('brands.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('brands.*') ? $subLinkActive : '' }}"><i class="fas fa-tags mr-2"></i>Marcas</a>
                <a href="{{ route('brand-models.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('brand-models.*') ? $subLinkActive : '' }}"><i class="fas fa-wrench mr-2"></i>Modelos</a>
                <a href="{{ route('vehicletypes.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('vehicletypes.*') ? $subLinkActive : '' }}"><i class="fas fa-car mr-2"></i>Tipo de vehiculo</a>
                <a href="{{ route('vehicles.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('vehicles.*') ? $subLinkActive : '' }}"><i class="fas fa-car-side mr-2"></i>Vehiculos</a>
                <a href="{{ route('maintenances.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('maintenances.*') ? $subLinkActive : '' }}"><i class="fas fa-toolbox mr-2"></i>Mantenimiento</a>
                <a href="{{ route('maintenance-schedules.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('maintenance-schedules.*') ? $subLinkActive : '' }}"><i class="fas fa-calendar-alt mr-2"></i>Horarios</a>
                <a href="{{ route('maintenance-records.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('maintenance-records.*') ? $subLinkActive : '' }}"><i class="fas fa-clipboard-check mr-2"></i>Registros</a>
            </div>
        </details>

        <details class="group" @if($activeStaff) open @endif>
            <summary class="{{ $summaryBase }} {{ $activeStaff ? $summaryActive : '' }}">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-emerald-200 border border-white/10 group-open:border-emerald-300/50">
                        <i class="fas fa-users"></i>
                    </span>
                    <span class="font-semibold">Gestion de Personal</span>
                </div>
                <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="pl-14 mt-2 space-y-1">
                <a href="{{ route('usertypes.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('usertypes.*') ? $subLinkActive : '' }}"><i class="fas fa-user-tie mr-2"></i>Tipo de personal</a>
                <a href="{{ route('personal.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('personal.*') ? $subLinkActive : '' }}"><i class="fas fa-user mr-2"></i>Personal</a>
                <a href="{{ route('contracts.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('contracts.*') ? $subLinkActive : '' }}"><i class="fas fa-file-contract mr-2"></i>Contratos</a>
                <a href="{{ route('attendances.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('attendances.*') ? $subLinkActive : '' }}"><i class="fas fa-user-clock mr-2"></i>Asistencias</a>
                <a href="{{ route('vacations.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('vacations.*') ? $subLinkActive : '' }}"><i class="fas fa-plane mr-2"></i>Vacaciones</a>
            </div>
        </details>

        <details class="group" @if($activeScheduling) open @endif>
            <summary class="{{ $summaryBase }} {{ $activeScheduling ? $summaryActive : '' }}">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-emerald-200 border border-white/10 group-open:border-emerald-300/50">
                        <i class="fas fa-calendar-alt"></i>
                    </span>
                    <span class="font-semibold">Programacion</span>
                </div>
                <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="pl-14 mt-2 space-y-1">
                <a href="{{  route('schedules.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('schedules.*') ? $subLinkActive : '' }}"><i class="fas fa-clock mr-2"></i>Turnos</a>
                <a href="{{ route('zones.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('zones.*') ? $subLinkActive : '' }}"><i class="fas fa-map-marker-alt mr-2"></i>Zonas</a>
                <a href="{{ route('groups.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('groups.*') ? $subLinkActive : '' }}"><i class="fas fa-users mr-2"></i>Grupos</a>
                <a href="{{ route('schedulings.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('schedulings.*') ? $subLinkActive : '' }}"><i class="fas fa-calendar-check mr-2"></i>Programacion</a>
            </div>
        </details>

        <details class="group" @if($activeChanges) open @endif>
            <summary class="{{ $summaryBase }} {{ $activeChanges ? $summaryActive : '' }}">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-emerald-200 border border-white/10 group-open:border-emerald-300/50">
                        <i class="fas fa-exchange-alt"></i>
                    </span>
                    <span class="font-semibold">Gestion de cambios</span>
                </div>
                <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="pl-14 mt-2 space-y-1">
                <a href="#" class="{{ $subLinkBase }}"><i class="fas fa-clipboard-list mr-2"></i>Motivos</a>
                <a href="{{ route('history.index') }}" class="{{ $subLinkBase }} {{ request()->routeIs('history.*') ? $subLinkActive : '' }}"><i class="fas fa-retweet mr-2"></i>Cambios</a>
            </div>
        </details>
    </nav>

    <div class="px-5 py-5 border-t border-white/10 space-y-3">
        <p class="text-xs uppercase tracking-[0.24em] text-slate-400 font-semibold">Usuario</p>
        <button id="userProfileBtnMovil"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl border border-white/10 bg-white/5 text-white hover:border-emerald-300/40 hover:text-emerald-50 transition">
            <i class="fa-solid fa-user text-emerald-200"></i> <span>Mi perfil</span>
        </button>
        <button id="logoutBtn"
            class="w-full flex items-center justify-between gap-2 px-4 py-3 rounded-2xl border border-red-200/40 bg-red-500/15 text-red-100 hover:bg-red-500/25 transition">
            <span class="inline-flex items-center gap-2"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Cerrar sesion</span></span>
            <i class="fa-solid fa-circle-arrow-right"></i>
        </button>
    </div>
</aside>

<div id="overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden md:hidden z-40"></div>
<form id="logoutForm" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>

<script>
const setupNavigation = () => {
    const sidebar = document.getElementById('sidebarMenu');
    if (!sidebar) return;

    const overlay = document.getElementById('overlay');
    const navEnergyToggle = document.getElementById('navEnergyToggle');
    const toggleBtn = document.getElementById('menuToggle');
    const logoutBtn = document.getElementById('logoutBtn');
    const profileBtn = document.getElementById('userProfileBtnMovil');
    const logoutForm = document.getElementById('logoutForm');
    let energyTimeout;

    const openMenu = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay?.classList.remove('hidden');
    };
    const closeMenu = () => {
        sidebar.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
    };
    const energizeNav = () => {
        clearTimeout(energyTimeout);
        sidebar.classList.add('nav-energized');
        energyTimeout = window.setTimeout(() => sidebar.classList.remove('nav-energized'), 850);
    };
    const collapseNav = () => {
        document.body.classList.add('nav-collapsed');
        navEnergyToggle?.classList.add('is-active');
    };
    const expandNav = () => {
        document.body.classList.remove('nav-collapsed');
        navEnergyToggle?.classList.remove('is-active');
        sidebar.classList.remove('-translate-x-full');
        overlay?.classList.add('hidden');
    };
    const toggleFuturisticNav = () => {
        const isDesktop = window.matchMedia('(min-width: 768px)').matches;
        if (isDesktop) {
            const shouldCollapse = !document.body.classList.contains('nav-collapsed');
            shouldCollapse ? collapseNav() : expandNav();
        } else {
            const willOpen = sidebar.classList.contains('-translate-x-full');
            willOpen ? openMenu() : closeMenu();
            navEnergyToggle?.classList.toggle('is-active', !willOpen);
        }
        energizeNav();
    };

    // evitar listeners duplicados al navegar con Turbo
    toggleBtn && (toggleBtn.onclick = openMenu);
    overlay && (overlay.onclick = closeMenu);
    navEnergyToggle && (navEnergyToggle.onclick = (event) => {
        event.preventDefault();
        toggleFuturisticNav();
    });

    profileBtn && (profileBtn.onclick = () => {
        closeMenu();
        Swal.fire({
            icon: 'info',
            title: 'Perfil del usuario',
            html: `
                <div class="text-left leading-7">
                    <p><b>Nombre:</b> {{ $user->firstname ?? '' }} {{ $user->lastname ?? '' }}</p>
                    <p><b>DNI:</b> {{ $user->dni ?? '-' }}</p>
                    <p><b>Rol:</b> {{ $user->usertype->name ?? 'Usuario' }}</p>
                    <p><b>Correo:</b> {{ $user->email ?? '' }}</p>
                    <p><b>Telefono:</b> {{ $user->phone ?? '-' }}</p>
                    <p><b>Direccion:</b> {{ $user->address ?? '-' }}</p>
                </div>`,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#10b981'
        });
    });

    logoutBtn && (logoutBtn.onclick = () => {
        Swal.fire({
            icon: 'question',
            title: 'Deseas cerrar sesion?',
            showCancelButton: true,
            confirmButtonText: 'Si, cerrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#ef4444'
        }).then((result) => {
            if (result.isConfirmed) logoutForm?.submit();
        });
    });
};

document.addEventListener('DOMContentLoaded', setupNavigation);
document.addEventListener('turbo:load', setupNavigation);
</script>
