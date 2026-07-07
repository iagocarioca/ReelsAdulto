<?php
/**
 * Template Name: Cadastro
 * Página de cadastro de novos usuários (e modelos).
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

<div class="tp-auth">
	<h1 class="tp-auth-titulo"><?php esc_html_e( 'Criar conta', 'tikporn' ); ?></h1>

	<?php if ( $tp_erro ) : ?>
		<div class="tp-aviso tp-aviso-erro"><?php echo esc_html( $tp_erro ); ?></div>
	<?php endif; ?>

	<form class="tp-form" method="post" action="<?php echo esc_url( site_url( '/cadastro/' ) ); ?>">
		<?php wp_nonce_field( 'tikporn_cadastro', 'tikporn_cadastro_nonce' ); ?>

		<label class="tp-campo">
			<span><?php esc_html_e( 'Nome de usuário', 'tikporn' ); ?></span>
			<input type="text" name="usuario" autocomplete="username" required>
		</label>

		<label class="tp-campo">
			<span><?php esc_html_e( 'E-mail', 'tikporn' ); ?></span>
			<input type="email" name="email" autocomplete="email" required>
		</label>

		<label class="tp-campo">
			<span><?php esc_html_e( 'Senha (mínimo 6 caracteres)', 'tikporn' ); ?></span>
			<input type="password" name="senha" autocomplete="new-password" minlength="6" required>
		</label>

		<label class="tp-check">
			<input type="checkbox" name="quero_ser_modelo" value="1">
			<span><?php esc_html_e( 'Quero me cadastrar como modelo', 'tikporn' ); ?></span>
		</label>

		<p class="tp-nota">
			<?php esc_html_e( 'Ao criar a conta você declara ter 18 anos ou mais e concorda com os termos de uso.', 'tikporn' ); ?>
		</p>

		<button class="tp-botao tp-botao-cheio" type="submit" name="tikporn_cadastro" value="1">
			<?php esc_html_e( 'Cadastrar', 'tikporn' ); ?>
		</button>
	</form>

	<div class="tp-auth-rodape">
		<span><?php esc_html_e( 'Já tem conta?', 'tikporn' ); ?></span>
		<a href="<?php echo esc_url( site_url( '/entrar/' ) ); ?>"><?php esc_html_e( 'Entrar', 'tikporn' ); ?></a>
	</div>
</div>

<?php
get_footer();
