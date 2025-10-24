@extends('layouts.app')
@section('title', 'Editar Personal — RSU Reciclaje')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">✏️ Editar personal</h1>
        <p class="text-slate-500">Modifica los datos del personal seleccionado.</p>
    </div>

    <div class="bg-white shadow-md rounded-xl border border-slate-100 p-6">
        <form action="{{ route('personal.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('personal._form', ['buttonText' => 'Actualizar'])
        </form>
    </div>
</div>
@endsection
