<?php

/**
 * Plugin Name:       TAG FTP
 * Description:       اتصال وردپرس به هاست دانلود
 * Version:           1.0.0
 * Author:			  تگ تچ
 * Author URI: 		  https://tagteach.ir/blog/tag-ftp/
 * Text Domain:       tag-ftp
 */

if ( ! defined( 'ABSPATH' ) ) {
    header('Location: /');
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'cmb2/init.php';

define( 'TAG_FTP_VERSION', '1.0.0' );

require plugin_dir_path( __FILE__ ) . 'includes/index.php';

function run_tag_ftp() {

	$plugin = new TAG_Ftp();
	$plugin->run();

}
run_tag_ftp();
