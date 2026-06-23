<?php
/**
 * Plugin Name: TEFCPT Licensure Page
 * Description: Guided licensure and exam-fee flow for TEF CPT participants. Registers the tefcpt/licensure-page Gutenberg block and enqueues assets on pages using the page-licensure.php template.
 * Version:     0.1.6
 */
defined( 'ABSPATH' ) || exit;

// ── Page template ──────────────────────────────────────────────────────────

add_filter( 'theme_page_templates', function ( $templates ) {
	$templates['page-licensure.php'] = 'TEF-CPT Licensure Page';
	return $templates;
} );

// ── Block registration ─────────────────────────────────────────────────────

add_action( 'init', function () {
	register_block_type( plugin_dir_path( __FILE__ ) . 'gutenberg-block/' );
} );

// ── Asset enqueue (page-scoped) ────────────────────────────────────────────

add_action( 'wp_enqueue_scripts', function () {
	if ( get_page_template_slug() !== 'page-licensure.php' ) {
		return;
	}
	$v = '0.1.6';
	wp_enqueue_style(
		'tefcpt-licensure',
		plugin_dir_url( __FILE__ ) . 'assets/styles.css',
		[],
		$v
	);
	wp_enqueue_script(
		'tefcpt-licensure-flow',
		plugin_dir_url( __FILE__ ) . 'assets/licensure-flow.js',
		[],
		$v,
		true
	);
} );

// ── Body class (used to scope CSS overrides of the theme) ─────────────────

add_filter( 'body_class', function ( $classes ) {
	if ( get_page_template_slug() === 'page-licensure.php' ) {
		$classes[] = 'tefcpt-licensure-page';
	}
	return $classes;
} );

// ── ACF JSON load path ─────────────────────────────────────────────────────

add_filter( 'acf/settings/load_json', function ( $paths ) {
	$paths[] = plugin_dir_path( __FILE__ ) . 'gutenberg-block';
	return $paths;
} );
