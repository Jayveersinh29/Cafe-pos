<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_login();
$pageTitle = 'POS';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
  <h4 class="mb-3">Point of Sale</h4>

  <div class="row g-3">
    <!-- LEFT: products -->
    <div class="col-lg-7">
      <div class="d-flex gap-2 align-items-center mb-2">
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
          <input type="text" id="searchBox" class="form-control" placeholder="Search product...">
        </div>
      </div>
      <div id="categoryBar" class="d-flex flex-wrap gap-2 mb-3"></div>
      <div id="productGrid" class="row g-3" style="max-height:65vh; overflow-y:auto;"></div>
    </div>

    <!-- RIGHT: cart -->
    <div class="col-lg-5">
      <div class="cart-panel p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0"><i class="bi bi-cart3"></i> Current Order</h6>
          <button type="button" class="btn btn-sm btn-outline-warning" id="heldBillsBtn"
                  data-bs-toggle="modal" data-bs-target="#heldBillsModal">
            <i class="bi bi-pause-circle"></i> Held
            <span class="badge bg-warning text-dark" id="heldCountBadge">0</span>
          </button>
        </div>

        <div class="cart-items mb-2">
          <table class="table table-sm align-middle">
            <thead>
              <tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th><th></th></tr>
            </thead>
            <tbody id="cartBody"></tbody>
          </table>
        </div>

        <div class="mb-2">
          <label class="form-label small mb-1">Customer (optional for walk-in)</label>
          <input type="text" id="customerName" class="form-control form-control-sm mb-1" placeholder="Customer name">
          <input type="text" id="customerMobile" class="form-control form-control-sm mb-1" placeholder="Mobile number">
          <input type="text" id="customerNotes" class="form-control form-control-sm" placeholder="Notes (optional)">
        </div>

        <div class="cart-summary">
          <div class="d-flex justify-content-between">
            <span>Subtotal</span><span id="subtotalAmount">₹0.00</span>
          </div>
          <div class="d-flex justify-content-between align-items-center my-1">
            <span>Discount</span>
            <input type="number" id="discountInput" class="form-control form-control-sm" style="width:100px;" value="0" min="0">
          </div>
          <div class="d-flex justify-content-between">
            <span>Tax</span><span id="taxAmount">₹0.00</span>
          </div>
          <div class="d-flex justify-content-between mt-2">
            <span class="grand-total">TOTAL</span>
            <span class="grand-total" id="grandTotalAmount">₹0.00</span>
          </div>
        </div>

        <div class="mt-3">
          <label class="form-label small mb-1">Payment Method</label>
          <select id="paymentMethod" class="form-select form-select-sm mb-2">
            <option value="Cash">Cash</option>
            <option value="UPI">UPI</option>
            <option value="Card">Card</option>
            <option value="Other">Other</option>
          </select>
          <div id="cashFields">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="small mb-0">Amount Received</label>
              <input type="number" id="amountReceived" class="form-control form-control-sm" style="width:120px;" min="0">
            </div>
            <div class="d-flex justify-content-between">
              <span class="small">Change</span><span id="changeAmount">₹0.00</span>
            </div>
          </div>
        </div>

        <div class="d-grid gap-2 mt-3">
          <div class="d-flex gap-2">
            <button id="clearCartBtn" class="btn btn-outline-secondary flex-fill btn-sm">Clear Cart</button>
            <button id="holdBillBtn" class="btn btn-outline-warning flex-fill btn-sm">Hold Bill</button>
          </div>
          <button id="saveBillBtn" class="btn btn-dark"><i class="bi bi-printer"></i> Save &amp; Print Bill</button>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Held Bills Modal -->
<div class="modal fade" id="heldBillsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pause-circle"></i> Held Bills</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead>
              <tr><th>Bill No</th><th>Customer</th><th>Items</th><th class="text-end">Total</th><th>Held At</th><th></th></tr>
            </thead>
            <tbody id="heldBillsBody">
              <tr><td colspan="6" class="text-center text-muted py-3">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$pageScripts = ['../assets/js/pos.js'];
require __DIR__ . '/../includes/footer.php';
?>
