<?php
/**
 * Vídeos relacionados no single (layout tube).
 * Prioriza a mesma categoria; completa com recentes. Exclui o vídeo atual.
 *
 * @package tikporn
 */

$tp_id   = get_the_ID();
$tp_terms = wp_get_post_terms( $tp_id, TIKPORN_TAX_CAT, array( 'fields' => 'ids' ) );

$tp_args = array(
	'post_type'           => 'video',
	'post_status'         => 'publish',
	'posts_per_page'      => 12,
	'post__not_in'        => array( $tp_id ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
	'orderby'             => 'date',
	'order'               => 'DESC',
);

if ( ! empty( $tp_terms ) && ! is_wp_error( $tp_terms ) ) {
	$tp_args['tax_query'] = array(
		array(
			'taxonomy' => TIKPORN_TAX_CAT,
			'field'    => 'term_id',
			'terms'    => $tp_terms,
		),
	);
}

$tp_bare = ! empty( $args['bare'] );

$tp_rel = new WP_Query( $tp_args );
if ( $tp_rel->have_posts() ) :
	if ( ! $tp_bare ) :
		?>
		<section class="xf-secao xf-single__rel">
			<div class="xf-secao__cab">
				<h2 class="xf-secao__titulo"><?php esc_html_e( 'Vídeos relacionados', 'tikporn' ); ?></h2>
			</div>
		<?php endif; ?>

		<div class="xf-grade <?php echo $tp_bare ? 'xf-grade--panel' : ''; ?>">
			<?php
			while ( $tp_rel->have_posts() ) :
				$tp_rel->the_post();
				get_template_part( 'template-parts/card-grade' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>

		<?php if ( ! $tp_bare ) : ?>
		</section>
		<?php
	endif;
endif;
