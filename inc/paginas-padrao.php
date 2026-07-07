<?php
/**
 * Cria automaticamente as páginas do tema quando ele é ativado,
 * e define a página inicial como o feed.
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lista de páginas a criar: slug => [título, modelo de página].
 */
function tikporn_paginas_do_tema() {
	return array(
		'entrar'      => array( __( 'Entrar', 'tikporn' ), 'page-templates/login.php' ),
		'cadastro'    => array( __( 'Cadastro', 'tikporn' ), 'page-templates/cadastro.php' ),
		'area-modelo' => array( __( 'Área da modelo', 'tikporn' ), 'page-templates/area-modelo.php' ),
		'buscar'      => array( __( 'Buscar', 'tikporn' ), 'page-templates/buscar.php' ),
		'minhas-playlists' => array( __( 'Minhas playlists', 'tikporn' ), 'page-templates/minhas-playlists.php' ),
		'minha-conta'      => array( __( 'Minha conta', 'tikporn' ), 'page-templates/minha-conta.php' ),
	);
}

/**
 * Cria as páginas ao ativar o tema.
 */
function tikporn_criar_paginas() {
	foreach ( tikporn_paginas_do_tema() as $slug => $dados ) {
		$existente = get_page_by_path( $slug );
		if ( $existente ) {
			continue;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => $dados[0],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			)
		);

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', $dados[1] );
		}
	}

	// Página inicial passa a ser o feed (front-page.php cuida do visual).
	update_option( 'show_on_front', 'posts' );

	// Recria as regras de links (por causa do novo tipo de conteúdo).
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'tikporn_criar_paginas' );
