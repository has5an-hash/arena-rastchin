<?php
define('DB_NAME', 'beautia_hair');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('WP_HOME', 'http://localhost/beautia/demoes/hair');
define('WP_SITEURL', 'http://localhost/beautia/demoes/hair');
define('WP_ENVIRONMENT_TYPE', 'local');
define('AUTH_KEY', '916a1a5834d408a8ce8afa92c6a39a220ce2da272737e149c4de3097ccb048195a2309d3afe95a5ac8213f7d59fcb75c');
define('SECURE_AUTH_KEY', '882fecbf0901e26a0cb7672a8efec3cc5d813ff6a38af5757fb242119940a4eeeeabc6b17ba450cb199b2870dc6e69b5');
define('LOGGED_IN_KEY', '96656cbbd157520538af21f32387d4bacf950ba45ddfd33a7c7d62269a143e2376950cdf68903189e727bb5f2c3abea6');
define('NONCE_KEY', 'd2cf563155cdecce40b5a2715f57383753c9871efe82cb05324294562dc08ea3e2b2818ad1553210d4a8c5bc0271a532');
define('AUTH_SALT', 'cfed0ad2e63dbb9ec5052eaa8d22bd5c881afed888987017441ade5d89acb8c0c043d2d8ab7ca156da36333ee5bef727');
define('SECURE_AUTH_SALT', 'f797f3dc038e9e6c41d7fd804937b9d00eba352107aa3716ad8df4eb5bfab9632303692464cc26d84e5284f031ab65bc');
define('LOGGED_IN_SALT', 'ef2827c8b1ea526d4899ecbc1128c0c0f1e27adc96bbe1bbcfcc21254be584604aa4d0dcaf439121ba97e6e60b4227d7');
define('NONCE_SALT', '565b2ebf603af1a641181dd913680cb19559b363686fc770c6c2e641226d6945c0322eb9efdd06546158e8064358531a');
define('WP_DEBUG', false);
define('DISALLOW_FILE_EDIT', true);
$table_prefix = 'wp_';
if (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');
require_once ABSPATH . 'wp-settings.php';

