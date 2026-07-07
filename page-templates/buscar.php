<?php
/**
 * Template Name: Buscar
 * Página de busca de vídeos e modelos.
 *
 * @package tikporn
 */

get_header();

$tp_termo = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
?>

<div class="tp-buscar">
	<h1 class="tp-painel-titulo"><?php esc_html_e( 'Buscar', 'tikporn' ); ?></h1>

	<form class="tp-form-busca" method="get" action="<?php echo esc_url( site_url( '/buscar/' ) ); ?>">
		<input type="search" name="q" value="<?php echo esc_attr( $tp_termo ); ?>" placeholder="<?php esc_attr_e( 'Buscar vídeos...', 'tikporn' ); ?>">
		<button class="tp-botao" type="submit"><?php esc_html_e( 'Buscar', 'tikporn' ); ?></button>
	</form>

	<?php if ( $tp_termo ) : ?>
		<?php
		$tp_res = new WP_Query(
			array(
				'post_type'      => 'video',
				'post_status'    => 'publish',
				's'              => $tp_termo,
				'posts_per_page' => 24,
			)
		);
		?>
		<?php if ( $tp_res->have_posts() ) : ?>
			<div class="tp-grade">
				<?php
				while ( $tp_res->have_posts() ) :
					$tp_res->the_post();
					?>
					<a class="tp-grade-item" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'tikporn_miniatura' ); ?>
						<?php else : ?>
							<span class="tp-grade-semcapa"><?php the_title(); ?></span>
						<?php endif; ?>
					</a>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<p class="tp-nota"><?php esc_html_e( 'Nada encontrado para essa busca.', 'tikporn' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>
</div>

<?php
get_footer();
