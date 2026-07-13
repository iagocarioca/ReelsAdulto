<?php
/**
 * "Continuar com Google" — login/cadastro via Google Identity Services (GIS).
 *
 * O front recebe um ID token (JWT assinado pelo Google) do fluxo GIS e o envia
 * por AJAX. Aqui o token é validado LOCALMENTE (assinatura RS256 com as chaves
 * públicas do Google + claims aud/iss/exp/email_verified) e o usuário é
 * encontrado pelo e-mail ou criado (subscriber) e logado.
 *
 * O Client ID é público (vai no HTML do botão) e vem das Opções do tema, da
 * constante TIKPORN_GOOGLE_CLIENT_ID ou de um filtro.
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Client ID do OAuth (Google). Constante > opção do tema > filtro.
 */
function tikporn_google_client_id() {
	if ( defined( 'TIKPORN_GOOGLE_CLIENT_ID' ) && TIKPORN_GOOGLE_CLIENT_ID ) {
		return (string) TIKPORN_GOOGLE_CLIENT_ID;
	}
	$id = function_exists( 'tikporn_opcao' ) ? (string) tikporn_opcao( 'google_client_id', '' ) : '';
	return (string) apply_filters( 'tikporn_google_client_id', $id );
}

/** O login com Google está configurado? */
function tikporn_google_ativo() {
	return '' !== trim( tikporn_google_client_id() );
}

/* -------------------------------------------------------------------------
 * Validação do ID token (RS256 + JWKS do Google), sem dependências externas.
 * ---------------------------------------------------------------------- */

function tikporn_google_base64url_decode( $data ) {
	$rem = strlen( $data ) % 4;
	if ( $rem ) {
		$data .= str_repeat( '=', 4 - $rem );
	}
	return (string) base64_decode( strtr( $data, '-_', '+/' ), true );
}

function tikporn_google_der_len( $len ) {
	if ( $len < 128 ) {
		return chr( $len );
	}
	$bytes = '';
	while ( $len > 0 ) {
		$bytes = chr( $len & 0xff ) . $bytes;
		$len >>= 8;
	}
	return chr( 0x80 | strlen( $bytes ) ) . $bytes;
}

function tikporn_google_der_int( $bytes ) {
	$bytes = ltrim( $bytes, "\x00" );
	if ( '' === $bytes ) {
		$bytes = "\x00";
	}
	if ( ord( $bytes[0] ) & 0x80 ) {
		$bytes = "\x00" . $bytes;
	}
	return "\x02" . tikporn_google_der_len( strlen( $bytes ) ) . $bytes;
}

/** Converte uma JWK RSA (n, e) em chave pública PEM. */
function tikporn_google_jwk_to_pem( $n_b64, $e_b64 ) {
	$n = tikporn_google_base64url_decode( $n_b64 );
	$e = tikporn_google_base64url_decode( $e_b64 );
	if ( '' === $n || '' === $e ) {
		return '';
	}
	$rsa_pub   = "\x30" . tikporn_google_der_len( strlen( tikporn_google_der_int( $n ) . tikporn_google_der_int( $e ) ) )
		. tikporn_google_der_int( $n ) . tikporn_google_der_int( $e );
	$alg_id    = "\x30\x0d\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
	$bitstring = "\x03" . tikporn_google_der_len( strlen( $rsa_pub ) + 1 ) . "\x00" . $rsa_pub;
	$spki      = "\x30" . tikporn_google_der_len( strlen( $alg_id . $bitstring ) ) . $alg_id . $bitstring;
	return "-----BEGIN PUBLIC KEY-----\n" . chunk_split( base64_encode( $spki ), 64, "\n" ) . "-----END PUBLIC KEY-----\n";
}

