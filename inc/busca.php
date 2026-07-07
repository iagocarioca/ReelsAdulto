<?php
/**
 * Sugestões de busca (autocomplete) — vídeos por título.
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Endpoint AJAX público: devolve até 6 vídeos que casam com o termo.
 */
function tikporn_ajax_busca_sugestoes() {
	$termo = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

	if ( mb_strlen( $termo ) < 2 ) {
		wp_send_json_success( array( 'items' => array() ) );
	}

	$q = new WP_Query(
		array(
			'post_type'           => 'video',
			'post_status'         => 'publish',
			'posts_per_page'      => 6,
			's'                   => $termo,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	$items = array();
	foreach ( $q->posts as $p ) {
		$items[] = array(
			'title'  => get_the_title( $p ),
			'url'    => get_permalink( $p ),
			'poster' => tikporn_capa_url( $p->ID, 'tikporn_miniatura' ),
			'views'  => tikporn_numero_k( tikporn_views( $p->ID ) ),
		);
	}

	wp_send_json_success( array( 'items' => $items ) );
}
add_action( 'wp_ajax_tikporn_busca', 'tikporn_ajax_busca_sugestoes' );
add_action( 'wp_ajax_nopriv_tikporn_busca', 'tikporn_ajax_busca_sugestoes' );
