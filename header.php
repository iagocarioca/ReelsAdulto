<?php
/**
 * Cabeçalho do tema — layout tube ("xfree").
 *
 * @package tikporn
 */

$tp_logado   = is_user_logged_in();
$tp_uid      = get_current_user_id();
$tp_url_env  = tikporn_eh_modelo() ? site_url( '/area-modelo/#enviar' ) : site_url( '/entrar/' );
$tp_url_user = $tp_logado ? site_url( '/minha-conta/' ) : site_url( '/entrar/' );

$tp_menu_cats = get_terms(
	array( 'taxonomy' => TIKPORN_TAX_CAT, 'hide_empty' => false, 'number' => 40, 'orderby' => 'count', 'order' => 'DESC' )
);
$tp_menu_cats = is_wp_error( $tp_menu_cats ) ? array() : $tp_menu_cats;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'xf-body' ); ?>>
<?php wp_body_open(); ?>

<div class="xf-app">
	<header class="xf-topo">
		<div class="xf-topo__inner">
			<button class="xf-menu-btn" type="button" data-drawer-open aria-label="<?php esc_attr_e( 'Menu', 'tikporn' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
			</button>

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="xf-logo">
				<img src="<?php echo esc_url( TIKPORN_URI . '/assets/img/logo.png?v=' . TIKPORN_VERSION ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			</a>

			<form role="search" method="get" class="xf-busca" data-busca action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="hidden" name="post_type" value="video">
				<button type="button" class="xf-busca__btn" data-busca-toggle aria-label="<?php esc_attr_e( 'Buscar', 'tikporn' ); ?>">
					<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4 9a5 5 0 1110 0A5 5 0 014 9zm5-7a7 7 0 104.2 12.6.999.999 0 00.093.107l3 3a1 1 0 001.414-1.414l-3-3a.999.999 0 00-.107-.093A7 7 0 009 2z"/></svg>
				</button>
				<input type="search" class="xf-busca__campo" name="s" data-busca-input
					value="<?php echo esc_attr( get_search_query() ); ?>"
					placeholder="<?php echo esc_attr( tikporn_opcao( 'busca_placeholder' ) ); ?>"
					autocomplete="off">
				<button type="button" class="xf-busca__fechar" data-busca-close aria-label="<?php esc_attr_e( 'Fechar', 'tikporn' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
				</button>
				<div class="xf-busca__sugestoes" data-busca-sugestoes hidden></div>
			</form>

			<div class="xf-topo__acoes">
				<a class="xf-icone-btn xf-so-desktop" href="<?php echo esc_url( $tp_url_user ); ?>" aria-label="<?php esc_attr_e( 'Notificações', 'tikporn' ); ?>">
					<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8.35179 20.2418C9.19288 21.311 10.5142 22 12 22C13.4858 22 14.8071 21.311 15.6482 20.2418C13.2264 20.57 10.7736 20.57 8.35179 20.2418Z"/><path d="M18.7491 9V9.7041C18.7491 10.5491 18.9903 11.3752 19.4422 12.0782L20.5496 13.8012C21.5612 15.3749 20.789 17.5139 19.0296 18.0116C14.4273 19.3134 9.57274 19.3134 4.97036 18.0116C3.21105 17.5139 2.43882 15.3749 3.45036 13.8012L4.5578 12.0782C5.00972 11.3752 5.25087 10.5491 5.25087 9.7041V9C5.25087 5.13401 8.27256 2 12 2C15.7274 2 18.7491 5.13401 18.7491 9Z"/></svg>
				</a>

				<div class="xf-conta" data-conta-menu>
					<button class="xf-conta__btn" type="button" data-conta-toggle aria-label="<?php echo esc_attr( $tp_logado ? __( 'Minha conta', 'tikporn' ) : __( 'Entrar', 'tikporn' ) ); ?>">
						<?php if ( $tp_logado && function_exists( 'tikporn_avatar_url' ) ) : ?>
							<img src="<?php echo esc_url( tikporn_avatar_url( $tp_uid ) ); ?>" alt="">
						<?php else : ?>
							<svg viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M256,265.308c73.252,0,132.644-59.391,132.644-132.654C388.644,59.412,329.252,0,256,0 c-73.262,0-132.643,59.412-132.643,132.654C123.357,205.917,182.738,265.308,256,265.308z"/><path d="M425.874,393.104c-5.922-35.474-36-84.509-57.552-107.465c-5.829-6.212-15.948-3.628-19.504-1.427 c-27.04,16.672-58.782,26.399-92.819,26.399c-34.036,0-65.778-9.727-92.818-26.399c-3.555-2.201-13.675-4.785-19.505,1.427 c-21.55,22.956-51.628,71.991-57.551,107.465C71.573,480.444,164.877,512,256,512C347.123,512,440.427,480.444,425.874,393.104z"/></svg>
						<?php endif; ?>
					</button>
					<div class="xf-conta__menu" data-conta-dropdown hidden>
						<?php if ( $tp_logado ) : ?>
							<a class="xf-conta__topo" href="<?php echo esc_url( site_url( '/minha-conta/' ) ); ?>">
								<img src="<?php echo esc_url( function_exists( 'tikporn_avatar_url' ) ? tikporn_avatar_url( $tp_uid ) : get_avatar_url( $tp_uid ) ); ?>" alt="">
								<span class="xf-conta__topo-info">
									<b><?php echo esc_html( get_the_author_meta( 'display_name', $tp_uid ) ); ?></b>
									<em>@<?php echo esc_html( get_the_author_meta( 'user_nicename', $tp_uid ) ); ?></em>
								</span>
							</a>
							<a class="xf-conta__link" href="<?php echo esc_url( site_url( '/minha-conta/' ) ); ?>">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
								<?php esc_html_e( 'Minha conta', 'tikporn' ); ?>
							</a>
							<a class="xf-conta__link" href="<?php echo esc_url( site_url( '/minhas-playlists/' ) ); ?>">
								<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 6h13v2H3V6zm0 4h13v2H3v-2zm0 4h9v2H3v-2zm12 0 6 3-6 3v-6z"/></svg>
								<?php esc_html_e( 'Minhas playlists', 'tikporn' ); ?>
							</a>
							<a class="xf-conta__link" href="<?php echo esc_url( $tp_url_env ); ?>">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
								<?php esc_html_e( 'Enviar vídeo', 'tikporn' ); ?>
							</a>
							<div class="xf-conta__sep" aria-hidden="true"></div>
							<a class="xf-conta__link xf-conta__sair" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
								<?php esc_html_e( 'Sair', 'tikporn' ); ?>
							</a>
						<?php else : ?>
							<a class="xf-conta__link" href="<?php echo esc_url( site_url( '/entrar/' ) ); ?>">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
								<?php esc_html_e( 'Entrar', 'tikporn' ); ?>
							</a>
							<a class="xf-conta__link" href="<?php echo esc_url( site_url( '/cadastro/' ) ); ?>">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
								<?php esc_html_e( 'Criar conta', 'tikporn' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>

				<a class="xf-enviar xf-so-desktop" href="<?php echo esc_url( $tp_url_env ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12H15"/><path d="M12 9L12 15"/><path d="M3 12C3 4.5885 4.5885 3 12 3C19.4115 3 21 4.5885 21 12C21 19.4115 19.4115 21 12 21C4.5885 21 3 19.4115 3 12Z"/></svg>
					<span><?php esc_html_e( 'Enviar', 'tikporn' ); ?></span>
				</a>
			</div>
		</div>
	</header>

	<!-- Drawer de navegação (mobile) -->
	<div class="xf-drawer" data-drawer aria-hidden="true">
		<div class="xf-drawer__backdrop" data-drawer-close></div>
		<nav class="xf-drawer__panel" aria-label="<?php esc_attr_e( 'Menu', 'tikporn' ); ?>">
			<div class="xf-drawer__top">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="xf-logo"><img src="<?php echo esc_url( TIKPORN_URI . '/assets/img/logo.png?v=' . TIKPORN_VERSION ); ?>" alt=""></a>
				<button class="xf-drawer__x" type="button" data-drawer-close aria-label="<?php esc_attr_e( 'Fechar', 'tikporn' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
				</button>
			</div>
			<?php if ( $tp_logado ) : ?>
				<a class="xf-drawer__perfil" href="<?php echo esc_url( site_url( '/minha-conta/' ) ); ?>">
					<img src="<?php echo esc_url( function_exists( 'tikporn_avatar_url' ) ? tikporn_avatar_url( $tp_uid ) : get_avatar_url( $tp_uid ) ); ?>" alt="">
					<span class="xf-conta__topo-info">
						<b><?php echo esc_html( get_the_author_meta( 'display_name', $tp_uid ) ); ?></b>
						<em>@<?php echo esc_html( get_the_author_meta( 'user_nicename', $tp_uid ) ); ?></em>
					</span>
				</a>
			<?php endif; ?>
			<a class="xf-drawer__link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
				<?php esc_html_e( 'Início', 'tikporn' ); ?>
			</a>
			<a class="xf-drawer__link" href="<?php echo esc_url( get_post_type_archive_link( 'video' ) ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
				<?php esc_html_e( 'Categorias', 'tikporn' ); ?>
			</a>
			<?php if ( $tp_logado ) : ?>
				<a class="xf-drawer__link" href="<?php echo esc_url( site_url( '/minhas-playlists/' ) ); ?>">
					<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 6h13v2H3V6zm0 4h13v2H3v-2zm0 4h9v2H3v-2zm12 0 6 3-6 3v-6z"/></svg>
					<?php esc_html_e( 'Minhas playlists', 'tikporn' ); ?>
				</a>
				<a class="xf-drawer__link" href="<?php echo esc_url( site_url( '/minha-conta/' ) ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
					<?php esc_html_e( 'Minha conta', 'tikporn' ); ?>
				</a>
				<a class="xf-drawer__link" href="<?php echo esc_url( $tp_url_env ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
					<?php esc_html_e( 'Enviar vídeo', 'tikporn' ); ?>
				</a>
				<a class="xf-drawer__link xf-drawer__link--sair" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
					<?php esc_html_e( 'Sair', 'tikporn' ); ?>
				</a>
			<?php else : ?>
				<a class="xf-drawer__link" href="<?php echo esc_url( site_url( '/entrar/' ) ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
					<?php esc_html_e( 'Entrar', 'tikporn' ); ?>
				</a>
				<a class="xf-drawer__link" href="<?php echo esc_url( site_url( '/cadastro/' ) ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
					<?php esc_html_e( 'Criar conta', 'tikporn' ); ?>
				</a>
			<?php endif; ?>
		</nav>
	</div>

	<!-- Aba lateral "Categorias" (mobile) -->
	<button class="xf-cats-tab" type="button" data-cats-open aria-label="<?php esc_attr_e( 'Categorias', 'tikporn' ); ?>"><?php esc_html_e( 'Categorias', 'tikporn' ); ?></button>
	<div class="xf-cats-panel" data-cats-panel aria-hidden="true">
		<div class="xf-cats-panel__backdrop" data-cats-close></div>
		<div class="xf-cats-panel__sheet">
			<div class="xf-cats-panel__head">
				<h2><?php esc_html_e( 'Categorias', 'tikporn' ); ?></h2>
				<button class="xf-drawer__x" type="button" data-cats-close aria-label="<?php esc_attr_e( 'Fechar', 'tikporn' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
				</button>
			</div>
			<?php if ( ! empty( $tp_menu_cats ) ) : ?>
				<div class="xf-chips">
					<?php foreach ( $tp_menu_cats as $tp_mc ) : ?>
						<a class="xf-chip" href="<?php echo esc_url( get_term_link( $tp_mc ) ); ?>"><?php echo esc_html( $tp_mc->name ); ?></a>
					<?php endforeach; ?>
				</div>
				<?php $tp_pg_cats = get_page_by_path( 'categorias' ); ?>
				<?php if ( $tp_pg_cats && 'publish' === $tp_pg_cats->post_status ) : ?>
					<a class="xf-mais-cats xf-mais-cats--link" href="<?php echo esc_url( get_permalink( $tp_pg_cats ) ); ?>">
						<span><?php esc_html_e( 'Ver todas as categorias', 'tikporn' ); ?></span>
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
					</a>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>

	<main class="xf-conteudo">
