<?php
/**
 * Template Name: Página legal (DMCA, 2257, Contato…)
 * Página de texto corrido, largura de leitura confortável.
 *
 * @package tikporn
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="xf-legal">
		<header class="xf-legal__cab">
			<h1 class="xf-legal__titulo"><?php the_title(); ?></h1>
			<?php if ( get_the_modified_date() ) : ?>
				<p class="xf-legal__data">
					<?php echo esc_html( sprintf( __( 'Atualizado em %s', 'tikporn' ), get_the_modified_date( 'd/m/Y' ) ) ); ?>
				</p>
			<?php endif; ?>
		</header>

		<div class="xf-legal__conteudo">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
