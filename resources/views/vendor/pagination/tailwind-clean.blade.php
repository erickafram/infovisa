@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginação" class="flex items-center justify-between">
        <div class="hidden sm:block">
            <p class="text-sm text-gray-600">
                Mostrando <span class="font-medium">{{ $paginator->firstItem() }}</span> a <span class="font-medium">{{ $paginator->lastItem() }}</span> de <span class="font-medium">{{ $paginator->total() }}</span> resultados
            </p>
        </div>

        <div class="flex gap-1">
            {{-- Link Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-2 text-sm text-gray-400 bg-white border border-gray-200 rounded cursor-not-allowed">
                    ← Anterior
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded hover:bg-gray-50 transition-colors">
                    ← Anterior
                </a>
            @endif

            {{-- Links de Página --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="px-3 py-2 text-sm text-gray-400">{{ $element }}</span>
                @endif

                {{-- Array de Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded hover:bg-gray-50 transition-colors">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Link Próximo --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded hover:bg-gray-50 transition-colors">
                    Próximo →
                </a>
            @else
                <span class="px-3 py-2 text-sm text-gray-400 bg-white border border-gray-200 rounded cursor-not-allowed">
                    Próximo →
                </span>
            @endif
        </div>
    </nav>
@endif
