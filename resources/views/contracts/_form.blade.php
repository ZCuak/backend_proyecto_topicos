<div class="space-y-6">
    @php $modalId = isset($contract->id) ? 'editModal' : 'createModal'; @endphp

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
                <option value="{{ $user->id }}" {{ (old('user_id', $contract->user_id ?? '') == $user->id) ? 'selected' : '' }}>
                    {{ $user->firstname }} {{ $user->lastname }}{{ isset($user->dni) ? ' - ' . $user->dni : '' }}
                </option>
                @endforeach
            </select>
            @error('user_id')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- TIPO --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de contrato <span class="text-red-500">*</span></label>
        <div class="relative">
            <i class="fa-solid fa-file-contract absolute left-3 top-2.5 text-slate-400"></i>
            <select name="type" required class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">-- Seleccionar tipo --</option>
                @foreach($contractTypes as $type)
                <option value="{{ $type }}" {{ (old('type', $contract->type ?? '') == $type) ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                @endforeach
            </select>
        </div>
        @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- FECHA INICIO --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha inicio <span class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-clock absolute left-3 top-2.5 text-slate-400"></i>
                <input type="date"
                    name="date_start"
                    value="{{ old('date_start', isset($contract->date_start) ? \Carbon\Carbon::parse($contract->date_start)->format('Y-m-d') : '') }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
            </div>
            @error('date_start')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- FECHA FIN --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha fin</label>
            <div class="relative">
                <i class="fa-solid fa-clock-rotate-left absolute left-3 top-2.5 text-slate-400"></i>
                <input type="date"
                    name="date_end"
                    value="{{ old('date_end', isset($contract->date_end) ? \Carbon\Carbon::parse($contract->date_end)->format('Y-m-d') : '') }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            @error('date_end')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- DÍAS VACACIONES POR AÑO --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Días de vacaciones por año</label>
        <div class="relative">
            <i class="fa-solid fa-calendar-days absolute left-3 top-2.5 text-slate-400"></i>
            <input type="number"
                name="vacation_days_per_year"
                value="{{ old('vacation_days_per_year', $contract->vacation_days_per_year ?? 0) }}"
                class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                placeholder="0">
        </div>
        @error('vacation_days_per_year')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- SALARY --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Salario <span class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-money-bill-transfer absolute left-3 top-2.5 text-slate-400"></i>
                <input type="number" step="0.01"
                    name="salary"
                    value="{{ old('salary', $contract->salary ?? '') }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="0.00"
                    required>
            </div>
            @error('salary')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- POSITION (UserType) --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Puesto (tipo de usuario) <span class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-briefcase absolute left-3 top-2.5 text-slate-400"></i>
                <select name="position_id" required class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Seleccionar puesto --</option>
                    @foreach($positions as $p)
                        <option value="{{ $p->id }}" {{ (old('position_id', $contract->position_id ?? '') == $p->id) ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            @error('position_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- DEPARTMENT --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Departamento <span class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-building absolute left-3 top-2.5 text-slate-400"></i>
                <select name="department_id" required class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">-- Seleccionar departamento --</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ (old('department_id', $contract->department_id ?? '') == $d->id) ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            @error('department_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- PROBATION PERIOD --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Periodo de prueba (meses) <span class="text-red-500">*</span></label>
            <div class="relative">
                <i class="fa-solid fa-hourglass-half absolute left-3 top-2.5 text-slate-400"></i>
                <input type="number"
                    name="probation_period_months"
                    value="{{ old('probation_period_months', $contract->probation_period_months ?? 0) }}"
                    class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500"
                    required>
            </div>
            @error('probation_period_months')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- descripcion removida (campo eliminado) --}}

    {{-- TERMINATION REASON --}}
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Motivo de terminación</label>
        <div class="relative">
            <i class="fa-solid fa-file-lines absolute left-3 top-2.5 text-slate-400"></i>
            <textarea name="termination_reason" rows="2" class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Opcional">{{ old('termination_reason', $contract->termination_reason ?? '') }}</textarea>
        </div>
        @error('termination_reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>

    {{-- ACTIVO (solo edición normalmente) --}}
    @if(isset($contract->id))
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Activo</label>
        <div class="relative">
            <i class="fa-solid fa-toggle-on absolute left-3 top-2.5 text-slate-400"></i>
            <select name="is_active" class="w-full pl-10 pr-3 py-2 rounded-lg border-slate-300 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="1" {{ (old('is_active', $contract->is_active ?? 0) == 1) ? 'selected' : '' }}>Sí</option>
                <option value="0" {{ (old('is_active', $contract->is_active ?? 0) == 0) ? 'selected' : '' }}>No</option>
            </select>
        </div>
        @error('is_active')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
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
            <i class="fa-solid fa-save"></i> {{ $buttonText ?? (isset($contract->id) ? 'Actualizar' : 'Registrar') }}
        </button>
    </div>
</div>