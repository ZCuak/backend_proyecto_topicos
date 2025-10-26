<div class="space-y-6">
    @php $modalId = isset($vacation->id) ? 'editModal' : 'createModal'; @endphp

    {{-- USUARIO --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Usuario <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <i class="fa-solid fa-user absolute left-3 top-2.5 text-slate-400"></i>
            <select name="user_id" required
                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">-- Seleccionar usuario --</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ (old('user_id', $vacation->user_id ?? '') == $user->id) ? 'selected' : '' }}>
                    {{ $user->firstname }} {{ $user->lastname }}{{ isset($user->dni) ? ' - ' . $user->dni : '' }}
                </option>
                @endforeach
            </select>
            @error('user_id')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- AÑO --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Año <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <i class="fa-solid fa-calendar-alt absolute left-3 top-2.5 text-slate-400"></i>
            <input type="number"
                name="year"
                value="{{ old('year', $vacation->year ?? now()->year) }}"
                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                placeholder="Ej. 2025..."
                required>
        </div>
        @error('year')
        <p class="text-red-500 text-xs mt-1">
            <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
        </p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- FECHA INICIO --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha inicio <span class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-clock absolute left-3 top-2.5 text-slate-400"></i>
                <input type="date"
                    name="start_date"
                    value="{{ old('start_date', isset($vacation->start_date) ? \Carbon\Carbon::parse($vacation->start_date)->format('Y-m-d') : '') }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
            </div>
            @error('start_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- FECHA FIN --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha fin <span class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-clock-rotate-left absolute left-3 top-2.5 text-slate-400"></i>
                <input type="date"
                    name="end_date"
                    value="{{ old('end_date', isset($vacation->end_date) ? \Carbon\Carbon::parse($vacation->end_date)->format('Y-m-d') : '') }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
            </div>
            @error('end_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- DÍAS MÁXIMOS --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Días máximo disponibles <span class="text-red-500">*</span></label>
        <div class="relative">
            <i class="fa-solid fa-calendar-days absolute left-3 top-2.5 text-slate-400"></i>
            <input type="number"
                name="max_days"
                value="{{ old('max_days', $vacation->max_days ?? 30) }}"
                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                required>
        </div>
        @error('max_days')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- MOTIVO --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
            Motivo
        </label>
        <div class="relative">
            <i class="fa-solid fa-ellipsis-vertical absolute left-3 top-2.5 text-slate-400"></i>
            <textarea name="reason" rows="3"
                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                placeholder="Opcional">{{ old('reason', $vacation->reason ?? '') }}</textarea>
        </div>
        @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- ESTADO (solo edición normalmente) --}}
    @if(isset($vacation->id))
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
        <div class="relative">
            <i class="fa-solid fa-toggle-on absolute left-3 top-3 text-slate-400"></i>
            <select name="status" class="w-full pl-10 pr-3 py-3 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="pendiente" {{ (old('status', $vacation->status ?? '') == 'pendiente') ? 'selected' : '' }}>Pendiente</option>
                <option value="aprobada" {{ (old('status', $vacation->status ?? '') == 'aprobada') ? 'selected' : '' }}>Aprobada</option>
                <option value="rechazada" {{ (old('status', $vacation->status ?? '') == 'rechazada') ? 'selected' : '' }}>Rechazada</option>
            </select>
        </div>
        @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    @endif

    {{-- BOTONES --}}
    <div class="flex justify-end gap-3 pt-5 border-t border-slate-200 mt-6">
        <button type="button"
            onclick="FlyonUI.modal.close('{{ $modalId }}')"
            class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
            <i class="fa-solid fa-xmark mr-1"></i> Cancelar
        </button>

        <button type="submit"
            class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition flex items-center gap-2">
            <i class="fa-solid fa-save"></i> {{ $buttonText ?? (isset($vacation->id) ? 'Actualizar' : 'Registrar') }}
        </button>
    </div>
</div>