/**
 * Beautia — admin scripts (gateway switcher, test SMS, demo importer).
 */
jQuery(function ($) {
  // Gateway field toggling.
  function toggleGateway() {
    var gw = $('#beautia-gateway').val();
    $('.beautia-gw').hide();
    $('.beautia-gw-' + gw).show();
  }
  if ($('#beautia-gateway').length) {
    toggleGateway();
    $('#beautia-gateway').on('change', toggleGateway);
  }

  // Test SMS.
  $('#beautia-test-sms').on('click', function () {
    var btn = $(this);
    btn.prop('disabled', true);
    $.post(BeautiaAdmin.ajaxUrl, {
      action: 'beautia_test_sms',
      nonce: BeautiaAdmin.nonce,
      mobile: $('#beautia-test-mobile').val()
    }, function (res) {
      btn.prop('disabled', false);
      $('#beautia-test-result')
        .text(res.data && res.data.message ? res.data.message : 'Error')
        .css('color', res.success ? '#1E7A4F' : '#C33355');
    });
  });

  // Demo import.
  $('.beautia-import').on('click', function () {
    var btn = $(this);
    var status = btn.siblings('.beautia-import-status');
    if (!window.confirm('Import this demo? Existing Beautia content with the same titles will be updated.')) { return; }
    btn.prop('disabled', true).text('Importing…');
    status.text('Creating services, team, portfolio, pages and menus…');
    $.post(BeautiaAdmin.ajaxUrl, {
      action: 'beautia_import_demo',
      nonce: BeautiaAdmin.nonce,
      demo: btn.data('demo')
    }, function (res) {
      btn.prop('disabled', false).text('Import this demo');
      if (res.success) {
        status.css('color', '#1E7A4F').text('✓ ' + res.data.message);
      } else {
        status.css('color', '#C33355').text(res.data.message);
      }
    });
  });
});
