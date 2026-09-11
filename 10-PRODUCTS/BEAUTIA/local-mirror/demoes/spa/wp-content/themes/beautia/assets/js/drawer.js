/**
 * Beautia — floating quick-booking drawer.
 *
 * Uses the same AJAX endpoints as the full wizard, so a slot picked here is
 * a real, validated slot; the "continue" link carries the choice over to the
 * booking page.
 */
(function () {
  'use strict';

  var fab = document.getElementById('beautia-fab');
  var drawer = document.getElementById('beautia-drawer');
  if (!fab || !drawer) { return; }

  function $(s, c) { return (c || document).querySelector(s); }
  function $$(s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); }

  var i18n = (window.BeautiaData && BeautiaData.i18n) || {};
  var panel = $('.drawer-panel', drawer);
  var slotBox = $('#drawer-slots', drawer);
  var serviceSel = $('#drawer-service', drawer);
  var dateInput = $('#drawer-date', drawer);
  var cta = $('#drawer-continue', drawer);
  var lastFocus = null;
  var picked = { time: '', date: '' };

  function open() {
    lastFocus = document.activeElement;
    drawer.hidden = false;
    document.body.classList.add('drawer-open');
    requestAnimationFrame(function () { drawer.classList.add('is-open'); });
    fab.setAttribute('aria-expanded', 'true');
    setTimeout(function () { serviceSel.focus(); }, 220);
    if (!slotBox.dataset.loaded) { loadSlots(); }
  }

  function close() {
    drawer.classList.remove('is-open');
    fab.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('drawer-open');
    setTimeout(function () { drawer.hidden = true; }, 320);
    if (lastFocus) { lastFocus.focus(); }
  }

  function updateCta() {
    var base = cta.dataset.base || cta.getAttribute('href').split('?')[0];
    cta.dataset.base = base;
    var q = ['service=' + encodeURIComponent(serviceSel.value)];
    if (picked.date) { q.push('date=' + encodeURIComponent(picked.date)); }
    if (picked.time) { q.push('time=' + encodeURIComponent(picked.time)); }
    cta.setAttribute('href', base + '?' + q.join('&'));
  }

  function loadSlots() {
    if (!window.Beautia || !Beautia.post) { return; }
    slotBox.dataset.loaded = '1';
    picked.time = '';
    picked.date = dateInput.dataset.iso || dateInput.value;
    slotBox.innerHTML = '<p class="loading">' + (i18n.loading || '…') + '</p>';
    Beautia.post('beautia_get_slots', {
      service_id: serviceSel.value,
      staff_id: 0,
      date: dateInput.dataset.iso || dateInput.value
    }).then(function (res) {
      if (!res.success || !res.data.slots || !res.data.slots.length) {
        slotBox.innerHTML = '<p class="hint">' + (i18n.noSlots || '') + '</p>';
        updateCta();
        return;
      }
      slotBox.innerHTML = '';
      res.data.slots.slice(0, 12).forEach(function (slot) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'slot';
        b.textContent = slot.label;
        b.addEventListener('click', function () {
          $$('.slot', slotBox).forEach(function (x) { x.classList.remove('is-selected'); });
          b.classList.add('is-selected');
          picked.time = slot.time;
          picked.date = dateInput.dataset.iso || dateInput.value;
          updateCta();
        });
        slotBox.appendChild(b);
      });
      updateCta();
    });
  }

  fab.addEventListener('click', function () {
    if (drawer.hidden) { open(); } else { close(); }
  });
  $$('[data-drawer-close]', drawer).forEach(function (el) {
    el.addEventListener('click', close);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !drawer.hidden) { close(); }
    // Simple focus trap while the drawer is open.
    if (e.key === 'Tab' && !drawer.hidden) {
      var f = $$('a[href],button:not([disabled]),select,input,textarea', panel)
        .filter(function (el) { return el.offsetParent !== null; });
      if (!f.length) { return; }
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });

  serviceSel.addEventListener('change', loadSlots);
  dateInput.addEventListener('change', loadSlots);
  dateInput.addEventListener('beautia:datechange', loadSlots);

  // Hide the FAB while the full booking wizard is on screen — no point
  // offering a shortcut to the thing you are already looking at.
  var wizard = document.getElementById('beautia-booking');
  if (wizard && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      fab.classList.toggle('is-hidden', entries[0].isIntersecting);
    }, { threshold: 0.15 }).observe(wizard);
  }
})();
