@extends('layouts.app')
@section('title', 'Nuevo Motivo')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Nuevo Motivo</h1>
        <p class="text-slate-500">Crea un motivo para asociarlo a auditorías.</p>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
        <form action="{{ route('motives.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('motives._form', ['buttonText' => 'Crear'])
        </form>
    </div>
</div>
@endsection
