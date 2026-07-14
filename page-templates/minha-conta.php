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
			<a class="xf-conta__perfil-link" href="<?php echo esc_url( tikporn_url_perfil( $tp_uid ) ); ?>"><?php esc_html_e( 'Ver meu perfil público', 'tikporn' ); ?> ›</a>
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
			<label class="xf-form__campo">
				<span><?php esc_html_e( 'Nome público', 'tikporn' ); ?></span>
				<input type="text" name="nome_publico" value="<?php echo esc_attr( $tp_nome ); ?>" maxlength="80">
			</label>
			<label class="xf-form__campo">
				<span><?php esc_html_e( 'Bio', 'tikporn' ); ?></span>
				<textarea name="biografia" rows="3" maxlength="300"><?php echo esc_textarea( $tp_bio ); ?></textarea>
			</label>
			<label class="xf-form__campo">
				<span><?php esc_html_e( 'Foto de perfil', 'tikporn' ); ?></span>
				<input type="file" name="foto" accept="image/*">
			</label>
			<label class="xf-form__campo">
				<span><?php esc_html_e( 'Site', 'tikporn' ); ?></span>
				<input type="url" name="link_site" value="<?php echo esc_attr( get_user_meta( $tp_uid, 'tikporn_link_site', true ) ); ?>" placeholder="https://seusite.com">
			</label>
			<label class="xf-form__campo">
				<span>X (Twitter)</span>
				<input type="url" name="link_x" value="<?php echo esc_attr( get_user_meta( $tp_uid, 'tikporn_link_x', true ) ); ?>" placeholder="https://x.com/usuario">
			</label>
			<label class="xf-form__campo">
				<span>TikTok</span>
				<input type="url" name="link_tiktok" value="<?php echo esc_attr( get_user_meta( $tp_uid, 'tikporn_link_tiktok', true ) ); ?>" placeholder="https://tiktok.com/@usuario">
			</label>
			<label class="xf-form__campo">
				<span>Instagram</span>
				<input type="url" name="link_instagram" value="<?php echo esc_attr( get_user_meta( $tp_uid, 'tikporn_link_instagram', true ) ); ?>" placeholder="https://instagram.com/usuario">
			</label>
			<button type="submit" name="tikporn_salvar_conta" value="1" class="xf-btn"><?php esc_html_e( 'Salvar', 'tikporn' ); ?></button>
		</form>
	</section>

</div>

<?php
get_footer();
