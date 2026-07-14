<?php
/**
 * tikporn - funções centrais do tema.
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Impede acesso direto.
}

define( 'TIKPORN_VERSION', '2.16.0' );
define( 'TIKPORN_DIR', get_template_directory() );
define( 'TIKPORN_URI', get_template_directory_uri() );

/**
 * Configurações básicas do tema.
 */
function tikporn_setup() {
	load_theme_textdomain( 'tikporn', TIKPORN_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// Tamanho de capa dos vídeos (retrato, estilo TikTok).
	add_image_size( 'tikporn_capa', 720, 1280, true );
	add_image_size( 'tikporn_miniatura', 360, 640, true );

	register_nav_menus(
		array(
			'principal' => __( 'Menu principal', 'tikporn' ),
		)
	);
}
add_action( 'after_setup_theme', 'tikporn_setup' );

/**
 * Atualiza as regras de rewrite quando a versão do tema muda (CPTs novos etc.).
 */
function tikporn_maybe_flush_rewrite() {
	if ( get_option( 'tikporn_rewrite_version' ) !== TIKPORN_VERSION ) {
		// Cria páginas novas (ex.: Minhas playlists) e recria as regras de rewrite.
		if ( function_exists( 'tikporn_criar_paginas' ) ) {
			tikporn_criar_paginas();
		} else {
			flush_rewrite_rules( false );
		}
		update_option( 'tikporn_rewrite_version', TIKPORN_VERSION );
	}
}
add_action( 'init', 'tikporn_maybe_flush_rewrite', 99 );

/**
 * Carrega estilos e scripts.
 */
function tikporn_assets() {
	wp_enqueue_style( 'tikporn-style', get_stylesheet_uri(), array(), TIKPORN_VERSION );
	wp_enqueue_style( 'tikporn-main', TIKPORN_URI . '/assets/css/main.css', array(), TIKPORN_VERSION );

	// Fonte do tema: Poppins (Google Fonts).
	wp_enqueue_style( 'tikporn-poppins', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap', array(), null );

	// Layout tube (estilo "xfree"): design claro do cabeçalho, home e cards.
	wp_enqueue_style( 'tikporn-tube', TIKPORN_URI . '/assets/css/tube.css', array( 'tikporn-main' ), TIKPORN_VERSION );

	// Cor de destaque definida nas Opções do tema (sobrescreve as vars do CSS).
	$css_cor = tikporn_css_cor_destaque();
	if ( $css_cor ) {
		wp_add_inline_style( 'tikporn-tube', $css_cor );
	}

	wp_enqueue_script( 'tikporn-main', TIKPORN_URI . '/assets/js/main.js', array(), TIKPORN_VERSION, true );

	// Feed vertical (estilo Reelix) na página do vídeo.
	if ( is_singular( 'video' ) ) {
		wp_enqueue_script( 'tikporn-feed', TIKPORN_URI . '/assets/js/feed.js', array(), TIKPORN_VERSION, true );
	}

	// Playlists (menu "Salvar", criar/gerir).
	wp_enqueue_script( 'tikporn-playlists', TIKPORN_URI . '/assets/js/playlists.js', array( 'tikporn-main' ), TIKPORN_VERSION, true );

	// Dados que o JavaScript precisa (endereço do ajax e token de segurança).
	wp_localize_script(
		'tikporn-main',
		'tikpornDados',
		array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'tikporn_nonce' ),
			'logado'       => is_user_logged_in(),
			'loginUrl'     => site_url( '/entrar/' ),
			'playlistsUrl' => site_url( '/minhas-playlists/' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'tikporn_assets' );

// Módulos do tema. O file_exists tolera a janela do deploy via FTP em que o
// functions.php novo chega antes de um módulo recém-criado (evita erro fatal).
$tikporn_modulos = array(
	'opcoes-tema',       // Página de opções (Aparência → Opções do tema).
	'tipos-de-conteudo', // Tipo de conteúdo "vídeo".
	'papeis',            // Papel "modelo".
	'autenticacao',      // Cadastro, login, logout.
	'google-auth',       // Login com Google (GIS).
	'area-modelo',       // Envio e exclusão de vídeos.
	'interacoes',        // Curtir e seguir.
	'playlists',         // Playlists (públicas/privadas).
	'conta',             // Minha conta (perfil do usuário).
	'feed',              // Feed vertical (pushState) na página do vídeo.
	'busca',             // Sugestões de busca (autocomplete).
	'comentarios',       // Comentários dos vídeos (AJAX).
	'scroll-infinito',   // Scroll infinito das grades.
	'api-canais',        // REST: criar/atualizar canais (usuário modelo).
	'paginas-padrao',    // Cria páginas ao ativar o tema.
	'ajudantes',         // Funções de apoio para os templates.
);
foreach ( $tikporn_modulos as $tikporn_modulo ) {
	$tikporn_arquivo = TIKPORN_DIR . '/inc/' . $tikporn_modulo . '.php';
	if ( file_exists( $tikporn_arquivo ) ) {
		require_once $tikporn_arquivo;
	}
}
