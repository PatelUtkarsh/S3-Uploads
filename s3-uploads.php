<?php

/*
Plugin Name: S3 Uploads
Description: Store uploads in S3
Author: Human Made Limited
Version: 1.0
Author URI: http://hmn.md
*/

$php_version = phpversion();

if ( version_compare( $php_version ,'5.5', '<' ) ) {
	return;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once dirname( __FILE__ ) . '/inc/class-s3-uploads-wp-cli-command.php';
}

add_action( 'plugins_loaded', 's3_uploads_init' );

function s3_uploads_init() {
	if ( ! defined( 'S3_UPLOADS_BUCKET' ) ) {
		return;
	}

	if ( ( ! defined( 'S3_UPLOADS_KEY' ) || ! defined( 'S3_UPLOADS_SECRET' ) ) && ! defined( 'S3_UPLOADS_USE_INSTANCE_PROFILE' ) ) {
		return;
	}

	if ( ! s3_uploads_enabled() ) {
		return;
	}

	$instance = S3_Uploads::get_instance();
	$instance->setup();
}

/**
 * Check if URL rewriting is enabled.
 *
 * Define S3_UPLOADS_AUTOENABLE to false in your wp-config to disable, or use the
 * s3_uploads_enabled option.
 *
 * @return bool
 */
function s3_uploads_enabled() {
	// Make sure the plugin is enabled when autoenable is on
	$constant_autoenable_off = ( defined( 'S3_UPLOADS_AUTOENABLE' ) && false === S3_UPLOADS_AUTOENABLE );

	if ( $constant_autoenable_off && 'enabled' !== get_option( 's3_uploads_enabled' ) ) {                         // If the plugin is not enabled, skip
		return false;
	}

	return true;
}

/**
 * Autoload callback.
 *
 * @param $class_name Name of the class to load.
 */
function s3_uploads_autoload( $class_name ) {
	/*
	 * Load plugin classes:
	 * - Class name: S3_Uploads_Image_Editor_Imagick.
	 * - File name: class-s3-uploads-image-editor-imagick.php.
	 */
	$class_file = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';
	$class_path = dirname( __FILE__ ) . '/inc/' . $class_file;

	if ( file_exists( $class_path ) ) {
		require $class_path;

		return;
	}
}

spl_autoload_register( 's3_uploads_autoload');

// Require AWS Autoloader file.
require_once dirname( __FILE__ ) . '/lib/aws-sdk/aws-autoloader.php';
