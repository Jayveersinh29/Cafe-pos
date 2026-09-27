<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_admin();
$pageTitle = 'Products';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Manage Products</h4>
    <button id="addProductBtn" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Add Product</button>
  </div>

  <div class="card stat-card p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Name</th><th>Category</th><th>Price</th><th>Status</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody id="productsTableBody">
          <tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Add/Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="productForm">
        <div class="modal-header">
          <h5 class="modal-title" id="productModalTitle">Add Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="productId">
          <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" id="productName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Category</label>
            <select id="categorySelect" class="form-select" required></select>
          </div>
          <div class="mb-3">
            <label class="form-label">Price (₹)</label>
            <input type="number" id="productPrice" class="form-control" min="0" step="0.01" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-dark">Save Product</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$pageScripts = ['../assets/js/products.js'];
require __DIR__ . '/../includes/footer.php';
?>
