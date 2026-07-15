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
						<a class="xf-secao__link" href="<?php echo esc_url( site_url( '/playlists/' ) ); ?>">
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
					<span class="xf-secao__ico xf-secao__ico--pl" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.50989 2.00001H15.49C15.7225 1.99995 15.9007 1.99991 16.0565 2.01515C17.1643 2.12352 18.0711 2.78958 18.4556 3.68678H5.54428C5.92879 2.78958 6.83555 2.12352 7.94337 2.01515C8.09917 1.99991 8.27741 1.99995 8.50989 2.00001Z" fill="currentColor"/><path d="M6.31052 4.72312C4.91989 4.72312 3.77963 5.56287 3.3991 6.67691C3.39117 6.70013 3.38356 6.72348 3.37629 6.74693C3.77444 6.62636 4.18881 6.54759 4.60827 6.49382C5.68865 6.35531 7.05399 6.35538 8.64002 6.35547H15.5321C17.1181 6.35538 18.4835 6.35531 19.5639 6.49382C19.9833 6.54759 20.3977 6.62636 20.7958 6.74693C20.7886 6.72348 20.781 6.70013 20.773 6.67691C20.3925 5.56287 19.2522 4.72312 17.8616 4.72312H6.31052Z" fill="currentColor"/><path fill-rule="evenodd" clip-rule="evenodd" d="M15.3276 7.54204H8.67239C5.29758 7.54204 3.61017 7.54204 2.66232 8.52887C1.71447 9.5157 1.93748 11.0403 2.38351 14.0896L2.80648 16.9811C3.15626 19.3724 3.33115 20.568 4.22834 21.284C5.12553 22 6.4488 22 9.09534 22H14.9046C17.5512 22 18.8745 22 19.7717 21.284C20.6689 20.568 20.8437 19.3724 21.1935 16.9811L21.6165 14.0896C22.0625 11.0404 22.2855 9.51569 21.3377 8.52887C20.3898 7.54204 18.7024 7.54204 15.3276 7.54204ZM14.5812 15.7942C15.1396 15.4481 15.1396 14.5519 14.5812 14.2058L11.2096 12.1156C10.6669 11.7792 10 12.2171 10 12.9099V17.0901C10 17.7829 10.6669 18.2208 11.2096 17.8844L14.5812 15.7942Z" fill="currentColor"/></svg>
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
