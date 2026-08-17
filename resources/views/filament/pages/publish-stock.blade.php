<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                📱 Preview Pesan Telegram Channel
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Berikut adalah format pesan yang akan dikirimkan ke Telegram Channel resmi toko Anda beserta tombol deep link transaksi untuk setiap produk.
            </p>

            <div class="bg-gray-50 dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 font-mono text-sm leading-relaxed whitespace-pre-line text-gray-800 dark:text-gray-200">
                {!! $previewText !!}
            </div>

            <div class="mt-6 flex gap-3">
                <button
                    wire:click="refreshPreview"
                    type="button"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                    🔄 Refresh Preview
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
