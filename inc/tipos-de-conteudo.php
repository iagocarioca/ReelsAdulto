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
 * Taxonomia canônica de categoria dos vídeos.
 *
 * Usamos a 'category' padrão do WordPress porque é a taxonomia que a
 * integração (Aurora) envia via REST (rest_base=categories). Assim os vídeos
 * importados já vêm categorizados. A antiga 'categoria_video' continua
 * registrada por retrocompatibilidade, mas o front usa esta constante.
 */
if ( ! defined( 'TIKPORN_TAX_CAT' ) ) {
	define( 'TIKPORN_TAX_CAT', 'category' );
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
		'taxonomies'          => array( 'category', 'categoria_video', 'tag_video' ),
		// Usa permissões próprias (edit_videos, publish_videos etc.).
		'capability_type'     => array( 'video', 'videos' ),
		'map_meta_cap'        => true,
	);

	register_post_type( 'video', $args );
}
add_action( 'init', 'tikporn_registrar_video' );

/**
 * Anexa a taxonomia 'category' (padrão do WP) ao CPT vídeo.
 *
 * Necessário para que os vídeos aceitem as categorias que o Aurora envia via
 * REST em rest_base=categories. Roda tarde (prioridade 20) para garantir que
 * a 'category' do core já esteja registrada. Sem isto, o WP ignora o vínculo
 * silenciosamente e o count fica em 0.
 */
function tikporn_anexar_category_ao_video() {
	register_taxonomy_for_object_type( 'category', 'video' );
}
add_action( 'init', 'tikporn_anexar_category_ao_video', 20 );

/**
 * Faz o arquivo de categoria listar os vídeos do CPT 'video'.
 *
 * Por padrão o WP consulta só 'post' no arquivo de categoria. Como agora os
 * vídeos usam a taxonomia 'category', incluímos o CPT 'video' (só ele) para
 * que a página da categoria mostre os vídeos, no mesmo layout da playlist.
 */
function tikporn_categoria_lista_videos( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_category() ) {
		$query->set( 'post_type', array( 'video' ) );
	}
}
add_action( 'pre_get_posts', 'tikporn_categoria_lista_videos' );

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
 * Registra os metas do vídeo na REST API.
 *
 * Assim é possível IMPORTAR vídeos via REST (igual ao plugin Reelix):
 * basta fazer POST em /wp-json/wp/v2/video com o campo "meta" preenchido.
 *
 * As chaves internas usam prefixo "_" (metas protegidas), então expomos
 * chaves públicas equivalentes (sem "_") que a REST aceita, e sincronizamos
 * uma na outra logo abaixo — o player continua lendo a interna.
 */
