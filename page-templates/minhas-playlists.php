<?php
/**
 * Template de página: Minhas playlists.
 *
 * @package tikporn
 */

get_header();
?>

<div class="xf-home">
	<div class="xf-home__main">
		<section class="xf-secao">

			<div class="xf-secao__cab">
				<h1 class="xf-secao__titulo"><?php esc_html_e( 'Minhas playlists', 'tikporn' ); ?></h1>
				<?php if ( is_user_logged_in() ) : ?>
					<button type="button" class="xf-secao__link" data-pl-nova-abrir><?php esc_html_e( '+ Nova playlist', 'tikporn' ); ?></button>
				<?php endif; ?>
			</div>

			<?php if ( ! is_user_logged_in() ) : ?>
				<div class="xf-vazio">
					<p><?php esc_html_e( 'Entre para criar e ver suas playlists.', 'tikporn' ); ?></p>
					<a class="xf-btn" href="<?php echo esc_url( site_url( '/entrar/' ) ); ?>"><?php esc_html_e( 'Entrar', 'tikporn' ); ?></a>
				</div>
			<?php else : ?>

				<!-- Form de nova playlist (oculto por padrão) -->
				<form class="xf-pl-nova" data-pl-nova hidden>
					<input type="text" name="titulo" class="xf-pl-nova__campo" placeholder="<?php esc_attr_e( 'Nome da playlist', 'tikporn' ); ?>" maxlength="80" required>
					<label class="xf-pl-nova__check">
						<input type="checkbox" name="publica"> <?php esc_html_e( 'Pública', 'tikporn' ); ?>
					</label>
					<button type="submit" class="xf-btn xf-btn--sm"><?php esc_html_e( 'Criar', 'tikporn' ); ?></button>
				</form>

				<?php
				$tp_playlists = tikporn_user_playlists( get_current_user_id(), true );
				if ( ! empty( $tp_playlists ) ) :
					?>
					<div class="xf-grade xf-grade--pl" data-pl-lista>
						<?php foreach ( $tp_playlists as $tp_pl ) :
							$tp_capa = tikporn_playlist_capa_url( $tp_pl->ID );
							$tp_pub  = 'publish' === $tp_pl->post_status;
							$tp_qtd  = count( tikporn_playlist_videos( $tp_pl->ID ) );
							?>
							<a class="xf-plcard" href="<?php echo esc_url( get_permalink( $tp_pl->ID ) ); ?>">
								<span class="xf-plcard__thumb"<?php echo $tp_capa ? ' style="background-image:url(\'' . esc_url( $tp_capa ) . '\')"' : ''; ?>>
									<span class="xf-plcard__badge"><?php echo $tp_pub ? esc_html__( 'Pública', 'tikporn' ) : esc_html__( 'Privada', 'tikporn' ); ?></span>
									<span class="xf-plcard__count"><?php echo esc_html( $tp_qtd ); ?> <?php esc_html_e( 'vídeos', 'tikporn' ); ?></span>
								</span>
								<span class="xf-plcard__title"><?php echo esc_html( get_the_title( $tp_pl ) ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="xf-vazio" data-pl-vazio><p><?php esc_html_e( 'Você ainda não tem playlists. Crie a primeira!', 'tikporn' ); ?></p></div>
				<?php endif; ?>

			<?php endif; ?>

		</section>
	</div>

	<?php get_template_part( 'template-parts/sidebar-categorias' ); ?>
</div>

<?php
get_footer();
