<?php
/**
 * Cadastro, login e logout no próprio site (sem usar as telas do painel).
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Processa o cadastro de um novo usuário.
 */
function tikporn_processar_cadastro() {
	if ( ! isset( $_POST['tikporn_cadastro'] ) ) {
		return;
	}

	if ( ! isset( $_POST['tikporn_cadastro_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['tikporn_cadastro_nonce'] ), 'tikporn_cadastro' ) ) {
		tikporn_redirecionar_com_erro( '/cadastro/', __( 'Sessão expirada. Tente novamente.', 'tikporn' ) );
	}

	$usuario = sanitize_user( wp_unslash( $_POST['usuario'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$senha   = (string) ( $_POST['senha'] ?? '' );
	$modelo  = ! empty( $_POST['quero_ser_modelo'] );

	// Validações.
	if ( empty( $usuario ) || empty( $email ) || empty( $senha ) ) {
		tikporn_redirecionar_com_erro( '/cadastro/', __( 'Preencha todos os campos.', 'tikporn' ) );
	}
	if ( ! is_email( $email ) ) {
		tikporn_redirecionar_com_erro( '/cadastro/', __( 'E-mail inválido.', 'tikporn' ) );
	}
	if ( strlen( $senha ) < 6 ) {
		tikporn_redirecionar_com_erro( '/cadastro/', __( 'A senha precisa ter ao menos 6 caracteres.', 'tikporn' ) );
	}
	if ( username_exists( $usuario ) ) {
		tikporn_redirecionar_com_erro( '/cadastro/', __( 'Esse nome de usuário já está em uso.', 'tikporn' ) );
	}
	if ( email_exists( $email ) ) {
		tikporn_redirecionar_com_erro( '/cadastro/', __( 'Esse e-mail já tem conta.', 'tikporn' ) );
	}

	$user_id = wp_insert_user(
		array(
			'user_login' => $usuario,
			'user_email' => $email,
			'user_pass'  => $senha,
			'role'       => $modelo ? 'modelo' : 'subscriber',
		)
	);

	if ( is_wp_error( $user_id ) ) {
		tikporn_redirecionar_com_erro( '/cadastro/', $user_id->get_error_message() );
	}

	// Já entra com a conta recém-criada.
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );

	// Modelo vai para a área da modelo; usuário comum vai para o feed.
	$destino = $modelo ? '/area-modelo/' : '/';
	wp_safe_redirect( site_url( $destino ) );
	exit;
}
add_action( 'template_redirect', 'tikporn_processar_cadastro' );

/**
 * Processa o login.
 */
function tikporn_processar_login() {
	if ( ! isset( $_POST['tikporn_login'] ) ) {
		return;
	}

	if ( ! isset( $_POST['tikporn_login_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['tikporn_login_nonce'] ), 'tikporn_login' ) ) {
		tikporn_redirecionar_com_erro( '/entrar/', __( 'Sessão expirada. Tente novamente.', 'tikporn' ) );
	}

	$credenciais = array(
		'user_login'    => sanitize_text_field( wp_unslash( $_POST['usuario'] ?? '' ) ),
		'user_password' => (string) ( $_POST['senha'] ?? '' ),
		'remember'      => ! empty( $_POST['lembrar'] ),
	);

	$usuario = wp_signon( $credenciais, is_ssl() );

	if ( is_wp_error( $usuario ) ) {
		tikporn_redirecionar_com_erro( '/entrar/', __( 'Usuário ou senha incorretos.', 'tikporn' ) );
	}

	$destino = tikporn_eh_modelo( $usuario->ID ) ? '/area-modelo/' : '/';
	wp_safe_redirect( site_url( $destino ) );
	exit;
}
add_action( 'template_redirect', 'tikporn_processar_login' );

/**
 * Logout pelo próprio site.
 */
function tikporn_processar_logout() {
	if ( ! isset( $_GET['tikporn_sair'] ) ) {
		return;
	}
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'tikporn_sair' ) ) {
		return;
	}
	wp_logout();
	wp_safe_redirect( site_url( '/' ) );
	exit;
}
add_action( 'template_redirect', 'tikporn_processar_logout' );

/**
 * Redireciona guardando a mensagem de erro na sessão via transient por sessão.
 * Usa cookie leve para exibir a mensagem uma única vez.
 */
function tikporn_redirecionar_com_erro( $caminho, $mensagem ) {
	$chave = 'tikporn_msg_' . wp_generate_password( 8, false );
	set_transient( $chave, $mensagem, 60 );
	setcookie( 'tikporn_msg', $chave, time() + 60, COOKIEPATH, COOKIE_DOMAIN );
	wp_safe_redirect( site_url( $caminho ) );
	exit;
}

/**
 * Lê e apaga a mensagem de erro pendente (para mostrar nos formulários).
 */
function tikporn_pegar_mensagem() {
	if ( empty( $_COOKIE['tikporn_msg'] ) ) {
		return '';
	}
	$chave    = sanitize_key( wp_unslash( $_COOKIE['tikporn_msg'] ) );
	$mensagem = get_transient( $chave );
	delete_transient( $chave );
	setcookie( 'tikporn_msg', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
	return $mensagem ? $mensagem : '';
}

/**
 * Impede que modelos e assinantes entrem no painel do WordPress;
 * eles usam apenas as telas do site.
 */
function tikporn_bloquear_painel() {
	if ( ! is_admin() || wp_doing_ajax() ) {
		return;
	}
	if ( current_user_can( 'edit_others_videos' ) ) {
		return; // Administradores e editores continuam entrando.
	}
	wp_safe_redirect( site_url( '/area-modelo/' ) );
	exit;
}
add_action( 'admin_init', 'tikporn_bloquear_painel' );

/**
 * Esconde a barra do topo do WordPress para quem não administra.
 */
add_filter(
	'show_admin_bar',
	function ( $mostrar ) {
		return current_user_can( 'edit_others_videos' ) ? $mostrar : false;
	}
);
