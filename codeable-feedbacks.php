<?php
/**
 * Plugin Name: Codeable Feedbacks Plugin
 * Plugin URI: https://codeable.io/plugins/codeable-feedbacks
 * Description: Feedbacks Plugin for Codeable Test
 * Version: 1.0.0
 * Author: xuezhe
 * Author URI: https://codeable.io/developer/xuezhe
 * Text Domain: codeable-feedbacks
 * Domain Path: /languages
 * Stable tag: 1.0
*/

// Plugin version.
if ( ! defined( 'CODEABLE_FEEDBACKS_VERSION' ) ) {
	define( 'CODEABLE_FEEDBACKS_VERSION', '1.0.0' );
}

// Plugin Folder Path.
if ( ! defined( 'CODEABLE_FEEDBACKS_PATH' ) ) {
	define( 'CODEABLE_FEEDBACKS_PATH', wp_normalize_path( dirname( __FILE__ ) ) );
}

// Plugin Folder URL.
if ( ! defined( 'CODEABLE_FEEDBACKS_URL' ) ) {
	define( 'CODEABLE_FEEDBACKS_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Register a custom post type named codeable_feedback
 *
 * @since 1.0.0
 * @return void
 */
function codeable_init_feedback() {
	$labels = array(
		'name'               => _x( 'Feedbacks', 'Post type general name', 'codeable' ),
		'singular_name'      => _x( 'Feedback', 'Post type singular name', 'codeable' ),
		'add_new'            => esc_attr__( 'Add Feedback', 'codeable' ),
		'add_new_item'       => esc_attr__( 'Add New Feedback', 'codeable' ),
		'edit_item'          => esc_attr__( 'Edit Feedback', 'codeable' ),
		'new_item'           => esc_attr__( 'New Feedback', 'codeable' ),
		'all_items'          => esc_attr__( 'All Feedbacks', 'codeable' ),
		'view_item'          => esc_attr__( 'View Feedback', 'codeable' ),
		'search_items'       => esc_attr__( 'Search Feedbacks', 'codeable' ),
		'not_found'          => esc_attr__( 'Nothing found', 'codeable' ),
		'not_found_in_trash' => esc_attr__( 'Nothing found in Trash', 'codeable' ),
		'parent_item_colon'  => '',
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => false,
		'can_export'         => true,
		'query_var'          => false,
		'has_archive'        => false,
		'capability_type'    => 'post',
		'map_meta_cap'       => true,
		'hierarchical'       => false,
		'supports'           => array( 'title' ),
	);

	register_post_type( 'codeable_feedback', $args );

}
// init codeable_feedback custom post type.
add_action( 'init', 'codeable_init_feedback' );

/**
 * Find and include all shortcodes within /shortcodes sub folder.
 *
 * @since 1.0.0
 * @return void
 */
function codeable_init_shortcodes() {
	foreach ( glob( plugin_dir_path( __FILE__ ) . '/shortcodes/*.php', GLOB_NOSORT ) as $filename ) {
		require_once wp_normalize_path( $filename );
	}
}
// Load all shortcode elements.
add_action( 'init', 'codeable_init_shortcodes' );

/**
 * Enqueue Styles and Scripts
 *
 * @since 1.0.0
 * @return void
 */
function codeable_enqueue_scripts() {
	wp_enqueue_style( 'codeable_feedbacks_style', CODEABLE_FEEDBACKS_URL . 'css/main.css', array(), CODEABLE_FEEDBACKS_VERSION );

	wp_enqueue_script( 'jquery-pagination', CODEABLE_FEEDBACKS_URL . 'js/jquery.simplePagination.js', array( 'jquery' ), CODEABLE_FEEDBACKS_VERSION, true );
	wp_enqueue_script( 'codeable_feedbacks_script', CODEABLE_FEEDBACKS_URL . 'js/main.js', array( 'jquery', 'jquery-pagination' ), CODEABLE_FEEDBACKS_VERSION, true );

	// localize variables
	wp_localize_script( 'codeable_feedbacks_script', 'codeable_var', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
    ) );

}
// Enqueue plugin dependent styles and scripts
add_action( 'wp_enqueue_scripts', 'codeable_enqueue_scripts' );

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 * @return void
 */
function codeable_load_textdomain() {
// var_dump(determine_locale()); exit;
  load_plugin_textdomain( 'codeable', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
// Make Plugin Translation Ready
add_action( 'plugins_loaded', 'codeable_load_textdomain' );
