/**
 * Beautia — customizer live preview.
 */
(function ($) {
  'use strict';

  wp.customize('blogname', function (value) {
    value.bind(function (to) { $('.brand-text strong').text(to); });
  });
  wp.customize('blogdescription', function (value) {
    value.bind(function (to) { $('.brand-text em').text(to); });
  });

  var vars = {
    beautia_color_accent: '--c-accent',
    beautia_color_accent_2: '--c-accent-2',
    beautia_color_dark: '--c-dark',
    beautia_color_body: '--c-body',
    beautia_color_bg: '--c-bg',
    beautia_color_soft: '--c-soft'
  };
  Object.keys(vars).forEach(function (setting) {
    wp.customize(setting, function (value) {
      value.bind(function (to) {
        if (to) { document.documentElement.style.setProperty(vars[setting], to); }
      });
    });
  });
})(jQuery);
