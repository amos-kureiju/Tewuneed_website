// Fungsi untuk membuka modal produk
function openModal(modalId) {
  document.getElementById(modalId).style.display = 'block';
  document.body.style.overflow = 'hidden'; // Mencegah scroll di background
}

// Fungsi untuk menutup modal produk
function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
  document.body.style.overflow = 'auto'; // Mengembalikan scroll
}

// Tutup modal ketika mengklik di luar area modal
window.onclick = function(event) {
  if (event.target.className === 'product-modal') {
      event.target.style.display = 'none';
      document.body.style.overflow = 'auto';
  }
}

// Inisialisasi quantity selector untuk semua modal
document.addEventListener('DOMContentLoaded', function() {
  // Quantity selector functionality
  document.querySelectorAll('.quantity-selector').forEach(selector => {
      const decreaseBtn = selector.querySelector('.decrease');
      const increaseBtn = selector.querySelector('.increase');
      const qtyInput = selector.querySelector('.qty-input');
      
      decreaseBtn.addEventListener('click', () => {
          let value = parseInt(qtyInput.value);
          if (value > 1) {
              qtyInput.value = value - 1;
          }
      });
      
      increaseBtn.addEventListener('click', () => {
          let value = parseInt(qtyInput.value);
          qtyInput.value = value + 1;
      });
  });

  // Tambahkan event listener untuk semua tombol "Lihat Detail"
  document.querySelectorAll('.btn-detail').forEach(button => {
      button.addEventListener('click', function() {
          const productCard = this.closest('.product-card');
          const productTitle = productCard.querySelector('.product-title').textContent;
          
          // Cari modal yang sesuai berdasarkan judul produk
          const modals = document.querySelectorAll('.product-modal');
          modals.forEach(modal => {
              const modalTitle = modal.querySelector('.modal-product-title').textContent;
              if (modalTitle === productTitle) {
                  const modalId = modal.id;
                  openModal(modalId);
              }
          });
      });
  });

  // Update cart counter saat halaman dimuat
  updateCartCounter();
});

// Quantity selector functionality
document.querySelectorAll('.quantity-selector').forEach(selector => {
  const decreaseBtn = selector.querySelector('.decrease');
  const increaseBtn = selector.querySelector('.increase');
  const qtyInput = selector.querySelector('.qty-input');
  
  decreaseBtn.addEventListener('click', () => {
      let value = parseInt(qtyInput.value);
      if (value > 1) {
          qtyInput.value = value - 1;
      }
  });
  
  increaseBtn.addEventListener('click', () => {
      let value = parseInt(qtyInput.value);
      qtyInput.value = value + 1;
  });
});

// Function to add to cart with selected quantity
function addToCartWithQuantity(id, name, price, image) {
  const quantity = parseInt(document.querySelector('.qty-input').value);
  
  // Ambil data keranjang dari localStorage atau buat baru jika kosong
  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  
  // Cek apakah item sudah ada di keranjang
  const existingItem = cart.find(item => item.id === id);
  
  if (existingItem) {
      // Jika sudah ada, tambah quantity sesuai input
      existingItem.quantity += quantity;
  } else {
      // Jika belum ada, tambahkan item baru dengan quantity yang dipilih
      cart.push({
          id: id,
          name: name,
          price: price,
          image: image,
          quantity: quantity
      });
  }
  
  // Simpan ke localStorage
  localStorage.setItem('cart', JSON.stringify(cart));
  
  // Update counter keranjang
  updateCartCounter();
  
  // Tampilkan notifikasi
  showToast(`${quantity} ${name} ditambahkan ke keranjang`, 'success');
  
  // Redirect ke halaman order setelah 1 detik
  setTimeout(() => {
      window.location.href = 'keranjang.html';
  }, 1000);
}

// Original addToCart function (kept for compatibility)
function addToCart(id, name, price, image) {
  addToCartWithQuantity(id, name, price, image, 1);
}

// Fungsi untuk update counter keranjang
function updateCartCounter() {
  const cart = JSON.parse(localStorage.getItem('cart')) || [];
  const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
  const cartCountElements = document.querySelectorAll('.cart-count');
  
  if (cartCountElements) {
      cartCountElements.forEach(element => {
          element.textContent = totalItems;
      });
  }
}

// Fungsi untuk menampilkan notifikasi
function showToast(message, type) {
  let background = '';
  switch(type) {
      case 'success':
          background = 'linear-gradient(to right, #00b09b, #96c93d)';
          break;
      case 'danger':
          background = 'linear-gradient(to right, #ff416c, #ff4b2b)';
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

// Fungsi untuk search produk (jika diperlukan)
function searchProducts() {
  const searchTerm = document.querySelector('.search-bar').value.toLowerCase();
  const productCards = document.querySelectorAll('.product-card');
  
  productCards.forEach(card => {
      const title = card.querySelector('.product-title').textContent.toLowerCase();
      if (title.includes(searchTerm)) {
          card.style.display = 'block';
      } else {
          card.style.display = 'none';
      }
  });
}

// Event listener untuk search bar
const searchBar = document.querySelector('.search-bar');
if (searchBar) {
  searchBar.addEventListener('keyup', function(event) {
      if (event.key === 'Enter') {
          searchProducts();
      }
  });
  
  const searchIcon = document.querySelector('.fa-search');
  if (searchIcon) {
      searchIcon.addEventListener('click', searchProducts);
  }
}