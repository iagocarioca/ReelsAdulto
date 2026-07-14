<?php
/**
 * API de canais — cria/atualiza um usuário com papel "modelo" via REST,
 * com nome, foto de perfil (URL de imagem) e descrição (bio).
 *
 * POST /wp-json/tikporn/v1/canais
 *   nome      (obrigatório)  Nome público do canal.
 *   usuario   (opcional)     Login; se omitido, é derivado do nome.
 *   descricao (opcional)     Bio do canal.
 *   imagem    (opcional)     URL de imagem — baixada para a biblioteca e
 *                            usada como foto de perfil (tikporn_foto_id).
 *   email     (opcional)     E-mail; se omitido, gera um placeholder.
 *
 * Upsert por login: se o usuário já existe, atualiza nome/descrição/foto
 * (e garante o papel modelo) em vez de falhar — bom para automação.
 * Requer autenticação com capacidade create_users (ex.: Application
 * Password de um administrador, como no envio de vídeos).
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra a rota.
 */
function tikporn_api_canais_rotas() {
	register_rest_route(
		'tikporn/v1',
		'/canais',
		array(
			'methods'             => 'POST',
			'callback'            => 'tikporn_api_criar_canal',
			'permission_callback' => function () {
				return current_user_can( 'create_users' );
			},
			'args'                => array(
				'nome'      => array( 'type' => 'string', 'required' => true ),
				'usuario'   => array( 'type' => 'string', 'required' => false ),
				'descricao' => array( 'type' => 'string', 'required' => false ),
				'imagem'    => array( 'type' => 'string', 'required' => false, 'format' => 'uri' ),
				'email'     => array( 'type' => 'string', 'required' => false, 'format' => 'email' ),
			),
		)
	);
}
add_action( 'rest_api_init', 'tikporn_api_canais_rotas' );

/**
 * Cria (ou atualiza) o canal e devolve os dados essenciais.
 *
 * @param WP_REST_Request $req Requisição.
 * @return WP_REST_Response|WP_Error
 */
function tikporn_api_criar_canal( WP_REST_Request $req ) {
	$nome = sanitize_text_field( (string) $req['nome'] );
	if ( '' === $nome ) {
		return new WP_Error( 'tikporn_canal_nome', __( 'Informe o nome do canal.', 'tikporn' ), array( 'status' => 400 ) );
	}

	$login = sanitize_user( (string) ( $req['usuario'] ?: sanitize_title( $nome ) ), true );
	if ( '' === $login ) {
		return new WP_Error( 'tikporn_canal_login', __( 'Não foi possível derivar um login válido do nome.', 'tikporn' ), array( 'status' => 400 ) );
	}

	$descricao = sanitize_textarea_field( (string) ( $req['descricao'] ?? '' ) );

	$user   = get_user_by( 'login', $login );
	$criado = false;

	if ( $user ) {
		// Upsert: atualiza os campos enviados e garante o papel.
		$campos = array( 'ID' => $user->ID, 'display_name' => $nome );
		if ( '' !== $descricao ) {
			$campos['description'] = $descricao;
		}
		$uid = wp_update_user( $campos );
		if ( is_wp_error( $uid ) ) {
			$uid->add_data( array( 'status' => 500 ) );
			return $uid;
		}
		if ( ! in_array( 'modelo', (array) $user->roles, true ) ) {
			$user->add_role( 'modelo' );
		}
	} else {
		$email = sanitize_email( (string) ( $req['email'] ?? '' ) );
		if ( ! $email ) {
			$dominio = wp_parse_url( home_url(), PHP_URL_HOST );
			$email   = $login . '@' . ( $dominio ? $dominio : 'canal.local' );
		}
		if ( email_exists( $email ) ) {
			$email = $login . '.' . wp_generate_password( 6, false ) . '@' . ( wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'canal.local' );
		}

		$uid = wp_insert_user(
			array(
				'user_login'   => $login,
				'user_pass'    => wp_generate_password( 24 ),
				'user_email'   => $email,
				'display_name' => $nome,
				'description'  => $descricao,
				'role'         => 'modelo',
			)
		);
		if ( is_wp_error( $uid ) ) {
			$uid->add_data( array( 'status' => 400 ) );
			return $uid;
		}
		$criado = true;
	}

	// Foto de perfil: baixa a imagem para a biblioteca e aponta o meta do tema.
	$imagem   = esc_url_raw( (string) ( $req['imagem'] ?? '' ) );
	$foto_url = '';
	$aviso    = '';
	if ( $imagem ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$att = media_sideload_image( $imagem, 0, $nome, 'id' );
		if ( is_wp_error( $att ) ) {
			$aviso = sprintf( __( 'Canal salvo, mas a imagem falhou: %s', 'tikporn' ), $att->get_error_message() );
		} else {
			update_user_meta( $uid, 'tikporn_foto_id', (int) $att );
			$foto_url = (string) wp_get_attachment_image_url( $att, 'thumbnail' );
		}
	} else {
		$foto_att = (int) get_user_meta( $uid, 'tikporn_foto_id', true );
		$foto_url = $foto_att ? (string) wp_get_attachment_image_url( $foto_att, 'thumbnail' ) : '';
	}

	$resposta = array(
		'id'      => (int) $uid,
		'criado'  => $criado,
		'usuario' => $login,
		'handle'  => get_the_author_meta( 'user_nicename', $uid ),
		'nome'    => $nome,
		'perfil'  => get_author_posts_url( $uid ),
		'foto'    => $foto_url,
	);
	if ( $aviso ) {
		$resposta['aviso'] = $aviso;
	}

	return rest_ensure_response( $resposta );
}
