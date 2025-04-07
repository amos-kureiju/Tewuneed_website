// Koneksi ke Supabase
const supabaseUrl = 'https://iyoedluuipinhcxppyub.supabase.co';
const supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml5b2VkbHV1aXBpbmhjeHBweXViIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM5ODkwMzEsImV4cCI6MjA1OTU2NTAzMX0.nxK36ecQqI5lqK_074oTBjMTJOlh2uwtwIYzCVk6HcU';
const supabase = supabase.createClient(supabaseUrl, supabaseKey);

// DOM Elements
const loginSection = document.getElementById('login-section');
const adminPanel = document.getElementById('admin-panel');
const loginBtn = document.getElementById('login-btn');
const logoutBtn = document.getElementById('logout-btn');
    
// Login Admin
loginBtn.addEventListener('click', async () => {
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  
  const { data, error } = await supabase.auth.signInWithPassword({
    email: email,
    password: password
  });

  if (error) {
    alert('Login gagal: ' + error.message);
  } else {
    loginSection.style.display = 'none';
    adminPanel.style.display = 'block';
    loadProducts(); // Muat daftar produk
  }
});

// Logout
logoutBtn.addEventListener('click', async () => {
  await supabase.auth.signOut();
  adminPanel.style.display = 'none';
  loginSection.style.display = 'block';
});

// Tambah Produk
document.getElementById('add-product-btn').addEventListener('click', async () => {
  const name = document.getElementById('product-name').value;
  const price = parseFloat(document.getElementById('product-price').value);
  const stock = parseInt(document.getElementById('product-stock').value);

  const { data, error } = await supabase
    .from('products')
    .insert([{ name, price, stock, status: stock > 0 ? 'available' : 'out_of_stock' }]);

  if (error) {
    alert('Gagal menambah produk: ' + error.message);
  } else {
    alert('Produk berhasil ditambah!');
    loadProducts(); // Segarkan daftar
  }
});

// Muat Daftar Produk
async function loadProducts() {
  const { data: products, error } = await supabase
    .from('products')
    .select('*');

  if (error) {
    console.error('Gagal memuat produk:', error);
    return;
  }

  const productsList = document.getElementById('products-list');
  productsList.innerHTML = '';

  products.forEach(product => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${product.name}</td>
      <td>${product.price}</td>
      <td>${product.stock}</td>
      <td>
        <button onclick="editProduct(${product.id})" class="btn btn-sm btn-warning">Edit</button>
        <button onclick="deleteProduct(${product.id})" class="btn btn-sm btn-danger">Hapus</button>
      </td>
    `;
    productsList.appendChild(row);
  });
}

// Hapus Produk
async function deleteProduct(id) {
  if (!confirm('Hapus produk ini?')) return;
  
  const { error } = await supabase
    .from('products')
    .delete()
    .eq('id', id);

  if (error) {
    alert('Gagal menghapus: ' + error.message);
  } else {
    loadProducts();
  }
}

// Edit Produk (Contoh Sederhana)
async function editProduct(id) {
  const newStock = prompt('Masukkan stok baru:');
  if (newStock === null) return;

  const { error } = await supabase
    .from('products')
    .update({ stock: newStock, status: newStock > 0 ? 'available' : 'out_of_stock' })
    .eq('id', id);

  if (error) {
    alert('Gagal update: ' + error.message);
  } else {
    loadProducts();
  }
}

// Cek Session Saat Halaman Dimuat
supabase.auth.getSession().then(({ data: { session } }) => {
  if (session) {
    loginSection.style.display = 'none';
    adminPanel.style.display = 'block';
    loadProducts();
  }
});