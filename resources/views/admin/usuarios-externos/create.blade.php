@extends('layouts.admin')

@section('title', 'Novo Usuário Externo')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Novo Usuário Externo</h1>
            <p class="text-gray-600 mb-6">Esta funcionalidade está em desenvolvimento</p>
            <a href="{{ route('admin.usuarios-externos.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Voltar para Lista
            </a>
        </div>
    </div>
</div>
@endsection
