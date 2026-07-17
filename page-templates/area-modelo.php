<?php
/**
 * Template Name: Área da modelo
 * Painel da modelo: enviar vídeo, ver/excluir vídeos e editar o perfil.
 *
 * @package tikporn
 */

// Precisa estar logado.
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( site_url( '/entrar/' ) );
	exit;
}

// Precisa ser modelo.
if ( ! tikporn_eh_modelo() ) {
	get_header();
	?>
	<div class="xf-bloqueio">
		<div class="xf-bloqueio__card">
			<span class="xf-bloqueio__ico" aria-hidden="true">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
			</span>
			<h1 class="xf-bloqueio__titulo"><?php esc_html_e( 'Área exclusiva de modelos', 'tikporn' ); ?></h1>
			<p class="xf-bloqueio__texto"><?php esc_html_e( 'Sua conta é de usuário comum. Para publicar vídeos, cadastre-se como modelo.', 'tikporn' ); ?></p>
			<a class="xf-btn" href="<?php echo esc_url( site_url( '/cadastro/' ) ); ?>"><?php esc_html_e( 'Criar conta de modelo', 'tikporn' ); ?></a>
			<a class="xf-bloqueio__voltar" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '‹ Voltar para o início', 'tikporn' ); ?></a>
		</div>
	</div>
	<?php
	get_footer();
	return;
}

get_header();

$tp_uid  = get_current_user_id();
$tp_erro = tikporn_pegar_mensagem();
?>

