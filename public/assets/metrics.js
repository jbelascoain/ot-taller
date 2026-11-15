(function () {
  'use strict';

  // Filtro de período: "Personalizado" muestra las fechas; el resto se aplica solo.
  var form = document.querySelector('[data-metrics-form]');
  var preset = document.querySelector('[data-metrics-preset]');
  var custom = document.querySelector('[data-metrics-custom]');

  if (form && preset && custom) {
    var syncCustom = function () {
      var isCustom = preset.value === 'custom';
      custom.classList.toggle('hidden', !isCustom);
      custom.querySelectorAll('input').forEach(function (input) {
        input.disabled = !isCustom;
      });
    };

    preset.addEventListener('change', function () {
      syncCustom();

      if (preset.value !== 'custom') {
        form.submit();
      }
    });

    syncCustom();
  }

  var dataEl = document.getElementById('metrics-data');

  if (!dataEl || typeof window.Chart === 'undefined') {
    return;
  }

  var data;

  try {
    data = JSON.parse(dataEl.textContent);
  } catch (error) {
    return;
  }

  var charts = [];
  var statusColors = {
    ingreso: '#0ea5e9',
    presupuestado: '#f59e0b',
    reparacion: '#f97316',
    'para-entregar': '#8b5cf6',
    entregado: '#10b981',
    cancelado: '#94a3b8'
  };

  function cssVar(name, fallback) {
    var value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return value || fallback;
  }

  function formatMoney(value) {
    var formatted = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 0 }).format(Number(value) || 0);
    return (data.currency || '$') + ' ' + formatted;
  }

  function baseOptions() {
    return {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          position: 'bottom',
          labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, padding: 14 }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { maxRotation: 0, autoSkip: true, color: cssVar('--muted', '#6b7280') }
        },
        y: {
          beginAtZero: true,
          grid: { color: cssVar('--border', '#e3e6eb') },
          ticks: { color: cssVar('--muted', '#6b7280'), precision: 0 }
        }
      }
    };
  }

  function buildActivity() {
    var canvas = document.getElementById('metrics-activity');
    if (!canvas) return;

    charts.push(new Chart(canvas, {
      type: 'bar',
      data: {
        labels: data.series.labels,
        datasets: [
          {
            label: 'Ingresadas',
            data: data.series.created,
            backgroundColor: cssVar('--primary', '#1f6feb'),
            borderRadius: 6,
            maxBarThickness: 28
          },
          {
            label: 'Entregadas',
            data: data.series.delivered,
            backgroundColor: '#10b981',
            borderRadius: 6,
            maxBarThickness: 28
          }
        ]
      },
      options: baseOptions()
    }));
  }

  function buildRevenue() {
    var canvas = document.getElementById('metrics-revenue');
    if (!canvas) return;

    var options = baseOptions();
    options.scales.x.stacked = true;
    options.scales.y.stacked = true;
    options.scales.y.ticks.callback = function (value) { return formatMoney(value); };
    options.plugins.tooltip = {
      callbacks: {
        label: function (context) {
          return context.dataset.label + ': ' + formatMoney(context.parsed.y);
        }
      }
    };

    charts.push(new Chart(canvas, {
      type: 'bar',
      data: {
        labels: data.series.labels,
        datasets: [
          {
            label: 'Mano de obra',
            data: data.series.labor,
            backgroundColor: cssVar('--primary', '#1f6feb'),
            borderRadius: 6,
            maxBarThickness: 32
          },
          {
            label: 'Repuestos',
            data: data.series.parts,
            backgroundColor: '#f59e0b',
            borderRadius: 6,
            maxBarThickness: 32
          }
        ]
      },
      options: options
    }));
  }

  function buildStatus() {
    var canvas = document.getElementById('metrics-status');
    if (!canvas) return;

    charts.push(new Chart(canvas, {
      type: 'doughnut',
      data: {
        labels: data.statuses.map(function (status) { return status.label; }),
        datasets: [{
          data: data.statuses.map(function (status) { return status.count; }),
          backgroundColor: data.statuses.map(function (status) {
            return statusColors[status.class] || '#94a3b8';
          }),
          borderWidth: 0,
          hoverOffset: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, padding: 14 }
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return ' ' + context.label + ': ' + context.parsed;
              }
            }
          }
        }
      }
    }));
  }

  function buildTopParts() {
    var canvas = document.getElementById('metrics-top-parts');
    if (!canvas) return;

    charts.push(new Chart(canvas, {
      type: 'bar',
      data: {
        labels: data.topParts.map(function (part) { return part.label; }),
        datasets: [{
          data: data.topParts.map(function (part) { return part.amount; }),
          backgroundColor: cssVar('--primary', '#1f6feb'),
          borderRadius: 6,
          maxBarThickness: 24
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (context) {
                var part = data.topParts[context.dataIndex];
                return formatMoney(context.parsed.x) + ' · ' + part.qty + ' u.';
              }
            }
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            grid: { color: cssVar('--border', '#e3e6eb') },
            ticks: { callback: function (value) { return formatMoney(value); }, color: cssVar('--muted', '#6b7280') }
          },
          y: {
            grid: { display: false },
            ticks: { color: cssVar('--text', '#16181d') }
          }
        }
      }
    }));
  }

  function buildAll() {
    charts.forEach(function (chart) { chart.destroy(); });
    charts = [];

    Chart.defaults.color = cssVar('--muted', '#6b7280');
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.borderColor = cssVar('--border', '#e3e6eb');

    buildActivity();
    buildRevenue();
    buildStatus();
    buildTopParts();
  }

  buildAll();

  var observer = new MutationObserver(function () { buildAll(); });
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
})();
