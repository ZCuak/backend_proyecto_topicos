@extends('layouts.guest')
@section('title', 'Marcar Asistencia - RSU Reciclaje')

@section('content')
    <div class="relative flex min-h-screen w-full items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-900 px-4 py-10 overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(16,185,129,0.14),transparent_35%),radial-gradient(circle_at_80%_0%,rgba(59,130,246,0.12),transparent_32%)] pointer-events-none"></div>

        <div class="relative w-full max-w-lg bg-white/95 dark:bg-gray-900/90 shadow-2xl rounded-2xl p-8 border border-emerald-200/70 dark:border-gray-700 backdrop-blur">

            {{-- ENCABEZADO  --}}
            <div class="text-center mb-6">
                <div
                    class="bg-green-100 dark:bg-green-900 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-clock text-green-600 dark:text-green-400 text-4xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Marcar Asistencia</h1>
                <h1 class="text-3xl font-bold text-green-700 dark:text-green-400"></h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Sistema de gestión de rutas y personal - USAT 🌱
                </p>
                
                <div class="mt-3 text-xs text-gray-500 dark:text-gray-500">
                    <i class="fa-solid fa-calendar-day mr-1"></i>
                    <span id="currentDate"></span>
                    <span class="mx-2">•</span>
                    <i class="fa-solid fa-clock mr-1"></i>
                    <span id="currentTime"></span>
                </div>
            </div>

            {{-- ALERTAS (Mostrar si hay mensaje de sesión) --}}
            @if (session('success'))
                <div id="successAlert"
                    class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-lg animate-[flyonui-fade-in_0.3s_ease-out]">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-2xl"></i>
                        <div>
                            <p class="font-semibold">{{ session('success') }}</p>
                            @if (session('attendance_data'))
                                <p class="text-sm mt-1">
                                    Hora: {{ session('attendance_data')['time'] ?? '' }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-xmark text-2xl"></i>
                        <div>
                            <p class="font-semibold">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- FORMULARIO DE MARCADO --}}
            <form id="attendanceForm" method="POST" action="{{ route('attendance.mark') }}" class="space-y-5">
                @csrf

                {{-- USERNAME / DNI --}}
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fa-solid fa-user mr-1 text-green-600"></i>
                        Usuario o DNI
                    </label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" autofocus
                        placeholder="Ingresa tu usuario o DNI"
                        class="w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 
                              focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none 
                              px-4 py-3 text-gray-900 dark:text-gray-100 transition">
                    @error('username')
                        <p class="text-red-500 text-xs mt-1">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- PASSWORD --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fa-solid fa-lock mr-1 text-green-600"></i>
                        Contraseña
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required placeholder="Ingresa tu contraseña"
                            class="w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 
                                  focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none 
                                  px-4 py-3 text-gray-900 dark:text-gray-100 transition">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i id="toggleIcon" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- NOTAS (OPCIONAL) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fa-solid fa-note-sticky mr-1 text-green-600"></i>
                        Notas (opcional)
                    </label>
                    <textarea name="notes" rows="2" placeholder="Ej: Llegué tarde por tráfico..."
                        class="w-full rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 
                                 focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:outline-none 
                                 px-4 py-3 text-gray-900 dark:text-gray-100 transition resize-none">{{ old('notes') }}</textarea>
                </div>

                {{-- BOTÓN MARCAR --}}
                <button type="submit" id="submitBtn"
                    class="w-full py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 
                           text-white rounded-lg font-bold text-lg transition duration-200 shadow-lg hover:shadow-xl
                           flex items-center justify-center gap-3">
                    <i class="fa-solid fa-clock text-2xl"></i>
                    <span>Marcar Asistencia</span>
                </button>
            </form>

            {{-- FOOTER --}}
            <div class="mt-6 text-center space-y-2">
                <a href="{{ route('login') }}"
                    class="text-sm text-green-600 hover:text-green-700 hover:underline inline-flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver al inicio de sesión
                </a>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    © {{ date('Y') }} Proyecto RSU Reciclaje · USAT
                </p>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT --}}
    <script>
        function updateDateTime() {
            const now = new Date();

            // const myTimeZone = 'America/Lima';        // 🇵🇪 Perú (UTC-5) [ACTIVA]
            // const myTimeZone = 'Etc/GMT+12';       // 🏝️ UTC-12 (Técnico: recuerda que el signo va al revés)
            // const myTimeZone = 'Australia/Brisbane'; // 🇦🇺 UTC+10 (Brisbane)
            // Fecha - 
            const dateOptions = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            document.getElementById('currentDate').textContent = now.toLocaleDateString('es-PE', dateOptions);

            // Hora
            const timeOptions = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('es-PE', timeOptions);
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        document.getElementById('attendanceForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const submitBtn = document.getElementById('submitBtn');
            const formData = new FormData(form);

            // Deshabilitar botón mientras procesa
            submitBtn.disabled = true;
            submitBtn.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin text-2xl"></i> <span>Procesando...</span>';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    const attendanceData = data.data;
                    const type = attendanceData.type;
                    const user = attendanceData.user;
                    const userName = (user?.firstname && user?.lastname) ?
                        `${user.firstname} ${user.lastname}` :
                        user?.username || 'Usuario Desconocido';

                    let icon = type === 'ENTRADA' ? '🟢' : '🔴';
                    let title = type === 'ENTRADA' ? '¡Entrada Registrada!' : '¡Salida Registrada!';
                    let html = `
                <div class="text-left space-y-2">
                    <p class="text-lg font-semibold">${icon} ${userName} </p>
                    <p class="text-sm text-gray-600">
                        <i class="fa-solid fa-clock mr-1"></i>
                        ${type === 'ENTRADA' ? 'Hora de entrada' : 'Hora de salida'}: 
                        <strong>${type === 'ENTRADA' ? attendanceData.check_in : attendanceData.check_out}</strong>
                    </p>
                    <p class="text-sm text-gray-600">
                        <i class="fa-solid fa-calendar mr-1"></i>
                        Fecha: <strong>${attendanceData.formatted_date}</strong>
                    </p>
                </div>
            `;

                    Swal.fire({
                        icon: 'success',
                        title: title,
                        html: html,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Aceptar',
                    });

                    // Limpiar formulario
                    form.reset();
                    document.getElementById('username').focus();

                } else {
                    // ERROR
                    let errorMessage = data.message || 'Ocurrió un error al marcar la asistencia';

                    // Construir lista de errores si existen
                    if (data.errors) {
                        let errorList = '<ul class="text-left text-sm mt-2 space-y-1">';
                        Object.values(data.errors).forEach(errorArray => {
                            errorArray.forEach(error => {
                                errorList +=
                                    `<li><i class="fa-solid fa-circle-exclamation mr-1"></i> ${error}</li>`;
                            });
                        });
                        errorList += '</ul>';
                        errorMessage += errorList;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error al marcar asistencia',
                        html: errorMessage,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Entendido'
                    });
                }

            } catch (error) {
                // ERROR DE RED O SERVIDOR
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor. Por favor, verifica tu conexión a internet e intenta nuevamente.',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Reintentar'
                });
            } finally {
                // Rehabilitar botón
                submitBtn.disabled = false;
                submitBtn.innerHTML =
                    '<i class="fa-solid fa-clock text-2xl"></i> <span>Marcar Asistencia</span>';
            }
        });

        @if (session('success'))
            setTimeout(() => {
                const alert = document.getElementById('successAlert');
                if (alert) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 5000);
        @endif

        @if (session('success'))
            document.getElementById('attendanceForm').reset();
            document.getElementById('username').focus();
        @endif
    </script>
@endsection
