<?php

if ( ! defined( 'ABSPATH' ) ) {
    header('Location: /');
	exit;
}

class TAG_FTP_Admin {

	private $plugin_name;

	private $version;

	public $upload_dir;
	public $upload_url;
	public $upload_yrm;
	public $settings;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/admin.css', array(), $this->version, 'all' );

	}

	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/admin.js', array( 'jquery' ), $this->version, false );

	}

	public function tag_ftp_cmb2_fields() {

		$cmb_options = new_cmb2_box(array(
			'id' => 'tf_options',
			'title' => 'TAG FTP',
			'object_types' => array('options-page'),
			'option_key' => 'tf_options',
			'menu_title' => 'TAG FTP',
			'parent_slug' => 'options-general.php'
		));
		$cmb_options->add_field(array(
			'name' => 'آدرس هاست FTP',
			'desc' => 'لطفا آدرس سرور یا آیپی سرور دانلود خود را وارد کنید.',
			'id' => 'tf_hostname',
			'type' => 'text',
			'attributes' => array(
				'placeholder' => 'cpanel.tagteach.ir یا 1.1.1.1'
			)
		));
		$cmb_options->add_field(array(
			'name' => 'پورت FTP',
			'desc' => 'پورت سرور FTP را وارد کنید،معمولا این پورت 21 است و نیازی به تغییر آن نیست!',
			'id' => 'tf_port',
			'type' => 'text',
			'default' => '21',
			'attributes' => array(
				'type' => 'number',
				'maxlenth' => '2',
				'pattern' => '\d*'
			)
		));
		$cmb_options->add_field(array(
			'name' => 'نام کاربری FTP',
			'desc' => 'این نام کاربری برای اتصال به سرور FTP استفاده میشود.',
			'id' => 'tf_username',
			'type' => 'text'
		));
		$cmb_options->add_field(array(
			'name' => 'رمزعبور FTP',
			'desc' => 'این رمزعبور برای اتصال به سرور FTP استفاده میشود.',
			'id' => 'tf_password',
			'type' => 'text',
			'attributes' => array(
				'type' => 'password'
			)
		));
		$cmb_options->add_field(array(
			'name' => 'آدرس هاست دانلود FTP',
			'desc' => 'ساب دامنه یا دامنه ای که به هاست دانلود خود متصل کردید را وارد کنید.',
			'id' => 'tf_cdn',
			'type' => 'text',
			'attributes' => array(
				'placeholder' => 'مثلا https://dl.tagteach.ir'
			)
		));

	}

	public function tag_ftp_connection_info(){
		
		$this->upload_dir = wp_upload_dir();
		$this->upload_url = get_option('upload_url_path');
		$this->upload_yrm = get_option('uploads_use_yearmonth_folders');

		$this->settings = array(
			'host'	  =>	cmb2_get_option('tf_options','tf_hostname'),
			'port'    =>  cmb2_get_option('tf_options','tf_port'),
			'user'	  =>	cmb2_get_option('tf_options','tf_username'),
			'pass'	  =>	cmb2_get_option('tf_options','tf_password'),
			'cdn'     =>  cmb2_get_option('tf_options','tf_cdn'),
			'path'	  =>	"/",
			'base'	  =>  wp_upload_dir()['basedir']
		);

		update_option( 'upload_url_path', esc_url( $this->settings['cdn'] ) );

	}

	public function tag_ftp_upload( $args ) {

		$connection = ftp_connect( $this->settings['host'], $this->settings['port'] );

		$login = ftp_login( $connection, $this->settings['user'], $this->settings['pass'] );

		ftp_pasv($connection, true);

		if ( !$connection || !$login ) {
				die('اتصال ناموفق بود،لطفا مشخصات وارد شده را بررسی کنید');
		}


		function ftp_putAll($conn_id, $src_dir, $dst_dir, $created) {
							$d = dir($src_dir);
				while($file = $d->read()) {
						if ($file != "." && $file != "..") {
								if (is_dir($src_dir."/".$file)) {
										if (!@ftp_chdir($conn_id, $dst_dir."/".$file)) {
												ftp_mkdir($conn_id, $dst_dir."/".$file);
										}
										$created  = ftp_putAll($conn_id, $src_dir."/".$file, $dst_dir."/".$file, $created);
								} else {
										$upload = ftp_put($conn_id, $dst_dir."/".$file, $src_dir."/".$file, FTP_BINARY);
										if($upload)
											$created[] = $src_dir."/".$file;
								}
						}
				}
				$d->close();
				return $created;
		}

		$delete = ftp_putAll($connection, $this->settings['base'], $this->settings['path'], array());
		


		foreach ( $delete as $file ) {
			unlink( $file );
		}
		
		return $args;
	}

	public function tag_ftp_delete( $args ){

		$connection = ftp_connect( $this->settings['host'], $this->settings['port'] );
		$login = ftp_login( $connection, $this->settings['user'], $this->settings['pass'] );
		ftp_pasv($connection, true);
		if ( !$connection || !$login ) {
			die('اتصال ناموفق بود،لطفا مشخصات وارد شده را بررسی کنید');
		};

		if( !empty($this->upload_yrm) ) {
			$file_year = substr(wp_get_attachment_metadata($args)['file'],0,8);
			$file = array(
				'original' => str_replace($this->settings['cdn'].'/',"",wp_get_attachment_url($args)),
				'thumb' => $file_year.wp_get_attachment_metadata($args)['sizes']['thumbnail']['file'],
				'medium' => $file_year.wp_get_attachment_metadata($args)['sizes']['medium']['file'],
				'mdium_large' => $file_year.wp_get_attachment_metadata($args)['sizes']['medium_large']['file'],
				'large' => $file_year.wp_get_attachment_metadata($args)['sizes']['large']['file'],
				'post' => $file_year.wp_get_attachment_metadata($args)['sizes']['post-thumbnail']['file']
			);
		} else {
			$file = array(
				'original' => str_replace($this->settings['cdn'].'/',"",wp_get_attachment_url($args)),
				'thumb' => 	wp_get_attachment_metadata($args)['sizes']['thumbnail']['file'],
				'medium' => wp_get_attachment_metadata($args)['sizes']['medium']['file'],
				'mdium_large' => wp_get_attachment_metadata($args)['sizes']['medium_large']['file'],
				'large' => wp_get_attachment_metadata($args)['sizes']['large']['file'],
				'post' => wp_get_attachment_metadata($args)['sizes']['post-thumbnail']['file']
			);
		};

		foreach ($file as $path) {
			ftp_delete($connection, $path);
		}
		
		ftp_close($connection);
	}
}
