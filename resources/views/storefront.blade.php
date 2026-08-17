<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BGPrime — Software & Digital Tools Store</title>
    <meta name="description" content="Toko akun software dan tools digital resmi: ChatGPT Plus, Canva Pro, CapCut, Netflix, Spotify. Pembayaran QRIS otomatis dan pengiriman via Telegram.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('css/material-theme.css') }}">
</head>
<body>

    <div class="grid-background"></div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container navbar-inner">
            <a href="{{ url('/') }}" class="brand">
                <div class="brand-badge">⚡</div>
                <div class="brand-text">BGPrime</div>
            </a>

            <div class="nav-actions">
                <div class="nav-status-pill">
                    <span class="status-dot"></span>
                    <span>Bot Online</span>
                </div>
                <button type="button" class="btn btn-ghost" onclick="openModal()">
                    Lacak Order
                </button>
                <a href="https://t.me/{{ $transactionBotUsername }}" target="_blank" class="btn btn-telegram">
                    Telegram Bot
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-pill">
                Reseller & Akun Software Resmi
            </div>
            <h1 class="hero-title">
                Software & Tool Digital Premium.<br>Instan Tanpa Ribet.
            </h1>
            <p class="hero-subtitle">
                Beli akun ChatGPT Plus, Canva Pro, CapCut, dan layanan streaming favorit Anda. Pembayaran aman via QRIS, pengiriman otomatis ke Telegram.
            </p>

            <div class="hero-actions">
                <a href="https://t.me/{{ $transactionBotUsername }}" target="_blank" class="btn btn-primary" style="padding: 12px 24px; font-size: 0.95rem;">
                    Buka Bot Transaksi
                </a>
                <button type="button" class="btn btn-ghost" style="padding: 12px 24px; font-size: 0.95rem;" onclick="openModal()">
                    Cek Status Transaksi
                </button>
            </div>
        </section>

        <!-- Feature Trust Bar -->
        <div class="feature-row">
            <div class="feature-item">
                <span class="feature-icon">🛡️</span>
                <span>Garansi Akun Aktif</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">⚡</span>
                <span>Auto-Delivery Bot</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">💳</span>
                <span>QRIS Semua Bank & E-Wallet</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">💬</span>
                <span>Customer Support 24/7</span>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="filter-bar">
            <div class="chips-wrapper">
                <div class="chip active" data-category="all" onclick="setCategory('all', this)">
                    Semua ({{ $products->count() }})
                </div>
                @foreach($categories as $cat)
                <div class="chip" data-category="{{ $cat->id }}" onclick="setCategory('{{ $cat->id }}', this)">
                    {{ $cat->name }} ({{ $cat->products_count }})
                </div>
                @endforeach
            </div>

            <div class="search-box">
                <span class="search-box-icon">🔍</span>
                <input type="text" id="searchInput" placeholder="Cari produk..." oninput="filterCatalog()">
            </div>
        </div>

        <!-- Catalog Grid -->
        <div class="products-grid" id="productsGrid">
            @forelse($products as $product)
            <div class="product-card" data-cat="{{ $product->category_id }}" data-name="{{ strtolower($product->name) }}" data-desc="{{ strtolower($product->description) }}">
                <div class="card-top">
                    <div class="card-header">
                        <span class="badge-tag {{ $product->isInstant() ? 'badge-instant' : 'badge-manual' }}">
                            {{ $product->isInstant() ? '⚡ Instant' : '🛒 Reseller' }}
                        </span>
                        <span class="stock-tag">
                            Stok: {{ $product->stock_qty }}
                        </span>
                    </div>

                    <div class="card-category">{{ $product->category->name ?? 'Tools' }}</div>
                    <h3 class="card-title">{{ $product->name }}</h3>
                    <p class="card-desc">{{ $product->description ?: 'Akun resmi bergaransi sesuai durasi yang dipilih.' }}</p>
                    <span class="card-duration">⏳ {{ $product->duration_label }}</span>
                </div>

                <div class="card-bottom">
                    <div class="price-box">
                        <span class="price-label">Harga</span>
                        <span class="price-value">{{ $product->formatted_price }}</span>
                    </div>

                    <a href="https://t.me/{{ $transactionBotUsername }}?start=product_{{ $product->id }}" target="_blank" class="btn-card-buy">
                        Beli Sekarang
                    </a>
                </div>
            </div>
            @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: var(--text-muted);">
                Saat ini belum ada produk yang siap dijual.
            </div>
            @endforelse
        </div>

        <div id="noResults" style="display: none; grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--text-muted);">
            Produk dengan kata kunci tersebut tidak ditemukan.
        </div>
    </main>

    <!-- Order Tracking Modal -->
    <div id="orderModal" class="modal-backdrop" onclick="closeOnBackdrop(event)">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title">Lacak Pesanan</h3>
                <button type="button" class="modal-close" onclick="closeModal()">✕</button>
            </div>

            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 16px;">
                Masukkan nomor invoice transaksi untuk melihat status pembayaran & kredensial akun.
            </p>

            <form onsubmit="searchOrder(event)" style="display: flex; gap: 8px;">
                <input type="text" id="invInput" style="flex: 1; height: 40px; background: var(--bg-surface-elevated); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0 12px; color: #fff; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;" placeholder="INV-20260817-00001" required>
                <button type="submit" id="searchBtn" class="btn btn-primary" style="padding: 0 16px;">
                    Cari
                </button>
            </form>

            <div id="trackLoading" style="display: none; font-size: 0.85rem; color: var(--text-muted); margin-top: 14px; text-align: center;">
                Sedang mencari data...
            </div>

            <div id="trackError" style="display: none; margin-top: 14px; font-size: 0.825rem; color: #f87171;"></div>

            <div id="trackResult" class="result-card" style="display: none;">
                <div class="result-row">
                    <span style="color: var(--text-muted);">Invoice</span>
                    <span id="rInvoice" style="font-family: 'JetBrains Mono', monospace; font-weight: 600;"></span>
                </div>
                <div class="result-row">
                    <span style="color: var(--text-muted);">Produk</span>
                    <span id="rProduct" style="font-weight: 600;"></span>
                </div>
                <div class="result-row">
                    <span style="color: var(--text-muted);">Total</span>
                    <span id="rAmount" style="font-family: 'JetBrains Mono', monospace; font-weight: 600;"></span>
                </div>
                <div class="result-row">
                    <span style="color: var(--text-muted);">Status</span>
                    <span id="rStatus" style="color: var(--success); font-weight: 600;"></span>
                </div>
                <div style="margin-top: 16px;">
                    <a id="rBotLink" href="https://t.me/{{ $deliveryBotUsername }}?start=activate" target="_blank" class="btn btn-telegram" style="width: 100%;">
                        Buka Delivery Bot
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-inner">
            <div>
                © {{ date('Y') }} BGPrime Digital Store. All rights reserved.
            </div>

            <div class="footer-links">
                <a href="https://t.me/{{ $transactionBotUsername }}" target="_blank">Transaction Bot</a>
                <a href="https://t.me/{{ $deliveryBotUsername }}" target="_blank">Delivery Bot</a>
                <a href="{{ url('/admin') }}">Admin Panel</a>
            </div>
        </div>
    </footer>

    <!-- JS Logic -->
    <script>
        let activeCat = 'all';

        function setCategory(id, el) {
            activeCat = id;
            document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
            filterCatalog();
        }

        function filterCatalog() {
            const q = document.getElementById('searchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.product-card');
            let count = 0;

            cards.forEach(card => {
                const cat = card.getAttribute('data-cat');
                const name = card.getAttribute('data-name');
                const desc = card.getAttribute('data-desc');

                const catMatch = (activeCat === 'all' || cat === activeCat);
                const searchMatch = (!q || name.includes(q) || desc.includes(q));

                if (catMatch && searchMatch) {
                    card.style.display = 'flex';
                    count++;
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('noResults').style.display = (count === 0 && cards.length > 0) ? 'block' : 'none';
        }

        function openModal() {
            document.getElementById('orderModal').classList.add('open');
            document.getElementById('invInput').focus();
        }

        function closeModal() {
            document.getElementById('orderModal').classList.remove('open');
        }

        function closeOnBackdrop(e) {
            if (e.target.id === 'orderModal') closeModal();
        }

        async function searchOrder(e) {
            e.preventDefault();
            const invoice = document.getElementById('invInput').value.trim();
            const loading = document.getElementById('trackLoading');
            const err = document.getElementById('trackError');
            const res = document.getElementById('trackResult');

            loading.style.display = 'block';
            err.style.display = 'none';
            res.style.display = 'none';

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

                if (response.ok && json.success) {
                    const data = json.data;
                    document.getElementById('rInvoice').textContent = data.invoice_number;
                    document.getElementById('rProduct').textContent = data.product_name;
                    document.getElementById('rAmount').textContent = data.amount_formatted;
                    document.getElementById('rStatus').textContent = data.status_label;
                    document.getElementById('rBotLink').href = data.delivery_bot_url;
                    res.style.display = 'block';
                } else {
                    err.textContent = json.message || 'Nomor invoice tidak ditemukan.';
                    err.style.display = 'block';
                }
            } catch (error) {
                loading.style.display = 'none';
                err.textContent = 'Gagal memuat status pesanan.';
                err.style.display = 'block';
            }
        }
    </script>
</body>
</html>
