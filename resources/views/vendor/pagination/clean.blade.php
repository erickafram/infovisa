@if ($paginator->hasPages())
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white/90 text-gray-800 border border-gray-200/80 shadow-sm rounded-2xl px-5 py-4">
        <div class="text-sm font-medium text-gray-600">
            Exibindo {{ $paginator->firstItem() }} a {{ $paginator->lastItem() }} de {{ $paginator->total() }} resultado{{ $paginator->total() !== 1 ? 's' : '' }}.
        </div>

        <nav role="navigation" aria-label="Paginação" class="flex items-center gap-2">
            {{-- Link Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="px-3.5 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 rounded-full cursor-not-allowed border border-gray-100">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   class="px-3.5 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-blue-50 hover:border-blue-200 hover:text-blue-600 transition-colors">Anterior</a>
            @endif

            {{-- Links das páginas --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-3.5 py-1.5 text-sm font-medium text-gray-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="px-3.5 py-1.5 text-sm font-semibold text-blue-700 bg-blue-100 border border-blue-200 rounded-full shadow-sm ring-2 ring-blue-100">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="px-3.5 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-blue-50 hover:border-blue-200 hover:text-blue-600 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Link Próximo --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   class="px-3.5 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-blue-50 hover:border-blue-200 hover:text-blue-600 transition-colors">Próximo</a>
            @else
                <span class="px-3.5 py-1.5 text-sm font-medium text-gray-400 bg-gray-100 rounded-full cursor-not-allowed border border-gray-100">Próximo</span>
            @endif
        </nav>
    </div>
@endif
