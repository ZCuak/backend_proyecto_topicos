@extends('layouts.app')
@section('title', 'Perfil del Personal — RSU Reciclaje')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <div class="flex items-center gap-4 bg-white p-6 rounded-xl shadow-md border border-slate-100">
        <img src="{{ $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path) : asset('img/default-avatar.png') }}"
             class="w-20 h-20 rounded-full object-cover border border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $user->firstname }} {{ $user->lastname }}</h1>
            <p class="text-slate-500">{{ $user->email }}</p>
            <span class="px-2 py-1 text-xs rounded-full {{ $user->status == 'ACTIVO' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                {{ $user->status }}
            </span>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl shadow-md border border-slate-100">
        <h2 class="text-lg font-semibold text-slate-800 mb-3">📋 Información General</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-600">
            <p><strong>DNI:</strong> {{ $user->dni }}</p>
            <p><strong>Usuario:</strong> {{ $user->username }}</p>
            <p><strong>Teléfono:</strong> {{ $user->phone ?? '—' }}</p>
            <p><strong>Dirección:</strong> {{ $user->address ?? '—' }}</p>
            <p><strong>Rol:</strong> {{ $user->usertype->name ?? '—' }}</p>
            <p><strong>Licencia:</strong> {{ $user->license ?? '—' }}</p>
            <p><strong>Fecha de Nacimiento:</strong> {{ $user->birthdate ?? '—' }}</p>
        </div>
    </div>

    <div class="flex justify-end">
        <a href="{{ route('personal.edit', $user->id) }}" 
           class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
            Editar información
        </a>
    </div>
</div>
@endsection
