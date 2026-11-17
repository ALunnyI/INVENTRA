<div x-data="confirmDialog"
     x-on:submit.document.capture="intercept($event)"
     x-on:keydown.escape.window="open && close()"
     x-effect="document.body.classList.toggle('overflow-hidden', open)">

    <div x-show="open" style="display: none;" x-transition.opacity.duration.150ms
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         role="alertdialog" aria-modal="true"
         aria-labelledby="confirm-title" aria-describedby="confirm-message">

        <!-- Latar gelap (klik untuk menutup) -->
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" x-on:click="close()"></div>

        <!-- Kartu dialog -->
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                     :class="tone === 'danger' ? 'bg-red-100 text-red-600' : 'bg-indigo-100 text-indigo-600'">
                    <!-- Ikon peringatan -->
                    <svg x-show="tone === 'danger'" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <!-- Ikon tanya -->
                    <svg x-show="tone !== 'danger'" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.8" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <h3 id="confirm-title" class="text-lg font-semibold text-gray-900" x-text="title"></h3>
                    <p id="confirm-message" class="mt-1 text-sm text-gray-600" x-text="message"></p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button" x-ref="cancelButton" x-on:click="close()"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Batal
                </button>
                <button type="button" x-on:click="accept()" x-text="confirmText"
                        class="rounded-lg px-4 py-2 text-sm font-semibold text-white shadow transition focus:outline-none focus:ring-2 focus:ring-offset-2"
                        :class="tone === 'danger'
                            ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
                            : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500'">
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('confirmDialog', () => ({
            open: false,
            title: '',
            message: '',
            confirmText: '',
            tone: 'danger',
            form: null,

            // Menahan submit form yang punya atribut data-confirm
            intercept(event) {
                const form = event.target;

                if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
                    return;
                }

                event.preventDefault();

                this.form = form;
                this.title = form.dataset.confirmTitle || 'Konfirmasi';
                this.message = form.dataset.confirm;
                this.confirmText = form.dataset.confirmButton || 'Ya, lanjutkan';
                this.tone = form.dataset.confirmTone || 'danger';
                this.open = true;

                // Fokus awal ke tombol Batal supaya aksi berisiko tidak terpicu tak sengaja
                this.$nextTick(() => this.$refs.cancelButton.focus());
            },

            accept() {
                const form = this.form;
                this.close();
                if (form) form.submit();
            },

            close() {
                this.open = false;
                this.form = null;
            },
        }));
    });
</script>
