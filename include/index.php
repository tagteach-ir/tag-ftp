<?php

if ( ! defined( 'ABSPATH' ) ) {
    header('Location: /');
	exit;
}

class TAG_FTP {

	protected $loader;

	protected $plugin_name;

	protected $version;

	public function __construct() {
		if ( defined( 'TAG_FTP_VERSION' ) ) {
			$this->version = TAG_FTP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'tag-ftp';

		$this->load_dependencies();
		$this->define_admin_hooks();

	}

	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'include/loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'include/admin.php';

		$this->loader = new TAG_FTP_Loader();

	}

	private function define_admin_hooks() {

		$plugin_admin = new TAG_FTP_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'cmb2_admin_init',$plugin_admin, 'tag_ftp_cmb2_fields');
		$this->loader->add_action( 'admin_init', $plugin_admin, 'tag_ftp_connection_info');
		$this->loader->add_action( 'wp_generate_attachment_metadata', $plugin_admin, 'tag_ftp_upload');
		$this->loader->add_action( 'delete_attachment', $plugin_admin, 'tag_ftp_delete');

	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

}
