<?php
/**
 * Perfil público do usuário/modelo (arquivo do autor) — layout tube.
 * Mostra vídeos e as playlists PÚBLICAS da pessoa.
 *
 * @package tikporn
 */

get_header();

$tp_modelo = get_queried_object();
$tp_id     = $tp_modelo ? (int) $tp_modelo->ID : 0;
$tp_nome   = get_the_author_meta( 'display_name', $tp_id );
$tp_bio    = get_the_author_meta( 'description', $tp_id );
$tp_segue  = function_exists( 'tikporn_usuario_segue' ) ? tikporn_usuario_segue( $tp_id ) : false;
$tp_eu     = get_current_user_id() === $tp_id;
$tp_pls    = function_exists( 'tikporn_user_playlists' ) ? tikporn_user_playlists( $tp_id, false ) : array();
?>

<div class="xf-perfil">

	<header class="xf-perfil__cab">
		<span class="xf-perfil__foto"><?php echo tikporn_foto_perfil( $tp_id, 96 ); // phpcs:ignore ?></span>
		<div class="xf-perfil__id">
			<h1 class="xf-perfil__nome"><?php echo esc_html( $tp_nome ); ?></h1>
			<span class="xf-perfil__handle">@<?php echo esc_html( get_the_author_meta( 'user_nicename', $tp_id ) ); ?></span>
			<div class="xf-perfil__stats">
				<span><strong><?php echo esc_html( tikporn_numero_k( tikporn_total_videos( $tp_id ) ) ); ?></strong> <?php esc_html_e( 'vídeos', 'tikporn' ); ?></span>
				<span><strong><?php echo esc_html( tikporn_numero_k( tikporn_seguidores( $tp_id ) ) ); ?></strong> <?php esc_html_e( 'seguidores', 'tikporn' ); ?></span>
			</div>
			<?php if ( $tp_bio ) : ?><p class="xf-perfil__bio"><?php echo esc_html( $tp_bio ); ?></p><?php endif; ?>
		</div>
		<div class="xf-perfil__acao">
			<?php if ( $tp_eu ) : ?>
				<a class="xf-follow" href="<?php echo esc_url( site_url( '/minha-conta/' ) ); ?>"><span><?php esc_html_e( 'Editar perfil', 'tikporn' ); ?></span></a>
			<?php elseif ( is_user_logged_in() ) : ?>
				<button class="xf-follow tp-seguir <?php echo $tp_segue ? 'ativo' : ''; ?>" type="button" data-modelo-id="<?php echo esc_attr( $tp_id ); ?>">
					<span class="tp-acao-legenda"><?php echo $tp_segue ? esc_html__( 'Seguindo', 'tikporn' ) : esc_html__( 'Seguir', 'tikporn' ); ?></span>
				</button>
			<?php else : ?>
				<a class="xf-follow" href="<?php echo esc_url( site_url( '/entrar/' ) ); ?>"><span><?php esc_html_e( 'Seguir', 'tikporn' ); ?></span></a>
			<?php endif; ?>
		</div>
	</header>

	<?php if ( ! empty( $tp_pls ) ) : ?>
		<section class="xf-secao">
			<div class="xf-secao__cab"><h2 class="xf-secao__titulo"><?php esc_html_e( 'Playlists', 'tikporn' ); ?></h2></div>
			<div class="xf-grade xf-grade--pl">
				<?php foreach ( $tp_pls as $tp_pl ) :
					$tp_capa = tikporn_playlist_capa_url( $tp_pl->ID );
					?>
					<a class="xf-plcard" href="<?php echo esc_url( get_permalink( $tp_pl->ID ) ); ?>">
						<span class="xf-plcard__thumb"<?php echo $tp_capa ? ' style="background-image:url(\'' . esc_url( $tp_capa ) . '\')"' : ''; ?>>
							<span class="xf-plcard__count"><?php echo esc_html( count( tikporn_playlist_videos( $tp_pl->ID ) ) ); ?> <?php esc_html_e( 'vídeos', 'tikporn' ); ?></span>
						</span>
						<span class="xf-plcard__title"><?php echo esc_html( get_the_title( $tp_pl ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<section class="xf-secao">
		<div class="xf-secao__cab"><h2 class="xf-secao__titulo"><?php esc_html_e( 'Vídeos', 'tikporn' ); ?></h2></div>
		<?php
		$tp_videos = new WP_Query(
			array(
				'post_type'      => 'video',
				'author'         => $tp_id,
				'post_status'    => 'publish',
				'posts_per_page' => 24,
				'paged'          => max( 1, get_query_var( 'paged' ) ),
			)
		);
		if ( $tp_videos->have_posts() ) :
			?>
			<div class="xf-grade">
				<?php
				while ( $tp_videos->have_posts() ) :
					$tp_videos->the_post();
					get_template_part( 'template-parts/card-grade' );
				endwhile;
				?>
			</div>
			<div class="xf-paginacao"><?php echo wp_kses_post( paginate_links( array( 'total' => $tp_videos->max_num_pages, 'prev_text' => '‹', 'next_text' => '›' ) ) ); ?></div>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<div class="xf-vazio"><p><?php esc_html_e( 'Ainda não há vídeos por aqui.', 'tikporn' ); ?></p></div>
		<?php endif; ?>
	</section>

</div>

<?php
get_footer();
