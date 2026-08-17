\# Product Requirements Document



\## Telegram Digital Product Store MVP



\*\*Version:\*\* 2.0

\*\*Status:\*\* Final MVP Scope

\*\*Platform:\*\* Telegram + Laravel Web Admin

\*\*Backend:\*\* Laravel

\*\*Admin Panel:\*\* Filament

\*\*Database:\*\* MySQL / PostgreSQL

\*\*Main Interface:\*\* Telegram Bot

\*\*Target:\*\* Penjualan produk digital yang legal dan memiliki hak distribusi/resell



\---



\# 1. Product Overview



Sistem ini adalah platform penjualan produk digital berbasis Telegram.



Customer menemukan produk melalui Telegram Channel, kemudian melakukan transaksi melalui Telegram Transaction Bot.



Setelah pembayaran berhasil, order masuk ke Laravel Admin Panel untuk diproses secara manual oleh admin.



Admin dapat membeli/mendapatkan akun dari supplier, kemudian memasukkan informasi akun seperti email, username, password, dan catatan melalui Laravel Admin Panel.



Setelah admin klik \*\*Send to Customer\*\*, sistem mengirim informasi akun ke customer menggunakan Telegram Delivery Bot.



Arsitektur utama:



```text

Telegram Channel

&#x20;     ↓

Transaction Bot

&#x20;     ↓

Checkout

&#x20;     ↓

QRIS

&#x20;     ↓

Payment

&#x20;     ↓

Stock -1

&#x20;     ↓

Waiting Fulfillment

&#x20;     ↓

Laravel Filament Admin

&#x20;     ↓

Admin beli / ambil akun ke Supplier

&#x20;     ↓

Input Email / Password

&#x20;     ↓

Send to Customer

&#x20;     ↓

Delivery Bot

&#x20;     ↓

Customer

```



\---



\# 2. System Components



Sistem terdiri dari empat komponen utama.



\## 2.1 Telegram Ready Stock Channel



Digunakan untuk:



\* Publish ready stock harian.

\* Informasi harga.

\* Promo sederhana.

\* Mengarahkan customer ke Transaction Bot.

\* Menampilkan tombol checkout langsung ke produk.



Contoh:



```text

🔥 READY STOCK HARI INI



🎨 Canva Pro 30 Hari

💰 Rp20.000

📦 Stock: 5



🎬 CapCut Pro

💰 Rp25.000

📦 Stock: 7



🤖 ChatGPT Premium

💰 Rp45.000

📦 Stock: 3



👇 Order sekarang

```



Button:



```text

\[ 🛒 Canva ]

\[ 🛒 CapCut ]

\[ 🛒 ChatGPT ]

```



Button menggunakan Telegram deep link menuju Transaction Bot.



Contoh:



```text

https://t.me/transaction\_bot?start=product\_15

```



\---



\## 2.2 Telegram Transaction Bot



Digunakan customer untuk:



\* Melihat katalog.

\* Melihat produk.

\* Checkout.

\* Mendapat invoice.

\* Melakukan pembayaran QRIS.

\* Melihat status order.

\* Melihat history order.

\* Mendapat bantuan terkait transaksi.



Transaction Bot \*\*tidak digunakan untuk mengirim credential akun\*\*.



\---



\## 2.3 Telegram Delivery \& Customer Service Bot



Digunakan untuk:



\* Mengirim akun.

\* Mengirim username/email.

\* Mengirim password.

\* Mengirim catatan akun.

\* Customer melaporkan masalah akun.

\* Komunikasi customer dengan admin terkait fulfillment.



Bot ini digunakan setelah order customer dibayar dan diproses.



\---



\## 2.4 Laravel Filament Admin



Digunakan oleh admin untuk:



\* Dashboard.

\* Product management.

\* Category management.

\* Stock management.

\* Order management.

\* Payment verification.

\* Fulfillment.

\* Kirim credential.

\* Customer management.

\* Publish ready stock.

\* Customer support.

\* Settings.



Laravel merupakan pusat seluruh business logic.



Telegram bot dan admin panel membaca database yang sama.



\---



\# 3. Main Architecture