function tikporn_registrar_meta_rest() {
	$campos = array(
		'tikporn_video_url'     => 'string',  // Endereço do vídeo (arquivo enviado ou link).
		'tikporn_video_uuid'    => 'string',  // UUID do vídeo (Aurora5) — resolvido pra URL assinada.
		'video_uuid'            => 'string',  // Alias canônico (mesma chave do dedada/Reelix).
		'tikporn_video_tipo'    => 'string',  // 'arquivo' ou 'incorporado'.
		'tikporn_video_capa'    => 'string',  // URL da imagem de capa (vira thumbnail).
		'tikporn_duracao'       => 'string',  // Duração (ex.: "0:45").
		'tikporn_visualizacoes' => 'integer', // Contador de views inicial (opcional).
	);

	foreach ( $campos as $chave => $tipo ) {
		register_post_meta(
			'video',
			$chave,
			array(
				'type'          => $tipo,
				'single'        => true,
				'show_in_rest'  => true,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'tikporn_registrar_meta_rest', 11 );

/**
 * Sincroniza a chave pública (REST) com a chave interna que o player lê.
 *
 * Quando um vídeo é criado/editado via REST e grava "tikporn_video_url",
 * copiamos para "_tikporn_video_url" (e o tipo, capa e duração equivalentes).
 */
function tikporn_sincronizar_meta_rest( $meta_id, $post_id, $meta_key, $meta_value ) {
	if ( 'video' !== get_post_type( $post_id ) ) {
		return;
	}

	switch ( $meta_key ) {
		case 'tikporn_video_url':
			update_post_meta( $post_id, '_tikporn_video_url', esc_url_raw( $meta_value ) );
			// Se o tipo não foi informado, adivinha pelo endereço.
			if ( '' === (string) get_post_meta( $post_id, '_tikporn_video_tipo', true ) ) {
				$eh_arquivo = (bool) preg_match( '/\.(mp4|webm|ogv|mov|m3u8)(\?.*)?$/i', (string) $meta_value );
				update_post_meta( $post_id, '_tikporn_video_tipo', $eh_arquivo ? 'arquivo' : 'incorporado' );
			}
			break;

		case 'tikporn_video_uuid':
		case 'video_uuid':
			// Guarda o UUID (o vídeo é resolvido pra URL assinada na hora de tocar).
			$uuid = sanitize_text_field( (string) $meta_value );
			update_post_meta( $post_id, '_tikporn_video_uuid', $uuid );
			if ( '' === (string) get_post_meta( $post_id, '_tikporn_video_tipo', true ) ) {
				update_post_meta( $post_id, '_tikporn_video_tipo', 'arquivo' );
			}
			break;

		case 'tikporn_video_tipo':
			$tipo = in_array( $meta_value, array( 'arquivo', 'incorporado' ), true ) ? $meta_value : 'incorporado';
			update_post_meta( $post_id, '_tikporn_video_tipo', $tipo );
			break;

		case 'tikporn_video_capa':
			// Define a capa a partir de uma URL (baixa e vira imagem destacada).
			if ( ! empty( $meta_value ) && ! has_post_thumbnail( $post_id ) ) {
				tikporn_definir_capa_por_url( $post_id, esc_url_raw( $meta_value ) );
			}
			break;

		case 'tikporn_duracao':
			update_post_meta( $post_id, '_tikporn_duracao', sanitize_text_field( (string) $meta_value ) );
			break;

		case 'tikporn_visualizacoes':
			update_post_meta( $post_id, '_tikporn_views', absint( $meta_value ) );
			break;
	}
}
add_action( 'added_post_meta', 'tikporn_sincronizar_meta_rest', 10, 4 );
add_action( 'updated_post_meta', 'tikporn_sincronizar_meta_rest', 10, 4 );

/**
 * Baixa uma imagem de capa a partir de uma URL e a define como destacada.
 */
function tikporn_definir_capa_por_url( $post_id, $url ) {
	if ( empty( $url ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$anexo_id = media_sideload_image( $url, $post_id, null, 'id' );
	if ( ! is_wp_error( $anexo_id ) ) {
		set_post_thumbnail( $post_id, $anexo_id );
	}
}

/**
 * Resolve um UUID do Aurora5 numa URL MP4 assinada.
 *
 * Mesma lógica do plugin Reelix: se o site já tiver a função do tema
 * (vs_url_assinada / aurora5GetVideoUrl), usa ela; senão assina aqui
 * mesmo com um secret configurável (constante TIKPORN_AURORA5_SECRET ou filtro).
 */
function tikporn_aurora5_url( $uuid ) {
	$uuid = trim( (string) $uuid );
	if ( '' === $uuid ) {
		return '';
	}

	if ( function_exists( 'vs_url_assinada' ) ) {
		return (string) vs_url_assinada( $uuid );
	}
	if ( function_exists( 'aurora5GetVideoUrl' ) ) {
		return (string) aurora5GetVideoUrl( $uuid );
	}

	// Secret: constante (wp-config) tem prioridade -> opções do tema -> filtro.
	$secret = defined( 'TIKPORN_AURORA5_SECRET' ) ? (string) TIKPORN_AURORA5_SECRET : '';
	if ( '' === $secret && function_exists( 'tikporn_opcao' ) ) {
		$secret = (string) tikporn_opcao( 'aurora5_secret', '' );
	}
	$secret = (string) apply_filters( 'tikporn_aurora5_secret', $secret );
	if ( '' === $secret ) {
		return '';
	}

	$base = function_exists( 'tikporn_opcao' ) ? (string) tikporn_opcao( 'aurora5_base' ) : 'https://api.aurora5.com/secure-video/';
	$base = (string) apply_filters( 'tikporn_aurora5_base', $base );
	$ttl  = function_exists( 'tikporn_opcao' ) ? (int) tikporn_opcao( 'aurora5_ttl' ) : 1800;
	$ttl  = (int) apply_filters( 'tikporn_aurora5_ttl', $ttl );
	$exp  = time() + max( 60, $ttl );
	$sig  = hash_hmac( 'sha256', $uuid . $exp, $secret );

	return rtrim( $base, '/' ) . '/' . rawurlencode( $uuid ) . '?sig=' . $sig . ':' . $exp;
}

/**
 * Retorna o UUID do vídeo (chave interna ou pública/REST).
 */
function tikporn_get_video_uuid( $post_id ) {
	$chaves = array( '_tikporn_video_uuid', 'tikporn_video_uuid', 'video_uuid' );
	foreach ( $chaves as $chave ) {
		$uuid = get_post_meta( $post_id, $chave, true );
		if ( ! empty( $uuid ) ) {
			return $uuid;
		}
	}
	return '';
}

/**
 * Resolve o endereço final do vídeo, com a mesma prioridade do Reelix:
 *  1) URL direta (arquivo enviado ou link incorporado)
 *  2) UUID (Aurora5) -> URL MP4 assinada em tempo real
 */
function tikporn_get_video_url( $post_id ) {
	// 1) URL direta.
	$url = get_post_meta( $post_id, '_tikporn_video_url', true );
	if ( empty( $url ) ) {
		$url = get_post_meta( $post_id, 'tikporn_video_url', true );
	}
	if ( ! empty( $url ) ) {
		return $url;
	}

	// 2) UUID -> URL assinada (Aurora5).
	$uuid = tikporn_get_video_uuid( $post_id );
	if ( ! empty( $uuid ) ) {
		return tikporn_aurora5_url( $uuid );
	}

	return '';
}

/**
 * Retorna o tipo do vídeo: 'arquivo' ou 'incorporado'.
 */
function tikporn_get_video_tipo( $post_id ) {
	$tipo = get_post_meta( $post_id, '_tikporn_video_tipo', true );
	if ( empty( $tipo ) ) {
		$tipo = get_post_meta( $post_id, 'tikporn_video_tipo', true );
	}
	return $tipo ? $tipo : 'arquivo';
}
