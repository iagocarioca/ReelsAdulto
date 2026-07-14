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
					<button type="button" class="xf-btn xf-btn--sm" data-pl-nova-abrir>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
						<?php esc_html_e( 'Nova playlist', 'tikporn' ); ?>
					</button>
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
								<span class="xf-plcard__thumb<?php echo $tp_capa ? '' : ' xf-plcard__thumb--vazia'; ?>"<?php echo $tp_capa ? ' style="background-image:url(\'' . esc_url( $tp_capa ) . '\')"' : ''; ?>>
									<?php if ( ! $tp_capa ) : ?>
										<span class="xf-plcard__ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h13v2H3V6zm0 4h13v2H3v-2zm0 4h9v2H3v-2zm12 0 6 3-6 3v-6z"/></svg></span>
									<?php endif; ?>
									<span class="xf-plcard__badge">
										<?php if ( $tp_pub ) : ?>
											<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 0 20 15.3 15.3 0 0 1 0-20z"/></svg><?php esc_html_e( 'Pública', 'tikporn' ); ?>
										<?php else : ?>
											<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><?php esc_html_e( 'Privada', 'tikporn' ); ?>
										<?php endif; ?>
									</span>
									<span class="xf-plcard__count">
										<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
										<?php echo esc_html( sprintf( _n( '%d vídeo', '%d vídeos', $tp_qtd, 'tikporn' ), $tp_qtd ) ); ?>
									</span>
								</span>
								<span class="xf-plcard__title"><?php echo esc_html( get_post_field( 'post_title', $tp_pl ) ); ?></span>
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
