<?php
/**
 * Comentários dos vídeos (feed vertical) — lista pública e envio via AJAX.
 * Usa os comentários nativos do WP no CPT video; quem comenta precisa
 * estar logado e o comentário entra aprovado (com anti-flood simples).
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formata um comentário para o JSON do feed.
 *
 * @param WP_Comment $c Comentário.
 * @return array
 */
function tikporn_comentario_para_json( $c ) {
	$avatar = '';
	if ( $c->user_id && function_exists( 'tikporn_avatar_url' ) ) {
		$avatar = tikporn_avatar_url( (int) $c->user_id );
	}
	if ( ! $avatar ) {
		$avatar = get_avatar_url( $c->comment_author_email ? $c->comment_author_email : (int) $c->user_id, array( 'size' => 64 ) );
	}

	return array(
		'id'     => (int) $c->comment_ID,
		'autor'  => $c->comment_author ? $c->comment_author : __( 'Anônimo', 'tikporn' ),
		'avatar' => $avatar,
		'tempo'  => sprintf( __( 'há %s', 'tikporn' ), human_time_diff( strtotime( $c->comment_date_gmt ), time() ) ),
		'texto'  => wp_strip_all_tags( $c->comment_content ),
	);
}

/**
 * Lista os comentários de um vídeo (público).
 */
function tikporn_ajax_comentarios() {
	$vid = absint( $_REQUEST['video_id'] ?? 0 );
	if ( ! $vid || 'video' !== get_post_type( $vid ) ) {
		wp_send_json_error();
	}

	$comments = get_comments(
		array(
			'post_id' => $vid,
			'status'  => 'approve',
			'number'  => 60,
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC',
		)
	);

	wp_send_json_success(
		array(
			'items' => array_map( 'tikporn_comentario_para_json', $comments ),
			'total' => (int) get_comments_number( $vid ),
		)
	);
}
add_action( 'wp_ajax_tikporn_comentarios', 'tikporn_ajax_comentarios' );
add_action( 'wp_ajax_nopriv_tikporn_comentarios', 'tikporn_ajax_comentarios' );

/**
 * Publica um comentário (logado; aprovado na hora; anti-flood de 15s).
 */
function tikporn_ajax_comentar() {
	check_ajax_referer( 'tikporn_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'msg' => __( 'Entre para comentar.', 'tikporn' ) ) );
	}

	$vid   = absint( $_POST['video_id'] ?? 0 );
	$texto = trim( sanitize_textarea_field( wp_unslash( $_POST['texto'] ?? '' ) ) );

	if ( ! $vid || 'video' !== get_post_type( $vid ) || '' === $texto || mb_strlen( $texto ) > 600 ) {
		wp_send_json_error( array( 'msg' => __( 'Comentário inválido.', 'tikporn' ) ) );
	}

	$uid = get_current_user_id();
	if ( get_transient( 'tp_coment_' . $uid ) ) {
		wp_send_json_error( array( 'msg' => __( 'Aguarde um instante antes de comentar de novo.', 'tikporn' ) ) );
	}
	set_transient( 'tp_coment_' . $uid, 1, 15 );

	$user = wp_get_current_user();
	$cid  = wp_insert_comment(
		array(
			'comment_post_ID'      => $vid,
			'user_id'              => $uid,
			'comment_author'       => $user->display_name,
			'comment_author_email' => $user->user_email,
			'comment_content'      => $texto,
			'comment_approved'     => 1,
		)
	);

	if ( ! $cid ) {
		wp_send_json_error( array( 'msg' => __( 'Não foi possível comentar agora.', 'tikporn' ) ) );
	}

	wp_send_json_success(
		array(
			'item'  => tikporn_comentario_para_json( get_comment( $cid ) ),
			'total' => (int) get_comments_number( $vid ),
		)
	);
}
add_action( 'wp_ajax_tikporn_comentar', 'tikporn_ajax_comentar' );
