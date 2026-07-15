<?php
/**
 * Template de página: Minha conta (dashboard do usuário logado).
 *
 * @package tikporn
 */

get_header();

if ( ! is_user_logged_in() ) :
	?>
	<div class="xf-conteudo-simples">
		<div class="xf-vazio">
			<p><?php esc_html_e( 'Entre para acessar sua conta.', 'tikporn' ); ?></p>
			<a class="xf-btn" href="<?php echo esc_url( site_url( '/entrar/' ) ); ?>"><?php esc_html_e( 'Entrar', 'tikporn' ); ?></a>
		</div>
	</div>
	<?php
	get_footer();
	return;
endif;

$tp_uid   = get_current_user_id();
$tp_nome  = get_the_author_meta( 'display_name', $tp_uid );
$tp_bio   = get_the_author_meta( 'description', $tp_uid );
$tp_pls   = tikporn_user_playlists( $tp_uid, true );
$tp_likes = tikporn_curtidos_do_usuario( $tp_uid );
$tp_seg   = tikporn_seguindo_do_usuario( $tp_uid );
?>

<div class="xf-conta">

	<header class="xf-conta__cab">
		<span class="xf-conta__avatar"><?php echo tikporn_foto_perfil( $tp_uid, 84 ); // phpcs:ignore ?></span>
		<div class="xf-conta__id">
			<h1 class="xf-conta__nome"><?php echo esc_html( $tp_nome ); ?></h1>
			<div class="xf-conta__stats">
				<span><strong><?php echo esc_html( count( $tp_pls ) ); ?></strong> <?php esc_html_e( 'playlists', 'tikporn' ); ?></span>
				<span><strong><?php echo esc_html( count( $tp_likes ) ); ?></strong> <?php esc_html_e( 'curtidos', 'tikporn' ); ?></span>
				<span><strong><?php echo esc_html( count( $tp_seg ) ); ?></strong> <?php esc_html_e( 'seguindo', 'tikporn' ); ?></span>
			</div>
			<a class="xf-conta__perfil-link" href="<?php echo esc_url( tikporn_url_perfil( $tp_uid ) ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
				<span><?php esc_html_e( 'Ver meu perfil público', 'tikporn' ); ?></span>
				<svg class="xf-conta__perfil-seta" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
			</a>
		</div>
	</header>

	<!-- Navegação por abas (âncoras) -->
	<nav class="xf-conta__nav">
		<a href="#playlists" class="is-active"><?php esc_html_e( 'Playlists', 'tikporn' ); ?></a>
		<a href="#curtidos"><?php esc_html_e( 'Curtidos', 'tikporn' ); ?></a>
		<a href="#seguindo"><?php esc_html_e( 'Seguindo', 'tikporn' ); ?></a>
		<a href="#perfil"><?php esc_html_e( 'Editar perfil', 'tikporn' ); ?></a>
	</nav>

	<!-- Playlists -->
	<section class="xf-conta__sec" id="playlists" data-conta-sec>
		<div class="xf-secao__cab">
			<h2 class="xf-secao__titulo"><?php esc_html_e( 'Minhas playlists', 'tikporn' ); ?></h2>
			<a class="xf-secao__link" href="<?php echo esc_url( site_url( '/minhas-playlists/' ) ); ?>"><?php esc_html_e( 'Gerenciar', 'tikporn' ); ?> ›</a>
		</div>
		<?php if ( ! empty( $tp_pls ) ) : ?>
			<div class="xf-grade xf-grade--pl">
				<?php foreach ( $tp_pls as $tp_pl ) :
					$tp_capa = tikporn_playlist_capa_url( $tp_pl->ID );
					$tp_pub  = 'publish' === $tp_pl->post_status;
					?>
					<a class="xf-plcard" href="<?php echo esc_url( get_permalink( $tp_pl->ID ) ); ?>">
						<span class="xf-plcard__thumb"<?php echo $tp_capa ? ' style="background-image:url(\'' . esc_url( $tp_capa ) . '\')"' : ''; ?>>
							<span class="xf-plcard__badge"><?php echo $tp_pub ? esc_html__( 'Pública', 'tikporn' ) : esc_html__( 'Privada', 'tikporn' ); ?></span>
							<span class="xf-plcard__count"><?php echo esc_html( count( tikporn_playlist_videos( $tp_pl->ID ) ) ); ?></span>
						</span>
						<span class="xf-plcard__title"><?php echo esc_html( get_post_field( 'post_title', $tp_pl ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="xf-vazio"><p><?php esc_html_e( 'Nenhuma playlist ainda.', 'tikporn' ); ?></p></div>
		<?php endif; ?>
	</section>

	<!-- Curtidos -->
	<section class="xf-conta__sec" id="curtidos" data-conta-sec hidden>
		<div class="xf-secao__cab"><h2 class="xf-secao__titulo"><?php esc_html_e( 'Vídeos curtidos', 'tikporn' ); ?></h2></div>
		<?php if ( ! empty( $tp_likes ) ) : ?>
			<div class="xf-grade">
				<?php
				$q = new WP_Query( array( 'post_type' => 'video', 'post__in' => $tp_likes, 'orderby' => 'post__in', 'posts_per_page' => 60, 'no_found_rows' => true ) );
				while ( $q->have_posts() ) : $q->the_post();
					get_template_part( 'template-parts/card-grade' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<div class="xf-vazio"><p><?php esc_html_e( 'Você ainda não curtiu vídeos.', 'tikporn' ); ?></p></div>
		<?php endif; ?>
	</section>

	<!-- Seguindo -->
	<section class="xf-conta__sec" id="seguindo" data-conta-sec hidden>
		<div class="xf-secao__cab"><h2 class="xf-secao__titulo"><?php esc_html_e( 'Seguindo', 'tikporn' ); ?></h2></div>
		<?php if ( ! empty( $tp_seg ) ) : ?>
			<div class="xf-seguindo">
				<?php foreach ( $tp_seg as $mid ) : ?>
					<a class="xf-seguindo__item" href="<?php echo esc_url( tikporn_url_perfil( $mid ) ); ?>">
						<span class="xf-seguindo__foto"><?php echo tikporn_foto_perfil( $mid, 56 ); // phpcs:ignore ?></span>
						<span class="xf-seguindo__nome"><?php echo esc_html( get_the_author_meta( 'display_name', $mid ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="xf-vazio"><p><?php esc_html_e( 'Você ainda não segue ninguém.', 'tikporn' ); ?></p></div>
		<?php endif; ?>
	</section>

	<!-- Editar perfil -->
	<section class="xf-conta__sec" id="perfil" data-conta-sec hidden>
		<div class="xf-secao__cab"><h2 class="xf-secao__titulo"><?php esc_html_e( 'Editar perfil', 'tikporn' ); ?></h2></div>
		<?php if ( isset( $_GET['salvo'] ) ) : ?>
			<p class="xf-aviso xf-aviso--ok"><?php esc_html_e( 'Perfil atualizado!', 'tikporn' ); ?></p>
		<?php endif; ?>
		<form class="xf-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( site_url( '/minha-conta/' ) ); ?>">
			<?php wp_nonce_field( 'tikporn_salvar_conta', 'tikporn_conta_nonce' ); ?>

			<!-- Foto de perfil: preview + botão custom -->
			<div class="xf-arquivo">
				<span class="xf-arquivo__preview"><?php echo tikporn_foto_perfil( $tp_uid, 64 ); // phpcs:ignore ?></span>
				<div class="xf-arquivo__info">
					<label class="xf-arquivo__btn">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
						<?php esc_html_e( 'Trocar foto', 'tikporn' ); ?>
						<input type="file" name="foto" accept="image/*" hidden data-arquivo>
					</label>
					<span class="xf-arquivo__nome" data-arquivo-nome><?php esc_html_e( 'JPG ou PNG, quadrada fica melhor', 'tikporn' ); ?></span>
				</div>
			</div>

			<label class="xf-form__campo">
				<span><?php esc_html_e( 'Nome público', 'tikporn' ); ?></span>
				<input type="text" name="nome_publico" value="<?php echo esc_attr( $tp_nome ); ?>" maxlength="80">
			</label>
			<label class="xf-form__campo">
				<span><?php esc_html_e( 'Bio', 'tikporn' ); ?></span>
				<textarea name="biografia" rows="3" maxlength="300" placeholder="<?php esc_attr_e( 'Conte algo sobre você…', 'tikporn' ); ?>"><?php echo esc_textarea( $tp_bio ); ?></textarea>
			</label>

			<h3 class="xf-form__sec"><?php esc_html_e( 'Links e redes sociais', 'tikporn' ); ?></h3>
			<p class="xf-form__dica"><?php esc_html_e( 'Aparecem no seu perfil público. Deixe em branco o que não quiser mostrar.', 'tikporn' ); ?></p>

			<div class="xf-form__linha">
				<label class="xf-form__campo xf-form__campo--icone">
					<span><?php esc_html_e( 'Site', 'tikporn' ); ?></span>
					<span class="xf-form__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
					<input type="url" name="link_site" value="<?php echo esc_attr( get_user_meta( $tp_uid, 'tikporn_link_site', true ) ); ?>" placeholder="https://seusite.com"></span>
				</label>
				<label class="xf-form__campo xf-form__campo--icone">
					<span>X (Twitter)</span>
					<span class="xf-form__ic"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
					<input type="url" name="link_x" value="<?php echo esc_attr( get_user_meta( $tp_uid, 'tikporn_link_x', true ) ); ?>" placeholder="https://x.com/usuario"></span>
				</label>
			</div>
			<div class="xf-form__linha">
				<label class="xf-form__campo xf-form__campo--icone">
					<span>TikTok</span>
					<span class="xf-form__ic"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
					<input type="url" name="link_tiktok" value="<?php echo esc_attr( get_user_meta( $tp_uid, 'tikporn_link_tiktok', true ) ); ?>" placeholder="https://tiktok.com/@usuario"></span>
				</label>
				<label class="xf-form__campo xf-form__campo--icone">
					<span>Instagram</span>
					<span class="xf-form__ic"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
					<input type="url" name="link_instagram" value="<?php echo esc_attr( get_user_meta( $tp_uid, 'tikporn_link_instagram', true ) ); ?>" placeholder="https://instagram.com/usuario"></span>
				</label>
			</div>

			<button type="submit" name="tikporn_salvar_conta" value="1" class="xf-btn xf-form__salvar"><?php esc_html_e( 'Salvar alterações', 'tikporn' ); ?></button>
		</form>
	</section>

</div>

<?php
get_footer();
