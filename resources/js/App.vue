<template>
  <div id="app">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
      <div class="container">
        <router-link to="/" class="navbar-brand">E-Commerce Store</router-link>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <router-link to="/" class="nav-link">Products</router-link>
            </li>
          </ul>
          <div class="navbar-nav">
            <span class="navbar-text me-3">
              Cart: {{ cartItems.length }} items
            </span>
          </div>
        </div>
      </div>
    </nav>

    <main class="container mt-4">
      <router-view :cart-items="cartItems" @add-to-cart="addToCart" @remove-from-cart="removeFromCart" @clear-cart="clearCart" />
    </main>

    <footer class="bg-light mt-5 py-4">
      <div class="container text-center">
        <p>&copy; 2024 E-Commerce Store. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      cartItems: []
    }
  },
  methods: {
    addToCart(product, quantity = 1) {
      const existingItem = this.cartItems.find(item => item.id === product.id)
      
      if (existingItem) {
        existingItem.quantity += quantity
      } else {
        this.cartItems.push({
          id: product.id,
          name: product.name,
          price: product.price,
          image_url: product.image_url,
          quantity: quantity
        })
      }
    },
    removeFromCart(productId) {
      const index = this.cartItems.findIndex(item => item.id === productId)
      if (index > -1) {
        this.cartItems.splice(index, 1)
      }
    },
    clearCart() {
      this.cartItems = []
    }
  }
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.navbar-brand {
  font-weight: bold;
}

.card {
  transition: transform 0.2s;
}

.card:hover {
  transform: translateY(-5px);
}

.btn-primary {
  background-color: #007bff;
  border-color: #007bff;
}

.btn-primary:hover {
  background-color: #0056b3;
  border-color: #0056b3;
}
</style>
