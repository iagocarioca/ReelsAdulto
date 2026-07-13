<?php
/**
 * Template Name: Entrar
 * Página de login — tela dividida (ilustração | formulário).
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
	<div class="xf-auth__hero">
		<div class="xf-auth__hero-inner">
			<h2 class="xf-auth__hero-titulo">
				<?php
				printf(
					/* translators: %s: destaque colorido */
					esc_html__( 'Os melhores vídeos %s.', 'tikporn' ),
					'<span>' . esc_html__( 'num só lugar', 'tikporn' ) . '</span>'
				);
				?>
			</h2>
			<img class="xf-auth__hero-img" src="<?php echo esc_url( TIKPORN_URI . '/assets/img/login.png?v=' . TIKPORN_VERSION ); ?>" alt="" loading="eager" decoding="async">
		</div>
	</div>

	<div class="xf-auth__col">
		<div class="xf-auth__box">
			<h1 class="xf-auth__titulo"><?php esc_html_e( 'Bem-vindo de volta', 'tikporn' ); ?></h1>
			<p class="xf-auth__desc"><?php esc_html_e( 'Entre na sua conta para continuar.', 'tikporn' ); ?></p>

			<?php if ( $tp_erro ) : ?>
				<div class="xf-auth__aviso"><?php echo esc_html( $tp_erro ); ?></div>
			<?php endif; ?>

			<?php if ( function_exists( 'tikporn_google_botao' ) ) { tikporn_google_botao(); } ?>

			<form class="xf-auth__form" method="post" action="<?php echo esc_url( site_url( '/entrar/' ) ); ?>">
				<?php wp_nonce_field( 'tikporn_login', 'tikporn_login_nonce' ); ?>
				<input type="text" name="usuario" class="xf-auth__input" placeholder="<?php esc_attr_e( 'E-mail ou nome de usuário', 'tikporn' ); ?>" autocomplete="username" required>
				<input type="password" name="senha" class="xf-auth__input" placeholder="<?php esc_attr_e( 'Senha', 'tikporn' ); ?>" autocomplete="current-password" required>
				<input type="hidden" name="lembrar" value="1">
				<button class="xf-btn-cheio" type="submit" name="tikporn_login" value="1"><?php esc_html_e( 'Entrar', 'tikporn' ); ?></button>
			</form>

			<a class="xf-auth__esqueci" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Esqueceu a senha?', 'tikporn' ); ?></a>

			<p class="xf-auth__rodape">
				<?php esc_html_e( 'Não tem uma conta?', 'tikporn' ); ?>
				<a class="xf-auth__link" href="<?php echo esc_url( site_url( '/cadastro/' ) ); ?>"><?php esc_html_e( 'Cadastre-se', 'tikporn' ); ?></a>
			</p>
		</div>
	</div>
</div>

<?php
get_footer();
