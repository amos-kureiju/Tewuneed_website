// Product data
const products = [
  {
      id: 1,
      name: "Panadol Extra Strength",
      description: "24 tablets",
      price: 380,
      image: "Images/Medicine/pain1.jpeg"
  },
  {
      id: 2,
      name: "Ibuprofen 200mg",
      description: "30 tablets",
      price: 450,
      image: "Images/Medicine/pain2.jpeg"
  },
  {
      id: 3,
      name: "Aspirin 325mg",
      description: "50 tablets",
      price: 320,
      image: "Images/Medicine/pain1.jpeg"
  },
  {
      id: 4,
      name: "Naproxen Sodium",
      description: "20 tablets",
      price: 520,
      image: "Images/Medicine/pain2.jpeg"
  }
];

// Function to load products into the product list
function loadProducts() {
  const productsList = document.getElementById('products-list');
  productsList.innerHTML = '';
  
  products.forEach(product => {
      const productElement = document.createElement('div');
      productElement.className = 'col';
      productElement.innerHTML = `
          <div class="card h-100 product-card">
              <img src="${product.image}" class="card-img-top" alt="${product.name}">
              <div class="card-body text-center">
                  <h5 class="card-title">${product.name}</h5>
                  <p class="card-text">${product.description}</p>
                  <p class="card-text text-muted">Rs ${product.price.toLocaleString()}</p>
                  <button onclick="addToCart(${product.id})" class="btn btn-primary">Add to Cart</button>
                  <button onclick="orderNow(${product.id})" class="btn btn-success">Order Now</button>
              </div>
          </div>
      `;
      productsList.appendChild(productElement);
  });
}

// Function to get product by ID
function getProductById(id) {
  return products.find(product => product.id === id);
}

// Order Now - Direct purchase
function orderNow(productId) {
  const product = getProductById(productId);
  if (!product) return;
  
  // Create a new order with just this product
  const order = {
      id: 'ORD-' + Date.now(),
      date: new Date().toLocaleDateString(),
      items: [{
          ...product,
          quantity: 1
      }],
      total: product.price,
      status: 'Processing'
  };
  
  // Save to orders
  saveOrder(order);
  
  // Show confirmation
  alert(`Your order for ${product.name} has been placed!`);
  
  // Navigate to orders page
  showPage('orders');
} 