```text

&#x20;                         LARAVEL

&#x20;                            │

&#x20;            ┌───────────────┼───────────────┐

&#x20;            │               │               │

&#x20;            ▼               ▼               ▼

&#x20;      READY STOCK      TRANSACTION      DELIVERY

&#x20;        CHANNEL            BOT             BOT

&#x20;            │               │               │

&#x20;            │               ▼               │

&#x20;            │            CUSTOMER ◄─────────┘

&#x20;            │               │

&#x20;            └──────────────►│

&#x20;                            │

&#x20;                            ▼

&#x20;                          ORDER

&#x20;                            │

&#x20;                            ▼

&#x20;                           QRIS

&#x20;                            │

&#x20;                            ▼

&#x20;                           PAID

&#x20;                            │

&#x20;                            ▼

&#x20;                        STOCK - 1

&#x20;                            │

&#x20;                            ▼

&#x20;                   WAITING FULFILLMENT

&#x20;                            │

&#x20;                            ▼

&#x20;                     FILAMENT ADMIN

&#x20;                            │

&#x20;                            ▼

&#x20;                   INPUT CREDENTIAL

&#x20;                            │

&#x20;                            ▼

&#x20;                    SEND TO CUSTOMER

&#x20;                            │

&#x20;                            ▼

&#x20;                      DELIVERY BOT

```



\---



\# 4. Roles



\## 4.1 Customer



Customer dapat:



\* Start Transaction Bot.

\* Membuka katalog.

\* Memilih kategori.

\* Memilih produk.

\* Melihat harga.

\* Melihat stock.

\* Checkout.

\* Mendapatkan invoice.

\* Membayar melalui QRIS.

\* Konfirmasi pembayaran.

\* Melihat order.

\* Melihat status order.

\* Membuka Delivery Bot.

\* Mendapat credential.

\* Melaporkan masalah akun.



\---



\## 4.2 Admin



Admin menggunakan Laravel Filament.



Admin dapat:



\* Login.

\* Kelola produk.

\* Kelola kategori.

\* Kelola stock.

\* Melihat order.

\* Approve pembayaran.

\* Reject pembayaran.

\* Memproses fulfillment.

\* Input credential.

\* Kirim credential.

\* Resend credential.

\* Publish ready stock.

\* Melihat customer.

\* Membalas customer support.



\---



\# 5. Transaction Bot Main Menu



Ketika customer menjalankan:



```text

/start

```



Tampilan:



```text

👋 Selamat datang di Premium Store!



Silakan pilih menu di bawah ini 👇



\[ 🛍️ Katalog Produk ]



\[ 📦 Pesanan Saya ]



\[ 💳 Cara Pembayaran ]

\[ 🆘 Bantuan ]

```



Menu MVP:



1\. Katalog Produk

2\. Pesanan Saya

3\. Cara Pembayaran

4\. Bantuan



Tidak perlu terlalu banyak menu pada MVP.



\---



\# 6. Product Categories



Produk dapat memiliki kategori.



Contoh:



```text

🎨 Design

📺 Streaming

🤖 AI Tools

🎵 Music

☁️ Cloud \& Productivity

📦 Lainnya

```



Tabel:



```text

categories



id

name

slug

icon

sort\_order

is\_active

created\_at

updated\_at

```



\---



\# 7. Product Catalog



Customer pilih:



```text

🛍️ Katalog Produk

```



Bot:



```text

🛍️ KATALOG PRODUK



Pilih kategori:



\[ 🎨 Design ]

\[ 📺 Streaming ]



\[ 🤖 AI Tools ]

\[ 🎵 Music ]



\[ 📦 Semua Produk ]

```



Pilih kategori:



```text

🤖 AI TOOLS

```



Bot:



```text

🤖 AI TOOLS



ChatGPT Premium

💰 Rp45.000

📦 Stock: 3



\[ 🛒 Lihat Produk ]



Claude Pro

💰 Rp40.000

📦 Stock: 2



\[ 🛒 Lihat Produk ]

```



Produk dengan:



```text

stock\_qty <= 0

```



dapat:



\* Tidak ditampilkan.



atau:



\* Ditampilkan dengan status Sold Out.



Untuk MVP pilih:



\*\*Tidak ditampilkan.\*\*



\---



\# 8. Product Detail



Contoh:



```text

🤖 ChatGPT Premium



📅 Durasi:

30 Hari



💰 Harga:

Rp45.000



📦 Stock:

3



📝 Keterangan:

Akses premium selama 30 hari.



\[ 🛒 BELI SEKARANG ]



\[ 🔙 Kembali ]

```



Callback:



```text

checkout:{product\_id}

```



\---



\# 9. Stock Concept



Stock pada sistem \*\*bukan jumlah akun yang sudah tersimpan\*\*.



Stock berarti:



> Jumlah order yang saat ini masih dapat diterima untuk produk tersebut.



Contoh:



```text

Canva Pro



Stock:

5

```



Customer melakukan pembayaran berhasil:



```text

5 → 4

```



Credential belum harus tersedia.



Admin dapat membeli credential dari supplier setelah order masuk.



\---



\# 10. Checkout Flow



Customer klik:



```text

🛒 BELI SEKARANG

```



Bot melakukan pengecekan ulang:



```text

product.is\_active = true

stock\_qty > 0

```



Kemudian:



