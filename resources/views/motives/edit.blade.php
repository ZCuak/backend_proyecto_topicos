@extends('layouts.app')
@section('title', 'Editar Motivo')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Editar Motivo</h1>
        <p class="text-slate-500">Actualiza el nombre del motivo.</p>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-slate-100 p-6">
        <form action="{{ route('motives.update', $motive) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            @include('motives._form', ['buttonText' => 'Actualizar', 'motive' => $motive])
        </form>
    </div>
</div>
@endsection
