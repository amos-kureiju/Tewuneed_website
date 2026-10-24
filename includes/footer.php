    </main>
    
    <!-- Footer -->
    <footer class="main-footer mt-5">
        <div class="container">
            <div class="row g-4 mb-4">
                <!-- Brand About -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="navbar-brand mb-3">
                        <span class="text-white">TEWU</span><span class="brand-need">NEED</span>
                    </h5>
                    <p class="text-secondary small mb-4" style="line-height: 1.7;">
                        Platform marketplace modern tepercaya untuk kebutuhan belanja harian Anda. Dari sembako, sayuran segar, kosmetik, hingga perlengkapan elektronik dengan harga hemat dan pengiriman cepat.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="https://facebook.com" target="_blank" rel="noopener" class="footer-social-link" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://instagram.com" target="_blank" rel="noopener" class="footer-social-link" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://twitter.com" target="_blank" rel="noopener" class="footer-social-link" title="Twitter">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                        <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" class="footer-social-link" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6>Menu Utama</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                        <li><a href="<?= SITE_URL ?>/products.php">Katalog Produk</a></li>
                        <li><a href="<?= SITE_URL ?>/orders.php">Status Pesanan</a></li>
                        <li><a href="<?= SITE_URL ?>/about.php">Tentang Kami</a></li>
                        <li><a href="<?= SITE_URL ?>/contact.php">Hubungi Kami</a></li>
                    </ul>
                </div>
                
                <!-- Categories -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h6>Kategori Belanja</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="<?= SITE_URL ?>/category.php?slug=food-snacks">Food & Snacks</a></li>
                        <li><a href="<?= SITE_URL ?>/category.php?slug=vegetables">Sayuran Segar</a></li>
                        <li><a href="<?= SITE_URL ?>/category.php?slug=cosmetics">Kosmetik</a></li>
                        <li><a href="<?= SITE_URL ?>/category.php?slug=electronics">Elektronik</a></li>
                        <li><a href="<?= SITE_URL ?>/category.php?slug=health-medicine">Kesehatan & Obat</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-4 col-md-6">
                    <h6>Layanan & Kontak</h6>
                    <ul class="list-unstyled small text-secondary d-flex flex-column gap-2 mb-3">
                        <li class="d-flex align-items-start gap-2">
                            <i class="fas fa-location-dot mt-1 text-primary"></i>
                            <span>Jl. Contoh No. 123, Menteng, Jakarta Pusat 10310</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="fas fa-phone text-primary"></i>
                            <span>+62 21 1234 5678 (CS Hotline)</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="fas fa-envelope text-primary"></i>
                            <span>support@tewuneed.com</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="fas fa-clock text-primary"></i>
                            <span>Setiap Hari: 08.00 - 22.00 WIB</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Partners & Security -->
            <div class="border-top border-secondary border-opacity-25 pt-4 pb-3">
                <div class="row align-items-center g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="small text-secondary me-2">Metode Pembayaran:</span>
                            <span class="badge bg-light text-dark fw-semibold px-2 py-1">BCA</span>
                            <span class="badge bg-light text-dark fw-semibold px-2 py-1">Mandiri</span>
                            <span class="badge bg-light text-dark fw-semibold px-2 py-1">BNI</span>
                            <span class="badge bg-light text-dark fw-semibold px-2 py-1">GoPay</span>
                            <span class="badge bg-light text-dark fw-semibold px-2 py-1">OVO</span>
                            <span class="badge bg-light text-dark fw-semibold px-2 py-1">DANA</span>
                            <span class="badge bg-light text-dark fw-semibold px-2 py-1">COD</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="d-flex align-items-center justify-content-md-end flex-wrap gap-2">
                            <span class="small text-secondary me-2">Mitra Ekspedisi:</span>
                            <span class="badge bg-secondary px-2 py-1">JNE</span>
                            <span class="badge bg-secondary px-2 py-1">J&T</span>
                            <span class="badge bg-secondary px-2 py-1">SiCepat</span>
                            <span class="badge bg-secondary px-2 py-1">GoSend</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="text-center pt-3 border-top border-secondary border-opacity-25 small text-secondary">
                <p class="mb-0">
                    &copy; <?= date('Y') ?> <strong><?= SITE_NAME ?> Marketplace</strong>. Hak Cipta Dilindungi Undang-Undang.
                </p>
            </div>
        </div>
    </footer>
    
    <!-- jQuery & Bootstrap Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Toastify Notification -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <!-- Core Helper JS -->
    <script>
        window.SITE_URL = window.SITE_URL || '<?= SITE_URL ?>';
        var SITE_URL = window.SITE_URL;
        
        function showToast(message, type = 'success') {
            const colors = {
                success: '#0F766E',
                error: '#EF4444',
                warning: '#F59E0B',
                info: '#0284C7'
            };
            
            Toastify({
                text: message,
                duration: 3000,
                close: true,
                gravity: 'top',
                position: 'right',
                style: {
                    background: colors[type] || colors.success,
                    borderRadius: '8px',
                    fontWeight: '500',
                    boxShadow: '0 8px 16px rgba(0,0,0,0.12)'
                },
                stopOnFocus: true
            }).showToast();
        }
        
        function addToCart(productId, quantity = 1) {
            $.ajax({
                url: SITE_URL + '/api/cart.php',
                method: 'POST',
                data: {
                    action: 'add',
                    product_id: productId,
                    quantity: quantity
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast(response.message || 'Produk ditambahkan ke keranjang!', 'success');
                        updateCartBadge(response.cart_count);
                    } else {
                        showToast(response.message || 'Gagal menambahkan produk', 'error');
                    }
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan sistem.';
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.message) msg = res.message;
                    } catch(e) {}
                    showToast(msg, 'error');
                }
            });
        }
        
        function updateCartBadge(count) {
            let badge = document.querySelector('.cart-badge');
            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'flex' : 'none';
            } else if (count > 0) {
                const cartBtn = document.querySelector('a[href*="cart.php"]');
                if (cartBtn) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'cart-badge';
                    newBadge.textContent = count;
                    cartBtn.appendChild(newBadge);
                }
            }
        }
        window.updateCartCount = updateCartBadge;
        
        function toggleWishlist(productId) {
            $.ajax({
                url: SITE_URL + '/api/wishlist.php',
                method: 'POST',
                data: {
                    action: 'toggle',
                    product_id: productId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const icon = document.querySelector(`.wishlist-btn[data-id="${productId}"] i`);
                        if (icon) {
                            if (icon.classList.contains('fas')) {
                                icon.classList.remove('fas', 'text-danger');
                                icon.classList.add('far');
                            } else {
                                icon.classList.remove('far');
                                icon.classList.add('fas', 'text-danger');
                            }
                        }
                        showToast(response.message, 'success');
                    } else {
                        if (response.redirect) {
                            window.location.href = SITE_URL + '/login.php';
                        } else {
                            showToast(response.message || 'Gagal memperbarui wishlist', 'error');
                        }
                    }
                },
                error: function() {
                    showToast('Terjadi kesalahan jaringan.', 'error');
                }
            });
        }
        
        function formatRupiah(number) {
            return 'Rp' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    </script>
</body>
</html>
