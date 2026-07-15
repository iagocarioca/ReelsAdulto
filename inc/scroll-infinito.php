<?php
/**
 * Scroll infinito das grades de vídeo (home, autor, categoria, busca,
 * arquivo e playlist). O JS observa uma sentinela após a grade e pede
 * a próxima página aqui; devolvemos os cards prontos (card-grade).
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Endpoint AJAX: próxima página de uma grade.
 * Leitura pública (sem nonce), como tikporn_feed.
 */
function tikporn_ajax_grade() {
	$tipo   = sanitize_key( $_POST['tipo'] ?? '' );
	$pagina = max( 2, absint( $_POST['pagina'] ?? 2 ) );
	$qtd    = min( 60, max( 1, absint( $_POST['qtd'] ?? 24 ) ) );

	$args = array(
		'post_type'      => 'video',
		'post_status'    => 'publish',
		'paged'          => $pagina,
		'posts_per_page' => $qtd,
	);

	switch ( $tipo ) {
		case 'home':
		case 'arquivo':
			// Mais recentes primeiro (padrão).
			break;

		case 'autor':
			$autor = absint( $_POST['autor'] ?? 0 );
			if ( ! $autor ) {
				wp_send_json_error();
			}
			$args['author'] = $autor;
			$ordem = sanitize_key( $_POST['ordem'] ?? '' );
			if ( 'vistos' === $ordem ) {
				$args['meta_key'] = '_tikporn_views';
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
			} elseif ( 'curtidos' === $ordem ) {
				$args['meta_key'] = '_tikporn_curtidas';
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
			}
			break;

		case 'termo':
			$tax  = sanitize_key( $_POST['tax'] ?? '' );
			$term = absint( $_POST['term'] ?? 0 );
			if ( ! $tax || ! $term || ! taxonomy_exists( $tax ) ) {
				wp_send_json_error();
			}
			$args['tax_query'] = array(
				array(
					'taxonomy' => $tax,
					'field'    => 'term_id',
					'terms'    => $term,
				),
			);
			break;

		case 'busca':
			$args['s'] = sanitize_text_field( wp_unslash( $_POST['busca'] ?? '' ) );
			break;

		case 'playlist':
			$pl = absint( $_POST['playlist'] ?? 0 );
			if ( ! $pl || ! function_exists( 'tikporn_playlist_videos' ) ) {
				wp_send_json_error();
			}
			$ids = tikporn_playlist_videos( $pl );
			if ( empty( $ids ) ) {
				wp_send_json_error();
			}
			$args['post__in'] = array_map( 'intval', $ids );
			$args['orderby']  = 'post__in';
			break;

		default:
			wp_send_json_error();
	}

	$q = new WP_Query( $args );

	// Cards de playlist carregam o contexto (?playlist=) no link.
	if ( 'playlist' === $tipo && ! empty( $pl ) ) {
		$GLOBALS['tikporn_playlist_ctx'] = $pl;
	}

	ob_start();
	while ( $q->have_posts() ) {
		$q->the_post();
		get_template_part( 'template-parts/card-grade' );
	}
	wp_reset_postdata();
	$html = ob_get_clean();

	wp_send_json_success(
		array(
			'html'     => $html,
			'tem_mais' => (int) $q->max_num_pages > $pagina,
		)
	);
}
add_action( 'wp_ajax_tikporn_grade', 'tikporn_ajax_grade' );
add_action( 'wp_ajax_nopriv_tikporn_grade', 'tikporn_ajax_grade' );

/**
 * Atributos data-* da grade infinita (uso nos templates).
 *
 * @param array $dados tipo/autor/tax/term/busca/playlist/ordem/qtd/pagina/tem_mais.
 */
function tikporn_grade_attrs( $dados ) {
	$attrs = ' data-grade-inf';
	foreach ( $dados as $chave => $valor ) {
		if ( '' === $valor || null === $valor ) {
			continue;
		}
		$attrs .= sprintf( ' data-%s="%s"', esc_attr( str_replace( '_', '-', $chave ) ), esc_attr( $valor ) );
	}
	echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput -- escapado acima
}
