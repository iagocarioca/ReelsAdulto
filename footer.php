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
