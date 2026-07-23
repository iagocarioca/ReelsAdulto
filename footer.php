<?php
/**
 * Rodapé do tema — layout tube ("xfree").
 *
 * @package tikporn
 */

?>
	</main>

	<footer class="xf-rodape">
		<div class="xf-rodape__inner">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="xf-logo xf-logo--rodape">
				<img src="<?php echo esc_url( TIKPORN_URI . '/assets/img/logo.png?v=' . TIKPORN_VERSION ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			</a>

			<?php
			// Links legais: só entram os que existem (páginas publicadas).
			$tp_legais = array(
				'categorias' => __( 'Categorias', 'tikporn' ),
				'dmca'       => __( 'DMCA', 'tikporn' ),
				'usc-2257'   => __( '2257', 'tikporn' ),
				'contato'    => __( 'Contato', 'tikporn' ),
			);
			$tp_links = array();
			foreach ( $tp_legais as $tp_slug => $tp_rotulo ) {
				$tp_pg = get_page_by_path( $tp_slug );
				if ( $tp_pg && 'publish' === $tp_pg->post_status ) {
					$tp_links[] = sprintf(
						'<a href="%s">%s</a>',
						esc_url( get_permalink( $tp_pg ) ),
						esc_html( $tp_rotulo )
					);
				}
			}
			if ( $tp_links ) :
				?>
				<nav class="xf-rodape__links" aria-label="<?php esc_attr_e( 'Links institucionais', 'tikporn' ); ?>">
					<?php echo implode( '', $tp_links ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado acima ?>
				</nav>
			<?php endif; ?>

			<p class="xf-rodape__nota">
				<?php
				printf(
					/* translators: %s: ano atual */
					esc_html__( '© %s — Todos os direitos reservados. Conteúdo destinado a maiores de 18 anos.', 'tikporn' ),
					esc_html( gmdate( 'Y' ) )
				);
				?>
			</p>
		</div>
	</footer>
</div><!-- .xf-app -->

<?php wp_footer(); ?>
</body>
</html>
