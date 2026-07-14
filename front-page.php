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
						<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet"><g fill="#ff9d27"><path d="M10.9 48.7c4-4 4.4-5 6.9-2.5s1.5 2.8-2.5 6.9c-3 3-6.8 2.4-6.8 2.4s-.6-3.8 2.4-6.8"/><path d="M18.5 52.8c1.6-4.2 2.1-4.7-.2-6c-2.3-1.3-2.3-.4-3.8 3.8c-1.2 3.1.2 5.9.2 5.9s2.7-.5 3.8-3.7"/></g><path d="M16.2 48.9c.9-2.3.9-2.8 2.1-2.1c1.3.7 1 1 .1 3.3c-.6 1.7-2.1 2.1-2.1 2.1s-.7-1.5-.1-3.3" fill="#fdf516"/><path d="M17.1 45.7c-1.3-2.3-1.8-1.8-6-.2c-3.1 1.2-3.7 3.8-3.7 3.8s2.8 1.4 5.9.2c4.2-1.6 5.1-1.6 3.8-3.8" fill="#ff9d27"/><g fill="#fdf516"><path d="M15 47.8c2.3-.9 2.8-.9 2.1-2.1c-.7-1.3-1-1-3.3-.1c-1.7.6-2.1 2.1-2.1 2.1s1.6.7 3.3.1"/><path d="M13.9 47.6c2.2-2.2 2.4-2.8 3.8-1.4s.8 1.6-1.4 3.8c-1.7 1.7-3.8 1.3-3.8 1.3s-.2-2 1.4-3.7"/></g><path d="M18.5 38C12.3 27.6 2 31.9 2 31.9s14.7-14.7 24.6-4.8L18.5 38z" fill="#3baacf"/><path d="M23.3 30.3l3.2-3.2C16.7 17.2 2 31.9 2 31.9s12.9-9.2 21.3-1.6" fill="#428bc1"/><path d="M26 45.5C36.4 51.7 32.1 62 32.1 62s14.7-14.7 4.8-24.6L26 45.5z" fill="#3baacf"/><path d="M33.7 40.7l3.2-3.2c9.9 9.9-4.8 24.6-4.8 24.6s9.2-13 1.6-21.4" fill="#428bc1"/><path d="M48.8 30.9C37.1 42.5 24.2 48.8 19.7 44.3c-4.5-4.5 1.8-17.4 13.4-29.1c13.6-13.6 28.7-13 28.7-13s.5 15.1-13 28.7" fill="#c5d0d8"/><path d="M45.8 27.6C34.2 39.2 22.6 46.8 19.9 44.1c-2.7-2.7 4.9-14.3 16.5-25.9C50 4.6 62 2 62 2s-2.6 12-16.2 25.6z" fill="#dae3ea"/><path d="M24.3 47.5c-.5.5-1.3.5-1.8 0l-6-6c-.5-.5-.5-1.4 0-1.9l1.8-1.8l7.8 7.8l-1.8 1.9" fill="#c94747"/><path d="M22.6 45.7c-.5.5-1.1.7-1.4.4l-3.4-3.4c-.3-.3-.1-.9.4-1.4l1.8-1.8l4.4 4.4l-1.8 1.8" fill="#f15744"/><path d="M20.9 48.2c-.3.3-1 .3-1.3 0l-3.9-3.9c-.3-.3-.2-.9.1-1.2l1.2-1.2l5.1 5.1l-1.2 1.2" fill="#3e4347"/><path d="M20.1 47.4c-.3.3-.9.4-1.1.2l-2.7-2.7c-.2-.2-.1-.7.3-1l1.2-1.2l3.5 3.5l-1.2 1.2" fill="#62727a"/><path d="M61.8 2.2S56.4 2 49.1 4.8l10.1 10.1C62 7.6 61.8 2.2 61.8 2.2" fill="#c94747"/><path d="M61.8 2.2s-4.3.9-10.8 4.6l6.2 6.2c3.7-6.5 4.6-10.8 4.6-10.8" fill="#f15744"/><circle cx="43.5" cy="20.5" r="5" fill="#edf4f9"/><circle cx="43.5" cy="20.5" r="3.3" fill="#3baacf"/><circle cx="33.5" cy="30.5" r="5" fill="#edf4f9"/><circle cx="33.5" cy="30.5" r="3.3" fill="#3baacf"/><g fill="#ffffff"><path d="M48.9 6.9c-.3.3-.9.3-1.2 0c-.3-.3-.3-.9 0-1.2c.3-.3.9-.3 1.2 0c.3.3.3.9 0 1.2"/><circle cx="50.6" cy="8.6" r=".8"/><circle cx="53" cy="11" r=".8"/><circle cx="55.3" cy="13.4" r=".8"/><circle cx="57.7" cy="15.7" r=".8"/></g></svg>
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
