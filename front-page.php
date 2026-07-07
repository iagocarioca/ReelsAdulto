<?php
/**
 * Página inicial — layout tube ("xfree"):
 * fileira de playlists + grade de tendências + sidebar de categorias.
 *
 * @package tikporn
 */

get_header();

// ── Playlists (mapeadas nas categorias com mais vídeos) ──────────────
$tp_playlists = get_terms(
	array(
		'taxonomy'   => 'categoria_video',
		'hide_empty' => true,
		'number'     => 4,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
$tp_playlists = is_wp_error( $tp_playlists ) ? array() : $tp_playlists;

// ── Grade de tendências (vídeos mais recentes) ───────────────────────
$tp_grade = new WP_Query(
	array(
		'post_type'      => 'video',
		'post_status'    => 'publish',
		'posts_per_page' => 18,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	)
);

// ── Categorias (chips da sidebar) ────────────────────────────────────
$tp_cats = get_terms(
	array(
		'taxonomy'   => 'categoria_video',
		'hide_empty' => false,
		'number'     => 24,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
$tp_cats = is_wp_error( $tp_cats ) ? array() : $tp_cats;

/** Capa de uma categoria: thumbnail do vídeo mais recente dela. */
function tikporn_capa_categoria( $term_id ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'video',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'categoria_video',
					'field'    => 'term_id',
					'terms'    => (int) $term_id,
				),
			),
			'meta_query'     => array(
				array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ),
			),
		)
	);
	$url = '';
	if ( $q->have_posts() ) {
		$q->the_post();
		$url = tikporn_capa_url( get_the_ID(), 'tikporn_miniatura' );
	}
	wp_reset_postdata();
	return $url;
}
?>

<div class="xf-home">

	<div class="xf-home__main">

		<?php if ( ! empty( $tp_playlists ) ) : ?>
			<section class="xf-secao">
				<div class="xf-secao__cab">
					<h2 class="xf-secao__titulo"><?php esc_html_e( 'Playlist &amp; Chill', 'tikporn' ); ?></h2>
					<a class="xf-secao__link" href="<?php echo esc_url( get_post_type_archive_link( 'video' ) ); ?>">
						<?php esc_html_e( 'Todas as Playlists', 'tikporn' ); ?> &rsaquo;
					</a>
				</div>

				<div class="xf-playlists">
					<?php foreach ( $tp_playlists as $tp_pl ) :
						$tp_capa = tikporn_capa_categoria( $tp_pl->term_id );
						?>
						<a class="xf-playlist" href="<?php echo esc_url( get_term_link( $tp_pl ) ); ?>">
							<div class="xf-playlist__thumb"<?php echo $tp_capa ? ' style="background-image:url(\'' . esc_url( $tp_capa ) . '\')"' : ''; ?>>
								<span class="xf-playlist__stack" aria-hidden="true">
									<svg viewBox="0 0 30 30" fill="currentColor"><path d="M4 5C3.446 5 3 5.446 3 6 3 6.554 3.446 7 4 7L19 7C19.554 7 20 6.554 20 6 20 5.446 19.554 5 19 5L4 5zM4 12C3.446 12 3 12.446 3 13 3 13.554 3.446 14 4 14L22 14C22.554 14 23 13.554 23 13 23 12.446 22.554 12 22 12L4 12zM21.949 17.004C21.606 17.004 21.272 17.037 20.949 17.104L20.949 20.955 17.1 20.955C17.034 21.278 17 21.612 17 21.955 17 22.298 17.034 22.632 17.1 22.955L20.949 22.955 20.949 26.805C21.272 26.871 21.606 26.904 21.949 26.904 22.292 26.904 22.626 26.871 22.949 26.805L22.949 22.955 26.801 22.955C26.867 22.632 26.9 22.298 26.9 21.955 26.9 21.612 26.867 21.278 26.801 20.955L22.949 20.955 22.949 17.104C22.626 17.037 22.292 17.004 21.949 17.004zM4 19C3.446 19 3 19.446 3 20 3 20.554 3.446 21 4 21L14 21C14.554 21 15 20.554 15 20 15 19.446 14.554 19 14 19L4 19z"/></svg>
									<b><?php echo esc_html( tikporn_numero_k( (int) $tp_pl->count ) ); ?></b>
								</span>
								<span class="xf-playlist__play" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
								</span>
							</div>
							<span class="xf-playlist__titulo"><?php echo esc_html( $tp_pl->name ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<section class="xf-secao">
			<div class="xf-secao__cab">
				<h2 class="xf-secao__titulo"><?php esc_html_e( 'Tendências porno móvel e vídeos de sexo', 'tikporn' ); ?></h2>
			</div>

			<?php if ( $tp_grade->have_posts() ) : ?>
				<div class="xf-grade">
					<?php
					while ( $tp_grade->have_posts() ) :
						$tp_grade->the_post();
						get_template_part( 'template-parts/card-grade' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			<?php else : ?>
				<div class="xf-vazio">
					<p><?php esc_html_e( 'Ainda não há vídeos por aqui.', 'tikporn' ); ?></p>
					<?php if ( tikporn_eh_modelo() ) : ?>
						<a class="xf-btn" href="<?php echo esc_url( site_url( '/area-modelo/#enviar' ) ); ?>">
							<?php esc_html_e( 'Enviar o primeiro vídeo', 'tikporn' ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</section>

	</div>

	<?php get_template_part( 'template-parts/sidebar-categorias' ); ?>

</div>

<?php
get_footer();
