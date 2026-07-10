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
				<img src="<?php echo esc_url( TIKPORN_URI . '/assets/img/logo.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			</a>

			<form role="search" method="get" class="xf-busca" data-busca action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="hidden" name="post_type" value="video">
				<button type="button" class="xf-busca__btn" data-busca-toggle aria-label="<?php esc_attr_e( 'Buscar', 'tikporn' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
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
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
				</a>

				<div class="xf-conta" data-conta-menu>
					<button class="xf-conta__btn" type="button" data-conta-toggle aria-label="<?php echo esc_attr( $tp_logado ? __( 'Minha conta', 'tikporn' ) : __( 'Entrar', 'tikporn' ) ); ?>">
						<?php if ( $tp_logado && function_exists( 'tikporn_avatar_url' ) ) : ?>
							<img src="<?php echo esc_url( tikporn_avatar_url( $tp_uid ) ); ?>" alt="">
						<?php else : ?>
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
						<?php endif; ?>
					</button>
					<div class="xf-conta__menu" data-conta-dropdown hidden>
						<?php if ( $tp_logado ) : ?>
							<span class="xf-conta__nome"><?php echo esc_html( get_the_author_meta( 'display_name', $tp_uid ) ); ?></span>
							<a class="xf-conta__link" href="<?php echo esc_url( site_url( '/minha-conta/' ) ); ?>"><?php esc_html_e( 'Minha conta', 'tikporn' ); ?></a>
							<a class="xf-conta__link" href="<?php echo esc_url( site_url( '/minhas-playlists/' ) ); ?>"><?php esc_html_e( 'Minhas playlists', 'tikporn' ); ?></a>
							<a class="xf-conta__link" href="<?php echo esc_url( $tp_url_env ); ?>"><?php esc_html_e( 'Enviar vídeo', 'tikporn' ); ?></a>
							<a class="xf-conta__link xf-conta__sair" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Sair', 'tikporn' ); ?></a>
						<?php else : ?>
							<a class="xf-conta__link" href="<?php echo esc_url( site_url( '/entrar/' ) ); ?>"><?php esc_html_e( 'Entrar', 'tikporn' ); ?></a>
							<a class="xf-conta__link" href="<?php echo esc_url( site_url( '/cadastro/' ) ); ?>"><?php esc_html_e( 'Criar conta', 'tikporn' ); ?></a>
						<?php endif; ?>
					</div>
				</div>

				<a class="xf-enviar xf-so-desktop" href="<?php echo esc_url( $tp_url_env ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
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
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="xf-logo"><img src="<?php echo esc_url( TIKPORN_URI . '/assets/img/logo.png' ); ?>" alt=""></a>
				<button class="xf-drawer__x" type="button" data-drawer-close aria-label="<?php esc_attr_e( 'Fechar', 'tikporn' ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
				</button>
			</div>
			<a class="xf-drawer__link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Início', 'tikporn' ); ?></a>
			<a class="xf-drawer__link" href="<?php echo esc_url( get_post_type_archive_link( 'video' ) ); ?>"><?php esc_html_e( 'Categorias', 'tikporn' ); ?></a>
			<?php if ( $tp_logado ) : ?>
				<a class="xf-drawer__link" href="<?php echo esc_url( site_url( '/minhas-playlists/' ) ); ?>"><?php esc_html_e( 'Minhas playlists', 'tikporn' ); ?></a>
				<a class="xf-drawer__link" href="<?php echo esc_url( site_url( '/minha-conta/' ) ); ?>"><?php esc_html_e( 'Minha conta', 'tikporn' ); ?></a>
				<a class="xf-drawer__link" href="<?php echo esc_url( $tp_url_env ); ?>"><?php esc_html_e( 'Enviar vídeo', 'tikporn' ); ?></a>
				<a class="xf-drawer__link xf-drawer__link--sair" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Sair', 'tikporn' ); ?></a>
			<?php else : ?>
				<a class="xf-drawer__link" href="<?php echo esc_url( site_url( '/entrar/' ) ); ?>"><?php esc_html_e( 'Entrar', 'tikporn' ); ?></a>
				<a class="xf-drawer__link" href="<?php echo esc_url( site_url( '/cadastro/' ) ); ?>"><?php esc_html_e( 'Criar conta', 'tikporn' ); ?></a>
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
			<?php endif; ?>
		</div>
	</div>

	<main class="xf-conteudo">
