<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Buat akun baru</h1>
        <p class="mt-1 text-sm text-gray-500">
            Daftar untuk mulai meminjam dan memantau aset perangkat IT.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Nama -->
        <div>
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" class="mt-1 block w-full py-2.5" type="text" name="name"
                          :value="old('name')" required autofocus autocomplete="name"
                          placeholder="Nama sesuai identitas kerja" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1 block w-full py-2.5" type="email" name="email"
                          :value="old('email')" required autocomplete="username"
                          placeholder="nama@perusahaan.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password dan konfirmasi (satu tombol tampilkan untuk keduanya) -->
        <div x-data="{ show: false }" class="space-y-5">
            <div>
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" class="mt-1 block w-full py-2.5" type="password"
                              x-bind:type="show ? 'text' : 'password'" name="password"
                              required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Konfirmasi Password" />
                <x-text-input id="password_confirmation" class="mt-1 block w-full py-2.5" type="password"
                              x-bind:type="show ? 'text' : 'password'" name="password_confirmation"
                              required autocomplete="new-password" placeholder="Ulangi password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <label class="inline-flex items-center">
                <input type="checkbox" x-model="show"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ms-2 text-sm text-gray-600">Tampilkan password</span>
            </label>
        </div>

        <p class="rounded-lg bg-indigo-50 px-4 py-3 text-xs text-indigo-800">
            Akun baru otomatis berperan sebagai <strong>staff</strong>. Hak akses admin
            diberikan oleh administrator.
        </p>

        <button type="submit"
                class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow
                       transition hover:bg-indigo-700 focus:outline-none focus:ring-2
                       focus:ring-indigo-500 focus:ring-offset-2">
            Daftar
        </button>

        <p class="text-center text-sm text-gray-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-800">Masuk</a>
        </p>
    </form>
</x-guest-layout>
