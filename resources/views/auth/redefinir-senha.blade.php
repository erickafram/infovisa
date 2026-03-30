@extends('layouts.auth')

@section('title', 'Redefinir Senha')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Nova Senha</h1>
            <p class="text-sm text-gray-500 mt-1">Crie uma nova senha para sua conta</p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
            @endif

            <form action="{{ route('recuperar-senha.redefinir') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nova Senha</label>
                    <input type="password" name="password" required minlength="8" placeholder="Mínimo 8 caracteres"
                           class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                           autofocus>
                    @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar Nova Senha</label>
                    <input type="password" name="password_confirmation" required minlength="8" placeholder="Digite novamente"
                           class="w-full px-4 py-3 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <button type="submit" class="w-full px-4 py-3 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Redefinir Senha
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
