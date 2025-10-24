<div class="space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">DNI</label>
            <input type="text" name="dni" value="{{ old('dni', $user->dni ?? '') }}" maxlength="8"
                   class="w-full rounded-lg border-slate-300 focus:ring-emerald-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Usuario</label>
            <input type="text" name="username" value="{{ old('username', $user->username ?? '') }}"
                   class="w-full rounded-lg border-slate-300 focus:ring-emerald-500">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Nombres</label>
            <input type="text" name="firstname" value="{{ old('firstname', $user->firstname ?? '') }}"
                   class="w-full rounded-lg border-slate-300 focus:ring-emerald-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Apellidos</label>
            <input type="text" name="lastname" value="{{ old('lastname', $user->lastname ?? '') }}"
                   class="w-full rounded-lg border-slate-300 focus:ring-emerald-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">Correo electrónico</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
               class="w-full rounded-lg border-slate-300 focus:ring-emerald-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">Contraseña</label>
        <input type="password" name="password"
               class="w-full rounded-lg border-slate-300 focus:ring-emerald-500">
        <small class="text-slate-400 text-xs">Déjelo en blanco para mantener la actual.</small>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">Tipo de Usuario</label>
        <select name="usertype_id" class="w-full rounded-lg border-slate-300 focus:ring-emerald-500">
            @foreach(App\Models\Usertype::all() as $type)
                <option value="{{ $type->id }}"
                    {{ old('usertype_id', $user->usertype_id ?? '') == $type->id ? 'selected' : '' }}>
                    {{ $type->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-600 mb-1">Estado</label>
        <select name="status" class="w-full rounded-lg border-slate-300 focus:ring-emerald-500">
            <option value="ACTIVO" {{ old('status', $user->status ?? '') == 'ACTIVO' ? 'selected' : '' }}>ACTIVO</option>
            <option value="INACTIVO" {{ old('status', $user->status ?? '') == 'INACTIVO' ? 'selected' : '' }}>INACTIVO</option>
        </select>
    </div>

    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
        <a href="{{ route('personal.index') }}" 
           class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200">Cancelar</a>
        <button type="submit" 
                class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition">
            {{ $buttonText }}
        </button>
    </div>
</div>
