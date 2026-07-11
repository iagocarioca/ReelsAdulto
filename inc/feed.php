<?php
/**
 * Feed vertical (estilo Reelix): dados de cada vídeo + endpoint de scroll infinito.
 * Cada item carrega o que o painel e o overlay precisam.
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL do avatar do usuário (foto enviada ou gravatar).
 */
function tikporn_avatar_url( $user_id, $size = 96 ) {
	$foto_id = get_user_meta( $user_id, 'tikporn_foto_id', true );
	if ( $foto_id ) {
		$url = wp_get_attachment_image_url( $foto_id, 'thumbnail' );
		if ( $url ) {
			return $url;
		}
	}
	return get_avatar_url( $user_id, array( 'size' => $size ) );
}

/**
 * Monta o pacote de dados de um vídeo para o feed/painel.
 *
 * @return array|null Null se não houver vídeo.
 */
function tikporn_video_para_feed( $post_id ) {
	$src = tikporn_get_video_url( $post_id );
	if ( ! $src ) {
		return null;
	}

	$autor = (int) get_post_field( 'post_author', $post_id );
	$terms = get_the_terms( $post_id, TIKPORN_TAX_CAT );
	$terms = ( $terms && ! is_wp_error( $terms ) ) ? $terms : array();

	$cats = array();
	foreach ( $terms as $t ) {
		$cats[] = array( 'nome' => $t->name, 'url' => get_term_link( $t ) );
	}

	return array(
		'id'        => $post_id,
		'permalink' => get_permalink( $post_id ),
		'title'     => get_the_title( $post_id ),
		'desc'      => wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ), 40 ),
		'src'       => $src,
		'tipo'      => tikporn_get_video_tipo( $post_id ),
		'poster'    => tikporn_capa_url( $post_id ),
		'views'     => tikporn_numero_k( tikporn_views( $post_id ) ),
		'likes'     => tikporn_numero_k( tikporn_curtidas( $post_id ) ),
		'curtiu'    => tikporn_usuario_curtiu( $post_id ),
		'autor'     => array(
			'id'      => $autor,
			'nome'    => get_the_author_meta( 'display_name', $autor ),
			'handle'  => get_the_author_meta( 'user_nicename', $autor ),
			'url'     => tikporn_url_perfil( $autor ),
			'avatar'  => tikporn_avatar_url( $autor ),
			'artista' => function_exists( 'tikporn_eh_modelo' ) && tikporn_eh_modelo( $autor ),
			'segue'   => function_exists( 'tikporn_usuario_segue' ) ? tikporn_usuario_segue( $autor ) : false,
		),
		'cats'      => $cats,
	);
}

/**
 * Endpoint AJAX: próxima página de vídeos do feed (público).
 */
function tikporn_ajax_feed() {
	$cursor  = absint( $_GET['cursor'] ?? 0 );
	$exclude = absint( $_GET['exclude'] ?? 0 );

	$per = 5;
	$q   = new WP_Query(
		array(
			'post_type'      => 'video',
			'post_status'    => 'publish',
			'posts_per_page' => $per,
			'offset'         => $cursor,
			'post__not_in'   => $exclude ? array( $exclude ) : array(),
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);

	$items = array();
	foreach ( $q->posts as $p ) {
		$d = tikporn_video_para_feed( $p->ID );
		if ( $d ) {
			$items[] = $d;
		}
	}

	wp_send_json_success(
		array(
			'items'       => $items,
			'next_cursor' => $cursor + count( $q->posts ),
			'has_more'    => count( $q->posts ) >= $per,
		)
	);
}
add_action( 'wp_ajax_tikporn_feed', 'tikporn_ajax_feed' );
add_action( 'wp_ajax_nopriv_tikporn_feed', 'tikporn_ajax_feed' );

/**
 * Endpoint AJAX: registra uma visualização do vídeo em foco no feed.
 *
 * Chamado pelo feed.js quando um vídeo entra na tela (troca por scroll/pushState),
 * já que essa navegação não recarrega a página. A deduplicação por visitante/6h
 * é feita dentro de tikporn_registrar_view().
 */
function tikporn_ajax_view() {
	$id = absint( $_POST['video_id'] ?? $_GET['video_id'] ?? 0 );
	if ( ! $id || 'video' !== get_post_type( $id ) ) {
		wp_send_json_error();
	}
	tikporn_registrar_view( $id );
	wp_send_json_success( array( 'views' => tikporn_views( $id ) ) );
}
add_action( 'wp_ajax_tikporn_view', 'tikporn_ajax_view' );
add_action( 'wp_ajax_nopriv_tikporn_view', 'tikporn_ajax_view' );
