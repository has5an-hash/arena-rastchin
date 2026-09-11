<?php
declare(strict_types=1);
if ($argc !== 2) exit(2);
$folder = preg_replace('/[^a-z]/', '', $argv[1]);
if (!in_array($folder, ['nail','clinic','spa','hair','makeup','lashes','barber'], true)) exit(2);
require dirname(__DIR__).'/demoes/'.$folder.'/wp-load.php';
$id = (int) get_option('page_on_front');
echo json_encode([
    'folder' => $folder,
    'front_id' => $id,
    'template' => get_post_meta($id, '_wp_page_template', true),
    'elementor_mode' => get_post_meta($id, '_elementor_edit_mode', true),
    'content_length' => strlen((string) get_post_field('post_content', $id)),
    'content_prefix' => substr(wp_strip_all_tags((string) get_post_field('post_content', $id)), 0, 120),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).PHP_EOL;
