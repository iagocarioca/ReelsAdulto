<?php
/**
 * Página de uma playlist — grade dos vídeos (layout tube).
 * Privada: só o dono acessa (garantido por tikporn_proteger_playlist_privada).
 *
 * @package tikporn
 */

get_header();

while ( have_posts() ) :
	the_post();

	$tp_id     = get_the_ID();
	$tp_dono   = tikporn_playlist_dono( $tp_id );
	$tp_publica = tikporn_playlist_e_publica( $tp_id );
	$tp_pode   = tikporn_pode_editar_playlist( $tp_id );
	$tp_videos = tikporn_playlist_videos( $tp_id );
	$tp_nome   = get_the_author_meta( 'display_name', $tp_dono );
	?>

	<div class="xf-home">
		<div class="xf-home__main">

			<section class="xf-secao">
				<div class="xf-plhead">
					<?php $c = tikporn_playlist_capa_url( $tp_id ); ?>
					<div class="xf-plhead__cover<?php echo $c ? ' xf-plhead__cover--img' : ''; ?>"<?php echo $c ? ' style="background-image:url(\'' . esc_url( $c ) . '\')"' : ''; ?>>
						<?php if ( ! $c ) : ?>
							<svg viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M3 6h13v2H3V6zm0 4h13v2H3v-2zm0 4h9v2H3v-2zm12 0 6 3-6 3v-6z"/></svg>
						<?php endif; ?>
						<span class="xf-plhead__cover-badge" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="#fff"><path d="M3 6h13v2H3V6zm0 4h13v2H3v-2zm0 4h9v2H3v-2zm12 0 6 3-6 3v-6z"/></svg>
						</span>
					</div>
					<div class="xf-plhead__info">
						<span class="xf-plhead__label">
							<?php echo $tp_publica ? esc_html__( 'Playlist pública', 'tikporn' ) : esc_html__( 'Playlist privada', 'tikporn' ); ?>
						</span>
						<h1 class="xf-plhead__title"><?php if ( function_exists( 'tikporn_icone_playlist_titulo' ) ) { tikporn_icone_playlist_titulo(); } ?><?php the_title(); ?></h1>
						<div class="xf-plhead__meta">
							<a href="<?php echo esc_url( tikporn_url_perfil( $tp_dono ) ); ?>"><?php echo esc_html( $tp_nome ); ?></a>
							· <?php echo esc_html( sprintf( _n( '%d vídeo', '%d vídeos', count( $tp_videos ), 'tikporn' ), count( $tp_videos ) ) ); ?>
						</div>

						<?php if ( $tp_pode ) : ?>
							<div class="xf-plhead__acoes" data-playlist-manage data-playlist-id="<?php echo esc_attr( $tp_id ); ?>">
								<button type="button" class="xf-btn-sm" data-pl-visibilidade data-publica="<?php echo $tp_publica ? '1' : '0'; ?>">
									<?php echo $tp_publica ? esc_html__( 'Tornar privada', 'tikporn' ) : esc_html__( 'Tornar pública', 'tikporn' ); ?>
								</button>
								<button type="button" class="xf-btn-sm xf-btn-sm--danger" data-pl-excluir>
									<?php esc_html_e( 'Excluir', 'tikporn' ); ?>
								</button>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( ! empty( $tp_videos ) ) : ?>
					<div class="xf-grade"<?php if ( function_exists( 'tikporn_grade_attrs' ) ) { tikporn_grade_attrs( array( 'tipo' => 'playlist', 'playlist' => $tp_id, 'qtd' => 24, 'tem_mais' => count( $tp_videos ) > 24 ? 1 : 0 ) ); } ?>>
						<?php
						$tp_q = new WP_Query(
							array(
								'post_type'      => 'video',
								'post__in'       => $tp_videos,
								'orderby'        => 'post__in',
								'posts_per_page' => 24,
								'no_found_rows'  => true,
							)
						);
						while ( $tp_q->have_posts() ) :
							$tp_q->the_post();
							get_template_part( 'template-parts/card-grade' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				<?php else : ?>
					<div class="xf-vazio"><p><?php esc_html_e( 'Esta playlist ainda está vazia.', 'tikporn' ); ?></p></div>
				<?php endif; ?>
			</section>
		</div>

		<?php get_template_part( 'template-parts/sidebar-categorias' ); ?>
	</div>

	<?php
endwhile;

get_footer();
