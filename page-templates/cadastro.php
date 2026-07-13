<?php
/**
 * Template Name: Cadastro
 * Página de cadastro — layout split (branding + formulário).
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
			<h2 class="xf-auth__slogan"><?php esc_html_e( 'Faça parte da comunidade.', 'tikporn' ); ?></h2>
			<p class="xf-auth__sub"><?php esc_html_e( 'Crie sua conta em segundos e comece a montar suas playlists.', 'tikporn' ); ?></p>
		</div>
	</div>

	<div class="xf-auth__form-wrap">
		<div class="xf-auth__card">
			<h1 class="xf-auth__titulo"><?php esc_html_e( 'Criar conta', 'tikporn' ); ?></h1>
			<p class="xf-auth__desc"><?php esc_html_e( 'É rápido e grátis.', 'tikporn' ); ?></p>

			<?php if ( $tp_erro ) : ?>
				<div class="xf-auth__aviso"><?php echo esc_html( $tp_erro ); ?></div>
			<?php endif; ?>

			<?php if ( function_exists( 'tikporn_google_botao' ) ) { tikporn_google_botao(); } ?>

			<form class="xf-auth__form" method="post" action="<?php echo esc_url( site_url( '/cadastro/' ) ); ?>">
				<?php wp_nonce_field( 'tikporn_cadastro', 'tikporn_cadastro_nonce' ); ?>

				<label class="xf-campo">
					<span class="xf-campo__label"><?php esc_html_e( 'Nome de usuário', 'tikporn' ); ?></span>
					<input type="text" name="usuario" autocomplete="username" required>
				</label>

				<label class="xf-campo">
					<span class="xf-campo__label"><?php esc_html_e( 'E-mail', 'tikporn' ); ?></span>
					<input type="email" name="email" autocomplete="email" required>
				</label>

				<label class="xf-campo">
					<span class="xf-campo__label"><?php esc_html_e( 'Senha', 'tikporn' ); ?></span>
					<input type="password" name="senha" autocomplete="new-password" minlength="6" required>
					<span class="xf-campo__ajuda"><?php esc_html_e( 'Mínimo de 6 caracteres.', 'tikporn' ); ?></span>
				</label>

				<label class="xf-check">
					<input type="checkbox" name="quero_ser_modelo" value="1">
					<span><?php esc_html_e( 'Quero me cadastrar como modelo', 'tikporn' ); ?></span>
				</label>

				<button class="xf-btn-cheio" type="submit" name="tikporn_cadastro" value="1">
					<?php esc_html_e( 'Criar conta', 'tikporn' ); ?>
				</button>

				<p class="xf-auth__nota">
					<?php esc_html_e( 'Ao criar a conta você declara ter 18 anos ou mais e concorda com os termos de uso.', 'tikporn' ); ?>
				</p>
			</form>

			<p class="xf-auth__rodape">
				<?php esc_html_e( 'Já tem conta?', 'tikporn' ); ?>
				<a class="xf-auth__link" href="<?php echo esc_url( site_url( '/entrar/' ) ); ?>"><?php esc_html_e( 'Entrar', 'tikporn' ); ?></a>
			</p>
		</div>
	</div>
</div>

<?php
get_footer();