<div class="xf-painel">

	<div class="xf-secao__cab">
		<h1 class="xf-secao__titulo"><?php esc_html_e( 'Área da modelo', 'tikporn' ); ?></h1>
		<a class="xf-conta__perfil-link" href="<?php echo esc_url( tikporn_url_perfil( $tp_uid ) ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
			<span><?php esc_html_e( 'Ver meu perfil público', 'tikporn' ); ?></span>
			<svg class="xf-conta__perfil-seta" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
		</a>
	</div>

	<?php if ( $tp_erro ) : ?>
		<div class="xf-aviso xf-aviso--erro"><?php echo esc_html( $tp_erro ); ?></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['enviado'] ) ) : ?>
		<div class="xf-aviso xf-aviso--ok"><?php esc_html_e( 'Vídeo enviado com sucesso!', 'tikporn' ); ?></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['excluido'] ) ) : ?>
		<div class="xf-aviso xf-aviso--ok"><?php esc_html_e( 'Vídeo excluído.', 'tikporn' ); ?></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['perfil'] ) ) : ?>
		<div class="xf-aviso xf-aviso--ok"><?php esc_html_e( 'Perfil atualizado.', 'tikporn' ); ?></div>
	<?php endif; ?>

	<div class="xf-painel__grid">

		<!-- Enviar novo vídeo -->
		<section class="xf-painel__bloco" id="enviar">
			<h2 class="xf-painel__subtitulo"><?php esc_html_e( 'Enviar novo vídeo', 'tikporn' ); ?></h2>
			<form class="xf-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( site_url( '/area-modelo/' ) ); ?>">
				<?php wp_nonce_field( 'tikporn_enviar_video', 'tikporn_video_nonce' ); ?>

				<label class="xf-form__campo">
					<span><?php esc_html_e( 'Título', 'tikporn' ); ?></span>
					<input type="text" name="titulo" required maxlength="140">
				</label>

				<label class="xf-form__campo">
					<span><?php esc_html_e( 'Legenda', 'tikporn' ); ?></span>
					<textarea name="legenda" rows="3" placeholder="<?php esc_attr_e( 'Descreva o vídeo…', 'tikporn' ); ?>"></textarea>
				</label>

				<div class="xf-form__campo">
					<span><?php esc_html_e( 'Arquivo de vídeo (MP4)', 'tikporn' ); ?></span>
					<div class="xf-arquivo">
						<label class="xf-arquivo__btn">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
							<?php esc_html_e( 'Escolher vídeo', 'tikporn' ); ?>
							<input type="file" name="arquivo_video" accept="video/mp4,video/*" hidden data-arquivo>
						</label>
						<span class="xf-arquivo__nome" data-arquivo-nome><?php esc_html_e( 'Nenhum arquivo escolhido', 'tikporn' ); ?></span>
					</div>
				</div>

				<label class="xf-form__campo">
					<span><?php esc_html_e( 'Ou informe um link (opcional)', 'tikporn' ); ?></span>
					<input type="url" name="link_video" placeholder="https://...">
				</label>

				<div class="xf-form__campo">
					<span><?php esc_html_e( 'Capa (imagem em pé)', 'tikporn' ); ?></span>
					<div class="xf-arquivo">
						<label class="xf-arquivo__btn">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
							<?php esc_html_e( 'Escolher capa', 'tikporn' ); ?>
							<input type="file" name="capa" accept="image/*" hidden data-arquivo>
						</label>
						<span class="xf-arquivo__nome" data-arquivo-nome><?php esc_html_e( 'JPG ou PNG em pé (9:16)', 'tikporn' ); ?></span>
					</div>
				</div>

				<button class="xf-btn xf-form__salvar" type="submit" name="tikporn_enviar_video" value="1">
					<?php esc_html_e( 'Publicar vídeo', 'tikporn' ); ?>
				</button>
			</form>
		</section>

		<!-- Editar perfil -->
		<section class="xf-painel__bloco">
			<h2 class="xf-painel__subtitulo"><?php esc_html_e( 'Meu perfil', 'tikporn' ); ?></h2>
			<form class="xf-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( site_url( '/area-modelo/' ) ); ?>">
				<?php wp_nonce_field( 'tikporn_salvar_perfil', 'tikporn_perfil_nonce' ); ?>

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
					<input type="text" name="nome_publico" value="<?php echo esc_attr( get_the_author_meta( 'display_name', $tp_uid ) ); ?>" maxlength="80">
				</label>

				<label class="xf-form__campo">
					<span><?php esc_html_e( 'Biografia', 'tikporn' ); ?></span>
					<textarea name="biografia" rows="3" placeholder="<?php esc_attr_e( 'Conte algo sobre você…', 'tikporn' ); ?>"><?php echo esc_textarea( get_the_author_meta( 'description', $tp_uid ) ); ?></textarea>
				</label>

				<button class="xf-btn xf-form__salvar" type="submit" name="tikporn_salvar_perfil" value="1">
					<?php esc_html_e( 'Salvar perfil', 'tikporn' ); ?>
				</button>
			</form>
		</section>

	</div>

	<!-- Meus vídeos -->
	<section class="xf-painel__bloco xf-painel__bloco--cheio">
		<h2 class="xf-painel__subtitulo"><?php esc_html_e( 'Meus vídeos', 'tikporn' ); ?></h2>
		<?php
		$tp_meus = new WP_Query(
			array(
				'post_type'      => 'video',
				'author'         => $tp_uid,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			)
		);
		?>
		<?php if ( $tp_meus->have_posts() ) : ?>
			<div class="xf-mv">
				<?php
				while ( $tp_meus->have_posts() ) :
					$tp_meus->the_post();
					$tp_v_id = get_the_ID();
					?>
					<div class="xf-mv__item">
						<a class="xf-mv__thumb" href="<?php the_permalink(); ?>"<?php echo has_post_thumbnail() ? ' style="background-image:url(\'' . esc_url( get_the_post_thumbnail_url( $tp_v_id, 'tikporn_miniatura' ) ) . '\')"' : ''; ?>></a>
						<div class="xf-mv__dados">
							<a class="xf-mv__titulo" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							<span class="xf-mv__stats">
								<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg> <?php echo esc_html( tikporn_numero_k( tikporn_views( $tp_v_id ) ) ); ?></span>
								<span><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg> <?php echo esc_html( tikporn_numero_k( tikporn_curtidas( $tp_v_id ) ) ); ?></span>
							</span>
						</div>
						<form method="post" action="<?php echo esc_url( site_url( '/area-modelo/' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Excluir este vídeo?', 'tikporn' ) ); ?>');">
							<?php wp_nonce_field( 'tikporn_excluir_video', 'tikporn_excluir_nonce' ); ?>
							<input type="hidden" name="video_id" value="<?php echo esc_attr( $tp_v_id ); ?>">
							<button class="xf-btn-sm xf-btn-sm--danger" type="submit" name="tikporn_excluir_video" value="1"><?php esc_html_e( 'Excluir', 'tikporn' ); ?></button>
						</form>
					</div>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<div class="xf-vazio"><p><?php esc_html_e( 'Você ainda não enviou nenhum vídeo.', 'tikporn' ); ?></p></div>
		<?php endif; ?>
	</section>

</div>

<?php
get_footer();
