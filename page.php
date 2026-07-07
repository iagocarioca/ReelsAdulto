<?php
/**
 * Template genérico de página.
 *
 * @package tikporn
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="tp-pagina">
		<h1 class="tp-pagina-titulo"><?php the_title(); ?></h1>
		<div class="tp-pagina-conteudo"><?php the_content(); ?></div>
	</article>
	<?php
endwhile;

get_footer();
