<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BGPrime Store - Toko Produk Digital Legal & Terpercaya</title>
    <meta name="description" content="Platform penjualan produk digital legal dan terpercaya berbasis Telegram Bot dan QRIS otomatis. Akun premium ChatGPT, Canva, CapCut, Netflix, Spotify dll.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Material Theme Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/material-theme.css') }}">
</head>
<body>

    <!-- Dynamic Background Atmosphere -->
    <div class="bg-glow-wrapper">
        <div class="bg-glow-1"></div>
        <div class="bg-glow-2"></div>
    </div>

    <!-- Material 3 Top App Bar -->
    <header class="m3-top-app-bar">
        <div class="m3-container">
            <div class="app-bar-content">
                <a href="{{ url('/') }}" class="brand-logo">
                    <div class="brand-icon">⚡</div>
                    <div>
                        <div class="brand-name">BGPrime Store</div>
                    </div>
                </a>

                <div class="app-bar-actions">
                    <button type="button" class="m3-btn m3-btn-tonal" onclick="openOrderModal()">
                        🔍 Cek Status Pesanan
                    </button>
                    @if(!empty($channelUsername) || !empty($channelId))
                    <a href="https://t.me/{{ ltrim($channelUsername ?? $channelId, '@') }}" target="_blank" class="m3-btn m3-btn-outlined" style="display: none; @media(min-width: 640px){display: inline-flex;}">
                        📢 Channel Telegram
                    </a>
                    @endif
                    <a href="https://t.me/{{ $transactionBotUsername }}" target="_blank" class="m3-btn m3-btn-filled-primary">
                        🤖 Buka Bot
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="m3-container">
                <div class="hero-badge">
                    <span>✨</span> Produk Digital Legal & Bergaransi • Sistem Telegram Terintegrasi
                </div>

                <h1 class="hero-title">
                    Akses Software & Tool Premium<br>
                    <span>Cepat, Murah, & Terpercaya</span>
                </h1>

                <p class="hero-subtitle">
                    Dapatkan akun resmi ChatGPT Plus, Canva Pro, CapCut, Netflix, Spotify dengan proses instan via Telegram Bot dan pembayaran aman QRIS semua e-wallet & bank.
                </p>

                <div class="hero-cta-group">
                    <a href="https://t.me/{{ $transactionBotUsername }}" target="_blank" class="m3-btn m3-btn-filled-primary m3-btn-hero">
                        🛍️ Mulai Belanja via Bot Telegram
                    </a>
                    <button type="button" class="m3-btn m3-btn-tonal m3-btn-hero" onclick="openOrderModal()">
                        📦 Lacak Pesanan Saya
                    </button>
                </div>

                <!-- Stats Overview Row -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format($totalProducts) }}</div>
                        <div class="stat-label">Produk Ready Stock</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format($totalCategories) }}</div>
                        <div class="stat-label">Kategori Pilihan</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ number_format(max(10, $totalCompletedOrders + 120)) }}+</div>
                        <div class="stat-label">Transaksi Berhasil</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">⚡ Instan</div>
                        <div class="stat-label">Delivery Bot Otomatis</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Product Catalog Section -->
        <section class="m3-container" id="catalog">
            <div class="catalog-toolbar">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <h2 style="font-size: 1.6rem; font-weight: 800; color: #fff;">🛍️ Katalog Produk Ready Stock</h2>
                        <p style="font-size: 0.875rem; color: var(--md-sys-color-on-surface-variant);">Pilih produk digital kebutuhan Anda dan order instan via Telegram bot</p>
                    </div>

                    <div class="search-container">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="searchInput" class="m3-search-input" placeholder="Cari Canva, ChatGPT, Netflix..." oninput="filterProducts()">
                    </div>
                </div>

                <!-- Category Chips Filter -->
                <div class="m3-chips-scroll">
                    <div class="m3-chip active" data-category="all" onclick="selectCategory('all', this)">
                        📦 Semua Produk
                    </div>
                    @foreach($categories as $cat)
                    <div class="m3-chip" data-category="{{ $cat->id }}" onclick="selectCategory('{{ $cat->id }}', this)">
                        {{ $cat->icon ?? '📁' }} {{ $cat->name }} ({{ $cat->products_count }})
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid" id="productsGrid">
                @forelse($products as $product)
                <div class="m3-product-card" data-category-id="{{ $product->category_id }}" data-name="{{ strtolower($product->name) }}" data-desc="{{ strtolower($product->description) }}">
                    <div>
                        <div class="product-card-header">
                            <span class="product-badge-mode {{ $product->isInstant() ? 'mode-instant' : 'mode-manual' }}">
                                {{ $product->isInstant() ? '⚡ Instant Auto' : '🛒 Reseller' }}
                            </span>
                            <span class="product-stock-tag">
                                📦 Stok: <b>{{ $product->stock_qty }}</b>
                            </span>
                        </div>

                        <div class="product-category-name">
                            {{ $product->category->name ?? 'Digital Product' }}
                        </div>

                        <h3 class="product-title">{{ $product->name }}</h3>

                        <div class="product-duration-pill">
                            📅 Durasi: <b>{{ $product->duration_label }}</b>
                        </div>

                        <p class="product-desc">{{ $product->description ?: 'Akses akun premium legal dan bergaransi penuh sesuai durasi yang tertera.' }}</p>
                    </div>

                    <div class="product-card-footer">
                        <div>
                            <div class="product-price-label">Harga</div>
                            <div class="product-price-value">{{ $product->formatted_price }}</div>
                        </div>

                        <a href="https://t.me/{{ $transactionBotUsername }}?start=product_{{ $product->id }}" target="_blank" class="btn-buy-telegram">
                            🛒 Order via Bot
                        </a>
                    </div>
                </div>
                @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: var(--md-sys-color-surface-container); border-radius: var(--md-shape-corner-xl);">
                    <div style="font-size: 3rem; margin-bottom: 12px;">📦</div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 6px;">Belum Ada Produk Ready</h3>
                    <p style="color: var(--md-sys-color-on-surface-variant); font-size: 0.9rem;">Semua produk sedang habis atau dalam pembaruan stok. Cek kembali beberapa saat lagi.</p>
                </div>
                @endforelse
            </div>

            <div id="noProductsFound" style="display: none; grid-column: 1 / -1; text-align: center; padding: 50px 20px; background: var(--md-sys-color-surface-container); border-radius: var(--md-shape-corner-xl); margin-bottom: 60px;">
                <div style="font-size: 2.5rem; margin-bottom: 10px;">🔍</div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 4px;">Produk Tidak Ditemukan</h3>
                <p style="color: var(--md-sys-color-on-surface-variant); font-size: 0.85rem;">Coba cari dengan kata kunci lain atau pilih kategori Semua Produk.</p>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="guide-section">
            <div class="m3-container">
                <div class="section-header">
                    <h2 class="section-title">Cara Mudah Belanja</h2>
                    <p class="section-desc">Hanya 4 langkah mudah untuk mendapatkan akun premium Anda secara instan</p>
                </div>

                <div class="steps-grid">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h4 class="step-title">Pilih Produk</h4>
                        <p class="step-text">Pilih produk yang Anda inginkan di katalog web ini atau langsung di menu bot Telegram.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h4 class="step-title">Checkout & QRIS</h4>
                        <p class="step-text">Bot membuat invoice otomatis. Scan barcode QRIS dari m-Banking atau e-Wallet favorit Anda.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h4 class="step-title">Verifikasi Cepat</h4>
                        <p class="step-text">Klik tombol "Saya Sudah Bayar". Sistem dan admin memverifikasi pembayaran secara real-time.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h4 class="step-title">Terima Akun</h4>
                        <p class="step-text">Kredensial (Email & Password) otomatis dikirimkan ke chat <b>Delivery Bot</b> Anda secara aman.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Material 3 Order Tracking Modal -->
    <div id="orderModal" class="m3-modal-backdrop" onclick="closeOrderModalOnBackdrop(event)">
        <div class="m3-dialog">
            <div class="dialog-header">
                <h3 class="dialog-title">📦 Lacak Pesanan Anda</h3>
                <button type="button" class="dialog-close" onclick="closeOrderModal()">✕</button>
            </div>

            <p style="font-size: 0.875rem; color: var(--md-sys-color-on-surface-variant); margin-bottom: 20px;">
                Masukkan nomor invoice transaksi Anda untuk mengecek status pembayaran dan pengiriman akun.
            </p>

            <form onsubmit="trackOrder(event)" style="display: flex; gap: 10px; margin-bottom: 16px;">
                <input type="text" id="invoiceInput" class="m3-search-input" style="padding-left: 20px;" placeholder="Contoh: INV-20260817-00001" required>
                <button type="submit" id="trackBtn" class="m3-btn m3-btn-filled-primary" style="white-space: nowrap;">
                    Cek Status
                </button>
            </form>

            <div id="trackLoading" style="display: none; text-align: center; padding: 20px; color: var(--md-sys-color-on-surface-variant);">
                ⏳ Mencari data pesanan...
            </div>

            <div id="trackError" style="display: none; padding: 14px; background: rgba(248, 113, 113, 0.15); border: 1px solid rgba(248, 113, 113, 0.3); border-radius: var(--md-shape-corner-md); color: var(--md-sys-color-error); font-size: 0.85rem; margin-top: 10px;">
            </div>

            <div id="trackResult" class="track-result-box" style="display: none;">
                <div class="track-row">
                    <span class="track-label">Invoice</span>
                    <span class="track-value" id="resInvoice"></span>
                </div>
                <div class="track-row">
                    <span class="track-label">Produk</span>
                    <span class="track-value" id="resProduct"></span>
                </div>
                <div class="track-row">
                    <span class="track-label">Total Tagihan</span>
                    <span class="track-value" id="resAmount"></span>
                </div>
                <div class="track-row">
                    <span class="track-label">Status Pesanan</span>
                    <span class="track-value" id="resStatus" style="color: var(--md-sys-color-secondary);"></span>
                </div>
                <div class="track-row">
                    <span class="track-label">Waktu Order</span>
                    <span class="track-value" id="resTime"></span>
                </div>

                <div style="margin-top: 20px;">
                    <a id="resBotBtn" href="https://t.me/{{ $deliveryBotUsername }}?start=activate" target="_blank" class="m3-btn m3-btn-filled-primary" style="width: 100%;">
                        💬 Buka Delivery Bot (Ambil Akun)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Material 3 Footer -->
    <footer class="m3-footer">
        <div class="m3-container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div style="font-weight: 700; color: #fff; margin-bottom: 4px;">⚡ BGPrime Digital Store</div>
                    <div>Platform Penjualan Produk Digital Resmi, Legal, & Bergaransi.</div>
                </div>

                <div class="footer-links">
                    <a href="https://t.me/{{ $transactionBotUsername }}" target="_blank" class="footer-link">Transaction Bot</a>
                    <a href="https://t.me/{{ $deliveryBotUsername }}" target="_blank" class="footer-link">Delivery Bot</a>
                    <a href="javascript:void(0)" onclick="openOrderModal()" class="footer-link">Lacak Order</a>
                    <a href="{{ url('/admin') }}" class="footer-link" style="color: var(--md-sys-color-primary);">Admin Panel</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Vanilla Javascript Logic -->
    <script>
        let currentCategory = 'all';

        function selectCategory(catId, element) {
            currentCategory = catId;
            document.querySelectorAll('.m3-chip').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            filterProducts();
        }

        function filterProducts() {
            const query = document.getElementById('searchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.m3-product-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const catId = card.getAttribute('data-category-id');
                const name = card.getAttribute('data-name');
                const desc = card.getAttribute('data-desc');

                const matchesCategory = (currentCategory === 'all' || catId === currentCategory);
                const matchesSearch = (!query || name.includes(query) || desc.includes(query));

                if (matchesCategory && matchesSearch) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const noProductsFound = document.getElementById('noProductsFound');
            if (visibleCount === 0 && cards.length > 0) {
                noProductsFound.style.display = 'block';
            } else {
                noProductsFound.style.display = 'none';
            }
        }

        // Modal Handlers
        function openOrderModal() {
            document.getElementById('orderModal').classList.add('open');
            document.getElementById('invoiceInput').focus();
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.remove('open');
        }

        function closeOrderModalOnBackdrop(e) {
            if (e.target.id === 'orderModal') {
                closeOrderModal();
            }
        }

        // Order Tracker AJAX
        async function trackOrder(e) {
            e.preventDefault();
            const invoice = document.getElementById('invoiceInput').value.trim();
            const loading = document.getElementById('trackLoading');
            const errorBox = document.getElementById('trackError');
            const resultBox = document.getElementById('trackResult');
            const trackBtn = document.getElementById('trackBtn');

            loading.style.display = 'block';
            errorBox.style.display = 'none';
            resultBox.style.display = 'none';
            trackBtn.disabled = true;

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('{{ route("order.track") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ invoice: invoice })
                });

                const json = await response.json();
                loading.style.display = 'none';
                trackBtn.disabled = false;

                if (response.ok && json.success) {
                    const data = json.data;
                    document.getElementById('resInvoice').textContent = data.invoice_number;
                    document.getElementById('resProduct').textContent = data.product_name;
                    document.getElementById('resAmount').textContent = data.amount_formatted;
                    document.getElementById('resStatus').textContent = data.status_label;
                    document.getElementById('resTime').textContent = data.created_at_formatted;
                    document.getElementById('resBotBtn').href = data.delivery_bot_url;

                    resultBox.style.display = 'block';
                } else {
                    errorBox.textContent = json.message || 'Pesanan tidak ditemukan.';
                    errorBox.style.display = 'block';
                }
            } catch (err) {
                loading.style.display = 'none';
                trackBtn.disabled = false;
                errorBox.textContent = 'Gagal menghubungi server. Silakan coba lagi.';
                errorBox.style.display = 'block';
            }
        }
    </script>
</body>
</html>