```text

🧾 CHECKOUT



Produk:

ChatGPT Premium



Durasi:

30 Hari



Harga:

Rp45.000



Jumlah:

1



Total:

Rp45.000



\[ 💳 BUAT PESANAN ]



\[ ❌ BATAL ]

```



MVP hanya mendukung:



```text

quantity = 1

```



Tidak perlu shopping cart.



\---



\# 11. Order Creation



Saat customer klik:



```text

💳 BUAT PESANAN

```



Sistem generate invoice.



Format:



```text

INV-YYYYMMDD-XXXXX

```



Contoh:



```text

INV-20260817-00001

```



Order status awal:



```text

payment\_status = pending



order\_status = waiting\_payment



fulfillment\_status = pending

```



\---



\# 12. QRIS Payment



Setelah order dibuat:



```text

🧾 INVOICE



Invoice:

INV-20260817-00001



Produk:

ChatGPT Premium



Total:

Rp45.000



Silakan lakukan pembayaran menggunakan QRIS berikut.



\[ QRIS IMAGE ]



Setelah pembayaran selesai:



\[ ✅ SAYA SUDAH BAYAR ]



\[ ❌ BATALKAN PESANAN ]

```



QRIS dapat berupa QRIS static.



QRIS disimpan di:



```text

storage/app/public/qris/

```



atau melalui Settings Admin.



\---



\# 13. Payment Confirmation



Customer klik:



```text

✅ SAYA SUDAH BAYAR

```



Order:



```text

payment\_status:

pending

→ waiting\_confirmation

```



Bot:



```text

⏳ Pembayaran sedang diperiksa.



Invoice:

INV-20260817-00001



Mohon tunggu konfirmasi admin.

```



Order muncul di Laravel Admin.



\---



\# 14. Payment Verification



Admin membuka:



```text

Admin

→ Orders

→ Waiting Confirmation

```



Contoh:



```text

INV-20260817-00001



Customer:

@customer



Product:

ChatGPT Premium



Amount:

Rp45.000



Payment:

Waiting Confirmation



\[ APPROVE PAYMENT ]



\[ REJECT PAYMENT ]

```



\---



\# 15. Payment Approval



Saat admin klik:



```text

APPROVE PAYMENT

```



Backend melakukan:



```text

payment\_status:

waiting\_confirmation

→ paid

```



Order:



```text

order\_status:

waiting\_payment

→ processing

```



Fulfillment:



```text

fulfillment\_status:

pending

→ waiting

```



Stock:



```text

stock\_qty:

5 → 4

```



\---



\# 16. Stock Reduction Rule



Stock \*\*hanya berkurang setelah pembayaran di-approve atau payment gateway mengkonfirmasi pembayaran berhasil\*\*.



Bukan ketika:



\* Order dibuat.

\* Customer klik beli.

\* Customer klik "Saya Sudah Bayar".



Flow:



```text

Order dibuat

Stock = 5



Customer bayar

Stock = 5



Admin Approve

Stock = 4

```



Gunakan database transaction.



Contoh:



```php

DB::transaction(function () use ($order) {



&#x20;   $product = Product::lockForUpdate()

&#x20;       ->findOrFail($order->product\_id);



&#x20;   if ($product->stock\_qty <= 0) {

&#x20;       throw new Exception('Stock habis');

&#x20;   }



&#x20;   $product->decrement('stock\_qty');



&#x20;   $order->update(\[

&#x20;       'payment\_status' => 'paid',

&#x20;       'order\_status' => 'processing',

&#x20;       'fulfillment\_status' => 'waiting',

&#x20;       'paid\_at' => now(),

&#x20;   ]);

});

```



\---



\# 17. Transaction Bot After Payment



Setelah payment berhasil:



```text

✅ PEMBAYARAN BERHASIL



Invoice:

INV-20260817-00001



Produk:

ChatGPT Premium



Total:

Rp45.000



📦 Pesanan sedang diproses.



Informasi akun akan dikirim melalui

Delivery Bot.



\[ 📦 BUKA DELIVERY BOT ]

```



Deep link:



```text

https://t.me/delivery\_bot?start=activate

```



\---



\# 18. Delivery Bot Activation



Customer perlu membuka Delivery Bot.



Bot:



```text

👋 Selamat datang di Delivery Bot.



Bot ini digunakan untuk menerima

informasi akun dari pesanan Anda.



✅ Delivery Bot sudah aktif.



Silakan kembali ke Transaction Bot.

```



Database simpan:



```text

delivery\_bot\_started\_at

```



Customer Telegram ID tetap menggunakan Telegram ID yang sama.



\---



\# 19. Order Status



Customer membuka:



```text

📦 Pesanan Saya

```



Contoh:



