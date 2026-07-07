<?php
/**
 * Registra o tipo de conteúdo "vídeo" e suas categorias.
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tipo de conteúdo: vídeo.
 */
function tikporn_registrar_video() {
	$labels = array(
		'name'               => __( 'Vídeos', 'tikporn' ),
		'singular_name'      => __( 'Vídeo', 'tikporn' ),
		'add_new'            => __( 'Adicionar vídeo', 'tikporn' ),
		'add_new_item'       => __( 'Adicionar novo vídeo', 'tikporn' ),
		'edit_item'          => __( 'Editar vídeo', 'tikporn' ),
		'new_item'           => __( 'Novo vídeo', 'tikporn' ),
		'view_item'          => __( 'Ver vídeo', 'tikporn' ),
		'search_items'       => __( 'Buscar vídeos', 'tikporn' ),
		'not_found'          => __( 'Nenhum vídeo encontrado', 'tikporn' ),
		'not_found_in_trash' => __( 'Nenhum vídeo na lixeira', 'tikporn' ),
		'menu_name'          => __( 'Vídeos', 'tikporn' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => true,
		'has_archive'         => true,
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-video-alt3',
		'menu_position'       => 5,
		'rewrite'             => array( 'slug' => 'video' ),
		'supports'            => array( 'title', 'editor', 'thumbnail', 'author', 'custom-fields' ),
		'taxonomies'          => array( 'categoria_video', 'tag_video' ),
		// Usa permissões próprias (edit_videos, publish_videos etc.).
		'capability_type'     => array( 'video', 'videos' ),
		'map_meta_cap'        => true,
	);

	register_post_type( 'video', $args );
}
add_action( 'init', 'tikporn_registrar_video' );

/**
 * Categoria de vídeo (taxonomia hierárquica).
 */
function tikporn_registrar_taxonomias() {
	register_taxonomy(
		'categoria_video',
		'video',
		array(
			'labels'            => array(
				'name'          => __( 'Categorias', 'tikporn' ),
				'singular_name' => __( 'Categoria', 'tikporn' ),
				'menu_name'     => __( 'Categorias', 'tikporn' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'categoria' ),
		)
	);

	register_taxonomy(
		'tag_video',
		'video',
		array(
			'labels'            => array(
				'name'          => __( 'Etiquetas', 'tikporn' ),
				'singular_name' => __( 'Etiqueta', 'tikporn' ),
				'menu_name'     => __( 'Etiquetas', 'tikporn' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'etiqueta' ),
		)
	);
}
add_action( 'init', 'tikporn_registrar_taxonomias' );

/**
 * Guarda o endereço do vídeo (arquivo enviado ou link incorporado)
 * como dado do post.
 */
function tikporn_get_video_url( $post_id ) {
	return get_post_meta( $post_id, '_tikporn_video_url', true );
}

/**
 * Retorna o tipo do vídeo: 'arquivo' ou 'incorporado'.
 */
function tikporn_get_video_tipo( $post_id ) {
	$tipo = get_post_meta( $post_id, '_tikporn_video_tipo', true );
	return $tipo ? $tipo : 'arquivo';
}
