<?php
/**
 * Template Name: Entrar
 * Página de login — layout split (branding + formulário).
 *
 * @package tikporn
 */

if ( is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/' ) );
	exit;
}

get_header();
$tp_erro = tikporn_pegar_mensagem();
?>

<div class="xf-auth">
	<div class="xf-auth__aside" aria-hidden="true">
		<div class="xf-auth__aside-inner">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="xf-auth__logo">
				<img src="<?php echo esc_url( TIKPORN_URI . '/assets/img/logo.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			</a>
			<h2 class="xf-auth__slogan"><?php esc_html_e( 'Os melhores vídeos, num só lugar.', 'tikporn' ); ?></h2>
			<p class="xf-auth__sub"><?php esc_html_e( 'Entre para curtir, salvar em playlists e seguir seus criadores favoritos.', 'tikporn' ); ?></p>
		</div>
	</div>

	<div class="xf-auth__form-wrap">
		<div class="xf-auth__card">
			<h1 class="xf-auth__titulo"><?php esc_html_e( 'Bem-vindo de volta', 'tikporn' ); ?></h1>
			<p class="xf-auth__desc"><?php esc_html_e( 'Entre na sua conta para continuar.', 'tikporn' ); ?></p>

			<?php if ( $tp_erro ) : ?>
				<div class="xf-auth__aviso"><?php echo esc_html( $tp_erro ); ?></div>
			<?php endif; ?>

			<?php if ( function_exists( 'tikporn_google_botao' ) ) { tikporn_google_botao(); } ?>

			<form class="xf-auth__form" method="post" action="<?php echo esc_url( site_url( '/entrar/' ) ); ?>">
				<?php wp_nonce_field( 'tikporn_login', 'tikporn_login_nonce' ); ?>

				<label class="xf-campo">
					<span class="xf-campo__label"><?php esc_html_e( 'E-mail ou usuário', 'tikporn' ); ?></span>
					<input type="text" name="usuario" autocomplete="username" required>
				</label>

				<label class="xf-campo">
					<span class="xf-campo__label"><?php esc_html_e( 'Senha', 'tikporn' ); ?></span>
					<input type="password" name="senha" autocomplete="current-password" required>
				</label>

				<div class="xf-auth__linha">
					<label class="xf-check">
						<input type="checkbox" name="lembrar" value="1" checked>
						<span><?php esc_html_e( 'Continuar conectado', 'tikporn' ); ?></span>
					</label>
					<a class="xf-auth__link" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Esqueci a senha', 'tikporn' ); ?></a>
				</div>

				<button class="xf-btn-cheio" type="submit" name="tikporn_login" value="1">
					<?php esc_html_e( 'Entrar', 'tikporn' ); ?>
				</button>
			</form>

			<p class="xf-auth__rodape">
				<?php esc_html_e( 'Não tem conta?', 'tikporn' ); ?>
				<a class="xf-auth__link" href="<?php echo esc_url( site_url( '/cadastro/' ) ); ?>"><?php esc_html_e( 'Criar conta', 'tikporn' ); ?></a>
			</p>
		</div>
	</div>
</div>

<?php
get_footer();
