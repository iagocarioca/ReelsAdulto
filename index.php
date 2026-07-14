<?php
/**
 * Arquivos, categoria, busca — grade tube ("xfree").
 *
 * @package tikporn
 */

get_header();

global $wp_query;
$tp_eh_termo = is_tax() || is_category() || is_tag();

// Título contextual (para busca / arquivo geral).
if ( is_search() ) {
	$tp_titulo = sprintf( __( 'Resultados para: %s', 'tikporn' ), get_search_query() );
} elseif ( is_post_type_archive( 'video' ) ) {
	$tp_titulo = __( 'Todos os vídeos', 'tikporn' );
} else {
	$tp_titulo = get_the_archive_title();
}
?>

<div class="xf-home">
	<div class="xf-home__main">
		<section class="xf-secao">

			<?php if ( $tp_eh_termo ) :
				// Cabeçalho estilo playlist (capa + título grande + contagem).
				$tp_termo = get_queried_object();
				$tp_qtd   = (int) $wp_query->found_posts;
				$tp_capa  = '';
				$tp_cq    = new WP_Query(
					array(
						'post_type'      => 'video',
						'posts_per_page' => 1,
						'no_found_rows'  => true,
						'tax_query'      => array(
							array( 'taxonomy' => $tp_termo->taxonomy, 'field' => 'term_id', 'terms' => $tp_termo->term_id ),
						),
						'meta_query'     => array( array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ) ),
					)
				);
				if ( $tp_cq->have_posts() ) {
					$tp_cq->the_post();
					$tp_capa = tikporn_capa_url( get_the_ID(), 'tikporn_miniatura' );
					wp_reset_postdata();
				}
				?>
				<div class="xf-plhead">
					<div class="xf-plhead__cover<?php echo $tp_capa ? ' xf-plhead__cover--img' : ''; ?>"<?php echo $tp_capa ? ' style="background-image:url(\'' . esc_url( $tp_capa ) . '\')"' : ''; ?>>
						<?php if ( ! $tp_capa ) : ?>
							<svg viewBox="0 0 30 30" fill="#fff" aria-hidden="true"><path d="M4 5C3.446 5 3 5.446 3 6 3 6.554 3.446 7 4 7L19 7C19.554 7 20 6.554 20 6 20 5.446 19.554 5 19 5L4 5zM4 12C3.446 12 3 12.446 3 13 3 13.554 3.446 14 4 14L22 14C22.554 14 23 13.554 23 13 23 12.446 22.554 12 22 12L4 12zM21.949 17.004C21.606 17.004 21.272 17.037 20.949 17.104L20.949 20.955 17.1 20.955C17.034 21.278 17 21.612 17 21.955 17 22.298 17.034 22.632 17.1 22.955L20.949 22.955 20.949 26.805C21.272 26.871 21.606 26.904 21.949 26.904 22.292 26.904 22.626 26.871 22.949 26.805L22.949 22.955 26.801 22.955C26.867 22.632 26.9 22.298 26.9 21.955 26.9 21.612 26.867 21.278 26.801 20.955L22.949 20.955 22.949 17.104C22.626 17.037 22.292 17.004 21.949 17.004zM4 19C3.446 19 3 19.446 3 20 3 20.554 3.446 21 4 21L14 21C14.554 21 15 20.554 15 20 15 19.446 14.554 19 14 19L4 19z"/></svg>
						<?php endif; ?>
						<span class="xf-plhead__cover-badge" aria-hidden="true">
							<svg viewBox="0 0 30 30" fill="#fff"><path d="M4 5C3.446 5 3 5.446 3 6 3 6.554 3.446 7 4 7L19 7C19.554 7 20 6.554 20 6 20 5.446 19.554 5 19 5L4 5zM4 12C3.446 12 3 12.446 3 13 3 13.554 3.446 14 4 14L22 14C22.554 14 23 13.554 23 13 23 12.446 22.554 12 22 12L4 12zM21.949 17.004C21.606 17.004 21.272 17.037 20.949 17.104L20.949 20.955 17.1 20.955C17.034 21.278 17 21.612 17 21.955 17 22.298 17.034 22.632 17.1 22.955L20.949 22.955 20.949 26.805C21.272 26.871 21.606 26.904 21.949 26.904 22.292 26.904 22.626 26.871 22.949 26.805L22.949 22.955 26.801 22.955C26.867 22.632 26.9 22.298 26.9 21.955 26.9 21.612 26.867 21.278 26.801 20.955L22.949 20.955 22.949 17.104C22.626 17.037 22.292 17.004 21.949 17.004zM4 19C3.446 19 3 19.446 3 20 3 20.554 3.446 21 4 21L14 21C14.554 21 15 20.554 15 20 15 19.446 14.554 19 14 19L4 19z"/></svg>
						</span>
					</div>
					<div class="xf-plhead__info">
						<span class="xf-plhead__label"><?php esc_html_e( 'Playlist', 'tikporn' ); ?></span>
						<h1 class="xf-plhead__title"><?php if ( function_exists( 'tikporn_icone_playlist_titulo' ) ) { tikporn_icone_playlist_titulo(); } ?><?php echo esc_html( $tp_termo->name ); ?></h1>
						<div class="xf-plhead__meta"><?php echo esc_html( sprintf( _n( '%d vídeo', '%d vídeos', $tp_qtd, 'tikporn' ), $tp_qtd ) ); ?></div>
						<?php if ( ! empty( $tp_termo->description ) ) : ?>
							<p class="xf-plhead__desc"><?php echo esc_html( $tp_termo->description ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php else : ?>
				<div class="xf-secao__cab">
					<h1 class="xf-secao__titulo"><?php echo esc_html( $tp_titulo ); ?></h1>
				</div>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<?php
				// Scroll infinito: identifica o contexto da listagem.
				$tp_pag = max( 1, get_query_var( 'paged' ) );
				$tp_inf = array(
					'qtd'      => (int) $wp_query->get( 'posts_per_page' ),
					'pagina'   => $tp_pag,
					'tem_mais' => $wp_query->max_num_pages > $tp_pag ? 1 : 0,
				);
				if ( is_search() ) {
					$tp_inf['tipo']  = 'busca';
					$tp_inf['busca'] = get_search_query();
				} elseif ( $tp_eh_termo ) {
					$tp_obj          = get_queried_object();
					$tp_inf['tipo']  = 'termo';
					$tp_inf['tax']   = $tp_obj->taxonomy;
					$tp_inf['term']  = $tp_obj->term_id;
				} else {
					$tp_inf['tipo'] = 'arquivo';
				}
				?>
				<div class="xf-grade"<?php if ( function_exists( 'tikporn_grade_attrs' ) ) { tikporn_grade_attrs( $tp_inf ); } ?>>
					<?php
					while ( have_posts() ) :
						the_post();
						if ( 'video' === get_post_type() ) {
							get_template_part( 'template-parts/card-grade' );
						} else {
							?>
							<a class="xf-card" href="<?php the_permalink(); ?>">
								<div class="xf-card__thumb"><span class="xf-card__semcapa"></span></div>
								<span class="xf-card__titulo"><?php echo esc_html( wp_trim_words( get_the_title(), 8 ) ); ?></span>
							</a>
							<?php
						}
					endwhile;
					?>
				</div>
			<?php else : ?>
				<div class="xf-vazio"><p><?php esc_html_e( 'Nada encontrado.', 'tikporn' ); ?></p></div>
			<?php endif; ?>
		</section>
	</div>

	<?php get_template_part( 'template-parts/sidebar-categorias' ); ?>
</div>

<?php
get_footer();