/** Chaves públicas do Google (JWKS), indexadas por kid, com cache. */
function tikporn_google_jwks() {
	$cached = get_transient( 'tikporn_google_jwks' );
	if ( is_array( $cached ) && ! empty( $cached ) ) {
		return $cached;
	}
	$res = wp_remote_get( 'https://www.googleapis.com/oauth2/v3/certs', array( 'timeout' => 8 ) );
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		return is_array( $cached ) ? $cached : array();
	}
	$body = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( empty( $body['keys'] ) || ! is_array( $body['keys'] ) ) {
		return array();
	}
	$keys = array();
	foreach ( $body['keys'] as $key ) {
		if ( ! empty( $key['kid'] ) ) {
			$keys[ $key['kid'] ] = $key;
		}
	}
	$ttl = 3600;
	$cc  = wp_remote_retrieve_header( $res, 'cache-control' );
	if ( $cc && preg_match( '/max-age=(\d+)/', $cc, $m ) ) {
		$ttl = max( 300, (int) $m[1] );
	}
	set_transient( 'tikporn_google_jwks', $keys, $ttl );
	return $keys;
}

/**
 * Valida o ID token do Google e devolve os claims.
 *
 * @return array|WP_Error
 */
function tikporn_google_verify_id_token( $jwt ) {
	$parts = explode( '.', $jwt );
	if ( 3 !== count( $parts ) ) {
		return new WP_Error( 'token', __( 'Token do Google inválido.', 'tikporn' ) );
	}
	list( $head_b64, $body_b64, $sig_b64 ) = $parts;

	$header = json_decode( tikporn_google_base64url_decode( $head_b64 ), true );
	$claims = json_decode( tikporn_google_base64url_decode( $body_b64 ), true );
	if ( ! is_array( $header ) || ! is_array( $claims ) ) {
		return new WP_Error( 'token', __( 'Token do Google inválido.', 'tikporn' ) );
	}
	if ( ( $header['alg'] ?? '' ) !== 'RS256' || empty( $header['kid'] ) ) {
		return new WP_Error( 'token', __( 'Assinatura do Google não suportada.', 'tikporn' ) );
	}

	$keys = tikporn_google_jwks();
	$jwk  = $keys[ $header['kid'] ] ?? null;
	if ( ! $jwk ) {
		delete_transient( 'tikporn_google_jwks' );
		return new WP_Error( 'token', __( 'Não foi possível validar o token do Google.', 'tikporn' ) );
	}

	$pem = tikporn_google_jwk_to_pem( $jwk['n'] ?? '', $jwk['e'] ?? '' );
	if ( '' === $pem ) {
		return new WP_Error( 'token', __( 'Falha ao validar a assinatura do Google.', 'tikporn' ) );
	}

	$ok = openssl_verify(
		$head_b64 . '.' . $body_b64,
		tikporn_google_base64url_decode( $sig_b64 ),
		$pem,
		OPENSSL_ALGO_SHA256
	);
	if ( 1 !== $ok ) {
		return new WP_Error( 'token', __( 'Assinatura do Google inválida.', 'tikporn' ) );
	}

	$iss = $claims['iss'] ?? '';
	if ( ! in_array( $iss, array( 'https://accounts.google.com', 'accounts.google.com' ), true ) ) {
		return new WP_Error( 'token', __( 'Emissor do token inválido.', 'tikporn' ) );
	}
	if ( ( $claims['aud'] ?? '' ) !== tikporn_google_client_id() ) {
		return new WP_Error( 'token', __( 'Este token não é deste site.', 'tikporn' ) );
	}
	if ( (int) ( $claims['exp'] ?? 0 ) < ( time() - 30 ) ) {
		return new WP_Error( 'token', __( 'Sessão do Google expirada. Tente novamente.', 'tikporn' ) );
	}
	$verificado = $claims['email_verified'] ?? false;
	if ( true !== $verificado && 'true' !== $verificado ) {
		return new WP_Error( 'token', __( 'O e-mail do Google não está verificado.', 'tikporn' ) );
	}
	if ( empty( $claims['email'] ) || ! is_email( $claims['email'] ) ) {
		return new WP_Error( 'token', __( 'O Google não retornou um e-mail válido.', 'tikporn' ) );
	}

	return $claims;
}

/** Gera um login único a partir do e-mail. */
function tikporn_google_login_unico( $email ) {
	$base  = sanitize_user( current( explode( '@', $email ) ), true );
	$base  = $base ? $base : 'user';
	$login = $base;
	$i     = 1;
	while ( username_exists( $login ) ) {
		$login = $base . $i;
		$i++;
	}
	return $login;
}

/* -------------------------------------------------------------------------
 * Endpoint AJAX: recebe o credential (ID token) e loga/cadastra.
 * ---------------------------------------------------------------------- */

function tikporn_ajax_google_login() {
	if ( ! tikporn_google_ativo() ) {
		wp_send_json_error( array( 'msg' => __( 'Login com Google não configurado.', 'tikporn' ) ) );
	}

	$credential = isset( $_POST['credential'] ) ? trim( (string) wp_unslash( $_POST['credential'] ) ) : '';
	if ( '' === $credential ) {
		wp_send_json_error( array( 'msg' => __( 'Token do Google ausente.', 'tikporn' ) ) );
	}

	$claims = tikporn_google_verify_id_token( $credential );
	if ( is_wp_error( $claims ) ) {
		wp_send_json_error( array( 'msg' => $claims->get_error_message() ) );
	}

	$email = sanitize_email( $claims['email'] );
	$nome  = sanitize_text_field( $claims['name'] ?? current( explode( '@', $email ) ) );
	$sub   = sanitize_text_field( (string) ( $claims['sub'] ?? '' ) );

	$user = get_user_by( 'email', $email );

	if ( ! $user ) {
		$user_id = wp_insert_user(
			array(
				'user_login'   => tikporn_google_login_unico( $email ),
				'user_email'   => $email,
				'user_pass'    => wp_generate_password( 24, true, true ),
				'display_name' => $nome,
				'first_name'   => $nome,
				'role'         => 'subscriber',
			)
		);
		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( array( 'msg' => __( 'Não foi possível criar a conta. Tente novamente.', 'tikporn' ) ) );
		}
		update_user_meta( $user_id, 'tikporn_oauth_provider', 'google' );
		if ( '' !== $sub ) {
			update_user_meta( $user_id, 'tikporn_google_sub', $sub );
		}
		$user = get_user_by( 'id', $user_id );
	} elseif ( '' !== $sub && ! get_user_meta( $user->ID, 'tikporn_google_sub', true ) ) {
		update_user_meta( $user->ID, 'tikporn_google_sub', $sub );
	}

	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true, is_ssl() );

	wp_send_json_success( array( 'redirect' => home_url( '/' ) ) );
}
add_action( 'wp_ajax_nopriv_tikporn_google_login', 'tikporn_ajax_google_login' );
add_action( 'wp_ajax_tikporn_google_login', 'tikporn_ajax_google_login' );

/**
 * Carrega o script do Google Identity Services nas páginas de auth.
 */
function tikporn_google_enqueue() {
	if ( ! tikporn_google_ativo() || is_user_logged_in() ) {
		return;
	}
	// Só nas páginas de login/cadastro.
	if ( ! is_page( array( 'entrar', 'cadastro' ) ) ) {
		return;
	}
	wp_enqueue_script( 'google-gsi', 'https://accounts.google.com/gsi/client', array(), null, true );
	wp_localize_script(
		'tikporn-main',
		'tikpornGoogle',
		array(
			'clientId' => tikporn_google_client_id(),
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'tikporn_google_enqueue', 20 );

/**
 * Renderiza o botão "Continuar com Google" (custom) + o divisor.
 * Usado nos templates de login e cadastro.
 */
function tikporn_google_botao() {
	if ( ! tikporn_google_ativo() ) {
		return;
	}
	?>
	<div id="g_id_onload"
		data-client_id="<?php echo esc_attr( tikporn_google_client_id() ); ?>"
		data-callback="tikpornGoogleCallback"
		data-auto_prompt="false"></div>

	<button type="button" class="xf-oauth-google" data-google-btn>
		<span class="xf-oauth-google__g" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="20" height="20"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23z"/><path fill="#FBBC05" d="M5.84 14.1a6.6 6.6 0 0 1 0-4.2V7.06H2.18a11 11 0 0 0 0 9.88l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1A11 11 0 0 0 2.18 7.06l3.66 2.84C6.71 7.3 9.14 5.38 12 5.38z"/></svg>
		</span>
		<span><?php esc_html_e( 'Continuar com Google', 'tikporn' ); ?></span>
	</button>

	<div class="xf-oauth-ou"><span><?php esc_html_e( 'ou', 'tikporn' ); ?></span></div>
	<?php
}
