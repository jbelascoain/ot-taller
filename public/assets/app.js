(function () {
  'use strict';
  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('mainNav');
  if (toggle && nav) toggle.addEventListener('click', function () { nav.classList.toggle('open'); });

  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (event) {
      if (!window.confirm(el.getAttribute('data-confirm') || '¿Confirmás?')) event.preventDefault();
    });
  });

  document.querySelectorAll('[data-toggle-target]').forEach(function (input) {
    var target = document.querySelector(input.getAttribute('data-toggle-target'));
    if (!target) return;
    var sync = function () {
      var show;
      if (input.type === 'radio') show = input.checked;
      else if (input.type === 'checkbox') show = input.checked;
      else show = input.value === input.getAttribute('data-toggle-value');
      target.classList.toggle('hidden', !show);
      target.querySelectorAll('input, select, textarea, button').forEach(function (el) {
        el.disabled = !show;
      });
      if (show) {
        target.querySelectorAll('[data-toggle-target]').forEach(function (child) {
          child.dispatchEvent(new Event('change'));
        });
      }
    };
    input.addEventListener('change', sync);
    if ((input.type === 'radio' || input.type === 'checkbox') && !input.checked) target.classList.add('hidden');
    else sync();
  });

  var partSelect = document.querySelector('[data-part-select]');
  if (partSelect) {
    partSelect.addEventListener('change', function () {
      var option = partSelect.options[partSelect.selectedIndex];
      if (!option) return;
      var price = option.getAttribute('data-price');
      var desc = option.getAttribute('data-desc');
      var priceInput = document.querySelector('[data-part-price]');
      var descInput = document.querySelector('[data-part-description]');
      if (priceInput && price !== null) priceInput.value = price;
      if (descInput && desc !== null) descInput.value = desc;
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
