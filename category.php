<?php
/**
 * Arquivo de categoria — grade dos vídeos do CPT 'video' (layout tube),
 * no mesmo estilo da página de playlist.
 *
 * A query principal é ajustada em tikporn_categoria_lista_videos()
 * (pre_get_posts) para trazer apenas o CPT 'video'.
 *
 * @package tikporn
 */

get_header();

$tp_termo  = get_queried_object();
$tp_nome   = $tp_termo && ! is_wp_error( $tp_termo ) ? $tp_termo->name : single_cat_title( '', false );
$tp_total  = $tp_termo && isset( $tp_termo->count ) ? (int) $tp_termo->count : 0;
$tp_desc   = $tp_termo && ! is_wp_error( $tp_termo ) ? term_description( $tp_termo ) : '';
$tp_capa   = ( $tp_termo && ! is_wp_error( $tp_termo ) && function_exists( 'tikporn_capa_categoria' ) )
	? tikporn_capa_categoria( $tp_termo->term_id )
	: '';
?>

<div class="xf-home">
	<div class="xf-home__main">

		<section class="xf-secao">
			<div class="xf-plhead">
				<div class="xf-plhead__cover<?php echo $tp_capa ? ' xf-plhead__cover--img' : ''; ?>"<?php echo $tp_capa ? ' style="background-image:url(\'' . esc_url( $tp_capa ) . '\')"' : ''; ?>>
					<?php if ( ! $tp_capa ) : ?>
						<svg viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M3 6h13v2H3V6zm0 4h13v2H3v-2zm0 4h9v2H3v-2zm12 0 6 3-6 3v-6z"/></svg>
					<?php endif; ?>
					<span class="xf-plhead__cover-badge" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="#fff"><path d="M3 6h13v2H3V6zm0 4h13v2H3v-2zm0 4h9v2H3v-2zm12 0 6 3-6 3v-6z"/></svg>
					</span>
				</div>
				<div class="xf-plhead__info">
					<span class="xf-plhead__label"><?php esc_html_e( 'Categoria', 'tikporn' ); ?></span>
					<h1 class="xf-plhead__title"><?php if ( function_exists( 'tikporn_icone_playlist_titulo' ) ) { tikporn_icone_playlist_titulo(); } ?><?php echo esc_html( $tp_nome ); ?></h1>
					<div class="xf-plhead__meta">
						<?php echo esc_html( sprintf( _n( '%d vídeo', '%d vídeos', $tp_total, 'tikporn' ), $tp_total ) ); ?>
					</div>
					<?php if ( $tp_desc ) : ?>
						<div class="xf-plhead__desc" data-ver-mais>
							<div class="xf-plhead__desc-texto"><?php echo esc_html( wp_strip_all_tags( $tp_desc ) ); ?></div>
							<button type="button" class="xf-plhead__desc-mais" data-ver-mais-btn>
								<span class="xf-vm-abrir"><?php esc_html_e( 'Ver mais', 'tikporn' ); ?></span>
								<span class="xf-vm-fechar"><?php esc_html_e( 'Ver menos', 'tikporn' ); ?></span>
							</button>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( have_posts() ) : ?>
				<?php global $wp_query; $tp_pag = max( 1, get_query_var( 'paged' ) ); ?>
				<div class="xf-grade"<?php if ( function_exists( 'tikporn_grade_attrs' ) ) { tikporn_grade_attrs( array( 'tipo' => 'termo', 'tax' => $tp_termo && ! is_wp_error( $tp_termo ) ? $tp_termo->taxonomy : 'category', 'term' => $tp_termo && ! is_wp_error( $tp_termo ) ? $tp_termo->term_id : 0, 'qtd' => (int) $wp_query->get( 'posts_per_page' ), 'pagina' => $tp_pag, 'tem_mais' => $wp_query->max_num_pages > $tp_pag ? 1 : 0 ) ); } ?>>
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/card-grade' );
					endwhile;
					?>
				</div>
			<?php else : ?>
				<div class="xf-vazio"><p><?php esc_html_e( 'Nenhum vídeo nesta categoria ainda.', 'tikporn' ); ?></p></div>
			<?php endif; ?>
		</section>
	</div>

	<?php get_template_part( 'template-parts/sidebar-categorias' ); ?>
</div>

<?php
get_footer();
