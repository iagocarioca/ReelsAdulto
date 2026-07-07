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
	echo '<div class="tp-auth"><h1 class="tp-auth-titulo">' . esc_html__( 'Área exclusiva de modelos', 'tikporn' ) . '</h1>';
	echo '<p class="tp-nota">' . esc_html__( 'Sua conta é de usuário comum. Para publicar vídeos, cadastre-se como modelo.', 'tikporn' ) . '</p>';
	echo '<a class="tp-botao tp-botao-cheio" href="' . esc_url( site_url( '/cadastro/' ) ) . '">' . esc_html__( 'Criar conta de modelo', 'tikporn' ) . '</a></div>';
	get_footer();
	return;
}

get_header();

$tp_uid  = get_current_user_id();
$tp_erro = tikporn_pegar_mensagem();
?>

<div class="tp-painel">

	<h1 class="tp-painel-titulo"><?php esc_html_e( 'Área da modelo', 'tikporn' ); ?></h1>

	<?php if ( $tp_erro ) : ?>
		<div class="tp-aviso tp-aviso-erro"><?php echo esc_html( $tp_erro ); ?></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['enviado'] ) ) : ?>
		<div class="tp-aviso tp-aviso-ok"><?php esc_html_e( 'Vídeo enviado com sucesso!', 'tikporn' ); ?></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['excluido'] ) ) : ?>
		<div class="tp-aviso tp-aviso-ok"><?php esc_html_e( 'Vídeo excluído.', 'tikporn' ); ?></div>
	<?php endif; ?>
	<?php if ( isset( $_GET['perfil'] ) ) : ?>
		<div class="tp-aviso tp-aviso-ok"><?php esc_html_e( 'Perfil atualizado.', 'tikporn' ); ?></div>
	<?php endif; ?>

	<!-- Enviar novo vídeo -->
	<section class="tp-painel-bloco" id="enviar">
		<h2><?php esc_html_e( 'Enviar novo vídeo', 'tikporn' ); ?></h2>
		<form class="tp-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( site_url( '/area-modelo/' ) ); ?>">
			<?php wp_nonce_field( 'tikporn_enviar_video', 'tikporn_video_nonce' ); ?>

			<label class="tp-campo">
				<span><?php esc_html_e( 'Título', 'tikporn' ); ?></span>
				<input type="text" name="titulo" required>
			</label>

			<label class="tp-campo">
				<span><?php esc_html_e( 'Legenda', 'tikporn' ); ?></span>
				<textarea name="legenda" rows="3"></textarea>
			</label>

			<label class="tp-campo">
				<span><?php esc_html_e( 'Arquivo de vídeo (MP4)', 'tikporn' ); ?></span>
				<input type="file" name="arquivo_video" accept="video/mp4,video/*">
			</label>

			<p class="tp-nota"><?php esc_html_e( 'Ou, em vez do arquivo, informe um link:', 'tikporn' ); ?></p>
			<label class="tp-campo">
				<span><?php esc_html_e( 'Link do vídeo (opcional)', 'tikporn' ); ?></span>
				<input type="url" name="link_video" placeholder="https://...">
			</label>

			<label class="tp-campo">
				<span><?php esc_html_e( 'Capa (imagem em pé)', 'tikporn' ); ?></span>
				<input type="file" name="capa" accept="image/*">
			</label>

			<button class="tp-botao tp-botao-cheio" type="submit" name="tikporn_enviar_video" value="1">
				<?php esc_html_e( 'Publicar vídeo', 'tikporn' ); ?>
			</button>
		</form>
	</section>

	<!-- Editar perfil -->
	<section class="tp-painel-bloco">
		<h2><?php esc_html_e( 'Meu perfil', 'tikporn' ); ?></h2>
		<form class="tp-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( site_url( '/area-modelo/' ) ); ?>">
			<?php wp_nonce_field( 'tikporn_salvar_perfil', 'tikporn_perfil_nonce' ); ?>

			<div class="tp-perfil-foto tp-perfil-foto-pequena">
				<?php echo tikporn_foto_perfil( $tp_uid, 80 ); // phpcs:ignore ?>
			</div>

			<label class="tp-campo">
				<span><?php esc_html_e( 'Nome público', 'tikporn' ); ?></span>
				<input type="text" name="nome_publico" value="<?php echo esc_attr( get_the_author_meta( 'display_name', $tp_uid ) ); ?>">
			</label>

			<label class="tp-campo">
				<span><?php esc_html_e( 'Biografia', 'tikporn' ); ?></span>
				<textarea name="biografia" rows="3"><?php echo esc_textarea( get_the_author_meta( 'description', $tp_uid ) ); ?></textarea>
			</label>

			<label class="tp-campo">
				<span><?php esc_html_e( 'Foto de perfil', 'tikporn' ); ?></span>
				<input type="file" name="foto" accept="image/*">
			</label>

			<button class="tp-botao tp-botao-cheio" type="submit" name="tikporn_salvar_perfil" value="1">
				<?php esc_html_e( 'Salvar perfil', 'tikporn' ); ?>
			</button>
		</form>
		<a class="tp-link" href="<?php echo esc_url( tikporn_url_perfil( $tp_uid ) ); ?>"><?php esc_html_e( 'Ver meu perfil público', 'tikporn' ); ?></a>
	</section>

	<!-- Meus vídeos -->
	<section class="tp-painel-bloco">
		<h2><?php esc_html_e( 'Meus vídeos', 'tikporn' ); ?></h2>
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
			<div class="tp-lista-videos">
				<?php
				while ( $tp_meus->have_posts() ) :
					$tp_meus->the_post();
					?>
					<div class="tp-lista-item">
						<div class="tp-lista-capa">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'tikporn_miniatura' ); ?>
							<?php endif; ?>
						</div>
						<div class="tp-lista-dados">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							<span class="tp-lista-curtidas">&#9829; <?php echo esc_html( tikporn_curtidas( get_the_ID() ) ); ?></span>
						</div>
						<form method="post" action="<?php echo esc_url( site_url( '/area-modelo/' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Excluir este vídeo?', 'tikporn' ) ); ?>');">
							<?php wp_nonce_field( 'tikporn_excluir_video', 'tikporn_excluir_nonce' ); ?>
							<input type="hidden" name="video_id" value="<?php echo esc_attr( get_the_ID() ); ?>">
							<button class="tp-botao tp-botao-perigo" type="submit" name="tikporn_excluir_video" value="1"><?php esc_html_e( 'Excluir', 'tikporn' ); ?></button>
						</form>
					</div>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php else : ?>
			<p class="tp-nota"><?php esc_html_e( 'Você ainda não enviou nenhum vídeo.', 'tikporn' ); ?></p>
		<?php endif; ?>
	</section>

</div>

<?php
get_footer();