```text

📦 PESANAN SAYA



INV-20260817-00001



ChatGPT Premium

Rp45.000



Status:

📦 Sedang Diproses



\[ LIHAT DETAIL ]

```



Status customer-friendly:



```text

⏳ Menunggu Pembayaran



🔎 Pembayaran Sedang Dicek



✅ Pembayaran Berhasil



📦 Pesanan Sedang Diproses



✅ Pesanan Selesai



❌ Pesanan Dibatalkan

```



Jangan expose status internal database.



\---



\# 20. Fulfillment Workflow



Order yang sudah paid muncul:



```text

Admin

→ Fulfillment

→ Need Fulfillment

```



Contoh:



```text

NEED FULFILLMENT



INV-00001

ChatGPT Premium

@customer



Paid:

14:02



Status:

WAITING



\[ PROCESS ]

```



\---



\# 21. Fulfillment Detail



Admin klik:



```text

PROCESS

```



Halaman:



```text

INVOICE

INV-20260817-00001



Customer

@customer



Telegram ID

123456789



Product

ChatGPT Premium



Amount

Rp45.000



Payment

PAID



────────────────────────



FULFILLMENT



Email / Username

\[\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_]



Password

\[\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_]



Additional Information

\[\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_]



\[ PREVIEW ]



\[ 🚀 SEND TO CUSTOMER ]

```



\---



\# 22. Send Credential



Admin memasukkan:



```text

Email:

premium123@gmail.com



Password:

abc123



Notes:

Jangan mengubah email utama.

```



Klik:



```text

🚀 SEND TO CUSTOMER

```



Laravel memanggil Delivery Bot.



Customer menerima:



```text

✅ PESANANMU SUDAH SIAP



Invoice:

INV-20260817-00001



Produk:

ChatGPT Premium



📧 Email / Username

premium123@gmail.com



🔑 Password

abc123



📝 Catatan

Jangan mengubah email utama.



Terima kasih sudah order ❤️

```



\---



\# 23. Fulfillment Completion



Setelah Telegram API berhasil mengirim:



```text

fulfillment\_status:

waiting

→ sent

```



Order:



```text

order\_status:

processing

→ completed

```



Set:



```text

fulfilled\_at = now()



delivery\_bot\_sent\_at = now()

```



\---



\# 24. Credential Storage



Credential tidak disimpan di product atau stock.



Gunakan tabel:



```text

order\_fulfillments

```



Fields:



```text

id

order\_id

username

password

notes

send\_status

sent\_at

created\_at

updated\_at

```



Username/password harus dienkripsi.



Laravel:



```php

Crypt::encryptString($value);

```



Jangan log credential plaintext.



\---



\# 25. Resend Credential



Admin dapat:



```text

\[ RESEND ]

```



Gunakan jika customer tidak menerima pesan.



Sistem tetap menggunakan credential order yang sama.



Log:



```text

credential\_resend\_count

last\_resend\_at

```



Jangan mengubah order menjadi order baru.



\---



\# 26. Delivery Bot Customer Support



Delivery Bot juga berfungsi sebagai CS untuk masalah akun.



Setelah credential dikirim:



```text

\[ ✅ SUDAH BISA LOGIN ]



\[ ❓ ADA MASALAH ]

```



Jika customer pilih:



```text

❓ ADA MASALAH

```



Bot:



```text

Silakan jelaskan masalah pada akun Anda.



Contoh:

\- Password salah

\- Akun tidak bisa login

\- Premium belum aktif



Kirim pesan Anda sekarang.

```



Customer:



```text

passwordnya invalid kak

```



Masuk ke Laravel:



```text

Support Ticket



INV-00001



Customer:

@customer



Message:

passwordnya invalid kak

```



\---



\# 27. Admin Support Reply



Admin membuka:



```text

Support

→ Ticket

```



Contoh:



```text

INV-00001



@customer



Customer:

Passwordnya invalid kak.



Admin Reply:



\[\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_]



\[ SEND REPLY ]

```



Laravel mengirim via Delivery Bot.



Customer:



```text

💬 Admin



Password sedang kami cek,

silakan coba kembali beberapa menit lagi.

```



Customer dapat membalas lagi.



\---



\# 28. Help Menu Transaction Bot



Customer pilih:



```text

🆘 Bantuan

```



Tampilan:



```text

🆘 BANTUAN



Pilih kendala:



\[ 💳 Masalah Pembayaran ]



\[ 📦 Pesanan Belum Diproses ]



\[ 🔑 Masalah Akun ]



\[ 💬 Hubungi Admin ]

```



Jika masalah akun:



```text

Masalah akun ditangani melalui

Delivery Bot.



\[ 💬 BUKA DELIVERY BOT ]

```



\---



\# 29. Payment Guide



