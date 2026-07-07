<?php
/**
 * Envio, edição e exclusão de vídeos pela área da modelo.
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Processa o envio de um novo vídeo pela modelo.
 */
function tikporn_processar_envio_video() {
	if ( ! isset( $_POST['tikporn_enviar_video'] ) ) {
		return;
	}

	if ( ! is_user_logged_in() || ! tikporn_eh_modelo() ) {
		tikporn_redirecionar_com_erro( '/entrar/', __( 'Faça login como modelo para enviar vídeos.', 'tikporn' ) );
	}

	if ( ! isset( $_POST['tikporn_video_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['tikporn_video_nonce'] ), 'tikporn_enviar_video' ) ) {
		tikporn_redirecionar_com_erro( '/area-modelo/', __( 'Sessão expirada. Tente novamente.', 'tikporn' ) );
	}

	$titulo   = sanitize_text_field( wp_unslash( $_POST['titulo'] ?? '' ) );
	$legenda  = sanitize_textarea_field( wp_unslash( $_POST['legenda'] ?? '' ) );
	$link     = esc_url_raw( wp_unslash( $_POST['link_video'] ?? '' ) );

	if ( empty( $titulo ) ) {
		tikporn_redirecionar_com_erro( '/area-modelo/', __( 'Dê um título ao vídeo.', 'tikporn' ) );
	}

	$user_id = get_current_user_id();

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'video',
			'post_title'   => $titulo,
			'post_content' => $legenda,
			'post_status'  => 'publish',
			'post_author'  => $user_id,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		tikporn_redirecionar_com_erro( '/area-modelo/', $post_id->get_error_message() );
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	// Arquivo de vídeo enviado.
	if ( ! empty( $_FILES['arquivo_video']['name'] ) ) {
		$anexo = media_handle_upload( 'arquivo_video', $post_id );
		if ( is_wp_error( $anexo ) ) {
			wp_delete_post( $post_id, true );
			tikporn_redirecionar_com_erro( '/area-modelo/', __( 'Falha ao enviar o vídeo: ', 'tikporn' ) . $anexo->get_error_message() );
		}
		update_post_meta( $post_id, '_tikporn_video_url', wp_get_attachment_url( $anexo ) );
		update_post_meta( $post_id, '_tikporn_video_tipo', 'arquivo' );
	} elseif ( ! empty( $link ) ) {
		// Link incorporado (YouTube, Vimeo, arquivo externo).
		update_post_meta( $post_id, '_tikporn_video_url', $link );
		update_post_meta( $post_id, '_tikporn_video_tipo', 'incorporado' );
	} else {
		wp_delete_post( $post_id, true );
		tikporn_redirecionar_com_erro( '/area-modelo/', __( 'Envie um arquivo ou informe um link do vídeo.', 'tikporn' ) );
	}

	// Capa do vídeo.
	if ( ! empty( $_FILES['capa']['name'] ) ) {
		$capa = media_handle_upload( 'capa', $post_id );
		if ( ! is_wp_error( $capa ) ) {
			set_post_thumbnail( $post_id, $capa );
		}
	}

	wp_safe_redirect( add_query_arg( 'enviado', '1', site_url( '/area-modelo/' ) ) );
	exit;
}
add_action( 'template_redirect', 'tikporn_processar_envio_video' );

/**
 * Exclusão de um vídeo pela dona.
 */
function tikporn_processar_exclusao_video() {
	if ( ! isset( $_POST['tikporn_excluir_video'] ) ) {
		return;
	}
	if ( ! isset( $_POST['tikporn_excluir_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['tikporn_excluir_nonce'] ), 'tikporn_excluir_video' ) ) {
		tikporn_redirecionar_com_erro( '/area-modelo/', __( 'Sessão expirada.', 'tikporn' ) );
	}

	$post_id = absint( $_POST['video_id'] ?? 0 );
	$video   = get_post( $post_id );

	if ( ! $video || 'video' !== $video->post_type ) {
		tikporn_redirecionar_com_erro( '/area-modelo/', __( 'Vídeo não encontrado.', 'tikporn' ) );
	}

	// Só a dona (ou administrador) pode excluir.
	if ( (int) $video->post_author !== get_current_user_id() && ! current_user_can( 'delete_others_videos' ) ) {
		tikporn_redirecionar_com_erro( '/area-modelo/', __( 'Você não pode excluir este vídeo.', 'tikporn' ) );
	}

	wp_trash_post( $post_id );
	wp_safe_redirect( add_query_arg( 'excluido', '1', site_url( '/area-modelo/' ) ) );
	exit;
}
add_action( 'template_redirect', 'tikporn_processar_exclusao_video' );

/**
 * Atualiza os dados do perfil da modelo (biografia e foto).
 */
function tikporn_processar_perfil() {
	if ( ! isset( $_POST['tikporn_salvar_perfil'] ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	if ( ! isset( $_POST['tikporn_perfil_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['tikporn_perfil_nonce'] ), 'tikporn_salvar_perfil' ) ) {
		tikporn_redirecionar_com_erro( '/area-modelo/', __( 'Sessão expirada.', 'tikporn' ) );
	}

	$user_id = get_current_user_id();
	$bio     = sanitize_textarea_field( wp_unslash( $_POST['biografia'] ?? '' ) );
	$nome    = sanitize_text_field( wp_unslash( $_POST['nome_publico'] ?? '' ) );

	$campos = array(
		'ID'          => $user_id,
		'description' => $bio,
	);
	if ( ! empty( $nome ) ) {
		$campos['display_name'] = $nome;
	}
	wp_update_user( $campos );

	// Foto de perfil (guardada como imagem destacada de um "avatar" via meta).
	if ( ! empty( $_FILES['foto']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$foto = media_handle_upload( 'foto', 0 );
		if ( ! is_wp_error( $foto ) ) {
			update_user_meta( $user_id, 'tikporn_foto_id', $foto );
		}
	}

	wp_safe_redirect( add_query_arg( 'perfil', '1', site_url( '/area-modelo/' ) ) );
	exit;
}
add_action( 'template_redirect', 'tikporn_processar_perfil' );
