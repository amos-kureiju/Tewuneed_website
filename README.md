# TEWUNEED Marketplace

TEWUNEED adalah platform e-commerce marketplace modern berbasis PHP dan MySQL yang menyediakan berbagai kebutuhan sehari-hari, mulai dari sembako, sayuran segar petani lokal, kosmetik & skincare, perlengkapan olahraga, vitamin & obat-obatan, hingga peralatan elektronik.

## Fitur Utama

- **Katalog Produk & Kategori**: Navigasi multi-kategori lengkap dengan filter harga, pencarian kata kunci, dan pengurutan dinamis (Terbaru, Harga, Terpopuler, Nama).
- **Pencarian Cerdas**: Pencarian produk terintegrasi (`search.php`) yang cepat dan responsif.
- **Keranjang Belanja Dinamis (`cart.php`)**: Menambah, mengubah jumlah, menghapus item, dan perhitungan subtotal otomatis dengan format mata uang Rupiah.
- **Sistem Kupon Diskon**: Mendukung kode promo diskon (contoh: `WELCOME15` dan `DISKON10K`).
- **Alamat & Pengiriman**: Manajemen multi-alamat pengiriman dan pilihan opsi kurir (JNE Regular, JNE Express, GoSend, Gratis Ongkir).
- **Metode Pembayaran**: Pilihan transfer bank (BCA, Mandiri, BNI), dompet digital (GoPay, OVO, DANA), dan COD (Bayar di Tempat).
- **Pelacakan & Riwayat Pesanan (`orders.php`, `order_detail.php`)**: Pemantauan status pesanan secara berkala (Pending, Diproses, Dikirim, Selesai) lengkap dengan cetak invoice.
- **Autentikasi & Akun (`login.php`, `signup.php`, `my_account.php`)**: Sistem login, registrasi akun aman dengan password hashing (bcrypt), dan manajemen profil pengguna.
- **Wishlist Produk (`wishlist.php`)**: Menyimpan produk favorit dan memindahkannya ke keranjang belanja kapan saja.
- **Panel Admin (`/admin`)**: Dashboard pengelolaan produk, kategori, pesanan pelanggan, kupon promo, pengguna, dan laporan stok.

## Arsitektur Teknologi

- **Backend**: PHP 8 (PDO MySQL, Session Management, Prepared Statements)
- **Database**: MySQL / MariaDB (`db_tewuneed2`)
- **Frontend & Styling**: Modern CSS3 Custom Design System (`css/style.css`), Bootstrap 5, Font Awesome 6
- **Typography**: Google Fonts (*Plus Jakarta Sans*)

## Panduan Instalasi Lokal (XAMPP)

1. Letakkan folder proyek di `C:\xampp\htdocs\tewuneed`.
2. Buka XAMPP Control Panel, lalu jalankan modul **Apache** dan **MySQL**.
3. Buka browser dan akses database via phpMyAdmin (`http://localhost/phpmyadmin`).
4. Buat database baru bernama `db_tewuneed2`, lalu import file `database/schema.sql`.
5. Akses website melalui:
   - **Toko**: `http://localhost/tewuneed/index.php`
   - **Admin Panel**: `http://localhost/tewuneed/admin/login.php`
     - *Username*: `admin`
     - *Password*: `password123`

## Hak Cipta & Lisensi

Hak Cipta &copy; 2026 TEWUNEED Marketplace. Semua hak dilindungi.
