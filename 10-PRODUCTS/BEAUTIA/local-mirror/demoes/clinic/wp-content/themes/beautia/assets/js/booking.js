/**
 * Beautia — booking wizard (service → staff → date/time → verify → done).
 */
(function () {
  'use strict';

  var wizard = document.getElementById('beautia-booking');
  if (!wizard) { return; }

  function $(s, c) { return (c || document).querySelector(s); }
  function $$(s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); }

  var i18n = BeautiaData.i18n;
  var requestedDate = new URLSearchParams(window.location.search).get('date') || '';
  var requestedStaff = parseInt(new URLSearchParams(window.location.search).get('staff') || '0',10);
  if (!/^\d{4}-\d{2}-\d{2}$/.test(requestedDate)) { requestedDate = ''; }
  var state = {
    step: 1,
    serviceId: 0,
    serviceName: '',
    servicePrice: '',
    staffId: 0,
    staffName: '',
    date: requestedDate,
    time: '',
    verified: BeautiaData.isLoggedIn,
    mobile: ''
  };

  /* ---------------- steps ---------------- */
  function goStep(n) {
    if (n > state.step && !validate(state.step)) { return; }
    state.step = n;
    $$('.wizard-pane', wizard).forEach(function (p) {
      p.classList.toggle('is-active', parseInt(p.dataset.pane, 10) === n);
    });
    $$('.wizard-steps li', wizard).forEach(function (li) {
      var s = parseInt(li.dataset.step, 10);
      li.classList.toggle('is-active', s === n);
      li.classList.toggle('is-done', s < n);
    });
    window.scrollTo({ top: wizard.getBoundingClientRect().top + window.pageYOffset - 110, behavior: 'smooth' });
    if (n === 2) { loadStaff(); }
    if (n === 3) { buildCalendar(); if (state.date) { loadSlots(); } }
    if (n === 4) { fillSummary(); }
  }

  function validate(step) {
    if (step === 1 && !state.serviceId) { window.alert(i18n.pickService); return false; }
    if (step === 3 && (!state.date || !state.time)) { window.alert(i18n.pickSlot); return false; }
    return true;
  }

  $$('[data-next]', wizard).forEach(function (b) {
    b.addEventListener('click', function () { goStep(parseInt(b.dataset.next, 10)); });
  });
  $$('[data-prev]', wizard).forEach(function (b) {
    b.addEventListener('click', function () { goStep(parseInt(b.dataset.prev, 10)); });
  });

  /* ---------------- step 1: service ---------------- */
  function bindPicks(scope, cb) {
    $$('.pick', scope).forEach(function (pick) {
      pick.addEventListener('click', function () {
        $$('.pick', scope).forEach(function (p) { p.classList.remove('is-selected'); });
        pick.classList.add('is-selected');
        var input = $('input', pick);
        if (input) { input.checked = true; }
        cb(pick);
      });
    });
  }

  bindPicks($('.service-picker', wizard), function (pick) {
    state.serviceId = parseInt($('input', pick).value, 10);
    state.serviceName = $('.pick-body strong', pick).textContent;
    state.servicePrice = $('.pick-body em', pick).textContent;
    state.staffId = 0;
    state.time = '';
  });

  // pre-selected service from ?service=
  var preselected = $('.pick.is-selected .pick-body strong', wizard);
  if (preselected) {
    var input = $('.pick.is-selected input', wizard);
    state.serviceId = parseInt(input.value, 10);
    state.serviceName = preselected.textContent;
    state.servicePrice = $('.pick.is-selected .pick-body em', wizard).textContent;
  }

  /* ---------------- step 2: staff ---------------- */
  function loadStaff() {
    var list = $('#beautia-staff-list');
    if (!list || list.dataset.for === String(state.serviceId)) { return; }
    list.dataset.for = String(state.serviceId);
    var keep = list.firstElementChild.outerHTML;
    list.innerHTML = '<p class="hint">' + i18n.loading + '</p>';

    Beautia.post('beautia_service_staff', { service_id: state.serviceId }).then(function (res) {
      list.innerHTML = keep;
      if (res.success) {
        res.data.staff.forEach(function (s) {
          var label = document.createElement('label');
          label.className = 'pick';
          label.innerHTML =
            '<input type="radio" name="staff_id" value="' + s.id + '" />' +
            '<span class="pick-ico">' + (s.avatar ? '<img src="' + s.avatar + '" alt="" style="width:100%;height:100%;border-radius:14px;object-fit:cover" />' : '') + '</span>' +
            '<span class="pick-body"><strong>' + s.name + '</strong><em>' + (s.role || '') + '</em></span>' +
            '<span class="pick-check">✓</span>';
          list.appendChild(label);
        });
      }
      bindPicks(list, function (pick) {
        state.staffId = parseInt($('input', pick).value, 10);
        state.staffName = $('.pick-body strong', pick).textContent;
        state.time = '';
      });
      if(requestedStaff){
        var preferred=list.querySelector('input[value="'+requestedStaff+'"]');
        if(preferred)preferred.closest('.pick').click();
        requestedStaff=0;
      }
    });
  }

  /* ---------------- Jalali (Shamsi) calendar helpers ---------------- */
  var JAL = {
    on: (BeautiaData.calendar === 'jalali'),
    months: ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'],
    dows: ['ش','ی','د','س','چ','پ','ج'],
    fa: function (n) {
      return String(n).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[d]; });
    },
    div: function (a, b) { return Math.floor(a / b); },
    g2j: function (gy, gm, gd) {
      var g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
      var gy2 = (gm > 2) ? (gy + 1) : gy;
      var days = 355666 + (365 * gy) + JAL.div(gy2 + 3, 4) - JAL.div(gy2 + 99, 100) +
        JAL.div(gy2 + 399, 400) + gd + g_d_m[gm - 1];
      var jy = -1595 + (33 * JAL.div(days, 12053));
      days %= 12053;
      jy += 4 * JAL.div(days, 1461);
      days %= 1461;
      if (days > 365) { jy += JAL.div(days - 1, 365); days = (days - 1) % 365; }
      var jm, jd;
      if (days < 186) { jm = 1 + JAL.div(days, 31); jd = 1 + (days % 31); }
      else { jm = 7 + JAL.div(days - 186, 30); jd = 1 + ((days - 186) % 30); }
      return [jy, jm, jd];
    },
    j2g: function (jy, jm, jd) {
      jy += 1595;
      var days = -355668 + (365 * jy) + (JAL.div(jy, 33) * 8) + JAL.div((jy % 33) + 3, 4) + jd +
        ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
      var gy = 400 * JAL.div(days, 146097);
      days %= 146097;
      if (days > 36524) {
        gy += 100 * JAL.div(--days, 36524);
        days %= 36524;
        if (days >= 365) { days++; }
      }
      gy += 4 * JAL.div(days, 1461);
      days %= 1461;
      if (days > 365) { gy += JAL.div(days - 1, 365); days = (days - 1) % 365; }
      var gd = days + 1;
      var sal_a = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28,
        31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
      var gm = 0;
      for (gm = 1; gm <= 12 && gd > sal_a[gm]; gm++) { gd -= sal_a[gm]; }
      return [gy, gm, gd];
    },
    monthLength: function (jy, jm) {
      if (jm <= 6) { return 31; }
      if (jm <= 11) { return 30; }
      // Esfand: 29 or 30 depending on the leap year.
      var g = JAL.j2g(jy + 1, 1, 1);
      var prev = new Date(g[0], g[1] - 1, g[2]);
      prev.setDate(prev.getDate() - 1);
      return JAL.g2j(prev.getFullYear(), prev.getMonth() + 1, prev.getDate())[2];
    }
  };

  /* ---------------- step 3: calendar + slots ---------------- */
  var view = new Date();
  view.setDate(1);
  // In Jalali mode the view anchors on the first day of the current Shamsi month.
  if (BeautiaData.calendar === 'jalali') {
    view = new Date();
  }
  if (requestedDate) { view = new Date(requestedDate + 'T12:00:00'); }

  function pad(n) { return n < 10 ? '0' + n : '' + n; }
  function ymd(dt) { return dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate()); }

  function buildCalendar() {
    var dp = $('#beautia-datepicker');
    if (!dp) { return; }
    var title = $('.dp-title', dp);
    var days = $('.dp-days', dp);
    days.innerHTML = '';

    var today = new Date(); today.setHours(0, 0, 0, 0);
    var cells = [];        // { date: Date, label: string }
    var offset = 0;
    var start = (typeof BeautiaData.weekStart === 'number') ? BeautiaData.weekStart : 0;
    var dows;

    if (JAL.on) {
      start = 6; // Persian weeks start on Saturday.
      dows = JAL.dows;
      var jv = JAL.g2j(view.getFullYear(), view.getMonth() + 1, 1);
      var jy = jv[0], jm = jv[1];
      title.textContent = JAL.months[jm - 1] + ' ' + JAL.fa(jy);
      var g1 = JAL.j2g(jy, jm, 1);
      var first = new Date(g1[0], g1[1] - 1, g1[2]);
      offset = (first.getDay() - start + 7) % 7;
      var len = JAL.monthLength(jy, jm);
      for (var jd = 1; jd <= len; jd++) {
        var gg = JAL.j2g(jy, jm, jd);
        cells.push({ date: new Date(gg[0], gg[1] - 1, gg[2]), label: JAL.fa(jd) });
      }
    } else {
      dows = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
      var fmt = new Intl.DateTimeFormat(document.documentElement.lang || 'en', { month: 'long', year: 'numeric' });
      title.textContent = fmt.format(view);
      var gfirst = new Date(view.getFullYear(), view.getMonth(), 1);
      offset = (gfirst.getDay() - start + 7) % 7;
      var total = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();
      for (var dnum = 1; dnum <= total; dnum++) {
        cells.push({ date: new Date(view.getFullYear(), view.getMonth(), dnum), label: dnum });
      }
    }

    for (var i = 0; i < 7; i++) {
      var head = document.createElement('div');
      head.className = 'dp-dow';
      head.textContent = dows[JAL.on ? i : ((i + start) % 7)];
      days.appendChild(head);
    }
    for (var o = 0; o < offset; o++) { days.appendChild(document.createElement('span')); }

    cells.forEach(function (cell) {
      var dt = cell.date;
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'dp-day';
      btn.textContent = cell.label;
      if (dt < today) { btn.disabled = true; }
      if (dt.getTime() === today.getTime()) { btn.classList.add('is-today'); }
      if (ymd(dt) === state.date) { btn.classList.add('is-selected'); }
      btn.addEventListener('click', function () {
        $$('.dp-day', days).forEach(function (b) { b.classList.remove('is-selected'); });
        btn.classList.add('is-selected');
        state.date = ymd(dt);
        state.time = '';
        loadSlots();
      });
      days.appendChild(btn);
    });

    function shift(dir) {
      if (JAL.on) {
        var c = JAL.g2j(view.getFullYear(), view.getMonth() + 1, view.getDate());
        var y = c[0], m = c[1] + dir;
        if (m < 1) { m = 12; y--; }
        if (m > 12) { m = 1; y++; }
        var g = JAL.j2g(y, m, 1);
        view = new Date(g[0], g[1] - 1, g[2]);
      } else {
        view.setDate(1);
        view.setMonth(view.getMonth() + dir);
      }
      buildCalendar();
    }
    $('.dp-prev', dp).onclick = function () { shift(-1); };
    $('.dp-next', dp).onclick = function () { shift(1); };
  }


  /* ---------------- waiting list (shown when a day is full) ---------- */
  function renderWaitlist(box) {
    var wrap = document.createElement('div');
    wrap.className = 'waitlist';
    wrap.innerHTML =
      '<p class="waitlist-lead">' + (i18n.waitlistLead || '') + '</p>' +
      '<div class="waitlist-row">' +
      '<input type="tel" class="waitlist-mobile" placeholder="' + (i18n.mobilePlaceholder || '09120000000') + '" />' +
      '<button type="button" class="btn btn-outline waitlist-join"><span>' + (i18n.waitlistJoin || '') + '</span></button>' +
      '</div><div class="msg waitlist-msg" role="status"></div>';
    box.appendChild(wrap);

    var btn = wrap.querySelector('.waitlist-join');
    var input = wrap.querySelector('.waitlist-mobile');
    var msg = wrap.querySelector('.waitlist-msg');
    btn.addEventListener('click', function () {
      var mobile = Beautia.toEnDigits(input.value.trim());
      if (!mobile) { Beautia.msg(msg, i18n.genericError, 'error'); return; }
      btn.disabled = true;
      Beautia.post('beautia_waitlist', {
        mobile: mobile,
        date: state.date,
        service_id: state.serviceId,
        name: (document.getElementById('bk-name') || {}).value || ''
      }).then(function (res) {
        btn.disabled = false;
        Beautia.msg(msg, res.data.message, res.success ? 'ok' : 'error');
        if (res.success) { input.value = ''; }
      });
    });
  }

  function loadSlots() {
    var box = $('#beautia-slots');
    box.innerHTML = '<p class="loading">' + i18n.loading + '</p>';
    Beautia.post('beautia_get_slots', {
      service_id: state.serviceId,
      staff_id: state.staffId,
      date: state.date
    }).then(function (res) {
      if (!res.success || !res.data.slots.length) {
        box.innerHTML = '<p class="hint">' + i18n.noSlots + '</p>';
        renderWaitlist(box);
        return;
      }
      box.innerHTML = '';
      res.data.slots.forEach(function (slot) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'slot';
        b.textContent = slot.label;
        b.addEventListener('click', function () {
          $$('.slot', box).forEach(function (x) { x.classList.remove('is-selected'); });
          b.classList.add('is-selected');
          state.time = slot.time;
        });
        box.appendChild(b);
      });
    });
  }

  /* ---------------- step 4: summary + OTP + submit ---------------- */
  function fillSummary() {
    var map = {
      service: state.serviceName || '—',
      staff: state.staffName || wizard.dataset.anyStaff || 'اولین متخصص آزاد',
      date: state.date ? new Intl.DateTimeFormat('fa-IR', {year:'numeric',month:'long',day:'numeric'}).format(new Date(state.date + 'T12:00:00')) : '—',
      time: state.time ? JAL.fa(state.time) : '—',
      price: state.servicePrice || '—'
    };
    Object.keys(map).forEach(function (k) {
      var el = $('[data-sum="' + k + '"]', wizard);
      if (el) { el.textContent = map[k]; }
    });
  }

  var otpWrap = $('#bk-otp');
  if (otpWrap) {
    var digits = $('.otp-digits', otpWrap);
    var sendBtn = $('#bk-send-code');
    var verifyBtn = $('#bk-verify-code');
    var resendBtn = $('#bk-resend');
    var inputsBox = $('.otp-inputs', otpWrap);

    function requestCode() {
      var mobile = $('#bk-mobile').value;
      if (!mobile) { Beautia.msg($('#bk-msg'), i18n.genericError, 'error'); return; }
      Beautia.post('beautia_otp_request', { mobile: mobile }).then(function (res) {
        if (!res.success) { Beautia.msg($('#bk-msg'), res.data.message, 'error'); return; }
        state.mobile = mobile;
        sendBtn.hidden = true;
        inputsBox.hidden = false;
        var hint = $('.otp-hint', otpWrap);
        var phone = document.createElement('bdi');
        phone.dir = 'ltr';
        phone.textContent = JAL.fa(res.data.mobile);
        hint.replaceChildren(document.createTextNode(i18n.otpSent + ' '), phone);
        Beautia.otpInputs(digits, function () { verify(); });
        Beautia.countdown(resendBtn, res.data.cooldown, i18n.resendIn, i18n.resend);
        Beautia.msg($('#bk-msg'), i18n.otpSent, 'ok');
      });
    }

    function verify() {
      Beautia.post('beautia_otp_verify', {
        mobile: state.mobile,
        code: digits.getCode(),
        first_name: $('#bk-name').value
      }).then(function (res) {
        if (!res.success) { Beautia.msg($('#bk-msg'), res.data.message, 'error'); digits.clear(); return; }
        state.verified = true;
        otpWrap.innerHTML = '<p class="msg is-ok">✓ ' + res.data.message + '</p>';
        Beautia.msg($('#bk-msg'), '', 'ok');
      });
    }

    sendBtn.addEventListener('click', requestCode);
    verifyBtn.addEventListener('click', verify);
    resendBtn.addEventListener('click', requestCode);
  }

  var submit = $('#bk-submit');
  if (submit) {
    submit.addEventListener('click', function () {
      var name = $('#bk-name').value.trim();
      var mobile = Beautia.toEnDigits($('#bk-mobile').value.trim());
      if (!name) { Beautia.msg($('#bk-msg'), i18n.genericError, 'error'); return; }
      if (wizard.dataset.requireLogin === '1' && !state.verified) {
        Beautia.msg($('#bk-msg'), i18n.otpSent, 'error');
        return;
      }
      submit.disabled = true;
      Beautia.post('beautia_create_booking', {
        service_id: state.serviceId,
        staff_id: state.staffId,
        date: state.date,
        time: state.time,
        name: name,
        mobile: mobile,
        note: $('#bk-note').value
      }).then(function (res) {
        submit.disabled = false;
        if (!res.success) { Beautia.msg($('#bk-msg'), res.data.message, 'error'); return; }
        $('#bk-code').textContent = res.data.code;
        $('#bk-done-text').textContent = res.data.message;
        goStep(5);
      });
    });
  }
})();
