<?php
/**
 * Perfil público do usuário/modelo (arquivo do autor) — estilo xfree:
 * cabeçalho completo (selo, link, redes sociais, compartilhar) +
 * grade de vídeos à esquerda, sidebar "Sobre" / categorias / criadores à direita.
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

// Links públicos (site + redes sociais).
$tp_links = tikporn_links_perfil( $tp_id );
$tp_site  = isset( $tp_links['site'] ) ? $tp_links['site']['url'] : '';
$tp_site_rotulo = $tp_site ? preg_replace( '/^www\./', '', (string) wp_parse_url( $tp_site, PHP_URL_HOST ) ) : '';

// Ícones das redes sociais.
$tp_icones_social = array(
	'x'         => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
	'tiktok'    => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>',
	'instagram' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
);

// Ordenação da grade (filtro "Tudo / Mais vistos / Mais curtidos").
$tp_ordem = isset( $_GET['ordem'] ) ? sanitize_key( $_GET['ordem'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! in_array( $tp_ordem, array( 'vistos', 'curtidos' ), true ) ) {
	$tp_ordem = '';
}

// Sidebar: categorias do autor + criadores recomendados.
$tp_cats      = tikporn_categorias_do_autor( $tp_id, 12 );
$tp_criadores = array_values(
	array_filter(
		tikporn_criadores( 5, 'videos' ),
		function ( $u ) use ( $tp_id ) {
			return (int) $u->ID !== $tp_id;
		}
	)
);
$tp_criadores = array_slice( $tp_criadores, 0, 4 );
?>

<div class="xf-perfil">

	<div class="xf-home">
		<div class="xf-home__main">

	<header class="xf-perfil__cab">
		<span class="xf-perfil__foto"><?php echo tikporn_foto_perfil( $tp_id, 160 ); // phpcs:ignore ?></span>

		<div class="xf-perfil__id">
			<h1 class="xf-perfil__nome"><?php echo esc_html( $tp_nome ); ?></h1>

			<div class="xf-perfil__handle-linha">
				<span class="xf-perfil__handle">@<?php echo esc_html( $tp_handle ); ?></span>
				<?php if ( $tp_modelo_role ) : ?>
					<span class="xf-perfil__check" title="<?php esc_attr_e( 'Verificado', 'tikporn' ); ?>"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22.25 12c0-1.43-.88-2.67-2.19-3.34.46-1.39.2-2.9-.81-3.91s-2.52-1.27-3.91-.81c-.66-1.31-1.91-2.19-3.34-2.19s-2.67.88-3.33 2.19c-1.4-.46-2.91-.2-3.92.81s-1.26 2.52-.8 3.91c-1.31.67-2.2 1.91-2.2 3.34s.89 2.67 2.2 3.34c-.46 1.39-.21 2.9.8 3.91s2.52 1.26 3.91.81c.67 1.31 1.91 2.19 3.34 2.19s2.68-.88 3.34-2.19c1.39.45 2.9.2 3.91-.81s1.27-2.52.81-3.91c1.31-.67 2.19-1.91 2.19-3.34zm-11.71 4.2L6.8 12.46l1.41-1.42 2.26 2.26 4.8-5.23 1.47 1.36-6.2 6.77z"/></svg></span>
					<span class="xf-perfil__badge"><?php esc_html_e( 'CANAL', 'tikporn' ); ?></span>
				<?php endif; ?>
			</div>

			<div class="xf-perfil__stats">
				<span><?php esc_html_e( 'Seguidores:', 'tikporn' ); ?> <strong><?php echo esc_html( tikporn_numero_k( $tp_n_seg ) ); ?></strong></span>
				<span><?php esc_html_e( 'Visualizações:', 'tikporn' ); ?> <strong><?php echo esc_html( tikporn_numero_k( $tp_n_views ) ); ?></strong></span>
				<span><?php esc_html_e( 'Curtidas:', 'tikporn' ); ?> <strong><?php echo esc_html( tikporn_numero_k( $tp_n_likes ) ); ?></strong></span>
			</div>

			<?php if ( $tp_bio ) : ?>
				<p class="xf-perfil__bio"><?php echo esc_html( $tp_bio ); ?></p>
			<?php endif; ?>

			<?php if ( $tp_site ) : ?>
				<a class="xf-perfil__site" href="<?php echo esc_url( $tp_site ); ?>" target="_blank" rel="nofollow noopener">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
					<?php echo esc_html( $tp_site_rotulo ); ?>
				</a>
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
			<button class="xf-perfil__share" type="button" data-compartilhar title="<?php esc_attr_e( 'Compartilhar perfil', 'tikporn' ); ?>">
				<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 9V5l7 7-7 7v-4.1c-5 0-8.5 1.6-11 5.1 1-5 4-10 11-11z"/></svg>
			</button>
		</div>

		<?php if ( ! empty( $tp_links['x'] ) || ! empty( $tp_links['tiktok'] ) || ! empty( $tp_links['instagram'] ) ) : ?>
			<div class="xf-perfil__social">
				<?php foreach ( array( 'x', 'tiktok', 'instagram' ) as $tp_rede ) : ?>
					<?php if ( ! empty( $tp_links[ $tp_rede ] ) ) : ?>
						<a class="xf-perfil__social-btn" href="<?php echo esc_url( $tp_links[ $tp_rede ]['url'] ); ?>" target="_blank" rel="nofollow noopener">
							<?php echo $tp_icones_social[ $tp_rede ]; // phpcs:ignore WordPress.Security.EscapeOutput -- SVG fixo ?>
							<span><?php echo esc_html( $tp_links[ $tp_rede ]['rotulo'] ); ?></span>
						</a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</header>

			<section class="xf-secao">
				<div class="xf-secao__cab">
					<h2 class="xf-secao__titulo"><?php echo esc_html( sprintf( __( '%s vídeos', 'tikporn' ), $tp_nome ) ); ?></h2>
					<div class="xf-perfil__filtro">
						<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.6-4.9-10-9.2C.5 8.3 2.1 5 5.3 5 7.2 5 8.6 6.1 12 9.2 15.4 6.1 16.8 5 18.7 5 21.9 5 23.5 8.3 22 11.8 19.6 16.1 12 21 12 21z"/></svg>
						<select data-perfil-ordem aria-label="<?php esc_attr_e( 'Ordenar vídeos', 'tikporn' ); ?>">
							<option value="" <?php selected( $tp_ordem, '' ); ?>><?php esc_html_e( 'Tudo', 'tikporn' ); ?></option>
							<option value="vistos" <?php selected( $tp_ordem, 'vistos' ); ?>><?php esc_html_e( 'Mais vistos', 'tikporn' ); ?></option>
							<option value="curtidos" <?php selected( $tp_ordem, 'curtidos' ); ?>><?php esc_html_e( 'Mais curtidos', 'tikporn' ); ?></option>
						</select>
					</div>
				</div>
				<?php
				$tp_args = array(
					'post_type'      => 'video',
					'author'         => $tp_id,
					'post_status'    => 'publish',
					'posts_per_page' => 24,
					'paged'          => max( 1, get_query_var( 'paged' ) ),
				);
				if ( 'vistos' === $tp_ordem ) {
					$tp_args['meta_key'] = '_tikporn_views';
					$tp_args['orderby']  = 'meta_value_num';
					$tp_args['order']    = 'DESC';
				} elseif ( 'curtidos' === $tp_ordem ) {
					$tp_args['meta_key'] = '_tikporn_curtidas';
					$tp_args['orderby']  = 'meta_value_num';
					$tp_args['order']    = 'DESC';
				}
				$tp_videos = new WP_Query( $tp_args );
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
					<div class="xf-paginacao"><?php echo wp_kses_post( paginate_links( array( 'total' => $tp_videos->max_num_pages, 'prev_text' => '‹', 'next_text' => '›', 'add_args' => $tp_ordem ? array( 'ordem' => $tp_ordem ) : false ) ) ); ?></div>
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

			<?php if ( ! empty( $tp_cats ) ) : ?>
				<div class="xf-sobre-bloco">
					<h2 class="xf-sobre__titulo"><?php echo esc_html( sprintf( __( 'Categorias de %s', 'tikporn' ), $tp_nome ) ); ?></h2>
					<div class="xf-sobre__cats">
						<?php foreach ( $tp_cats as $tp_cat ) : ?>
							<a class="xf-sobre__cat" href="<?php echo esc_url( get_term_link( $tp_cat ) ); ?>"><?php echo esc_html( $tp_cat->name ); ?></a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $tp_criadores ) ) : ?>
				<div class="xf-sobre-bloco">
					<h2 class="xf-sobre__titulo"><?php esc_html_e( 'Criadores que você pode gostar', 'tikporn' ); ?></h2>
					<div class="xf-sobre__criadores">
						<?php foreach ( $tp_criadores as $tp_cr ) : ?>
							<a class="xf-sobre__criador" href="<?php echo esc_url( tikporn_url_perfil( $tp_cr->ID ) ); ?>">
								<span class="xf-sobre__criador-foto"><?php echo tikporn_foto_perfil( $tp_cr->ID, 300 ); // phpcs:ignore ?></span>
								<span class="xf-sobre__criador-nome"><?php echo esc_html( $tp_cr->display_name ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</aside>
	</div>

</div>

<?php
get_footer();
