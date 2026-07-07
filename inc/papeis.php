<?php
/**
 * Papel de usuário "modelo" e suas permissões.
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cria o papel "modelo" na ativação do tema.
 * Um modelo pode enviar e gerenciar os próprios vídeos, mas não os de outras.
 */
function tikporn_criar_papel_modelo() {
	if ( get_role( 'modelo' ) ) {
		return;
	}

	add_role(
		'modelo',
		__( 'Modelo', 'tikporn' ),
		array(
			'read'                   => true,
			'upload_files'           => true,
			'edit_posts'             => true,
			// Permissões específicas do tipo de conteúdo "vídeo".
			'edit_videos'            => true,
			'edit_published_videos'  => true,
			'publish_videos'         => true,
			'delete_videos'          => true,
			'delete_published_videos'=> true,
			// Sem acesso aos vídeos de terceiros.
			'edit_others_videos'     => false,
			'delete_others_videos'   => false,
		)
	);
}
add_action( 'after_switch_theme', 'tikporn_criar_papel_modelo' );

/**
 * Garante que o administrador também tenha as permissões de vídeo.
 */
function tikporn_permissoes_admin() {
	$admin = get_role( 'administrator' );
	if ( ! $admin ) {
		return;
	}

	$caps = array(
		'edit_videos',
		'edit_published_videos',
		'edit_others_videos',
		'publish_videos',
		'delete_videos',
		'delete_published_videos',
		'delete_others_videos',
		'read_private_videos',
	);

	foreach ( $caps as $cap ) {
		$admin->add_cap( $cap );
	}
}
add_action( 'after_switch_theme', 'tikporn_permissoes_admin' );

/**
 * Verifica se um usuário é modelo.
 */
function tikporn_eh_modelo( $user_id = null ) {
	$user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();
	if ( ! $user || ! $user->exists() ) {
		return false;
	}
	return in_array( 'modelo', (array) $user->roles, true );
}
