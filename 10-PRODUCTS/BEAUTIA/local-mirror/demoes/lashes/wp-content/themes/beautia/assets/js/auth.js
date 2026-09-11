/**
 * Beautia — mobile + OTP authentication on the login page.
 */
(function () {
  'use strict';

  var box = document.getElementById('beautia-auth');
  if (!box) { return; }

  function $(s, c) { return (c || document).querySelector(s); }

  var i18n = BeautiaData.i18n;
  var stepMobile = $('[data-auth-step="mobile"]', box);
  var stepCode = $('[data-auth-step="code"]', box);
  var digits = $('.otp-digits', box);
  var msg = $('#auth-msg');
  var mobile = '';

  function send() {
    mobile = Beautia.toEnDigits($('#auth-mobile').value.trim());
    if (!mobile) { Beautia.msg(msg, i18n.genericError, 'error'); return; }
    var btn = $('#auth-send');
    btn.disabled = true;
    Beautia.post('beautia_otp_request', { mobile: mobile }).then(function (res) {
      btn.disabled = false;
      if (!res.success) { Beautia.msg(msg, res.data.message, 'error'); return; }
      stepMobile.hidden = true;
      stepCode.hidden = false;
      $('.auth-sent', box).textContent = i18n.otpSent + ' ' + res.data.mobile;
      if (res.data.isNew) { $('.auth-name', box).hidden = false; }
      Beautia.otpInputs(digits, verify);
      Beautia.countdown($('#auth-resend'), res.data.cooldown, i18n.resendIn, i18n.resend);
      Beautia.msg(msg, i18n.otpSent, 'ok');
    });
  }

  function verify() {
    var nameField = $('#auth-name');
    Beautia.post('beautia_otp_verify', {
      mobile: mobile,
      code: digits.getCode(),
      first_name: nameField ? nameField.value : ''
    }).then(function (res) {
      if (!res.success) { Beautia.msg(msg, res.data.message, 'error'); digits.clear(); return; }
      Beautia.msg(msg, res.data.message, 'ok');
      window.location = res.data.redirect;
    });
  }

  $('#auth-send').addEventListener('click', send);
  $('#auth-verify').addEventListener('click', verify);
  $('#auth-resend').addEventListener('click', send);
  $('#auth-change').addEventListener('click', function () {
    stepCode.hidden = true;
    stepMobile.hidden = false;
    Beautia.msg(msg, '', 'ok');
  });
  $('#auth-mobile').addEventListener('keyup', function (e) { if (e.key === 'Enter') { send(); } });
})();
