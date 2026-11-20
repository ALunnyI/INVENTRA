<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Laporan</h2></x-slot>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ $errors->first() }}</div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- Laporan status aset --}}
            <form method="GET" action="{{ route('reports.assets') }}"
                  class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="font-semibold text-indigo-900">Laporan Status Aset</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Daftar aset beserta kondisi dan status ketersediaannya.
                </p>

                <div class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="a_category" value="Kategori" />
                        <select id="a_category" name="category_id" class="mt-1 block w-full border-gray-300 rounded-md">
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="a_status" value="Status" />
                            <select id="a_status" name="status" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="">Semua</option>
                                @foreach (\App\Models\Asset::STATUSES as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="a_condition" value="Kondisi" />
                            <select id="a_condition" name="condition" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="">Semua</option>
                                @foreach (\App\Models\Asset::CONDITIONS as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Tampilkan dan Cetak
                    </button>
                    <button type="submit" formaction="{{ route('reports.assets.xlsx') }}"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Unduh Excel
                    </button>
                </div>
            </form>

            {{-- Laporan peminjaman --}}
            <form method="GET" action="{{ route('reports.loans') }}"
                  x-data="{ from: '', to: '' }"
                  class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h3 class="font-semibold text-indigo-900">Laporan Peminjaman</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Riwayat peminjaman per periode, lengkap dengan status keterlambatan.
                </p>

                <div class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="l_from" value="Dari tanggal" />
                            <input id="l_from" type="date" name="from" x-model="from"
                                   class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div>
                            <x-input-label for="l_to" value="Sampai tanggal" />
                            <input id="l_to" type="date" name="to" x-model="to"
                                   class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 text-xs">
                        <button type="button"
                                x-on:click="from = '{{ today()->startOfMonth()->toDateString() }}'; to = '{{ today()->toDateString() }}'"
                                class="rounded-full bg-indigo-50 px-3 py-1 text-indigo-700 hover:bg-indigo-100">Bulan ini</button>
                        <button type="button"
                                x-on:click="from = '{{ today()->subMonthNoOverflow()->startOfMonth()->toDateString() }}'; to = '{{ today()->subMonthNoOverflow()->endOfMonth()->toDateString() }}'"
                                class="rounded-full bg-indigo-50 px-3 py-1 text-indigo-700 hover:bg-indigo-100">Bulan lalu</button>
                        <button type="button"
                                x-on:click="from = '{{ today()->startOfYear()->toDateString() }}'; to = '{{ today()->toDateString() }}'"
                                class="rounded-full bg-indigo-50 px-3 py-1 text-indigo-700 hover:bg-indigo-100">Tahun ini</button>
                        <button type="button" x-on:click="from = ''; to = ''"
                                class="rounded-full bg-gray-100 px-3 py-1 text-gray-600 hover:bg-gray-200">Semua waktu</button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="l_status" value="Status" />
                            <select id="l_status" name="status" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="">Semua</option>
                                @foreach (\App\Models\Loan::STATUS_LABELS as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="l_user" value="Peminjam" />
                            <select id="l_user" name="user_id" class="mt-1 block w-full border-gray-300 rounded-md">
                                <option value="">Semua</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Tampilkan dan Cetak
                    </button>
                    <button type="submit" formaction="{{ route('reports.loans.xlsx') }}"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Unduh Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
