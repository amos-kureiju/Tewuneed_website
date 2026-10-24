<?php
/**
 * About Us Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

$pageTitle = 'Tentang Kami';

include 'includes/header.php';
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active">Tentang Kami</li>
        </ol>
    </nav>

    <!-- Hero Banner -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5" style="background: linear-gradient(135deg, #0F766E 0%, #115E59 100%);">
        <div class="card-body p-5 text-white">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold mb-3">Tentang TEWUNEED</span>
                    <h1 class="display-5 fw-bold mb-3">Solusi Belanja Harian Lengkap, Cepat & Tepercaya</h1>
                    <p class="lead mb-0 text-white-50">
                        Menghubungkan Anda dengan kebutuhan sehari-hari terbaik mulai dari sembako, sayuran segar petani lokal, kosmetik, hingga perlengkapan elektronik dengan jaminan kualitas terbaik.
                    </p>
                </div>
                <div class="col-lg-5 text-center d-none d-lg-block">
                    <img src="<?= SITE_URL ?>/Images/fres groceries.jpg" alt="About TEWUNEED" class="img-fluid rounded-4 shadow" style="max-height: 280px; object-fit: cover;">
                </div>
            </div>
        </div>
    </div>

    <!-- Our Values -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-seedling fa-2x"></i>
                </div>
                <h5 class="fw-bold mb-2">Segar & Berkualitas</h5>
                <p class="text-muted small mb-0">
                    Kami bekerja sama langsung dengan produsen dan petani tepercaya untuk memastikan setiap produk yang sampai di tangan Anda terjamin kesegarannya.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-shield-halved fa-2x"></i>
                </div>
                <h5 class="fw-bold mb-2">Aman & Terpercaya</h5>
                <p class="text-muted small mb-0">
                    Sistem transaksi terenkripsi aman, pilihan pembayaran resmi beragam, serta kebijakan retur transparan untuk kenyamanan belanja Anda.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="fas fa-truck-fast fa-2x"></i>
                </div>
                <h5 class="fw-bold mb-2">Pengiriman Kilat</h5>
                <p class="text-muted small mb-0">
                    Dukungan mitra kurir terkemuka seperti JNE, J&T, SiCepat, dan layanan GoSend instant untuk memastikan paket tiba tepat waktu.
                </p>
            </div>
        </div>
    </div>

    <!-- Story Section -->
    <div class="row align-items-center g-5 py-4">
        <div class="col-lg-6">
            <h3 class="fw-bold mb-3">Visi & Misi Kami</h3>
            <p class="text-muted mb-3">
                TEWUNEED didirikan dengan satu komitmen sederhana: mempermudah masyarakat dalam memenuhi kebutuhan harian tanpa ribet, dengan harga yang adil dan mutu yang teruji.
            </p>
            <ul class="list-unstyled text-muted d-flex flex-column gap-2 mb-4">
                <li class="d-flex align-items-start gap-2">
                    <i class="fas fa-check-circle text-primary mt-1"></i>
                    <span>Menyediakan produk original dan higienis langsung dari distributor resmi.</span>
                </li>
                <li class="d-flex align-items-start gap-2">
                    <i class="fas fa-check-circle text-primary mt-1"></i>
                    <span>Mendukung pemberdayaan rantai pasok lokal dan UMKM Indonesia.</span>
                </li>
                <li class="d-flex align-items-start gap-2">
                    <i class="fas fa-check-circle text-primary mt-1"></i>
                    <span>Memberikan pelayanan pelanggan 24/7 yang ramah dan responsif.</span>
                </li>
            </ul>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary px-4">
                Mulai Belanja Sekarang <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
                <h5 class="fw-bold mb-3">Fakta Cepat TEWUNEED</h5>
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="bg-white p-3 rounded-3 shadow-sm">
                            <div class="h3 fw-bold text-primary mb-1">100%</div>
                            <div class="small text-muted">Produk Terverifikasi</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-white p-3 rounded-3 shadow-sm">
                            <div class="h3 fw-bold text-primary mb-1">6+</div>
                            <div class="small text-muted">Kategori Lengkap</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-white p-3 rounded-3 shadow-sm">
                            <div class="h3 fw-bold text-primary mb-1">24/7</div>
                            <div class="small text-muted">Dukungan Pelanggan</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-white p-3 rounded-3 shadow-sm">
                            <div class="h3 fw-bold text-primary mb-1">Kilat</div>
                            <div class="small text-muted">Pengiriman Cepat</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
