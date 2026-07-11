<?php
/**
 * Sidebar de Categorias (layout tube). Reutilizada na home, playlists e perfis.
 * Chips com expandir/recolher + card de vídeo em destaque.
 *
 * @package tikporn
 */

$tp_limite = 15; // chips visíveis antes de "Mais categorias".

$tp_cats = get_terms(
	array(
		'taxonomy'   => TIKPORN_TAX_CAT,
		'hide_empty' => false,
		'number'     => 40,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
$tp_cats = is_wp_error( $tp_cats ) ? array() : $tp_cats;
$tp_tem_mais = count( $tp_cats ) > $tp_limite;

// Vídeo em destaque: mais visto; cai pro mais recente se não houver métrica.
$tp_dest = new WP_Query(
	array(
		'post_type'      => 'video',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_key'       => '_tikporn_views',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	)
);
if ( ! $tp_dest->have_posts() ) {
	$tp_dest = new WP_Query(
		array(
			'post_type'      => 'video',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);
}
?>
<aside class="xf-sidebar<?php echo $tp_tem_mais ? '' : ' is-expanded'; ?>" data-sidebar>

	<div class="xf-sidebar__cab">
		<h2 class="xf-sidebar__titulo"><?php esc_html_e( 'Categorias', 'tikporn' ); ?></h2>
	</div>

	<?php if ( ! empty( $tp_cats ) ) : ?>
		<div class="xf-chips" style="--limite: <?php echo (int) $tp_limite; ?>;">
			<?php foreach ( $tp_cats as $tp_cat ) : ?>
				<a class="xf-chip" href="<?php echo esc_url( get_term_link( $tp_cat ) ); ?>"><?php echo esc_html( $tp_cat->name ); ?></a>
			<?php endforeach; ?>
		</div>

		<?php if ( $tp_tem_mais ) : ?>
			<button type="button" class="xf-mais-cats" data-cats-toggle aria-expanded="false">
				<span class="xf-mais-cats__mais"><?php esc_html_e( 'Mais categorias', 'tikporn' ); ?></span>
				<span class="xf-mais-cats__menos"><?php esc_html_e( 'Menos categorias', 'tikporn' ); ?></span>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
			</button>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	if ( $tp_dest->have_posts() ) :
		$tp_dest->the_post();
		$tp_d_id   = get_the_ID();
		$tp_d_capa = tikporn_capa_url( $tp_d_id );
		?>
		<div class="xf-destaque">
			<span class="xf-destaque__label"><?php esc_html_e( 'Em destaque', 'tikporn' ); ?></span>
			<a class="xf-destaque__card" href="<?php the_permalink(); ?>">
				<span class="xf-destaque__thumb"<?php echo $tp_d_capa ? ' style="background-image:url(\'' . esc_url( $tp_d_capa ) . '\')"' : ''; ?>>
					<span class="xf-destaque__play" aria-hidden="true"><svg viewBox="0 0 24 24" fill="#fff"><path d="M8 5v14l11-7z"/></svg></span>
					<span class="xf-destaque__views">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
						<?php echo esc_html( tikporn_numero_k( tikporn_views( $tp_d_id ) ) ); ?>
					</span>
				</span>
				<span class="xf-destaque__titulo"><?php echo esc_html( wp_trim_words( get_the_title(), 10 ) ); ?></span>
			</a>
		</div>
		<?php wp_reset_postdata(); ?>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/sidebar-criadores' ); ?>

</aside>
