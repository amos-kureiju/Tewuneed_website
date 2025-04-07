let cart = JSON.parse(localStorage.getItem('cart')) || [];
let currentStep = 1;

// Inisialisasi halaman
document.addEventListener('DOMContentLoaded', function() {
    updateCartUI();
});

function showPage(page) {
    // Sembunyikan semua halaman
    document.querySelectorAll('.page').forEach(el => {
        el.classList.remove('active');
    });
    
    // Tampilkan halaman yang dipilih
    document.getElementById(page + '-page').classList.add('active');
    
    // Jika halaman keranjang, update tampilan
    if (page === 'cart') {
        updateCartUI();
    }
}

function addToCart(id, name, price, image) {
    // Cek apakah produk sudah ada di keranjang
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: id,
            name: name,
            price: price,
            image: image,
            quantity: 1
        });
    }
    
    // Simpan ke localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Update UI
    updateCartUI();
    
    // Tampilkan notifikasi
    showToast(`${name} ditambahkan ke keranjang`, 'success');
    
    // Tampilkan halaman keranjang
    showPage('cart');
}

function updateCartUI() {
    // Update jumlah di keranjang
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    document.querySelector('.cart-count').textContent = totalItems;
    document.getElementById('order-count').textContent = cart.length;
    
    // Update daftar keranjang
    const cartList = document.getElementById('cart-list');
    cartList.innerHTML = '';
    
    let totalPrice = 0;
    
    if (cart.length === 0) {
        cartList.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info">Keranjang belanja kosong</div>
                <button class="btn btn-primary" onclick="showPage('products')">Belanja Sekarang</button>
            </div>
        `;
        return;
    }
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        totalPrice += itemTotal;
        
        const cartItem = document.createElement('div');
        cartItem.className = 'col-12 mb-3';
        cartItem.innerHTML = `
            <div class="card">
                <div class="row g-0">
                    <div class="col-md-2">
                        <img src="${item.image}" class="img-fluid rounded-start" alt="${item.name}">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title">${item.name}</h5>
                            <p class="card-text">Harga: Rs ${item.price.toLocaleString()}</p>
                            <div class="d-flex align-items-center">
                                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.id}, -1)">-</button>
                                <span class="mx-2">${item.quantity}</span>
                                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${item.id}, 1)">+</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-end">
                        <button class="btn btn-danger me-2" onclick="removeFromCart(${item.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                        <h5>Rs ${itemTotal.toLocaleString()}</h5>
                    </div>
                </div>
            </div>
        `;
        cartList.appendChild(cartItem);
    });
    
    // Tambahkan total dan checkout button
    const totalElement = document.createElement('div');
    totalElement.className = 'col-12 mt-4';
    totalElement.innerHTML = `
        <div class="card">
            <div class="card-body text-end">
                <h4>Total Belanja: Rs ${totalPrice.toLocaleString()}</h4>
                <button class="btn btn-success btn-lg mt-3" onclick="startCheckout()">
                    <i class="bi bi-credit-card"></i> Checkout
                </button>
            </div>
        </div>
    `;
    cartList.appendChild(totalElement);
}

function updateQuantity(id, change) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity += change;
        
        // Jika quantity <= 0, hapus item
        if (item.quantity <= 0) {
            cart = cart.filter(item => item.id !== id);
            showToast("Produk dihapus dari keranjang", 'danger');
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartUI();
    }
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
    showToast("Produk dihapus dari keranjang", 'danger');
}

function startCheckout() {
    showPage('checkout');
    resetCheckoutSteps();
}

function resetCheckoutSteps() {
    currentStep = 1;
    document.querySelectorAll('.step').forEach((step, index) => {
        if (index + 1 === currentStep) {
            step.classList.add('active');
        } else {
            step.classList.remove('active');
        }
    });
    
    document.getElementById('address-section').style.display = 'block';
    document.getElementById('payment-section').style.display = 'none';
    document.getElementById('confirm-section').style.display = 'none';
}

function nextStep(step) {
    if (step === 2 && !validateAddress()) return;
    if (step === 3 && !validatePayment()) return;
    
    currentStep = step;
    updateCheckoutSteps();
}

function prevStep(step) {
    currentStep = step;
    updateCheckoutSteps();
}

function updateCheckoutSteps() {
    document.querySelectorAll('.step').forEach((step, index) => {
        if (index + 1 <= currentStep) {
            step.classList.add('active');
        } else {
            step.classList.remove('active');
        }
    });
    
    document.getElementById('address-section').style.display = currentStep === 1 ? 'block' : 'none';
    document.getElementById('payment-section').style.display = currentStep === 2 ? 'block' : 'none';
    document.getElementById('confirm-section').style.display = currentStep === 3 ? 'block' : 'none';
    
    if (currentStep === 3) {
        prepareConfirmation();
    }
}

function validateAddress() {
    const form = document.getElementById('address-form');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }
    return true;
}

function validatePayment() {
    // Validasi sederhana - pastikan metode dipilih
    const paymentMethod = document.querySelector('input[name="payment-method"]:checked');
    if (!paymentMethod) {
        showToast("Pilih metode pembayaran", 'warning');
        return false;
    }
    return true;
}

function prepareConfirmation() {
    // Tampilkan alamat
    const address = `
        <p><strong>${document.getElementById('name').value}</strong></p>
        <p>${document.getElementById('phone').value}</p>
        <p>${document.getElementById('address').value}</p>
        <p>${document.getElementById('city').value}, ${document.getElementById('postal-code').value}</p>
    `;
    document.getElementById('confirm-address').innerHTML = address;
    
    // Tampilkan metode pembayaran
    const paymentMethod = document.querySelector('input[name="payment-method"]:checked').value;
    let paymentText = '';
    switch(paymentMethod) {
        case 'bank-transfer':
            paymentText = 'Transfer Bank';
            break;
        case 'credit-card':
            paymentText = 'Kartu Kredit';
            break;
        case 'cod':
            paymentText = 'Bayar di Tempat (COD)';
            break;
    }
    document.getElementById('confirm-payment').innerHTML = `<p>${paymentText}</p>`;
    
    // Tampilkan item pesanan
    let itemsHtml = '';
    let totalPrice = 0;
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        totalPrice += itemTotal;
        
        itemsHtml += `
            <div class="d-flex justify-content-between mb-2">
                <div>
                    ${item.name} (${item.quantity}x)
                </div>
                <div>
                    Rs ${itemTotal.toLocaleString()}
                </div>
            </div>
        `;
    });
    
    document.getElementById('confirm-items').innerHTML = itemsHtml;
    document.getElementById('confirm-total').textContent = `Rs ${totalPrice.toLocaleString()}`;
}

function placeOrder() {
    // Simulasikan proses order
    const orderNumber = 'ORD-' + Math.floor(Math.random() * 1000000);
    document.getElementById('order-number').textContent = orderNumber;
    
    // Kosongkan keranjang
    cart = [];
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
    
    // Tampilkan halaman sukses
    showPage('success');
}

function showToast(message, type) {
    let background = '';
    switch(type) {
        case 'success':
            background = 'linear-gradient(to right, #00b09b, #96c93d)';
            break;
        case 'danger':
            background = 'linear-gradient(to right, #ff416c, #ff4b2b)';
            break;
        case 'warning':
            background = 'linear-gradient(to right, #f46b45, #eea849)';
            break;
        default:
            background = 'linear-gradient(to right, #00b09b, #96c93d)';
    }
    
    Toastify({
        text: message,
        duration: 3000,
        close: true,
        gravity: "top",
        position: "right",
        backgroundColor: background,
        stopOnFocus: true
    }).showToast();
}
