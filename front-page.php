<?php
/**
 * Página inicial — layout tube ("xfree"):
 * fileira de playlists + grade de tendências + sidebar de categorias.
 *
 * @package tikporn
 */

get_header();

// Textos e quantidades configuráveis (Aparência → Opções do tema).
$tp_titulo_playlists  = tikporn_opcao( 'home_playlists_titulo' );
$tp_link_playlists    = tikporn_opcao( 'home_playlists_link' );
$tp_titulo_tendencias = tikporn_opcao( 'home_tendencias_titulo' );
$tp_qtd_playlists     = (int) tikporn_opcao( 'home_qtd_playlists' );
$tp_qtd_videos        = (int) tikporn_opcao( 'home_qtd_videos' );
$tp_qtd_categorias    = (int) tikporn_opcao( 'home_qtd_categorias' );

// ── Playlists (mapeadas nas categorias com mais vídeos) ──────────────
$tp_playlists = $tp_qtd_playlists > 0 ? get_terms(
	array(
		'taxonomy'   => TIKPORN_TAX_CAT,
		'hide_empty' => true,
		'number'     => $tp_qtd_playlists,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
) : array();
$tp_playlists = is_wp_error( $tp_playlists ) ? array() : $tp_playlists;

// ── Grade de tendências (vídeos mais recentes, com scroll infinito) ──
$tp_grade = new WP_Query(
	array(
		'post_type'      => 'video',
		'post_status'    => 'publish',
		'posts_per_page' => max( 1, $tp_qtd_videos ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

// ── Categorias (chips da sidebar) ────────────────────────────────────
$tp_cats = $tp_qtd_categorias > 0 ? get_terms(
	array(
		'taxonomy'   => TIKPORN_TAX_CAT,
		'hide_empty' => false,
		'number'     => $tp_qtd_categorias,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
) : array();
$tp_cats = is_wp_error( $tp_cats ) ? array() : $tp_cats;

?>

<div class="xf-home">

	<div class="xf-home__main">

		<?php if ( ! empty( $tp_playlists ) ) : ?>
			<section class="xf-secao">
				<div class="xf-secao__cab">
					<h2 class="xf-secao__titulo xf-secao__titulo--icone">
						<?php if ( function_exists( 'tikporn_icone_playlist_titulo' ) ) { tikporn_icone_playlist_titulo( 'xf-secao__ico xf-secao__ico--pl' ); } ?>
						<span><?php echo esc_html( $tp_titulo_playlists ); ?></span>
					</h2>
					<?php if ( '' !== $tp_link_playlists ) : ?>
						<a class="xf-secao__link" href="<?php echo esc_url( get_post_type_archive_link( 'video' ) ); ?>">
							<?php echo esc_html( $tp_link_playlists ); ?> &rsaquo;
						</a>
					<?php endif; ?>
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
				<h2 class="xf-secao__titulo xf-secao__titulo--icone">
					<span class="xf-secao__ico" aria-hidden="true">
						<svg viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet"><path d="M113.69 45.18l-11.26 4.32l-22.34 49.17l-36.13 24.59s24.87 2.06 44.2-11.07s29.09-30.22 32.29-44.11c3.19-13.89 1.69-18.02 1.69-18.02l-8.45-4.88z" fill="#dc0d28"/><path d="M82.53 36.17s-7.13.94-10.89 13.51S69.95 72.2 61.5 83.09S45.36 98.11 31.85 95.48c-18.46-3.59-17.27-17.1-22.9-17.83c-4.32-.56-6.95 13.33-.38 24.4s19.33 19.71 33.22 21.02c13.89 1.31 38.1-6.57 51.62-27.41S108.62 60 109 50.62S96.42 36.35 96.42 36.35l-13.89-.18z" fill="#ff5117"/><path d="M86.1 49.92c-3.1-2.39-7.04 1.27-8.59 7.04c-1.55 5.77-1.69 10.7-2.53 13.51s-3.1 6.76-.14 8.45c2.96 1.69 5.91-3.8 7.04-8.02s2.11-10 3.66-12.81s3-6.29.56-8.17z" fill="#ffcb88"/><path d="M72.77 82.53c-3.19-1.13-3.85 1.6-4.41 2.44s-1.6 2.91.19 4.04c1.99 1.26 3.57-.19 4.5-1.6c.94-1.41 1.54-4.24-.28-4.88z" fill="#ffcb88"/><path d="M85.06 38.61c2.35 2.25-.94 4.5.75 5.82c1.99 1.55 7.6-1.6 12.2-.09s4.41 6.95 7.79 7.32c3.38.38 3.28-2.82 7.88-2.16c4.6.66 6.76 6.66 9.01 6.01s1.03-8.07-4.41-13.23c-4.34-4.11-7.23-4.97-7.51-6.95c-.28-1.97 1.78-11.07 2.44-15.58c.66-4.5.47-10.7-5.16-13.51c-5.63-2.82-10.42-.19-11.36 1.41c-1.14 1.93-.73 4.64 4.04 2.82c4.41-1.69 7.41 2.35 6.85 6.29s-3.57 9.57-5.35 11.83s-2.72 3.57-4.04 3.85c-1.31.28-5.63-.47-9.67-.28c-4.81.22-9.85 2.63-9.2 4.6s3.91.09 5.74 1.85z" fill="#98b71e"/></svg>
					</span>
					<span><?php echo esc_html( $tp_titulo_tendencias ); ?></span>
				</h2>
			</div>

			<?php if ( $tp_grade->have_posts() ) : ?>
				<div class="xf-grade"<?php if ( function_exists( 'tikporn_grade_attrs' ) ) { tikporn_grade_attrs( array( 'tipo' => 'home', 'qtd' => max( 1, $tp_qtd_videos ), 'tem_mais' => $tp_grade->max_num_pages > 1 ? 1 : 0 ) ); } ?>>
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
