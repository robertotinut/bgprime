<?php

namespace App\Services\Telegram;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class TransactionBotService
{
    protected TelegramClient $client;
    protected string $deliveryBotUsername;

    public function __construct()
    {
        $token = config('services.telegram.transaction_bot_token', '');
        $this->client = new TelegramClient($token);
        $this->deliveryBotUsername = config('services.telegram.delivery_bot_username', 'delivery_bot');
    }

    public function getClient(): TelegramClient
    {
        return $this->client;
    }

    public function sendWelcome(int|string $chatId, ?int $messageId = null): array
    {
        $text = "👋 <b>Selamat datang di Premium Store!</b>\n\n"
            . "Toko digital terpercaya untuk kebutuhan software, streaming, dan tool produktivitas Anda.\n\n"
            . "Silakan pilih menu di bawah ini 👇";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '🛍️ Katalog Produk', 'callback_data' => 'menu:catalog']],
                [['text' => '📦 Pesanan Saya', 'callback_data' => 'menu:my_orders']],
                [
                    ['text' => '💳 Cara Pembayaran', 'callback_data' => 'menu:payment_guide'],
                    ['text' => '🆘 Bantuan', 'callback_data' => 'menu:help'],
                ],
            ],
        ];

        return $this->sendOrEdit($chatId, $messageId, $text, $keyboard);
    }

    public function sendCatalog(int|string $chatId, ?int $messageId = null): array
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $text = "🛍️ <b>KATALOG PRODUK</b>\n\n"
            . "Pilih kategori produk yang Anda cari:";

        $buttons = [];
        $row = [];
        foreach ($categories as $index => $cat) {
            $icon = $cat->icon ? "{$cat->icon} " : '';
            $row[] = ['text' => "{$icon}{$cat->name}", 'callback_data' => "category:{$cat->id}"];
            if (count($row) === 2) {
                $buttons[] = $row;
                $row = [];
            }
        }
        if (!empty($row)) {
            $buttons[] = $row;
        }

        $buttons[] = [['text' => '📦 Semua Produk', 'callback_data' => 'category:all']];
        $buttons[] = [['text' => '🔙 Menu Utama', 'callback_data' => 'menu:main']];

        return $this->sendOrEdit($chatId, $messageId, $text, ['inline_keyboard' => $buttons]);
    }

    public function sendCategoryProducts(int|string $chatId, string|int $categoryId, ?int $messageId = null): array
    {
        $query = Product::where('is_active', true)->where('stock_qty', '>', 0)->orderBy('sort_order');

        $categoryName = 'Semua Produk';
        if ($categoryId !== 'all') {
            $query->where('category_id', $categoryId);
            $category = Category::find($categoryId);
            if ($category) {
                $categoryName = $category->name;
            }
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            $text = "📂 <b>Kategori: {$categoryName}</b>\n\n"
                . "Saat ini belum ada produk ready stock di kategori ini. Silakan cek kembali nanti.";

            $keyboard = [
                'inline_keyboard' => [
                    [['text' => '🔙 Kembali ke Katalog', 'callback_data' => 'menu:catalog']],
                    [['text' => '🏠 Menu Utama', 'callback_data' => 'menu:main']],
                ],
            ];

            return $this->sendOrEdit($chatId, $messageId, $text, $keyboard);
        }

        $text = "📂 <b>PRODUK READY - {$categoryName}</b>\n\n"
            . "Silakan pilih produk untuk melihat detail:";

        $buttons = [];
        foreach ($products as $p) {
            $buttons[] = [
                ['text' => "🛒 {$p->name} ({$p->formatted_price})", 'callback_data' => "product:{$p->id}"],
            ];
        }

        $buttons[] = [['text' => '🔙 Kembali ke Katalog', 'callback_data' => 'menu:catalog']];

        return $this->sendOrEdit($chatId, $messageId, $text, ['inline_keyboard' => $buttons]);
    }

    public function sendProductDetail(int|string $chatId, Product $product, ?int $messageId = null): array
    {
        if (!$product->is_active || $product->stock_qty <= 0) {
            $text = "⚠️ <b>Produk Tidak Tersedia</b>\n\n"
                . "Maaf, produk <b>{$product->name}</b> saat ini sedang habis atau nonaktif.";

            $keyboard = [
                'inline_keyboard' => [
                    [['text' => '🛍️ Lihat Produk Lain', 'callback_data' => 'menu:catalog']],
                    [['text' => '🏠 Menu Utama', 'callback_data' => 'menu:main']],
                ],
            ];

            return $this->sendOrEdit($chatId, $messageId, $text, $keyboard);
        }

        $desc = $product->description ?: 'Akses premium sesuai durasi tertera.';
        $text = "🤖 <b>{$product->name}</b>\n\n"
            . "📅 <b>Durasi:</b> {$product->duration_label}\n"
            . "💰 <b>Harga:</b> {$product->formatted_price}\n"
            . "📦 <b>Stock Tersedia:</b> {$product->stock_qty}\n\n"
            . "📝 <b>Keterangan:</b>\n{$desc}";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '🛒 BELI SEKARANG', 'callback_data' => "checkout:{$product->id}"]],
                [['text' => '🔙 Kembali ke Katalog', 'callback_data' => 'menu:catalog']],
            ],
        ];

        return $this->sendOrEdit($chatId, $messageId, $text, $keyboard);
    }

    public function sendCheckoutConfirmation(int|string $chatId, Product $product, ?int $messageId = null): array
    {
        $text = "🧾 <b>CHECKOUT PESANAN</b>\n\n"
            . "<b>Produk:</b> {$product->name}\n"
            . "<b>Durasi:</b> {$product->duration_label}\n"
            . "<b>Harga:</b> {$product->formatted_price}\n"
            . "<b>Jumlah:</b> 1\n"
            . "<b>Total Tagihan:</b> {$product->formatted_price}\n\n"
            . "Lanjutkan untuk mendapatkan invoice dan pembayaran QRIS:";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '💳 BUAT PESANAN', 'callback_data' => "create_order:{$product->id}"]],
                [['text' => '❌ BATAL', 'callback_data' => "product:{$product->id}"]],
            ],
        ];

        return $this->sendOrEdit($chatId, $messageId, $text, $keyboard);
    }

    public function sendInvoice(int|string $chatId, Order $order): array
    {
        $text = "🧾 <b>INVOICE PEMBAYARAN</b>\n\n"
            . "<b>Invoice:</b> <code>{$order->invoice_number}</code>\n"
            . "<b>Produk:</b> {$order->product_name}\n"
            . "<b>Total:</b> <b>{$order->formatted_amount}</b>\n\n"
            . "Silakan scan & transfer QRIS di bawah ini.\n"
            . "Setelah pembayaran selesai, klik tombol <b>✅ SAYA SUDAH BAYAR</b>:";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '✅ SAYA SUDAH BAYAR', 'callback_data' => "paid:{$order->id}"]],
                [['text' => '❌ BATALKAN PESANAN', 'callback_data' => "cancel_order:{$order->id}"]],
            ],
        ];

        $settingQris = \App\Models\Setting::get('qris_image');
        if ($settingQris && \Illuminate\Support\Facades\Storage::disk('public')->exists($settingQris)) {
            $absPath = \Illuminate\Support\Facades\Storage::disk('public')->path($settingQris);
            return $this->client->sendPhoto($chatId, $absPath, $text, $keyboard);
        }

        $qrisPath = config('services.telegram.qris_image_path');
        if ($qrisPath && file_exists(base_path($qrisPath))) {
            return $this->client->sendPhoto($chatId, base_path($qrisPath), $text, $keyboard);
        }

        return $this->client->sendMessage($chatId, $text, $keyboard);
    }

    public function sendPaymentWaitingConfirmation(int|string $chatId, Order $order, ?int $messageId = null): array
    {
        $text = "⏳ <b>Pembayaran sedang diverifikasi admin!</b>\n\n"
            . "<b>Invoice:</b> <code>{$order->invoice_number}</code>\n"
            . "<b>Produk:</b> {$order->product_name}\n"
            . "<b>Total:</b> {$order->formatted_amount}\n\n"
            . "Mohon tunggu sejenak. Setelah verifikasi berhasil, akun akan dikirimkan otomatis via Delivery Bot.";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '📦 Pesanan Saya', 'callback_data' => 'menu:my_orders']],
                [['text' => '🏠 Menu Utama', 'callback_data' => 'menu:main']],
            ],
        ];

        return $this->sendOrEdit($chatId, $messageId, $text, $keyboard);
    }

    public function sendMyOrders(int|string $chatId, User $user, ?int $messageId = null): array
    {
        $orders = Order::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        if ($orders->isEmpty()) {
            $text = "📦 <b>PESANAN SAYA</b>\n\n"
                . "Anda belum memiliki riwayat pesanan.";

            $keyboard = [
                'inline_keyboard' => [
                    [['text' => '🛍️ Belanja Sekarang', 'callback_data' => 'menu:catalog']],
                    [['text' => '🏠 Menu Utama', 'callback_data' => 'menu:main']],
                ],
            ];

            return $this->sendOrEdit($chatId, $messageId, $text, $keyboard);
        }

        $text = "📦 <b>RIWAYAT PESANAN SAYA</b>\n\n"
            . "Daftar 10 transaksi terakhir Anda. Klik salah satu untuk melihat detail:";

        $buttons = [];
        foreach ($orders as $o) {
            $buttons[] = [
                ['text' => "{$o->invoice_number} - {$o->customer_status_label}", 'callback_data' => "order_detail:{$o->id}"],
            ];
        }

        $buttons[] = [['text' => '🏠 Menu Utama', 'callback_data' => 'menu:main']];

        return $this->sendOrEdit($chatId, $messageId, $text, ['inline_keyboard' => $buttons]);
    }

    public function sendOrderDetail(int|string $chatId, Order $order, ?int $messageId = null): array
    {
        $text = "📦 <b>DETAIL PESANAN</b>\n\n"
            . "<b>Invoice:</b> <code>{$order->invoice_number}</code>\n"
            . "<b>Produk:</b> {$order->product_name}\n"
            . "<b>Total:</b> {$order->formatted_amount}\n"
            . "<b>Status:</b> {$order->customer_status_label}\n"
            . "<b>Tanggal:</b> " . $order->created_at->format('d M Y H:i');

        $buttons = [];

        if (in_array($order->payment_status, [Order::PAYMENT_PAID]) || $order->fulfillment_status === Order::FULFILLMENT_SENT) {
            $buttons[] = [
                ['text' => '📦 BUKA DELIVERY BOT', 'url' => "https://t.me/{$this->deliveryBotUsername}?start=activate"],
            ];
        } elseif ($order->payment_status === Order::PAYMENT_PENDING && $order->order_status === Order::ORDER_WAITING_PAYMENT) {
            $buttons[] = [
                ['text' => '✅ SAYA SUDAH BAYAR', 'callback_data' => "paid:{$order->id}"],
                ['text' => '❌ BATALKAN', 'callback_data' => "cancel_order:{$order->id}"],
            ];
        }

        $buttons[] = [['text' => '🔙 Kembali ke Pesanan Saya', 'callback_data' => 'menu:my_orders']];
        $buttons[] = [['text' => '🏠 Menu Utama', 'callback_data' => 'menu:main']];

        return $this->sendOrEdit($chatId, $messageId, $text, ['inline_keyboard' => $buttons]);
    }

    public function sendPaymentGuide(int|string $chatId, ?int $messageId = null): array
    {
        $text = "💳 <b>CARA PEMBAYARAN</b>\n\n"
            . "1. Pilih produk di <b>Katalog Produk</b>.\n"
            . "2. Klik <b>Beli Sekarang</b> dan konfirmasi checkout.\n"
            . "3. Scan barcode QRIS yang ditampilkan sistem.\n"
            . "4. Bayar sesuai nominal total di invoice (BCA, Mandiri, BRI, GoPay, OVO, Dana, ShopeePay, dll).\n"
            . "5. Klik tombol <b>✅ SAYA SUDAH BAYAR</b>.\n"
            . "6. Admin akan memverifikasi dan mengirimkan akun ke <b>Delivery Bot</b> Anda.\n\n"
            . "⚠️ <i>Pastikan nominal transfer sesuai dengan angka invoice.</i>";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '🛍️ Mulai Belanja', 'callback_data' => 'menu:catalog']],
                [['text' => '🏠 Menu Utama', 'callback_data' => 'menu:main']],
            ],
        ];

        return $this->sendOrEdit($chatId, $messageId, $text, $keyboard);
    }

    public function sendHelpMenu(int|string $chatId, ?int $messageId = null): array
    {
        $text = "🆘 <b>BANTUAN TRANSAKSI</b>\n\n"
            . "Pilih kendala yang ingin Anda tanyakan:";

        $keyboard = [
            'inline_keyboard' => [
                [['text' => '💳 Masalah Pembayaran', 'callback_data' => 'help:payment']],
                [['text' => '📦 Pesanan Belum Diproses', 'callback_data' => 'help:processing']],
                [['text' => '🔑 Masalah Akun / Credential', 'callback_data' => 'help:account']],
                [['text' => '🏠 Menu Utama', 'callback_data' => 'menu:main']],
            ],
        ];

        return $this->sendOrEdit($chatId, $messageId, $text, $keyboard);
    }

    public function sendHelpDetail(int|string $chatId, string $topic, ?int $messageId = null): array
    {
        $text = match ($topic) {
            'payment' => "💳 <b>Bantuan Pembayaran</b>\n\n"
                . "Jika saldo Anda sudah terpotong namun status belum berubah:\n"
                . "1. Pastikan Anda sudah klik 'Saya Sudah Bayar'.\n"
                . "2. Admin memverifikasi mutasi dalam 5-15 menit pada jam operasional.",
            'processing' => "📦 <b>Pesanan Belum Diproses</b>\n\n"
                . "Pesanan diproses segera setelah pembayaran diverifikasi. Kredensial akun akan langsung dikirim melalui <b>Delivery Bot</b>.",
            'account' => "🔑 <b>Masalah Akun / Kredensial</b>\n\n"
                . "Untuk kendala akun (password salah, tidak bisa login, garansi), penanganan resmi dilakukan melalui <b>Delivery Bot</b>.\n\n"
                . "Silakan buka Delivery Bot dan pilih menu <b>❓ ADA MASALAH</b>.",
            default => "Silakan pilih topik bantuan.",
        };

        $buttons = [];
        if ($topic === 'account') {
            $buttons[] = [
                ['text' => '💬 BUKA DELIVERY BOT', 'url' => "https://t.me/{$this->deliveryBotUsername}?start=activate"],
            ];
        }

        $buttons[] = [['text' => '🔙 Kembali ke Bantuan', 'callback_data' => 'menu:help']];
        $buttons[] = [['text' => '🏠 Menu Utama', 'callback_data' => 'menu:main']];

        return $this->sendOrEdit($chatId, $messageId, $text, ['inline_keyboard' => $buttons]);
    }

    protected function sendOrEdit(int|string $chatId, ?int $messageId, string $text, array $keyboard): array
    {
        if ($messageId !== null) {
            $result = $this->client->editMessageText($chatId, $messageId, $text, $keyboard);
            if ($result['ok'] ?? false) {
                return $result;
            }
        }

        return $this->client->sendMessage($chatId, $text, $keyboard);
    }
}