Customer pilih:



```text

💳 Cara Pembayaran

```



Bot:



```text

💳 CARA PEMBAYARAN



1\. Pilih produk.

2\. Klik Beli.

3\. Bot membuat invoice.

4\. Scan QRIS.

5\. Bayar sesuai nominal.

6\. Klik "Saya Sudah Bayar".

7\. Tunggu pembayaran dikonfirmasi.

8\. Pesanan akan diproses.



⚠️ Pastikan nominal pembayaran sesuai invoice.



\[ 🛍️ MULAI BELANJA ]

```



\---



\# 30. Laravel Admin Navigation



Filament menu:



```text

Dashboard



Products



Categories



Orders



Fulfillment



Customers



Support



Publish Stock



Settings

```



\---



\# 31. Admin Dashboard



Dashboard minimal menampilkan:



```text

Revenue Today



Orders Today



Paid Orders



Need Fulfillment



Completed Orders



Low Stock Products

```



Contoh:



```text

Rp450.000

Revenue Today



23

Orders Today



7

Need Fulfillment



3

Low Stock

```



\---



\# 32. Product Management



Fields:



```text

Name



Category



Description



Duration Label



Price



Stock Qty



Low Stock Threshold



Is Active



Sort Order

```



Contoh:



```text

Name:

Canva Pro



Duration:

30 Hari



Price:

20000



Stock:

5



Low Stock Alert:

2



Active:

Yes

```



\---



\# 33. Stock Management



Karena stock menggunakan quantity, admin cukup dapat:



```text

Current Stock:

5



\[ + ADD STOCK ]



\[ - ADJUST STOCK ]

```



Tambah:



```text

Add:

10

```



Hasil:



```text

5 → 15

```



Semua perubahan stock harus dicatat.



\---



\# 34. Stock Movement



Buat tabel:



```text

stock\_movements

```



Fields:



```text

id

product\_id

order\_id nullable

type

quantity

before\_qty

after\_qty

notes

created\_by

created\_at

```



Type:



```text

manual\_add



manual\_reduce



sale



refund



adjustment

```



\---



\# 35. Ready Stock Publisher



Admin membuka:



```text

Publish Stock

```



Laravel generate dari database:



```text

🔥 READY STOCK HARI INI



🎨 Canva Pro 30 Hari

💰 Rp20.000

📦 Stock: 5



🎬 CapCut Pro

💰 Rp25.000

📦 Stock: 7



🤖 ChatGPT Premium

💰 Rp45.000

📦 Stock: 3



👇 Pilih produk untuk order.

```



Admin melihat preview.



Button:



```text

\[ 📢 PUBLISH TO TELEGRAM ]



\[ CANCEL ]

```



\---



\# 36. Channel Product Buttons



Untuk setiap produk dapat dibuat button:



```text

\[ 🛒 Beli Canva ]

```



Link:



```text

https://t.me/transaction\_bot?start=product\_{product\_id}

```



Transaction Bot menerima start parameter.



Contoh:



```text

/start product\_15

```



langsung menampilkan Product Detail.



\---



\# 37. Database Tables



\## users



```text

id



telegram\_id



telegram\_username



first\_name



last\_name



transaction\_bot\_started\_at



delivery\_bot\_started\_at



created\_at



updated\_at

```



Telegram ID harus unique.



\---



\## categories



```text

id



name



slug



icon



sort\_order



is\_active



created\_at



updated\_at

```



\---



\## products



```text

id



category\_id



name



slug



description



duration\_label



price



stock\_qty



low\_stock\_threshold



is\_active



sort\_order



created\_at



updated\_at

```



\---



\## orders



```text

id



invoice\_number



user\_id



product\_id



product\_name



product\_price



amount



payment\_status



order\_status



fulfillment\_status



paid\_at



fulfilled\_at



created\_at



updated\_at

```



Simpan snapshot product name dan price pada order agar perubahan harga produk tidak mengubah order lama.



\---



\## order\_fulfillments



```text

id



order\_id



username



password



notes



send\_status



sent\_at



resend\_count



last\_resend\_at



created\_at



updated\_at

```



\---



\## stock\_movements



```text

id



product\_id



order\_id



type



quantity



before\_qty



after\_qty



notes



created\_by



created\_at

```



\---



\## support\_tickets



```text

id



order\_id



user\_id



status



created\_at



updated\_at

```



\---



\## support\_messages



```text

id



support\_ticket\_id



sender\_type



message



created\_at

```



sender\_type:



```text

customer



admin

```



\---



\# 38. Payment Status



Internal:



```text

pending



waiting\_confirmation



paid



rejected



refunded

```



\---



\# 39. Order Status



Internal:



```text

waiting\_payment



processing



completed



cancelled



failed

```



