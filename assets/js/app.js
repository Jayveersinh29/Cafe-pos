// Shared behavior across all pages (mobile sidebar toggle).
document.addEventListener('DOMContentLoaded', function () {
  const toggleBtn = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('appSidebar');
  const backdrop = document.getElementById('sidebarBackdrop');

  if (toggleBtn && sidebar && backdrop) {
    toggleBtn.addEventListener('click', function () {
      sidebar.classList.toggle('show');
      backdrop.classList.toggle('show');
    });
    backdrop.addEventListener('click', function () {
      sidebar.classList.remove('show');
      backdrop.classList.remove('show');
    });
  }
});

// Small helper used by several pages to format currency (Indian Rupee style).
function formatMoney(amount) {
  const n = Number(amount) || 0;
  return '₹' + n.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
