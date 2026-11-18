@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman"
         class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">

        <p class="text-sm text-gray-600">
            Menampilkan
            <span class="font-semibold text-indigo-900">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-indigo-900">{{ $paginator->total() }}</span>
            data
        </p>

        <div class="inline-flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm ring-1 ring-gray-200">

            {{-- Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="Halaman sebelumnya"
                      class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-300">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" />
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"
                   class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-600 transition hover:bg-indigo-50 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" />
                    </svg>
                </a>
            @endif

            {{-- Indikator ringkas untuk layar kecil --}}
            <span class="px-3 text-sm text-gray-600 sm:hidden">
                Hal. {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            {{-- Nomor halaman (layar lebar) --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true"
                          class="hidden h-9 min-w-[2.25rem] items-center justify-center px-2 text-sm text-gray-400 sm:inline-flex">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="hidden h-9 min-w-[2.25rem] items-center justify-center rounded-lg bg-indigo-600 px-3 text-sm font-semibold text-white shadow sm:inline-flex">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}"
                               class="hidden h-9 min-w-[2.25rem] items-center justify-center rounded-lg px-3 text-sm font-medium text-gray-700 transition hover:bg-indigo-50 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:inline-flex">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"
                   class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-600 transition hover:bg-indigo-50 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" />
                    </svg>
                </a>
            @else
                <span aria-disabled="true" aria-label="Halaman berikutnya"
                      class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-300">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                              d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
