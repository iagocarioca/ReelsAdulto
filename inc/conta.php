<?php
/**
 * Área "Minha conta" — edição de perfil de qualquer usuário logado.
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Processa o formulário de editar perfil da página /minha-conta/.
 */
function tikporn_processar_conta() {
	if ( ! isset( $_POST['tikporn_salvar_conta'] ) ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	if ( ! isset( $_POST['tikporn_conta_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['tikporn_conta_nonce'] ), 'tikporn_salvar_conta' ) ) {
		wp_safe_redirect( site_url( '/minha-conta/' ) );
		exit;
	}

	$user_id = get_current_user_id();
	$bio     = sanitize_textarea_field( wp_unslash( $_POST['biografia'] ?? '' ) );
	$nome    = sanitize_text_field( wp_unslash( $_POST['nome_publico'] ?? '' ) );

	$campos = array(
		'ID'          => $user_id,
		'description' => $bio,
	);
	if ( '' !== $nome ) {
		$campos['display_name'] = $nome;
	}
	wp_update_user( $campos );

	// Links públicos (site e redes sociais) mostrados no perfil.
	foreach ( array( 'site', 'x', 'tiktok', 'instagram' ) as $rede ) {
		$url = esc_url_raw( wp_unslash( $_POST[ 'link_' . $rede ] ?? '' ) );
		if ( $url ) {
			update_user_meta( $user_id, 'tikporn_link_' . $rede, $url );
		} else {
			delete_user_meta( $user_id, 'tikporn_link_' . $rede );
		}
	}

	if ( ! empty( $_FILES['foto']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$foto = media_handle_upload( 'foto', 0 );
		if ( ! is_wp_error( $foto ) ) {
			update_user_meta( $user_id, 'tikporn_foto_id', $foto );
		}
	}

	wp_safe_redirect( add_query_arg( 'salvo', '1', site_url( '/minha-conta/' ) ) );
	exit;
}
add_action( 'template_redirect', 'tikporn_processar_conta' );

/**
 * Vídeos que o usuário curtiu (IDs).
 */
function tikporn_curtidos_do_usuario( $user_id ) {
	$ids = (array) get_user_meta( $user_id, 'tikporn_curtidos', true );
	return array_values( array_filter( array_map( 'intval', $ids ) ) );
}

/**
 * Usuários que a pessoa segue (IDs).
 */
function tikporn_seguindo_do_usuario( $user_id ) {
	$ids = (array) get_user_meta( $user_id, 'tikporn_seguindo', true );
	return array_values( array_filter( array_map( 'intval', $ids ) ) );
}
