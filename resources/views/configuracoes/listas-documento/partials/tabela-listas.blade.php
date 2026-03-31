<table class="w-full">
    <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
            <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase">Nome</th>
            <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase">Tipo Processo</th>
            @if($listasTabela->first() && $listasTabela->first()->escopo === 'municipal')
            <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-500 uppercase">Município</th>
            @endif
            <th class="px-4 py-2.5 text-center text-[10px] font-semibold text-gray-500 uppercase">Atividades</th>
            <th class="px-4 py-2.5 text-center text-[10px] font-semibold text-gray-500 uppercase">Documentos</th>
            <th class="px-4 py-2.5 text-center text-[10px] font-semibold text-gray-500 uppercase">Status</th>
            <th class="px-4 py-2.5 text-right text-[10px] font-semibold text-gray-500 uppercase">Ações</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @foreach($listasTabela as $lista)
        <tr class="hover:bg-gray-50/50 transition">
            <td class="px-4 py-2.5">
                <span class="text-sm font-medium text-gray-900">{{ $lista->nome }}</span>
                @if($lista->descricao)
                <p class="text-[11px] text-gray-400 mt-0.5">{{ Str::limit($lista->descricao, 40) }}</p>
                @endif
            </td>
            <td class="px-4 py-2.5">
                @if($lista->tipoProcesso)
                <span class="px-2 py-0.5 text-[10px] font-medium bg-blue-100 text-blue-700 rounded-full">{{ $lista->tipoProcesso->nome }}</span>
                @else
                <span class="text-[10px] text-gray-300">—</span>
                @endif
            </td>
            @if($listasTabela->first() && $listasTabela->first()->escopo === 'municipal')
            <td class="px-4 py-2.5">
                <span class="text-xs text-gray-600">{{ $lista->municipio->nome ?? '—' }}</span>
            </td>
            @endif
            <td class="px-4 py-2.5 text-center">
                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-purple-100 text-purple-700 rounded-full">{{ $lista->atividades_count }}</span>
            </td>
            <td class="px-4 py-2.5 text-center">
                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-700 rounded-full">{{ $lista->tipos_documento_obrigatorio_count }}</span>
            </td>
            <td class="px-4 py-2.5 text-center">
                <span class="px-1.5 py-0.5 text-[10px] font-medium rounded-full {{ $lista->ativo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $lista->ativo ? 'Ativo' : 'Inativo' }}
                </span>
            </td>
            <td class="px-4 py-2.5 text-right">
                <div class="flex items-center justify-end gap-0.5">
                    <a href="{{ route('admin.configuracoes.listas-documento.show', $lista) }}" class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition" title="Ver">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                    <a href="{{ route('admin.configuracoes.listas-documento.edit', $lista) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form action="{{ route('admin.configuracoes.listas-documento.duplicate', $lista) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition" title="Duplicar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </button>
                    </form>
                    <form action="{{ route('admin.configuracoes.listas-documento.destroy', $lista) }}" method="POST" class="inline" onsubmit="return confirm('Excluir esta lista?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Excluir">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
