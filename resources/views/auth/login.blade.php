<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Selamat datang kembali</h1>
        <p class="mt-1 text-sm text-gray-500">
            Masuk untuk mengelola aset dan peminjaman perangkat IT.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1 block w-full py-2.5" type="email" name="email"
                          :value="old('email')" required autofocus autocomplete="username"
                          placeholder="nama@perusahaan.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div x-data="{ show: false }">
            <div class="flex items-center justify-between">
                <x-input-label for="password" value="Password" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-sm text-indigo-600 hover:text-indigo-800">
                        Lupa password?
                    </a>
                @endif
            </div>

            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full py-2.5 pr-20" type="password"
                              x-bind:type="show ? 'text' : 'password'" name="password"
                              required autocomplete="current-password" placeholder="Masukkan password" />
                <button type="button" tabindex="-1" x-on:click="show = ! show"
                        class="absolute inset-y-0 right-0 px-4 text-sm text-gray-500 hover:text-gray-700">
                    <span x-text="show ? 'Tutup' : 'Lihat'"></span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <label for="remember_me" class="inline-flex items-center">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
        </label>

        <button type="submit"
                class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow
                       transition hover:bg-indigo-700 focus:outline-none focus:ring-2
                       focus:ring-indigo-500 focus:ring-offset-2">
            Masuk
        </button>

        @if (Route::has('register'))
            <p class="text-center text-sm text-gray-500">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-800">Daftar</a>
            </p>
        @endif
    </form>
</x-guest-layout>
