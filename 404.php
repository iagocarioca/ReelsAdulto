<?php
/**
 * Página de erro 404.
 *
 * @package tikporn
 */

get_header();
?>
<div class="tp-vazio">
	<h1>404</h1>
	<p><?php esc_html_e( 'Página não encontrada.', 'tikporn' ); ?></p>
	<a class="tp-botao" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Voltar ao início', 'tikporn' ); ?></a>
</div>
<?php
get_footer();
