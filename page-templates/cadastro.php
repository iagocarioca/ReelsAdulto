<?php
/**
 * Template Name: Cadastro
 * Página de cadastro — tela dividida (ilustração | formulário).
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
					esc_html__( 'Faça parte da %s.', 'tikporn' ),
					'<span>' . esc_html__( 'comunidade', 'tikporn' ) . '</span>'
				);
				?>
			</h2>
			<img class="xf-auth__hero-img" src="<?php echo esc_url( TIKPORN_URI . '/assets/img/login.png?v=' . TIKPORN_VERSION ); ?>" alt="" loading="eager" decoding="async">
		</div>
	</div>

	<div class="xf-auth__col">
		<div class="xf-auth__box">
			<h1 class="xf-auth__titulo"><?php esc_html_e( 'Criar conta', 'tikporn' ); ?></h1>
			<p class="xf-auth__desc"><?php esc_html_e( 'É rápido e grátis.', 'tikporn' ); ?></p>

			<?php if ( $tp_erro ) : ?>
				<div class="xf-auth__aviso"><?php echo esc_html( $tp_erro ); ?></div>
			<?php endif; ?>

			<?php if ( function_exists( 'tikporn_google_botao' ) ) { tikporn_google_botao(); } ?>

			<form class="xf-auth__form" method="post" action="<?php echo esc_url( site_url( '/cadastro/' ) ); ?>">
				<?php wp_nonce_field( 'tikporn_cadastro', 'tikporn_cadastro_nonce' ); ?>
				<input type="email" name="email" class="xf-auth__input" placeholder="<?php esc_attr_e( 'E-mail', 'tikporn' ); ?>" autocomplete="email" required>
				<input type="text" name="usuario" class="xf-auth__input" placeholder="<?php esc_attr_e( 'Nome de usuário', 'tikporn' ); ?>" autocomplete="username" required>
				<input type="password" name="senha" class="xf-auth__input" placeholder="<?php esc_attr_e( 'Senha (mínimo 6 caracteres)', 'tikporn' ); ?>" autocomplete="new-password" minlength="6" required>

				<label class="xf-check xf-auth__check">
					<input type="checkbox" name="quero_ser_modelo" value="1">
					<span><?php esc_html_e( 'Quero me cadastrar como modelo', 'tikporn' ); ?></span>
				</label>

				<p class="xf-auth__nota">
					<?php esc_html_e( 'Ao se cadastrar, você declara ter 18 anos ou mais e concorda com os termos de uso.', 'tikporn' ); ?>
				</p>

				<button class="xf-btn-cheio" type="submit" name="tikporn_cadastro" value="1"><?php esc_html_e( 'Cadastrar', 'tikporn' ); ?></button>
			</form>

			<p class="xf-auth__rodape">
				<?php esc_html_e( 'Já tem uma conta?', 'tikporn' ); ?>
				<a class="xf-auth__link" href="<?php echo esc_url( site_url( '/entrar/' ) ); ?>"><?php esc_html_e( 'Entrar', 'tikporn' ); ?></a>
			</p>
		</div>
	</div>
</div>

<?php
get_footer();
