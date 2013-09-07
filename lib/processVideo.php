<?php
require_once('wp-load.php');

$post_id = $argv[1];
$source_id = $argv[2];
$source_name = preg_replace('/\.[^.]+$/', '', basename(get_attached_file($source_id)));

$upload_dir = wp_upload_dir();
$upload_dir_path = $upload_dir['path'].'/';
$source_in = get_attached_file($source_id);


// image
$image_out = $upload_dir_path.$source_name.'.jpg';

exec("/usr/bin/ffmpeg -y -ss 00:00:00.435 -i ".$source_in." -qscale 1 -f mjpeg -vframes 1 ".$image_out);

$wp_filetype = wp_check_filetype(basename($image_out), null );
$attachment_img = array(
	'guid' => $upload_dir['url'] . '/' . basename( $image_out ), 
	'post_mime_type' => $wp_filetype['type'],
	'post_title' => $source_name,
	'post_content' => '',
	'post_status' => 'inherit'
);
$attach_img_id = wp_insert_attachment( $attachment_img, $image_out, $post_id);

require_once('wp-admin/includes/image.php');
$attach_data = wp_generate_attachment_metadata( $attach_img_id, $image_out );
wp_update_attachment_metadata( $attach_img_id, $attach_data );

set_post_thumbnail( $post_id, $attach_img_id );


// video
$video_out = $upload_dir_path.$source_name.'.webm';

exec("/usr/bin/ffmpeg -i ".$source_in." -vcodec libvpx -b 1M -acodec libvorbis ".$video_out);

$wp_filetype = wp_check_filetype(basename($video_out), null );
$attachment_video= array(
	'guid' => $upload_dir['url'] . '/' . basename( $video_out ), 
	'post_mime_type' => $wp_filetype['type'],
	'post_title' => $source_name,
	'post_content' => '',
	'post_status' => 'inherit'
);
$attach_video_id = wp_insert_attachment( $attachment_video, $video_out, $post_id);

?>