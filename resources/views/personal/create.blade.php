@extends('layouts.app')
@section('title', 'Registrar Personal — RSU Reciclaje')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">🧍‍♂️ Registrar nuevo personal</h1>
        <p class="text-slate-500">Completa los campos requeridos para agregar nuevo personal al sistema.</p>
    </div>

    <div class="bg-white shadow-md rounded-xl border border-slate-100 p-6">
        <form action="{{ route('personal.store') }}" method="POST">
            @csrf
            @include('personal._form', ['buttonText' => 'Registrar'])
        </form>
    </div>
</div>
@endsection
