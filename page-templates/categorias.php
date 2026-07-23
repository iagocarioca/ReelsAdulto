<?php
/**
 * Template Name: Categorias
 * Todas as categorias com capa e contagem — pensada para o mobile
 * (a aba lateral só cabe algumas), mas funciona bem no desktop também.
 *
 * @package tikporn
 */

get_header();

$tp_cats = get_terms(
	array(
		'taxonomy'   => TIKPORN_TAX_CAT,
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
$tp_cats = is_wp_error( $tp_cats ) ? array() : $tp_cats;

// Ignora a categoria padrão ("Sem categoria").
$tp_padrao = (int) get_option( 'default_category' );
$tp_cats   = array_values(
	array_filter(
		$tp_cats,
		function ( $t ) use ( $tp_padrao ) {
			return (int) $t->term_id !== $tp_padrao;
		}
	)
);
?>

<div class="xf-home">
	<div class="xf-home__main">
		<section class="xf-secao">
			<div class="xf-secao__cab">
				<h1 class="xf-secao__titulo xf-secao__titulo--icone">
					<?php if ( function_exists( 'tikporn_icone_playlist_titulo' ) ) { tikporn_icone_playlist_titulo( 'xf-secao__ico xf-secao__ico--pl' ); } ?>
					<span><?php echo esc_html( get_the_title() ); ?></span>
				</h1>
				<span class="xf-secao__link"><?php echo esc_html( sprintf( _n( '%d categoria', '%d categorias', count( $tp_cats ), 'tikporn' ), count( $tp_cats ) ) ); ?></span>
			</div>

			<?php if ( ! empty( $tp_cats ) ) : ?>
				<div class="xf-catgrade">
					<?php foreach ( $tp_cats as $tp_cat ) :
						$tp_capa = tikporn_capa_categoria( $tp_cat->term_id );
						?>
						<a class="xf-catcard" href="<?php echo esc_url( get_term_link( $tp_cat ) ); ?>">
							<span class="xf-catcard__thumb"<?php echo $tp_capa ? ' style="background-image:url(\'' . esc_url( $tp_capa ) . '\')"' : ''; ?>></span>
							<span class="xf-catcard__info">
								<span class="xf-catcard__nome"><?php echo esc_html( $tp_cat->name ); ?></span>
								<span class="xf-catcard__qtd"><?php echo esc_html( sprintf( _n( '%d vídeo', '%d vídeos', (int) $tp_cat->count, 'tikporn' ), (int) $tp_cat->count ) ); ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="xf-vazio"><p><?php esc_html_e( 'Nenhuma categoria com vídeos ainda.', 'tikporn' ); ?></p></div>
			<?php endif; ?>
		</section>
	</div>

	<?php get_template_part( 'template-parts/sidebar-categorias' ); ?>
</div>

<?php
get_footer();