\---



\# 40. Fulfillment Status



```text

pending



waiting



processing



sent



failed

```



\---



\# 41. Laravel Architecture



Recommended:



```text

app/

│

├── Filament/

│   ├── Resources/

│   ├── Pages/

│   └── Widgets/

│

├── Http/

│   └── Controllers/

│       └── Telegram/

│

├── Models/

│   ├── User.php

│   ├── Category.php

│   ├── Product.php

│   ├── Order.php

│   ├── OrderFulfillment.php

│   ├── StockMovement.php

│   └── SupportTicket.php

│

├── Services/

│   ├── Telegram/

│   │   ├── TransactionBotService.php

│   │   ├── DeliveryBotService.php

│   │   └── ChannelService.php

│   │

│   ├── OrderService.php

│   ├── PaymentService.php

│   ├── StockService.php

│   └── FulfillmentService.php

│

└── Telegram/

&#x20;   ├── Transaction/

&#x20;   │   ├── MessageHandler.php

&#x20;   │   └── CallbackHandler.php

&#x20;   │

&#x20;   └── Delivery/

&#x20;       ├── MessageHandler.php

&#x20;       └── CallbackHandler.php

```



Jangan masukkan seluruh logic ke controller.



\---



\# 42. Telegram Webhooks



Gunakan endpoint terpisah.



Transaction Bot:



```text

POST /api/telegram/transaction/webhook

```



Delivery Bot:



```text

POST /api/telegram/delivery/webhook

```



Dengan token berbeda.



\---



\# 43. Environment Variables



```env

TRANSACTION\_BOT\_TOKEN=



TRANSACTION\_BOT\_USERNAME=



DELIVERY\_BOT\_TOKEN=



DELIVERY\_BOT\_USERNAME=



TELEGRAM\_CHANNEL\_ID=



TELEGRAM\_CHANNEL\_USERNAME=



TELEGRAM\_WEBHOOK\_SECRET=



QRIS\_IMAGE\_PATH=

```



Tidak boleh hardcoded.



\---



\# 44. Security Requirements



Wajib:



\* Bot token disimpan di environment.

\* `.env` tidak masuk repository.

\* Telegram webhook menggunakan HTTPS.

\* Gunakan webhook secret.

\* Credential akun terenkripsi.

\* Jangan log password.

\* Laravel admin harus authentication.

\* Hanya admin yang dapat melihat fulfillment credential.

\* Payment approval harus idempotent.

\* Stock deduction harus idempotent.

\* Gunakan DB transaction.

\* Gunakan row locking saat mengurangi stock.

\* Customer hanya dapat melihat order miliknya.

\* Customer tidak dapat mengakses order customer lain.



\---



\# 45. Duplicate Payment Protection



Admin mungkin klik Approve dua kali.



Sistem harus mencegah:



```text

Stock:

5 → 4 → 3

```



untuk order yang sama.



Sebelum proses:



```php

if ($order->payment\_status === 'paid') {

&#x20;   return;

}

```



Payment approval harus idempotent.



\---



\# 46. Duplicate Fulfillment Protection



Klik:



```text

SEND TO CUSTOMER

```



dua kali tidak boleh otomatis membuat order baru atau mengganti status secara salah.



Jika credential sudah pernah dikirim:



tampilkan:



```text

Credential sudah pernah dikirim.



Sent:

17 Aug 2026 14:22



\[ RESEND ]



\[ CANCEL ]

```



\---



\# 47. Low Stock Notification



Jika:



```text

stock\_qty <= low\_stock\_threshold

```



Filament dashboard menampilkan:



```text

⚠️ LOW STOCK



ChatGPT Premium

Stock: 1

```



Optional MVP:



admin dapat menerima Telegram notification.



\---



\# 48. Order Cancellation



Customer hanya dapat cancel jika:



```text

payment\_status = pending

```



Jika sudah:



```text

waiting\_confirmation

```



customer tidak dapat cancel otomatis.



\---



\# 49. Refund



Refund belum otomatis.



Admin dapat menandai:



```text

payment\_status = refunded

```



Jika order sudah mengurangi stock tetapi belum fulfilled:



stock dapat dikembalikan:



```text

stock\_qty + 1

```



dan buat:



```text

stock\_movements.type = refund

```



\---



\# 50. Logs



Catat event:



```text

order\_created



payment\_confirmation\_requested



payment\_approved



payment\_rejected



stock\_reduced



stock\_added



fulfillment\_started



credential\_sent



credential\_resend



support\_ticket\_created



support\_reply\_sent



channel\_stock\_published

```



Jangan log credential plaintext.



\---



\# 51. MVP Non-Goals



Jangan buat:



\* Reseller dashboard.

\* Supplier API.

