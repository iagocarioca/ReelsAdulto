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
 *
 * Barato para o banco: busca só no título (search_columns) e guarda o
 * resultado em transient por termo — buscas repetidas não geram query.
 */
function tikporn_ajax_busca_sugestoes() {
	$termo = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	$termo = mb_strtolower( trim( preg_replace( '/\s+/', ' ', $termo ) ) );

	if ( mb_strlen( $termo ) < 2 || mb_strlen( $termo ) > 60 ) {
		wp_send_json_success( array( 'items' => array() ) );
	}

	// Cache curto por termo (10 min) — inclui resultados vazios.
	$chave = 'tp_busca_' . md5( $termo );
	$cache = get_transient( $chave );
	if ( false !== $cache ) {
		wp_send_json_success( array( 'items' => $cache ) );
	}

	$q = new WP_Query(
		array(
			'post_type'           => 'video',
			'post_status'         => 'publish',
			'posts_per_page'      => 6,
			's'                   => $termo,
			'search_columns'      => array( 'post_title' ), // só o título: LIKE bem mais leve.
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

	set_transient( $chave, $items, 10 * MINUTE_IN_SECONDS );

	wp_send_json_success( array( 'items' => $items ) );
}
add_action( 'wp_ajax_tikporn_busca', 'tikporn_ajax_busca_sugestoes' );
add_action( 'wp_ajax_nopriv_tikporn_busca', 'tikporn_ajax_busca_sugestoes' );
