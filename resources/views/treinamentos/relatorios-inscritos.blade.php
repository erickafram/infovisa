@extends('layouts.admin')

@section('title', 'Relatório de inscritos')
@section('page-title', 'Relatório de inscritos')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-medium text-blue-600">{{ $evento->titulo }}</p>
            <h2 class="text-2xl font-bold text-gray-900">Inscritos no evento</h2>
            <p class="mt-1 text-sm text-gray-600">Total de {{ $evento->inscricoes->count() }} participante(s) cadastrados.</p>
        </div>
        <a href="{{ route('admin.treinamentos.show', $evento) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Voltar ao evento</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-500">Nome</th>
                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-500">Contato</th>
                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-500">Instituição</th>
                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-500">Cidade</th>
                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-500">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($evento->inscricoes as $inscricao)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ $inscricao->nome }}</p>
                                @if($inscricao->cargo)
                                    <p class="text-xs text-gray-500">{{ $inscricao->cargo }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <p>{{ $inscricao->email }}</p>
                                <p class="text-xs text-gray-500">{{ $inscricao->telefone ?: 'Telefone não informado' }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $inscricao->instituicao ?: 'Não informada' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $inscricao->cidade ?: 'Não informada' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $inscricao->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">Nenhum inscrito registrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection