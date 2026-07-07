<?php
/**
 * Template Name: Entrar
 * Página de login.
 *
 * @package tikporn
 */

// Já logado? Vai para o feed.
if ( is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/' ) );
	exit;
}

get_header();
$tp_erro = tikporn_pegar_mensagem();
?>

<div class="tp-auth">
	<h1 class="tp-auth-titulo"><?php esc_html_e( 'Entrar', 'tikporn' ); ?></h1>

	<?php if ( $tp_erro ) : ?>
		<div class="tp-aviso tp-aviso-erro"><?php echo esc_html( $tp_erro ); ?></div>
	<?php endif; ?>

	<form class="tp-form" method="post" action="<?php echo esc_url( site_url( '/entrar/' ) ); ?>">
		<?php wp_nonce_field( 'tikporn_login', 'tikporn_login_nonce' ); ?>

		<label class="tp-campo">
			<span><?php esc_html_e( 'E-mail ou usuário', 'tikporn' ); ?></span>
			<input type="text" name="usuario" autocomplete="username" required>
		</label>

		<label class="tp-campo">
			<span><?php esc_html_e( 'Senha', 'tikporn' ); ?></span>
			<input type="password" name="senha" autocomplete="current-password" required>
		</label>

		<label class="tp-check">
			<input type="checkbox" name="lembrar" value="1" checked>
			<span><?php esc_html_e( 'Continuar conectado', 'tikporn' ); ?></span>
		</label>

		<button class="tp-botao tp-botao-cheio" type="submit" name="tikporn_login" value="1">
			<?php esc_html_e( 'Entrar', 'tikporn' ); ?>
		</button>
	</form>

	<div class="tp-auth-rodape">
		<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Esqueci minha senha', 'tikporn' ); ?></a>
		<span>&middot;</span>
		<a href="<?php echo esc_url( site_url( '/cadastro/' ) ); ?>"><?php esc_html_e( 'Criar conta', 'tikporn' ); ?></a>
	</div>
</div>

<?php
get_footer();
