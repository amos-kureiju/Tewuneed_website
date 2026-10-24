<?php
/**
 * Contact Us Page
 * TeWuNeed E-Commerce Website
 */

require_once 'includes/functions.php';

$pageTitle = 'Hubungi Kami';
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $errorMessage = 'Mohon isi semua bidang yang wajib.';
    } else {
        $successMessage = 'Terima kasih, ' . htmlspecialchars($name) . '! Pesan Anda telah terkirim. Tim kami akan segera menghubungi Anda.';
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active">Hubungi Kami</li>
        </ol>
    </nav>

    <div class="row g-5">
        <!-- Contact Information -->
        <div class="col-lg-5">
            <div class="h-100 p-4 p-md-5 rounded-4 text-white shadow-sm" style="background: linear-gradient(135deg, #0F766E 0%, #115E59 100%);">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold mb-3">Pusat Bantuan</span>
                <h2 class="fw-bold mb-3">Kami Siap Membantu Anda</h2>
                <p class="text-white-50 mb-5">
                    Punya pertanyaan seputar produk, pengiriman, atau kendala transaksi? Hubungi customer service kami melalui saluran berikut.
                </p>

                <div class="d-flex flex-column gap-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center p-3 text-white" style="width: 50px; height: 50px;">
                            <i class="fas fa-location-dot"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Alamat Kantor</h6>
                            <p class="small text-white-50 mb-0">Jl. Contoh No. 123, Menteng, Jakarta Pusat 10310</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center p-3 text-white" style="width: 50px; height: 50px;">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Telepon & WhatsApp</h6>
                            <p class="small text-white-50 mb-0">+62 21 1234 5678 / +62 812 3456 7890</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center p-3 text-white" style="width: 50px; height: 50px;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Email Resmi</h6>
                            <p class="small text-white-50 mb-0">support@tewuneed.com / info@tewuneed.com</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center p-3 text-white" style="width: 50px; height: 50px;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Jam Operasional</h6>
                            <p class="small text-white-50 mb-0">Senin - Minggu: 08.00 - 22.00 WIB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <h3 class="fw-bold mb-2">Kirim Pesan</h3>
                <p class="text-muted small mb-4">Isi formulir di bawah ini dan kami akan membalas via email dalam waktu 1x24 jam.</p>

                <?php if ($successMessage): ?>
                <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <div><?= $successMessage ?></div>
                </div>
                <?php endif; ?>

                <?php if ($errorMessage): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <div><?= $errorMessage ?></div>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Nama Anda" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Subjek / Topik</label>
                            <input type="text" name="subject" class="form-control" placeholder="Misal: Pertanyaan Status Pesanan">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Isi Pesan <span class="text-danger">*</span></label>
                            <textarea name="message" rows="5" class="form-control" placeholder="Tuliskan pertanyaan atau kendala Anda secara rinci..." required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
