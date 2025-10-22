@if ($paginator->hasPages())
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white text-gray-900 shadow-md rounded-xl px-4 py-3">
        <div class="text-sm font-medium">
            Exibindo {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultado{{ $paginator->total() !== 1 ? 's' : '' }}.
        </div>

        <nav role="navigation" aria-label="Paginação" class="flex items-center gap-2">
            {{-- Link Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 text-sm font-semibold text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="px-3 py-1.5 text-sm font-semibold text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Anterior</a>
            @endif

            {{-- Links das páginas --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3 py-1.5 text-sm font-semibold text-gray-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="px-3 py-1.5 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="px-3 py-1.5 text-sm font-semibold text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Link Próximo --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="px-3 py-1.5 text-sm font-semibold text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">Próximo</a>
            @else
                <span class="px-3 py-1.5 text-sm font-semibold text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Próximo</span>
            @endif
        </nav>
    </div>
@endif
