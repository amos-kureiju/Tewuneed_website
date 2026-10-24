# TEWUNEED Marketplace

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![FontAwesome](https://img.shields.io/badge/Font%20Awesome-6.4-528DD7?style=flat&logo=font-awesome&logoColor=white)](https://fontawesome.com/)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-0D9488?style=flat)]()

**TEWUNEED** adalah platform e-commerce marketplace modern, cepat, dan responsif berbasis PHP Native & MySQL. Dibangun dengan standar arsitektur bersih, sistem desain bespoke berbasis palet warna *Deep Emerald Teal* & *Slate*, bebas dari dependensi berlebih atau template generik.

---

## 🌟 Fitur Utama

### 🛒 Toko & Pengalaman Belanja (Storefront)
- **Beranda Interaktif (`index.php`)**: Hero promo, jaminan belanja aman, navigasi kategori terpopuler, dan etalase produk unggulan.
- **Katalog & Filter Produk (`products.php`, `category.php`)**:
  - Filter kategori dinamis (Sembako, Sayuran Segar, Kosmetik & Skincare, Perlengkapan Olahraga, Vitamin & Obat, Elektronik).
  - Pengurutan harga (Termurah/Termahal), produk terbaru, nama produk, dan terpopuler.
  - Pagination otomatis.
- **Pencarian Cepat (`search.php`)**: Pencarian kata kunci real-time berbasis nama dan deskripsi produk.
- **Detail Produk Lengkap (`product.php`)**: Galeri foto, indikator stok dinamis, ulasan pelanggan (bintang rating), spesifikasi SKU, dan rekomendasi produk terkait.

### 💳 Keranjang, Kupon & Checkout
- **Keranjang Belanja Dinamis (`cart.php`)**: Tambah/kurang kuantitas item, hapus produk, dan kalkulasi subtotal instan berformat Rupiah.
- **Sistem Kupon Diskon**: Mendukung persentase dan potongan harga nominal (contoh: `WELCOME15` diskon 15%, `DISKON10K` potongan Rp 10.000).
- **Checkout Multi-Langkah (`checkout.php`)**:
  - Manajemen alamat pengiriman lengkap dengan modal penambahan alamat baru.
  - Pilihan ekspedisi kurir tepercaya (JNE Regular, JNE Express, SiCepat, GoSend Instant).
  - Pilihan metode pembayaran (Transfer Bank BCA, Mandiri, BNI, BRI, QRIS, dan COD).
- **Konfirmasi & Cetak Faktur (`order_confirmation.php`, `order_detail.php`)**: Kode resi otomatis, rincian biaya transparan, dan tombol cetak invoice belanja.

### 👤 Portal Akun Pelanggan
- **Autentikasi Aman (`login.php`, `signup.php`)**: Registrasi akun terenkripsi bcrypt, session management, dan proteksi CSRF.
- **Profil & Alamat (`my_account.php`)**: Ubah nama, telepon, kata sandi, dan kelola daftar alamat pengiriman.
- **Riwayat Pesanan (`orders.php`)**: Filter status pesanan (Menunggu Pembayaran, Diproses, Dikirim, Selesai, Dibatalkan).
- **Wishlist Favorit (`wishlist.php`)**: Simpan produk favorit dan langsung pindahkan ke keranjang kapan saja.

### 🛡️ Executive Admin Dashboard (`/admin`)
- **Statistik & Metrik Bisnis (`admin/index.php`)**: Total produk aktif, total stok gudang, jumlah produk terjual, pendapatan 30 hari terakhir, pesanan pending, dan peringatan stok menipis.
- **Kelola Produk (`admin/products.php`)**: Tambah, ubah, dan hapus katalog produk beserta foto, harga promo, SKU, dan kategori.
- **Kelola Kategori (`admin/categories.php`)**: Pengaturan kategori produk dan slug URL.
- **Kelola Pesanan (`admin/orders.php`)**: Update status proses pesanan pelanggan (Pending -> Processing -> Shipped -> Completed).
- **Kelola Kupon Promo (`admin/coupons.php`)**: Buat kupon diskon baru dengan masa berlaku dan batas pemakaian.
- **Kelola Pengguna (`admin/users.php`)**: Monitor status pengguna dan pelanggan terdaftar.
- **Pengaturan Toko (`admin/settings.php`)**: Konfigurasi nama toko, kontak, alamat, dan mata uang.

---

## 🏗️ Struktur Direktori

```
tewuneed/
├── admin/                    # Panel Administrator
│   ├── categories.php        # Manajemen kategori
│   ├── coupons.php           # Manajemen kupon diskon
│   ├── index.php             # Dashboard metrik & analitik
│   ├── login.php             # Login admin
│   ├── logout.php            # Logout admin
│   ├── orders.php            # Manajemen & update status order
│   ├── products.php          # CRUD produk & stok
│   ├── settings.php          # Konfigurasi sistem
│   └── users.php             # Manajemen pelanggan
├── api/                      # REST API endpoints (AJAX)
│   ├── address.php           # API manajemen alamat
│   ├── cart.php              # API keranjang belanja
│   ├── coupon.php            # API validasi kupon
│   ├── orders.php            # API pembatalan/konfirmasi order
│   ├── search.php            # API live search
│   └── wishlist.php          # API toggle wishlist
├── config/
│   └── database.php          # Koneksi PDO MySQL & konfigurasi global
├── css/
│   ├── admin.css             # Desain sistem executive admin
│   ├── bootstrap.min.css     # Framework CSS Bootstrap 5
│   └── style.css             # Desain sistem modern Teal/Slate
├── database/
│   └── schema.sql            # Skema tabel lengkap & seed data
├── Images/                   # Aset foto produk & banner kategori
├── includes/
│   ├── footer.php            # Komponen footer & helper JS
│   ├── functions.php         # Fungsi helper, auth, DB queries
│   └── header.php            # Komponen navbar & navigasi
├── js/                       # Skrip JS Bootstrap & vendor
├── index.php                 # Halaman utama (Homepage)
├── products.php              # Halaman katalog produk
├── category.php              # Halaman produk berdasarkan kategori
├── product.php               # Halaman detail produk
├── cart.php                  # Halaman keranjang belanja
├── checkout.php              # Halaman checkout & pembayaran
├── order_confirmation.php    # Halaman konfirmasi sukses pesanan
├── order_detail.php          # Halaman invoice & detail pesanan
├── orders.php                # Halaman riwayat belanja pelanggan
├── my_account.php            # Halaman pengaturan profil pelanggan
├── wishlist.php              # Halaman daftar keinginan (Wishlist)
├── login.php                 # Halaman login pelanggan
├── signup.php                # Halaman pendaftaran akun pelanggan
├── logout.php                # Script logout pelanggan
├── search.php                # Halaman hasil pencarian
├── about.php                 # Halaman profil & tentang toko
├── contact.php               # Halaman kontak & formulir bantuan
├── robots.txt                # Konfigurasi perayap mesin pencari
└── sitemap.xml               # Peta situs SEO
```

---

## 🚀 Panduan Instalasi Lokal (XAMPP)

### 1. Prasyarat Sistem
- **Web Server**: Apache 2.4+
- **PHP**: Versi 8.0 atau lebih baru (dengan ekstensi `pdo_mysql` dan `gd` aktif)
- **Database**: MySQL 5.7+ atau MariaDB 10.4+

### 2. Kloning / Salin Proyek
Pindahkan atau clone repositori ini ke folder `htdocs` XAMPP Anda:
```bash
cd c:\xampp\htdocs
git clone https://github.com/amos-kureiju/Tewuneed_website.git tewuneed
```

### 3. Konfigurasi Database
1. Buka aplikasi **XAMPP Control Panel**, nyalakan modul **Apache** dan **MySQL**.
2. Akses phpMyAdmin di browser: `http://localhost/phpmyadmin`.
3. Buat database baru dengan nama: `db_tewuneed2`.
4. Import file SQL yang tersedia di: `database/schema.sql`.

*Atau melalui terminal/Command Prompt:*
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS db_tewuneed2;"
mysql -u root db_tewuneed2 < database/schema.sql
```

### 4. Konfigurasi Aplikasi (Opsional)
File konfigurasi berada di `config/database.php`. Secara bawaan telah diset untuk XAMPP standar:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_tewuneed2');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_URL', 'http://localhost/tewuneed');
```

---

## 🔑 Kredensial Pengujian Bawaan

### 1. Panel Administrator
- **URL**: `http://localhost/tewuneed/admin/login.php`
- **Username**: `admin`
- **Password**: `password123`

### 2. Akun Pelanggan Demo
- **URL**: `http://localhost/tewuneed/login.php`
- **Email**: `budi.pratama@test.com`
- **Password**: `password123`
*(Anda juga dapat membuat akun baru melalui halaman `signup.php`)*

### 3. Kupon Promo Aktif
- `WELCOME15` — Diskon 15% untuk semua pembelian.
- `DISKON10K` — Potongan Rp 10.000 untuk transaksi belanja.

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah lisensi terbuka untuk tujuan pengembangan dan portofolio.
Hak Cipta &copy; 2026 **Amos Baringbing** / **TEWUNEED Marketplace**.
