// ===========================================================
// POS screen: products grid + cart + billing
// ===========================================================
(function () {
  let allProducts = [];
  let categories = [];
  let cart = []; // { product_id, name, price, qty }
  let activeCategory = 'All';

  const productGrid = document.getElementById('productGrid');
  const categoryBar = document.getElementById('categoryBar');
  const searchBox = document.getElementById('searchBox');
  const cartBody = document.getElementById('cartBody');
  const subtotalEl = document.getElementById('subtotalAmount');
  const discountInput = document.getElementById('discountInput');
  const taxAmountEl = document.getElementById('taxAmount');
  const grandTotalEl = document.getElementById('grandTotalAmount');
  const clearCartBtn = document.getElementById('clearCartBtn');
  const saveBillBtn = document.getElementById('saveBillBtn');
  const holdBillBtn = document.getElementById('holdBillBtn');
  const paymentMethodSelect = document.getElementById('paymentMethod');
  const cashFields = document.getElementById('cashFields');
  const amountReceivedInput = document.getElementById('amountReceived');
  const changeAmountEl = document.getElementById('changeAmount');
  const customerNameInput = document.getElementById('customerName');
  const customerMobileInput = document.getElementById('customerMobile');
  const customerNotesInput = document.getElementById('customerNotes');
  const heldBillsBtn = document.getElementById('heldBillsBtn');
  const heldBillsModalEl = document.getElementById('heldBillsModal');
  const heldBillsBody = document.getElementById('heldBillsBody');
  const heldCountBadge = document.getElementById('heldCountBadge');
  const heldBillsModal = bootstrap.Modal.getOrCreateInstance(heldBillsModalEl);
  const TAX_RATE = 0; // set e.g. 0.05 for 5% GST if needed

  function loadProducts() {
    fetch('../api/products.php')
      .then((r) => r.json())
      .then((data) => {
        allProducts = data.products || [];
        categories = data.categories || [];
        renderCategoryBar();
        renderProducts();
      })
      .catch(() => {
        productGrid.innerHTML = '<div class="col-12 text-danger">Could not load products.</div>';
      });
  }

  function renderCategoryBar() {
    let html = `<button class="btn btn-sm btn-outline-secondary category-pill active" data-cat="All">All</button>`;
    categories.forEach((c) => {
      html += `<button class="btn btn-sm btn-outline-secondary category-pill" data-cat="${escapeHtml(c.name)}">${escapeHtml(c.name)}</button>`;
    });
    categoryBar.innerHTML = html;
    categoryBar.querySelectorAll('button').forEach((btn) => {
      btn.addEventListener('click', () => {
        categoryBar.querySelectorAll('button').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        activeCategory = btn.dataset.cat;
        renderProducts();
      });
    });
  }

  function renderProducts() {
    const term = (searchBox.value || '').toLowerCase().trim();
    const filtered = allProducts.filter((p) => {
      const matchesCat = activeCategory === 'All' || p.category_name === activeCategory;
      const matchesSearch = !term || p.name.toLowerCase().includes(term);
      return matchesCat && matchesSearch;
    });

    if (filtered.length === 0) {
      productGrid.innerHTML = '<div class="col-12 text-muted text-center py-4">No products found.</div>';
      return;
    }

    productGrid.innerHTML = filtered
      .map(
        (p) => `
      <div class="col-6 col-md-4 col-xl-3">
        <div class="product-card h-100" data-id="${p.id}">
          <div class="product-img"><i class="bi bi-cup-straw"></i></div>
          <div class="product-body">
            <div class="fw-semibold small text-truncate">${escapeHtml(p.name)}</div>
            <div class="text-muted" style="font-size:.75rem;">${escapeHtml(p.category_name)}</div>
            <div class="d-flex justify-content-between align-items-center mt-1">
              <span class="product-price">${formatMoney(p.price)}</span>
              <button class="btn btn-sm btn-dark add-btn"><i class="bi bi-plus-lg"></i></button>
            </div>
          </div>
        </div>
      </div>`
      )
      .join('');

    productGrid.querySelectorAll('.product-card').forEach((card) => {
      card.addEventListener('click', () => {
        const id = parseInt(card.dataset.id, 10);
        const product = allProducts.find((p) => p.id === id);
        if (product) addToCart(product);
      });
    });
  }

  function addToCart(product) {
    const existing = cart.find((i) => i.product_id === product.id);
    if (existing) {
      existing.qty += 1;
    } else {
      cart.push({ product_id: product.id, name: product.name, price: parseFloat(product.price), qty: 1 });
    }
    renderCart();
  }

  function renderCart() {
    if (cart.length === 0) {
      cartBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3">Cart is empty</td></tr>`;
    } else {
      cartBody.innerHTML = cart
        .map(
          (item, idx) => `
        <tr class="cart-item-row">
          <td>${escapeHtml(item.name)}</td>
          <td>
            <div class="input-group input-group-sm" style="width:110px;">
              <button class="btn btn-outline-secondary dec-btn" data-idx="${idx}" type="button">-</button>
              <input type="number" min="1" class="form-control qty-input" data-idx="${idx}" value="${item.qty}">
              <button class="btn btn-outline-secondary inc-btn" data-idx="${idx}" type="button">+</button>
            </div>
          </td>
          <td>${formatMoney(item.price)}</td>
          <td>${formatMoney(item.price * item.qty)}</td>
          <td><button class="btn btn-sm btn-outline-danger remove-btn" data-idx="${idx}"><i class="bi bi-trash"></i></button></td>
        </tr>`
        )
        .join('');
    }

    cartBody.querySelectorAll('.inc-btn').forEach((b) =>
      b.addEventListener('click', () => {
        cart[b.dataset.idx].qty += 1;
        renderCart();
      })
    );
    cartBody.querySelectorAll('.dec-btn').forEach((b) =>
      b.addEventListener('click', () => {
        const item = cart[b.dataset.idx];
        item.qty = Math.max(1, item.qty - 1);
        renderCart();
      })
    );
    cartBody.querySelectorAll('.qty-input').forEach((inp) =>
      inp.addEventListener('change', () => {
        const val = Math.max(1, parseInt(inp.value, 10) || 1);
        cart[inp.dataset.idx].qty = val;
        renderCart();
      })
    );
    cartBody.querySelectorAll('.remove-btn').forEach((b) =>
      b.addEventListener('click', () => {
        cart.splice(b.dataset.idx, 1);
        renderCart();
      })
    );

    recalcTotals();
  }

  function getSubtotal() {
    return cart.reduce((sum, i) => sum + i.price * i.qty, 0);
  }

  function recalcTotals() {
    const subtotal = getSubtotal();
    const discount = Math.min(parseFloat(discountInput.value) || 0, subtotal);
    const tax = (subtotal - discount) * TAX_RATE;
    const grandTotal = Math.max(0, subtotal - discount + tax);

    subtotalEl.textContent = formatMoney(subtotal);
    taxAmountEl.textContent = formatMoney(tax);
    grandTotalEl.textContent = formatMoney(grandTotal);

    recalcChange(grandTotal);
    return { subtotal, discount, tax, grandTotal };
  }

  function recalcChange(grandTotal) {
    if (paymentMethodSelect.value !== 'Cash') {
      changeAmountEl.textContent = formatMoney(0);
      return;
    }
    const received = parseFloat(amountReceivedInput.value) || 0;
    const change = received - grandTotal;
    changeAmountEl.textContent = formatMoney(change > 0 ? change : 0);
  }

  function clearCart() {
    cart = [];
    discountInput.value = 0;
    amountReceivedInput.value = '';
    customerNameInput.value = '';
    customerMobileInput.value = '';
    customerNotesInput.value = '';
    renderCart();
  }

  function saveBill(status) {
    if (cart.length === 0) {
      alert('Cart is empty. Add at least one product.');
      return;
    }
    const totals = recalcTotals();
    const payload = {
      status: status, // 'completed' or 'held'
      customer: {
        name: customerNameInput.value.trim() || 'Walk-in',
        mobile: customerMobileInput.value.trim(),
        notes: customerNotesInput.value.trim(),
      },
      items: cart.map((i) => ({ product_id: i.product_id, name: i.name, price: i.price, qty: i.qty })),
      subtotal: totals.subtotal,
      discount: totals.discount,
      tax: totals.tax,
      grand_total: totals.grandTotal,
      payment_method: paymentMethodSelect.value,
      amount_received: paymentMethodSelect.value === 'Cash' ? parseFloat(amountReceivedInput.value) || 0 : null,
    };

    saveBillBtn.disabled = true;
    fetch('../api/create_bill.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })
      .then((r) => r.json())
      .then((res) => {
        saveBillBtn.disabled = false;
        if (res.success) {
          if (status === 'completed') {
            window.open('../pages/print_bill.php?id=' + res.bill_id, '_blank');
          } else {
            alert('Bill held: ' + res.bill_no);
            loadHeldBills();
          }
          clearCart();
        } else {
          alert('Error: ' + (res.message || 'Could not save bill.'));
        }
      })
      .catch(() => {
        saveBillBtn.disabled = false;
        alert('Network error while saving bill.');
      });
  }

  // ---- Held bills: list + resume ----
  function loadHeldBills() {
    fetch('../api/held_bills.php')
      .then((r) => r.json())
      .then((data) => {
        const held = data.held_bills || [];
        heldCountBadge.textContent = held.length;

        if (held.length === 0) {
          heldBillsBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">No held bills right now.</td></tr>`;
          return;
        }

        heldBillsBody.innerHTML = held
          .map(
            (h) => `
          <tr>
            <td>${escapeHtml(h.bill_no)}</td>
            <td>${escapeHtml(h.customer_name)}</td>
            <td>${h.item_count}</td>
            <td class="text-end">${formatMoney(h.grand_total)}</td>
            <td class="small">${new Date(h.created_at).toLocaleString()}</td>
            <td class="text-end"><button class="btn btn-sm btn-dark resume-btn" data-id="${h.id}">Resume</button></td>
          </tr>`
          )
          .join('');

        heldBillsBody.querySelectorAll('.resume-btn').forEach((b) =>
          b.addEventListener('click', () => resumeHeldBill(b.dataset.id, b))
        );
      })
      .catch(() => {
        heldBillsBody.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-3">Could not load held bills.</td></tr>`;
      });
  }

  function resumeHeldBill(id, btn) {
    if (cart.length > 0 && !confirm('This will replace the items currently in your cart with the held bill. Continue?')) {
      return;
    }
    if (btn) btn.disabled = true;

    fetch('../api/held_bills.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id }),
    })
      .then((r) => r.json())
      .then((res) => {
        if (!res.success) {
          alert(res.message || 'Could not resume this bill.');
          if (btn) btn.disabled = false;
          return;
        }

        const bill = res.bill;
        const items = res.items || [];

        cart = items.map((i) => ({
          product_id: i.product_id,
          name: i.product_name,
          price: parseFloat(i.price),
          qty: parseInt(i.quantity, 10),
        }));

        customerNameInput.value = bill.customer_name && bill.customer_name !== 'Walk-in' ? bill.customer_name : '';
        customerMobileInput.value = bill.customer_mobile || '';
        customerNotesInput.value = bill.customer_notes || '';
        discountInput.value = parseFloat(bill.discount) || 0;

        paymentMethodSelect.value = bill.payment_method || 'Cash';
        cashFields.classList.toggle('d-none', paymentMethodSelect.value !== 'Cash');
        amountReceivedInput.value = bill.amount_received ? parseFloat(bill.amount_received) : '';

        renderCart();
        heldBillsModal.hide();
        loadHeldBills();
      })
      .catch(() => {
        alert('Network error while resuming this bill.');
      })
      .finally(() => {
        if (btn) btn.disabled = false;
      });
  }

  heldBillsModalEl.addEventListener('show.bs.modal', loadHeldBills);

  // ---- Event wiring ----
  searchBox.addEventListener('input', renderProducts);
  discountInput.addEventListener('input', recalcTotals);
  amountReceivedInput.addEventListener('input', () => recalcTotals());
  paymentMethodSelect.addEventListener('change', () => {
    cashFields.classList.toggle('d-none', paymentMethodSelect.value !== 'Cash');
    recalcTotals();
  });
  clearCartBtn.addEventListener('click', () => {
    if (confirm('Clear the current order?')) clearCart();
  });
  saveBillBtn.addEventListener('click', () => saveBill('completed'));
  holdBillBtn.addEventListener('click', () => saveBill('held'));

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
  }

  renderCart();
  loadProducts();
  loadHeldBills();
})();
