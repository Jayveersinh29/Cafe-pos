// ===========================================================
// Product management (admin): list, add, edit, delete, toggle active
// ===========================================================
(function () {
  const tableBody = document.getElementById('productsTableBody');
  const form = document.getElementById('productForm');
  const modalEl = document.getElementById('productModal');
  const modal = new bootstrap.Modal(modalEl);
  const modalTitle = document.getElementById('productModalTitle');
  const categorySelect = document.getElementById('categorySelect');
  const addProductBtn = document.getElementById('addProductBtn');

  let categories = [];
  let products = [];

  function loadData() {
    fetch('../api/products.php')
      .then((r) => r.json())
      .then((data) => {
        categories = data.categories || [];
        products = data.products || [];
        renderCategoryOptions();
        renderTable();
      });
  }

  function renderCategoryOptions() {
    categorySelect.innerHTML = categories
      .map((c) => `<option value="${c.id}">${escapeHtml(c.name)}</option>`)
      .join('');
  }

  function renderTable() {
    if (products.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-3">No products yet.</td></tr>`;
      return;
    }
    tableBody.innerHTML = products
      .map(
        (p) => `
      <tr>
        <td>${escapeHtml(p.name)}</td>
        <td>${escapeHtml(p.category_name)}</td>
        <td>${formatMoney(p.price)}</td>
        <td>
          <span class="badge ${p.is_active == 1 ? 'bg-success' : 'bg-secondary'}">
            ${p.is_active == 1 ? 'Active' : 'Inactive'}
          </span>
        </td>
        <td class="text-end">
          <button class="btn btn-sm btn-outline-primary edit-btn" data-id="${p.id}"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-secondary toggle-btn" data-id="${p.id}" data-active="${p.is_active}">
            <i class="bi ${p.is_active == 1 ? 'bi-eye-slash' : 'bi-eye'}"></i>
          </button>
          <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${p.id}"><i class="bi bi-trash"></i></button>
        </td>
      </tr>`
      )
      .join('');

    tableBody.querySelectorAll('.edit-btn').forEach((b) => b.addEventListener('click', () => openEdit(b.dataset.id)));
    tableBody.querySelectorAll('.delete-btn').forEach((b) => b.addEventListener('click', () => deleteProduct(b.dataset.id)));
    tableBody.querySelectorAll('.toggle-btn').forEach((b) =>
      b.addEventListener('click', () => toggleActive(b.dataset.id, b.dataset.active))
    );
  }

  function openAdd() {
    form.reset();
    document.getElementById('productId').value = '';
    modalTitle.textContent = 'Add Product';
    modal.show();
  }

  function openEdit(id) {
    const p = products.find((x) => x.id == id);
    if (!p) return;
    document.getElementById('productId').value = p.id;
    document.getElementById('productName').value = p.name;
    categorySelect.value = p.category_id;
    document.getElementById('productPrice').value = p.price;
    modalTitle.textContent = 'Edit Product';
    modal.show();
  }

  function toggleActive(id, currentActive) {
    const p = products.find((x) => x.id == id);
    if (!p) return;
    saveProduct({
      id: p.id,
      name: p.name,
      category_id: p.category_id,
      price: p.price,
      is_active: currentActive == 1 ? 0 : 1,
    });
  }

  function deleteProduct(id) {
    if (!confirm('Delete this product? This cannot be undone.')) return;
    fetch('../api/delete_product.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id }),
    })
      .then((r) => r.json())
      .then((res) => {
        if (res.success) {
          loadData();
        } else {
          alert(res.message || 'Could not delete product (it may be used in past bills).');
        }
      });
  }

  function saveProduct(payload) {
    fetch('../api/products.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    })
      .then((r) => r.json())
      .then((res) => {
        if (res.success) {
          modal.hide();
          loadData();
        } else {
          alert(res.message || 'Could not save product.');
        }
      });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const payload = {
      id: document.getElementById('productId').value || null,
      name: document.getElementById('productName').value.trim(),
      category_id: categorySelect.value,
      price: parseFloat(document.getElementById('productPrice').value) || 0,
      is_active: 1,
    };
    if (!payload.name) {
      alert('Product name is required.');
      return;
    }
    saveProduct(payload);
  });

  addProductBtn.addEventListener('click', openAdd);

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
  }

  loadData();
})();
