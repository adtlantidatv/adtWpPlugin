<?php
require_once('wp-load.php');

$post_id = $argv[1];

update_post_meta($post_id, 'adt_is_converting', '0');
?>