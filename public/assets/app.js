(function () {
  'use strict';
  var themeToggle = document.getElementById('themeToggle');
  if (themeToggle) {
    themeToggle.addEventListener('click', function () {
      var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      if (isDark) {
        document.documentElement.removeAttribute('data-theme');
        try { localStorage.setItem('ot-theme', 'light'); } catch (e) {}
      } else {
        document.documentElement.setAttribute('data-theme', 'dark');
        try { localStorage.setItem('ot-theme', 'dark'); } catch (e) {}
      }
    });
  }

  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('mainNav');
  if (toggle && nav) toggle.addEventListener('click', function () { nav.classList.toggle('open'); });

  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (event) {
      if (!window.confirm(el.getAttribute('data-confirm') || '¿Confirmás?')) event.preventDefault();
    });
  });

  // Campos condicionales (radios y checkboxes). Se sincroniza todo el grupo,
  // porque al cambiar de radio solo dispara "change" el que queda seleccionado.
  var toggleInputs = Array.prototype.slice.call(document.querySelectorAll('[data-toggle-target]'));

  function syncToggle(input) {
    var peers = [input];

    if (input.type === 'radio' && input.name) {
      peers = toggleInputs.filter(function (peer) {
        return peer.type === 'radio' && peer.name === input.name && peer.form === input.form;
      });
      if (peers.indexOf(input) === -1) peers.push(input);
    }

    peers.forEach(function (peer) {
      var target = document.querySelector(peer.getAttribute('data-toggle-target'));
      if (!target) return;

      var show;
      if (peer.type === 'radio' || peer.type === 'checkbox') show = peer.checked;
      else show = peer.value === peer.getAttribute('data-toggle-value');

      target.classList.toggle('hidden', !show);
      target.querySelectorAll('input, select, textarea, button').forEach(function (el) {
        el.disabled = !show;
      });

      if (show) {
        target.querySelectorAll('[data-toggle-target]').forEach(function (child) {
          syncToggle(child);
        });
      }
    });
  }

  toggleInputs.forEach(function (input) {
    input.addEventListener('change', function () { syncToggle(input); });
  });
  toggleInputs.forEach(syncToggle);

  // El estado por defecto del ítem depende del modo elegido: un repuesto nuevo
  // todavía está "Presupuestar"; uno del catálogo ya queda "Presupuestado".
  var itemStatusSelect = document.getElementById('item_status');
  if (itemStatusSelect) {
    document.querySelectorAll('[data-item-status]').forEach(function (radio) {
      radio.addEventListener('change', function () {
        if (radio.checked) itemStatusSelect.value = radio.getAttribute('data-item-status');
      });
    });
  }

  // Selects que se envían solos (por ejemplo el estado de la orden).
  document.querySelectorAll('[data-auto-submit]').forEach(function (el) {
    el.addEventListener('change', function () {
      if (el.form) el.form.submit();
    });
  });

  // Selector de repuestos con búsqueda contra el servidor.
  document.querySelectorAll('[data-combo]').forEach(function (combo) {
    var endpoint  = combo.getAttribute('data-endpoint');
    var input     = combo.querySelector('[data-combo-input]');
    var hidden    = combo.querySelector('[data-combo-value]');
    var list      = combo.querySelector('[data-combo-list]');
    var priceEl   = combo.getAttribute('data-price-target')
      ? document.querySelector(combo.getAttribute('data-price-target'))
      : null;
    if (!endpoint || !input || !hidden || !list) return;

    var items = [];
    var activeIndex = -1;
    var timer = null;
    var controller = null;

    function closeList() {
      list.hidden = true;
      list.innerHTML = '';
      activeIndex = -1;
    }

    function highlight(index) {
      var options = list.querySelectorAll('.combo-option');
      if (!options.length) return;
      if (index < 0) index = options.length - 1;
      if (index >= options.length) index = 0;
      activeIndex = index;
      options.forEach(function (option, i) {
        option.classList.toggle('active', i === activeIndex);
      });
      var current = options[activeIndex];
      if (current && current.scrollIntoView) current.scrollIntoView({ block: 'nearest' });
    }

    function pick(item) {
      hidden.value = item.id;
      input.value = item.description + (item.sku ? ' · ' + item.sku : '');
      if (priceEl) priceEl.value = item.price;
      hidden.dispatchEvent(new Event('change'));
      closeList();
    }

    function render(rows) {
      items = rows || [];
      activeIndex = -1;
      list.innerHTML = '';

      if (!items.length) {
        var empty = document.createElement('div');
        empty.className = 'combo-empty';
        empty.textContent = input.value.trim() === ''
          ? 'No hay repuestos cargados.'
          : 'Sin resultados para “' + input.value.trim() + '”.';
        list.appendChild(empty);
        list.hidden = false;
        return;
      }

      items.forEach(function (item, index) {
        var option = document.createElement('button');
        option.type = 'button';
        option.className = 'combo-option';
        option.setAttribute('data-index', String(index));

        var head = document.createElement('span');
        head.className = 'combo-option-head';

        var title = document.createElement('span');
        title.className = 'combo-option-title';
        title.textContent = item.description;

        var price = document.createElement('span');
        price.className = 'combo-option-price';
        price.textContent = item.price_label;

        head.appendChild(title);
        head.appendChild(price);

        var meta = document.createElement('span');
        meta.className = 'combo-option-meta';
        meta.textContent = [item.uid, item.brand, item.sku].filter(Boolean).join(' · ');

        option.appendChild(head);
        option.appendChild(meta);

        option.addEventListener('mousedown', function (event) {
          // Evita que el input pierda el foco antes del click.
          event.preventDefault();
        });
        option.addEventListener('click', function (event) {
          event.preventDefault();
          pick(item);
        });

        list.appendChild(option);
      });

      list.hidden = false;
    }

    function search(term) {
      if (controller) controller.abort();
      controller = window.AbortController ? new AbortController() : null;

      var options = { headers: { Accept: 'application/json' } };
      if (controller) options.signal = controller.signal;

      fetch(endpoint + '?q=' + encodeURIComponent(term), options)
        .then(function (response) {
          if (!response.ok) throw new Error('HTTP ' + response.status);
          return response.json();
        })
        .then(function (data) { render(data.items || []); })
        .catch(function (error) {
          if (error && error.name === 'AbortError') return;
          list.innerHTML = '<div class="combo-empty">No se pudo buscar. Reintentá.</div>';
          list.hidden = false;
        });
    }

    function scheduleSearch() {
      clearTimeout(timer);
      timer = setTimeout(function () { search(input.value.trim()); }, 200);
    }

    input.addEventListener('input', function () {
      hidden.value = '';
      scheduleSearch();
    });
    input.addEventListener('focus', function () {
      clearTimeout(timer);
      search(input.value.trim());
    });
    input.addEventListener('blur', function () {
      setTimeout(closeList, 150);
    });
    input.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowDown') {
        if (list.hidden) { search(input.value.trim()); return; }
        event.preventDefault();
        highlight(activeIndex + 1);
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        highlight(activeIndex - 1);
      } else if (event.key === 'Enter') {
        if (!list.hidden && items.length) {
          event.preventDefault();
          pick(items[activeIndex >= 0 ? activeIndex : 0]);
        }
      } else if (event.key === 'Escape') {
        closeList();
      }
    });

    var form = input.closest('form');
    if (form) {
      form.addEventListener('reset', function () {
        hidden.value = '';
        closeList();
      });
    }
  });

  // Modal para editar un repuesto (ítem) de la orden y sus datos de catálogo.
  var itemModal = document.getElementById('item-edit-modal');
  if (itemModal && typeof itemModal.showModal === 'function') {
    var itemForm = itemModal.querySelector('form');
    var itemFields = {
      description: document.getElementById('edit_item_description'),
      sku: document.getElementById('edit_item_sku'),
      brand: document.getElementById('edit_item_brand'),
      purchasePrice: document.getElementById('edit_item_purchase_price'),
      salePrice: document.getElementById('edit_item_sale_price'),
      stock: document.getElementById('edit_item_stock'),
      minStock: document.getElementById('edit_item_min_stock'),
      supplier: document.getElementById('edit_item_supplier'),
      active: document.getElementById('edit_item_active'),
      notes: document.getElementById('edit_item_notes'),
      quantity: document.getElementById('edit_item_quantity'),
      price: document.getElementById('edit_item_price'),
      status: document.getElementById('edit_item_status')
    };

    document.querySelectorAll('[data-item-edit]').forEach(function (button) {
      button.addEventListener('click', function () {
        var data;
        try {
          data = JSON.parse(button.getAttribute('data-item') || '{}');
        } catch (error) {
          return;
        }

        itemForm.action = data.action || '';
        itemFields.description.value = data.description || '';
        itemFields.sku.value = data.sku || '';
        itemFields.brand.value = data.brand || '';
        itemFields.purchasePrice.value = data.purchase_price || '0';
        itemFields.salePrice.value = data.sale_price || '0';
        itemFields.stock.value = data.stock || '0';
        itemFields.minStock.value = data.min_stock || '0';
        itemFields.supplier.value = data.supplier_id ? String(data.supplier_id) : '';
        itemFields.active.checked = Number(data.active) === 1;
        itemFields.notes.value = data.notes || '';
        itemFields.quantity.value = data.quantity || '1';
        itemFields.price.value = data.unit_price || '';
        itemFields.status.value = data.status || '';
        itemModal.showModal();
      });
    });

    itemModal.querySelectorAll('[data-close-modal]').forEach(function (button) {
      button.addEventListener('click', function () { itemModal.close(); });
    });

    itemModal.addEventListener('click', function (event) {
      if (event.target === itemModal) itemModal.close();
    });
  }

  var board = document.querySelector('[data-board]');
  if (board) {
    var dragged = null;
    board.querySelectorAll('.job[draggable="true"]').forEach(function (card) {
      card.addEventListener('dragstart', function (event) {
        dragged = card;
        event.dataTransfer.setData('text/plain', card.getAttribute('data-id'));
        event.dataTransfer.effectAllowed = 'move';
      });
      card.addEventListener('dragend', function () { dragged = null; });
    });
    board.querySelectorAll('[data-status]').forEach(function (column) {
      column.addEventListener('dragover', function (event) { event.preventDefault(); column.classList.add('drop-target'); });
      column.addEventListener('dragleave', function () { column.classList.remove('drop-target'); });
      column.addEventListener('drop', function (event) {
        event.preventDefault();
        column.classList.remove('drop-target');
        if (!dragged) return;
        var id = dragged.getAttribute('data-id');
        var status = column.getAttribute('data-status');
        var token = document.querySelector('meta[name="csrf-token"]');
        var body = new URLSearchParams();
        body.set('status', status);
        if (token) body.set('csrf_test_name', token.getAttribute('content'));
        fetch('/orders/' + id + '/status', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
          },
          body: body.toString()
        }).then(function (response) {
          if (response.ok) window.location.reload();
          else window.alert('No se pudo actualizar el estado.');
        }).catch(function () { window.alert('No se pudo actualizar el estado.'); });
      });
    });
  }
})();
