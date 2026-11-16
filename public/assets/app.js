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
    var emptyLabel = combo.getAttribute('data-combo-empty') || 'No hay repuestos cargados.';
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
      input.value = item.input_label || item.label || item.description || '';
      if (priceEl && item.price !== undefined && item.price !== null) priceEl.value = item.price;
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
          ? emptyLabel
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
        title.textContent = item.label || item.description || '';

        head.appendChild(title);

        if (item.price_label) {
          var price = document.createElement('span');
          price.className = 'combo-option-price';
          price.textContent = item.price_label;
          head.appendChild(price);
        }

        var metaText = item.meta || [item.uid, item.brand, item.sku].filter(Boolean).join(' · ');

        option.appendChild(head);

        if (metaText) {
          var meta = document.createElement('span');
          meta.className = 'combo-option-meta';
          meta.textContent = metaText;
          option.appendChild(meta);
        }

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
      supplier: document.getElementById('edit_item_supplier'),
      active: document.getElementById('edit_item_active'),
      notes: document.getElementById('edit_item_notes'),
      quantity: document.getElementById('edit_item_quantity'),
      price: document.getElementById('edit_item_price'),
      status: document.getElementById('edit_item_status')
    };

    var newSupplierToggle = document.getElementById('edit_item_new_supplier');
    var newSupplierName = document.getElementById('edit_new_supplier_name');
    var newSupplierPhone = document.getElementById('edit_new_supplier_phone');

    if (newSupplierToggle) {
      newSupplierToggle.addEventListener('change', function () {
        if (newSupplierToggle.checked) {
          itemFields.supplier.value = '';
          itemFields.supplier.disabled = true;
        } else {
          itemFields.supplier.disabled = false;
        }
      });
    }

    document.querySelectorAll('[data-item-edit]').forEach(function (button) {
      button.addEventListener('click', function () {
        var data;
        try {
          data = JSON.parse(button.getAttribute('data-item') || '{}');
        } catch (error) {
          return;
        }

        itemForm.action = data.action || '';

        if (newSupplierToggle) {
          newSupplierToggle.checked = false;
          newSupplierToggle.dispatchEvent(new Event('change'));
        }

        if (newSupplierName) newSupplierName.value = '';
        if (newSupplierPhone) newSupplierPhone.value = '';

        itemFields.description.value = data.description || '';
        itemFields.sku.value = data.sku || '';
        itemFields.brand.value = data.brand || '';
        itemFields.purchasePrice.value = data.purchase_price || '0';
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

  // Filtro de texto del tablero (funciona sobre las tarjetas ya cargadas).
  var boardSearch = document.querySelector('[data-board-search]');
  if (boardSearch) {
    var searchBoard = document.querySelector('[data-board]');
    var searchStatus = document.querySelector('[data-board-search-status]');

    var normalizeText = function (text) {
      return String(text || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
    };

    var applyBoardSearch = function () {
      var term = normalizeText(boardSearch.value.trim());
      var totalVisible = 0;
      var results = [];

      searchBoard.querySelectorAll('.column').forEach(function (column) {
        var jobs = column.querySelectorAll('.job');
        var visible = 0;

        jobs.forEach(function (job) {
          var matches = term === '' || normalizeText(job.textContent).indexOf(term) !== -1;
          job.classList.toggle('hidden', !matches);
          if (matches) visible++;
        });

        var count = column.querySelector('.count');
        if (count) count.textContent = visible;

        var empty = column.querySelector('.column-empty');
        if (empty) {
          if (visible === 0) {
            empty.hidden = false;
            empty.textContent = jobs.length === 0 || term === '' ? 'Sin órdenes' : 'Sin resultados';
          } else {
            empty.hidden = true;
          }
        }

        totalVisible += visible;
        results.push({ column: column, visible: visible });
      });

      // Al buscar, ocultamos las columnas sin coincidencias para que los
      // resultados queden a la vista sin tener que scrollear el tablero.
      var hideEmpty = term !== '' && totalVisible > 0;
      results.forEach(function (entry) {
        entry.column.classList.toggle('hidden', hideEmpty && entry.visible === 0);
      });

      if (searchStatus) {
        searchStatus.textContent = term === ''
          ? ''
          : totalVisible + (totalVisible === 1 ? ' coincidencia' : ' coincidencias');
      }
    };

    boardSearch.addEventListener('input', applyBoardSearch);
    applyBoardSearch();
  }

  // Compartir el PDF de la orden junto con el mensaje de WhatsApp.
  document.querySelectorAll('[data-share-pdf]').forEach(function (button) {
    var pdfUrl  = button.getAttribute('data-pdf-url');
    var waUrl   = button.getAttribute('href');
    var filename = button.getAttribute('data-filename') || 'orden.pdf';
    if (!pdfUrl || !waUrl) return;

    var message = '';
    try { message = new URL(waUrl).searchParams.get('text') || ''; } catch (error) {}

    var filePromise = null;

    function loadFile() {
      if (!filePromise) {
        filePromise = fetch(pdfUrl, { credentials: 'same-origin' })
          .then(function (response) {
            var type = response.headers.get('content-type') || '';
            if (!response.ok || type.indexOf('application/pdf') === -1) {
              throw new Error('No se pudo generar el PDF');
            }
            return response.blob();
          })
          .then(function (blob) {
            return new File([blob], filename, { type: 'application/pdf' });
          });

        filePromise.catch(function () { filePromise = null; });
      }

      return filePromise;
    }

    function canShareFile(file) {
      return !!(navigator.canShare && navigator.canShare({ files: [file] }));
    }

    // Precargar el PDF para que el compartir sea inmediato (especialmente iOS).
    if (navigator.share && navigator.canShare) {
      loadFile().catch(function () {});
    }

    button.addEventListener('click', function (event) {
      if (!navigator.share || !navigator.canShare) {
        return;
      }

      event.preventDefault();

      loadFile()
        .then(function (file) {
          if (!canShareFile(file)) throw new Error('Sin soporte para compartir archivos');
          return navigator.share({ files: [file], text: message, title: filename });
        })
        .catch(function (error) {
          if (error && error.name === 'AbortError') return;

          var download = document.createElement('a');
          download.href = pdfUrl + (pdfUrl.indexOf('?') === -1 ? '?' : '&') + 'download=1';
          download.download = filename;
          document.body.appendChild(download);
          download.click();
          download.remove();

          var opened = window.open(waUrl, '_blank', 'noopener');
          if (!opened) window.location.href = waUrl;
        });
    });
  });

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
