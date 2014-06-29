<?php
require_once('wp-load.php');

$post_id = $argv[1];
$source_id = $argv[2];
$filetype = $argv[3];
$source_name = preg_replace('/\.[^.]+$/', '', basename(get_attached_file($source_id)));

$upload_dir = wp_upload_dir();
$upload_dir_path = $upload_dir['path'].'/';
$source_in = get_attached_file($source_id);

$sox_path = wpuf_get_option( 'sox_path', 'adtp_server');
$wav2png_path = wpuf_get_option( 'wav2png_path', 'adtp_server');

// image
// It creates the soundwave image from mp3 or ogg archive using wav2png and sox
// wav2png: https://github.com/beschulz/wav2png
// sox: http://sox.sourceforge.net/
$image_out = $upload_dir_path.$source_name.'.png';

if($filetype == 'audio/ogg'){
	exec($wav2png_path." --width=1170 --height=220 --foreground-color=00000000 --background-color=f1f1f1ff -o ".$image_out." ".$source_in);
}else{
	// if audiofile format is mp3, we use sox as beschulz says here:https://github.com/beschulz/wav2png
	exec($sox_path." ".$source_in." -c 1 -t wav - | /etc/wav2png-master/bin/Linux/wav2png --width=1910 --height=361 --foreground-color=00000000 --background-color=f1f1f1ff -o ".$image_out." /dev/stdin");	
}

// attach image to post
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

?>