\* Supplier automation.

\* Referral.

\* Affiliate.

\* Wallet.

\* Deposit balance.

\* Cashback.

\* Voucher.

\* Complex promo engine.

\* Multi admin permission kompleks.

\* Auto renew subscription.

\* Marketplace.

\* Mobile app.

\* React frontend terpisah.

\* Customer website.

\* Shopping cart.

\* Quantity > 1.



\---



\# 52. Recommended Technology



```text

Laravel



Filament



MySQL / PostgreSQL



Telegram Bot API



Laravel Queue



Laravel Scheduler



Redis optional

```



Frontend admin tidak perlu React/Vue terpisah.



Gunakan Filament.



\---



\# 53. Customer Main Flow



```text

CHANNEL

&#x20;  ↓

Customer klik product

&#x20;  ↓

TRANSACTION BOT

&#x20;  ↓

Product Detail

&#x20;  ↓

Checkout

&#x20;  ↓

Invoice

&#x20;  ↓

QRIS

&#x20;  ↓

Saya Sudah Bayar

&#x20;  ↓

Admin Verify

&#x20;  ↓

Payment PAID

&#x20;  ↓

Stock -1

&#x20;  ↓

WAITING FULFILLMENT

&#x20;  ↓

Admin beli ke supplier

&#x20;  ↓

Admin input credential

&#x20;  ↓

SEND

&#x20;  ↓

DELIVERY BOT

&#x20;  ↓

Customer menerima akun

&#x20;  ↓

Order COMPLETED

```



\---



\# 54. Admin Main Flow



```text

FILAMENT LOGIN

&#x20;  ↓

Dashboard

&#x20;  ↓

Need Fulfillment

&#x20;  ↓

Open Order

&#x20;  ↓

Beli / ambil akun dari supplier

&#x20;  ↓

Input Email / Username

&#x20;  ↓

Input Password

&#x20;  ↓

Input Notes

&#x20;  ↓

Preview

&#x20;  ↓

SEND TO CUSTOMER

&#x20;  ↓

Telegram Success

&#x20;  ↓

Order Completed

```



\---



\# 55. Transaction Bot Acceptance Criteria



\* \[ ] `/start` bekerja.

\* \[ ] Main menu tampil.

\* \[ ] Katalog dapat dibuka.

\* \[ ] Category dapat dipilih.

\* \[ ] Product detail tampil.

\* \[ ] Harga tampil.

\* \[ ] Stock tampil.

\* \[ ] Produk stock 0 tidak tampil.

\* \[ ] Customer dapat checkout.

\* \[ ] Invoice tergenerate.

\* \[ ] QRIS tampil.

\* \[ ] Customer dapat klik Saya Sudah Bayar.

\* \[ ] Payment masuk ke admin.

\* \[ ] Customer dapat melihat Pesanan Saya.

\* \[ ] Customer dapat melihat detail order.

\* \[ ] Customer dapat membuka Delivery Bot.



\---



\# 56. Delivery Bot Acceptance Criteria



\* \[ ] Customer dapat start Delivery Bot.

\* \[ ] Activation tersimpan.

\* \[ ] Delivery Bot dapat menerima credential dari Laravel.

\* \[ ] Credential hanya dikirim ke customer order tersebut.

\* \[ ] Customer dapat klik Ada Masalah.

\* \[ ] Customer dapat mengirim pesan.

\* \[ ] Pesan muncul di admin.

\* \[ ] Admin dapat reply.

\* \[ ] Reply terkirim ke customer.

\* \[ ] Credential dapat resend.



\---



\# 57. Admin Acceptance Criteria



\* \[ ] Admin login Filament.

\* \[ ] Dashboard tersedia.

\* \[ ] Product CRUD.

\* \[ ] Category CRUD.

\* \[ ] Stock adjustment.

\* \[ ] Stock movement tercatat.

\* \[ ] Order dapat dilihat.

\* \[ ] Payment dapat approve.

\* \[ ] Payment dapat reject.

\* \[ ] Stock berkurang saat payment approved.

\* \[ ] Stock tidak berkurang dua kali.

\* \[ ] Need Fulfillment tersedia.

\* \[ ] Credential dapat diinput.

\* \[ ] Credential terenkripsi.

\* \[ ] Credential dapat dikirim.

\* \[ ] Order completed setelah delivery sukses.

\* \[ ] Admin dapat publish stock ke Channel.

\* \[ ] Support ticket tersedia.



\---



\# 58. Suggested Development Phases



\## Phase 1 — Foundation



```text

Laravel setup



Filament setup



Database migrations



Models



Admin authentication

```



\---



\## Phase 2 — Product \& Stock



```text

Categories



Products



Stock quantity



Stock movements



Product admin

```



