<?php
/**
 * Perfil público do usuário/modelo (arquivo do autor) — estilo xfree:
 * cabeçalho completo + grade de vídeos à esquerda, sidebar "Sobre" à direita.
 *
 * @package tikporn
 */

get_header();

$tp_modelo = get_queried_object();
$tp_id     = $tp_modelo ? (int) $tp_modelo->ID : 0;
$tp_nome   = get_the_author_meta( 'display_name', $tp_id );
$tp_handle = get_the_author_meta( 'user_nicename', $tp_id );
$tp_bio    = get_the_author_meta( 'description', $tp_id );
$tp_segue  = function_exists( 'tikporn_usuario_segue' ) ? tikporn_usuario_segue( $tp_id ) : false;
$tp_eu     = get_current_user_id() === $tp_id;
$tp_modelo_role = function_exists( 'tikporn_eh_modelo' ) && tikporn_eh_modelo( $tp_id );

// Stats.
$tp_n_videos = tikporn_total_videos( $tp_id );
$tp_n_seg    = tikporn_seguidores( $tp_id );
$tp_n_views  = tikporn_soma_meta_autor( $tp_id, '_tikporn_views' );
$tp_n_likes  = tikporn_soma_meta_autor( $tp_id, '_tikporn_curtidas' );
?>

<div class="xf-perfil">

	<header class="xf-perfil__cab">
		<span class="xf-perfil__foto"><?php echo tikporn_foto_perfil( $tp_id, 160 ); // phpcs:ignore ?></span>

		<div class="xf-perfil__id">
			<div class="xf-perfil__nome-linha">
				<h1 class="xf-perfil__nome"><?php echo esc_html( $tp_nome ); ?></h1>
				<?php if ( $tp_modelo_role ) : ?>
					<span class="xf-perfil__badge"><?php esc_html_e( 'CANAL', 'tikporn' ); ?></span>
				<?php endif; ?>
			</div>
			<span class="xf-perfil__handle">@<?php echo esc_html( $tp_handle ); ?></span>

			<div class="xf-perfil__stats">
				<span><?php esc_html_e( 'Seguidores:', 'tikporn' ); ?> <strong><?php echo esc_html( tikporn_numero_k( $tp_n_seg ) ); ?></strong></span>
				<span><?php esc_html_e( 'Visualizações:', 'tikporn' ); ?> <strong><?php echo esc_html( tikporn_numero_k( $tp_n_views ) ); ?></strong></span>
				<span><?php esc_html_e( 'Curtidas:', 'tikporn' ); ?> <strong><?php echo esc_html( tikporn_numero_k( $tp_n_likes ) ); ?></strong></span>
			</div>

			<?php if ( $tp_bio ) : ?>
				<p class="xf-perfil__bio"><?php echo esc_html( $tp_bio ); ?></p>
			<?php endif; ?>
		</div>

		<div class="xf-perfil__acao">
			<?php if ( $tp_eu ) : ?>
				<a class="xf-btn-cheio" href="<?php echo esc_url( site_url( '/minha-conta/' ) ); ?>"><?php esc_html_e( 'Editar perfil', 'tikporn' ); ?></a>
			<?php elseif ( is_user_logged_in() ) : ?>
				<button class="xf-btn-cheio tp-seguir <?php echo $tp_segue ? 'ativo' : ''; ?>" type="button" data-modelo-id="<?php echo esc_attr( $tp_id ); ?>">
					<span class="tp-acao-legenda"><?php echo $tp_segue ? esc_html__( 'Seguindo', 'tikporn' ) : esc_html__( 'Seguir', 'tikporn' ); ?></span>
				</button>
			<?php else : ?>
				<a class="xf-btn-cheio" href="<?php echo esc_url( site_url( '/entrar/' ) ); ?>"><?php esc_html_e( 'Seguir', 'tikporn' ); ?></a>
			<?php endif; ?>
		</div>
	</header>

	<div class="xf-home">
		<div class="xf-home__main">
			<section class="xf-secao">
				<div class="xf-secao__cab">
					<h2 class="xf-secao__titulo"><?php echo esc_html( sprintf( __( 'Vídeos de %s', 'tikporn' ), $tp_nome ) ); ?></h2>
				</div>
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

		<aside class="xf-sidebar xf-sidebar--perfil is-expanded">
			<div class="xf-sobre">
				<h2 class="xf-sobre__titulo"><?php echo esc_html( sprintf( __( 'Sobre %s', 'tikporn' ), $tp_nome ) ); ?></h2>
				<?php if ( $tp_bio ) : ?>
					<p class="xf-sobre__bio"><?php echo esc_html( $tp_bio ); ?></p>
				<?php endif; ?>
				<ul class="xf-sobre__stats">
					<li>
						<span class="xf-sobre__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></span>
						<strong><?php echo esc_html( tikporn_numero_k( $tp_n_views ) ); ?></strong> <?php esc_html_e( 'visualizações', 'tikporn' ); ?>
					</li>
					<li>
						<span class="xf-sobre__ic xf-sobre__ic--like"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.6-4.9-10-9.2C.5 8.3 2.1 5 5.3 5 7.2 5 8.6 6.1 12 9.2 15.4 6.1 16.8 5 18.7 5 21.9 5 23.5 8.3 22 11.8 19.6 16.1 12 21 12 21z"/></svg></span>
						<strong><?php echo esc_html( tikporn_numero_k( $tp_n_likes ) ); ?></strong> <?php esc_html_e( 'curtidas', 'tikporn' ); ?>
					</li>
					<li>
						<span class="xf-sobre__ic"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-8 1a3.5 3.5 0 1 0-3.5-3.5A3.5 3.5 0 0 0 8 12zm0 2c-2.7 0-6 1.34-6 4v2h7.5v-2c0-1.06.53-2 1.4-2.76A9.4 9.4 0 0 0 8 14zm8 0c-.29 0-.62.02-.97.05C16.2 14.9 17 15.99 17 17.5V20h7v-2c0-2.66-3.3-4-6-4z"/></svg></span>
						<strong><?php echo esc_html( tikporn_numero_k( $tp_n_seg ) ); ?></strong> <?php esc_html_e( 'seguidores', 'tikporn' ); ?>
					</li>
					<li>
						<span class="xf-sobre__ic"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 4l2 4h-3l-2-4h-2l2 4h-3l-2-4H8l2 4H7L5 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V4z"/></svg></span>
						<strong><?php echo esc_html( $tp_n_videos ); ?></strong> <?php esc_html_e( 'vídeos', 'tikporn' ); ?>
					</li>
				</ul>
			</div>
		</aside>
	</div>

</div>

<?php
get_footer();
