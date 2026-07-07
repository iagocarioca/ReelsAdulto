<?php
/**
 * Curtir vídeos e seguir modelos (via requisições AJAX).
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Curtir / descurtir um vídeo.
 */
function tikporn_ajax_curtir() {
	check_ajax_referer( 'tikporn_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'mensagem' => __( 'Entre para curtir.', 'tikporn' ) ), 401 );
	}

	$post_id = absint( $_POST['video_id'] ?? 0 );
	if ( ! $post_id || 'video' !== get_post_type( $post_id ) ) {
		wp_send_json_error( array( 'mensagem' => __( 'Vídeo inválido.', 'tikporn' ) ), 400 );
	}

	$user_id  = get_current_user_id();
	$curtidos = (array) get_user_meta( $user_id, 'tikporn_curtidos', true );
	$curtidos = array_filter( $curtidos );

	if ( in_array( $post_id, $curtidos, true ) ) {
		$curtidos = array_diff( $curtidos, array( $post_id ) );
		$curtiu   = false;
	} else {
		$curtidos[] = $post_id;
		$curtiu     = true;
	}

	update_user_meta( $user_id, 'tikporn_curtidos', array_values( $curtidos ) );

	// Atualiza o contador total do vídeo.
	$total = (int) get_post_meta( $post_id, '_tikporn_curtidas', true );
	$total = max( 0, $curtiu ? $total + 1 : $total - 1 );
	update_post_meta( $post_id, '_tikporn_curtidas', $total );

	wp_send_json_success(
		array(
			'curtiu' => $curtiu,
			'total'  => $total,
		)
	);
}
add_action( 'wp_ajax_tikporn_curtir', 'tikporn_ajax_curtir' );

/**
 * Seguir / deixar de seguir uma modelo.
 */
function tikporn_ajax_seguir() {
	check_ajax_referer( 'tikporn_nonce', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'mensagem' => __( 'Entre para seguir.', 'tikporn' ) ), 401 );
	}

	$modelo_id = absint( $_POST['modelo_id'] ?? 0 );
	if ( ! $modelo_id || ! get_userdata( $modelo_id ) ) {
		wp_send_json_error( array( 'mensagem' => __( 'Modelo inválida.', 'tikporn' ) ), 400 );
	}

	$user_id = get_current_user_id();
	if ( $user_id === $modelo_id ) {
		wp_send_json_error( array( 'mensagem' => __( 'Você não pode seguir a si mesma.', 'tikporn' ) ), 400 );
	}

	$seguindo = (array) get_user_meta( $user_id, 'tikporn_seguindo', true );
	$seguindo = array_filter( $seguindo );

	if ( in_array( $modelo_id, $seguindo, true ) ) {
		$seguindo = array_diff( $seguindo, array( $modelo_id ) );
		$segue    = false;
	} else {
		$seguindo[] = $modelo_id;
		$segue      = true;
	}

	update_user_meta( $user_id, 'tikporn_seguindo', array_values( $seguindo ) );

	$total = (int) get_user_meta( $modelo_id, 'tikporn_seguidores', true );
	$total = max( 0, $segue ? $total + 1 : $total - 1 );
	update_user_meta( $modelo_id, 'tikporn_seguidores', $total );

	wp_send_json_success(
		array(
			'segue' => $segue,
			'total' => $total,
		)
	);
}
add_action( 'wp_ajax_tikporn_seguir', 'tikporn_ajax_seguir' );

/**
 * Diz se o usuário atual já curtiu um vídeo.
 */
function tikporn_usuario_curtiu( $post_id ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}
	$curtidos = (array) get_user_meta( get_current_user_id(), 'tikporn_curtidos', true );
	return in_array( $post_id, array_map( 'intval', $curtidos ), true );
}

/**
 * Diz se o usuário atual já segue uma modelo.
 */
function tikporn_usuario_segue( $modelo_id ) {
	if ( ! is_user_logged_in() ) {
		return false;
	}
	$seguindo = (array) get_user_meta( get_current_user_id(), 'tikporn_seguindo', true );
	return in_array( $modelo_id, array_map( 'intval', $seguindo ), true );
}
