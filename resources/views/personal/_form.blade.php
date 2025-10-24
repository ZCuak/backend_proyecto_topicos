<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- ===========================
         🧍 DATOS PERSONALES
    ============================ --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-slate-50/60 hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-id-card text-emerald-600"></i> Datos personales
        </legend>

        {{-- DNI y Usuario --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">DNI <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i class="fa-solid fa-address-card absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="text" name="dni" maxlength="8"
                        value="{{ old('dni', $user->dni ?? '') }}"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="Ej. 72834567">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Usuario <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i class="fa-solid fa-user absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="text" name="username"
                        value="{{ old('username', $user->username ?? '') }}"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="Ej. frank03">
                </div>
            </div>
        </div>

        {{-- Nombres y Apellidos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nombres <span class="text-red-500">*</span></label>
                <input type="text" name="firstname"
                    value="{{ old('firstname', $user->firstname ?? '') }}"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Ej. Frank Anthony">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Apellidos <span class="text-red-500">*</span></label>
                <input type="text" name="lastname"
                    value="{{ old('lastname', $user->lastname ?? '') }}"
                    class="w-full py-2 px-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Ej. Ocrospoma Ugaz">
            </div>
        </div>

        {{-- Fecha de nacimiento y teléfono --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de nacimiento</label>
                <div class="relative">
                    <i class="fa-solid fa-calendar absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="date" name="birthdate"
                        value="{{ old('birthdate', $user->birthdate ?? '') }}"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                <div class="relative">
                    <i class="fa-solid fa-phone absolute left-3 top-2.5 text-slate-400"></i>
                    <input type="text" name="phone"
                        value="{{ old('phone', $user->phone ?? '') }}"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="Ej. 987654321">
                </div>
            </div>
        </div>

        {{-- Correo y Dirección --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Correo electrónico <span class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-envelope absolute left-3 top-2.5 text-slate-400"></i>
                <input type="email" name="email"
                    value="{{ old('email', $user->email ?? '') }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="correo@ejemplo.com">
            </div>
        </div>

        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Dirección</label>
            <div class="relative">
                <i class="fa-solid fa-map-location-dot absolute left-3 top-2.5 text-slate-400"></i>
                <input type="text" name="address"
                    value="{{ old('address', $user->address ?? '') }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Ej. Calle Los Olivos 456, Chiclayo">
            </div>
        </div>
    </fieldset>

    {{-- ===========================
         🔒 SEGURIDAD Y CONFIG
    ============================ --}}
    <fieldset class="border border-slate-200 rounded-xl p-5 bg-white hover:shadow-sm transition">
        <legend class="px-2 text-sm font-semibold text-slate-600 flex items-center gap-2">
            <i class="fa-solid fa-lock text-emerald-600"></i> Seguridad y configuración
        </legend>

        {{-- Contraseña --}}
        <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
            <div class="relative">
                <i class="fa-solid fa-key absolute left-3 top-2.5 text-slate-400"></i>
                <input type="password" name="password"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="********">
            </div>
            <small class="text-slate-400 text-xs">Déjelo en blanco para mantener la actual.</small>
        </div>

        {{-- Licencia --}}
        <div class="mt-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Licencia de Conducir</label>
            <div class="relative">
                <i class="fa-solid fa-id-badge absolute left-3 top-2.5 text-slate-400"></i>
                <input type="text" name="license"
                    value="{{ old('license', $user->license ?? '') }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="Ej. B12345678">
            </div>
        </div>

        {{-- Foto de perfil --}}
        <div class="mt-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Foto de perfil</label>
            <input type="file" name="profile_photo_path"
                   class="block w-full text-sm text-slate-600
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-lg file:border-0
                          file:text-sm file:font-semibold
                          file:bg-emerald-50 file:text-emerald-700
                          hover:file:bg-emerald-100">
        </div>

        {{-- Tipo de usuario --}}
        <div class="mt-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de Usuario</label>
            <div class="relative">
                <i class="fa-solid fa-users absolute left-3 top-2.5 text-slate-400"></i>
                <select name="usertype_id"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                    @foreach(App\Models\Usertype::all() as $type)
                        <option value="{{ $type->id }}"
                            {{ old('usertype_id', $user->usertype_id ?? '') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Estado --}}
        <div class="mt-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
            <div class="relative">
                <i class="fa-solid fa-circle-check absolute left-3 top-2.5 text-slate-400"></i>
                <select name="status"
                        class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="ACTIVO" {{ old('status', $user->status ?? '') == 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
                    <option value="INACTIVO" {{ old('status', $user->status ?? '') == 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
                </select>
            </div>
        </div>
    </fieldset>
</div>

{{-- BOTONES --}}
<div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
    <button type="button"
        onclick="FlyonUI.modal.close('{{ isset($user->id) ? 'editModal' : 'createModal' }}')"
        class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark mr-1"></i> Cancelar
    </button>
    <button type="submit"
        class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
        <i class="fa-solid fa-save"></i> {{ $buttonText }}
    </button>
</div>
