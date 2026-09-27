// ===========================================================
// Reports page: date-range summary + simple sales chart
// ===========================================================
(function () {
  const rangeSelect = document.getElementById('rangeSelect');
  const totalSalesEl = document.getElementById('reportTotalSales');
  const totalBillsEl = document.getElementById('reportTotalBills');
  const avgBillEl = document.getElementById('reportAvgBill');
  const topProductsBody = document.getElementById('topProductsBody');
  const chartCanvas = document.getElementById('salesChart');
  let chartInstance = null;

  function loadReport() {
    const range = rangeSelect.value;
    fetch('../api/reports.php?range=' + encodeURIComponent(range))
      .then((r) => r.json())
      .then((data) => {
        totalSalesEl.textContent = formatMoney(data.total_sales || 0);
        totalBillsEl.textContent = data.total_bills || 0;
        const avg = data.total_bills > 0 ? (data.total_sales / data.total_bills) : 0;
        avgBillEl.textContent = formatMoney(avg);
        renderTopProducts(data.top_products || []);
        renderChart(data.daily_sales || []);
      });
  }

  function renderTopProducts(rows) {
    if (rows.length === 0) {
      topProductsBody.innerHTML = `<tr><td colspan="3" class="text-center text-muted py-3">No sales in this range.</td></tr>`;
      return;
    }
    topProductsBody.innerHTML = rows
      .map(
        (r) => `<tr>
          <td>${escapeHtml(r.product_name)}</td>
          <td>${r.total_qty}</td>
          <td>${formatMoney(r.total_amount)}</td>
        </tr>`
      )
      .join('');
  }

  function renderChart(daily) {
    const labels = daily.map((d) => d.day);
    const values = daily.map((d) => parseFloat(d.total));

    if (chartInstance) {
      chartInstance.destroy();
    }
    chartInstance = new Chart(chartCanvas, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Sales',
            data: values,
            borderColor: '#d98324',
            backgroundColor: 'rgba(217,131,36,0.15)',
            tension: 0.3,
            fill: true,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } },
      },
    });
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
  }

  rangeSelect.addEventListener('change', loadReport);
  loadReport();
})();