\---



\## Phase 3 — Transaction Bot



```text

Telegram webhook



/start



Main menu



Catalog



Category



Product detail

```



\---



\## Phase 4 — Checkout



```text

Order creation



Invoice



QRIS



Payment confirmation



Pesanan Saya

```



\---



\## Phase 5 — Payment Admin



```text

Waiting Confirmation



Approve



Reject



Stock deduction



Payment notifications

```



\---



\## Phase 6 — Fulfillment



```text

Need Fulfillment



Credential form



Encryption



Send to Delivery Bot



Completed order



Resend

```



\---



\## Phase 7 — Delivery Bot



```text

Activation



Credential delivery



Support ticket



Customer messages



Admin reply

```



\---



\## Phase 8 — Ready Stock Channel



```text

Publish page



Preview



Channel post



Deep-link product buttons

```



\---



\## Phase 9 — Hardening



```text

DB locking



Idempotency



Logging



Error handling



Telegram retry



Security



Testing

```



\---



\# 59. Development Rules for Antigravity



1\. Build sesuai scope MVP.

2\. Jangan menambahkan fitur reseller.

3\. Jangan menambahkan payment gateway terlebih dahulu.

4\. Jangan membuat React/Vue frontend terpisah.

5\. Gunakan Laravel + Filament.

6\. Telegram bot menggunakan Laravel sebagai backend.

7\. Gunakan dua Telegram bot token terpisah.

8\. Gunakan satu Telegram Channel.

9\. Pisahkan Transaction Bot dan Delivery Bot responsibilities.

10\. Product stock menggunakan quantity.

11\. Credential hanya berada pada fulfillment order.

12\. Credential terenkripsi.

13\. Stock berkurang saat payment confirmed.

14\. Jangan mengurangi stock saat order dibuat.

15\. Semua operasi payment dan stock harus idempotent.

16\. Gunakan database transactions.

17\. Gunakan row locking ketika update stock.

18\. Jangan meletakkan seluruh business logic di controller.

19\. Gunakan Service classes.

20\. Gunakan Filament Resources untuk CRUD standar.

21\. Gunakan custom Filament Pages/Actions untuk fulfillment dan Publish Stock.

22\. Semua Telegram callback harus menggunakan format konsisten.

23\. Gunakan user-friendly status pada Telegram.

24\. Jangan expose internal database status ke customer.

25\. Test satu phase sebelum lanjut ke phase berikutnya.

26\. Semua produk diasumsikan merupakan produk digital yang sah untuk dijual atau didistribusikan oleh operator toko.



\---



\# 60. Final Product Structure



```text

digital-store/

│

├── Laravel

│

├── Filament Admin

│   │

│   ├── Dashboard

│   ├── Categories

│   ├── Products

│   ├── Orders

│   ├── Fulfillment

│   ├── Customers

│   ├── Support

│   ├── Publish Stock

│   └── Settings

│

├── Transaction Bot

│   │

│   ├── Katalog Produk

│   ├── Pesanan Saya

│   ├── Cara Pembayaran

│   ├── Bantuan

│   ├── Checkout

│   ├── Invoice

│   └── QRIS

│

├── Delivery Bot

│   │

│   ├── Activation

│   ├── Credential Delivery

│   ├── Account Problem

│   └── Customer Support

│

├── Telegram Channel

│   │

│   ├── Ready Stock

│   ├── Price

│   └── Checkout Links

│

└── Database

```



\---



\# 61. MVP Success Definition



MVP dianggap berhasil ketika skenario berikut dapat dilakukan end-to-end:



```text

Admin membuat produk Canva

&#x20;       ↓

Admin set stock = 5

&#x20;       ↓

Admin publish ke Telegram Channel

&#x20;       ↓

Customer klik Canva

&#x20;       ↓

Transaction Bot terbuka

&#x20;       ↓

Customer checkout

&#x20;       ↓

Customer mendapat QRIS

&#x20;       ↓

Customer melakukan pembayaran

&#x20;       ↓

Customer konfirmasi

&#x20;       ↓

Admin approve pembayaran

&#x20;       ↓

Stock Canva 5 → 4

&#x20;       ↓

Order masuk Need Fulfillment

&#x20;       ↓

Admin membeli akun ke supplier

&#x20;       ↓

Admin mendapat email/password

&#x20;       ↓

Admin input credential di Filament

&#x20;       ↓

Admin klik SEND TO CUSTOMER

&#x20;       ↓

Delivery Bot mengirim credential

&#x20;       ↓

Customer menerima credential

&#x20;       ↓

Order = COMPLETED

```



\*\*Prioritas utama pengembangan adalah membuat flow di atas berjalan stabil sebelum menambahkan fitur tambahan apa pun.\*\*



