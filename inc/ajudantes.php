<?php
/**
 * Funções de apoio usadas pelos templates.
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Endereço do perfil público de uma modelo.
 */
function tikporn_url_perfil( $modelo_id ) {
	return get_author_posts_url( $modelo_id );
}

/**
 * Capa de uma categoria: thumbnail do vídeo mais recente dela.
 * Usada na home e no arquivo de categoria.
 */
if ( ! function_exists( 'tikporn_capa_categoria' ) ) {
	function tikporn_capa_categoria( $term_id ) {
		$q = new WP_Query(
			array(
				'post_type'      => 'video',
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => TIKPORN_TAX_CAT,
						'field'    => 'term_id',
						'terms'    => (int) $term_id,
					),
				),
				'meta_query'     => array(
					array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ),
				),
			)
		);
		$url = '';
		if ( $q->have_posts() ) {
			$q->the_post();
			$url = tikporn_capa_url( get_the_ID(), 'tikporn_miniatura' );
		}
		wp_reset_postdata();
		return $url;
	}
}

/**
 * Foto de perfil da modelo (usa a foto enviada ou o avatar padrão).
 */
function tikporn_foto_perfil( $modelo_id, $tamanho = 96 ) {
	$foto_id = get_user_meta( $modelo_id, 'tikporn_foto_id', true );
	if ( $foto_id ) {
		$src = wp_get_attachment_image_url( $foto_id, 'thumbnail' );
		if ( $src ) {
			return sprintf(
				'<img src="%s" width="%d" height="%d" class="tp-avatar" alt="%s" loading="lazy">',
				esc_url( $src ),
				(int) $tamanho,
				(int) $tamanho,
				esc_attr( get_the_author_meta( 'display_name', $modelo_id ) )
			);
		}
	}
	return get_avatar( $modelo_id, $tamanho, '', '', array( 'class' => 'tp-avatar' ) );
}

/**
 * Contador de curtidas de um vídeo.
 */
function tikporn_curtidas( $post_id ) {
	return (int) get_post_meta( $post_id, '_tikporn_curtidas', true );
}

/**
 * Contador de seguidores de uma modelo.
 */
function tikporn_seguidores( $modelo_id ) {
	return (int) get_user_meta( $modelo_id, 'tikporn_seguidores', true );
}

/**
 * Quantidade de vídeos de uma modelo.
 */
function tikporn_total_videos( $modelo_id ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'video',
			'author'         => $modelo_id,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
		)
	);
	return (int) $q->found_posts;
}

/**
 * Soma de views (ou curtidas) de todos os vídeos de um autor.
 *
 * @param int    $modelo_id ID do autor.
 * @param string $meta      '_tikporn_views' ou '_tikporn_curtidas'.
 * @return int
 */
function tikporn_soma_meta_autor( $modelo_id, $meta = '_tikporn_views' ) {
	$ids = get_posts(
		array(
			'post_type'      => 'video',
			'author'         => (int) $modelo_id,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	$total = 0;
	foreach ( $ids as $vid ) {
		$total += (int) get_post_meta( $vid, $meta, true );
	}
	return $total;
}

/**
 * Lista criadores (modelos) que têm ao menos um vídeo publicado.
 *
 * @param int    $limite Máximo de criadores.
 * @param string $ordem  'videos' (mais vídeos), 'seguidores' ou 'recentes'.
 * @return array Lista de WP_User.
 */
function tikporn_criadores( $limite = 6, $ordem = 'videos' ) {
	$args = array(
		'role'    => 'modelo',
		'number'  => max( 1, (int) $limite ) * 3, // margem p/ filtrar quem não tem vídeo
		'fields'  => array( 'ID', 'display_name' ),
	);

	if ( 'seguidores' === $ordem ) {
		$args['meta_key'] = 'tikporn_seguidores';
		$args['orderby']  = 'meta_value_num';
		$args['order']    = 'DESC';
	} elseif ( 'recentes' === $ordem ) {
		$args['orderby'] = 'registered';
		$args['order']   = 'DESC';
	}

	$users = get_users( $args );

	// Mantém só quem tem vídeo; ordena por nº de vídeos quando pedido.
	$com_video = array();
	foreach ( $users as $u ) {
		$total = tikporn_total_videos( $u->ID );
		if ( $total > 0 ) {
			$u->tikporn_total_videos = $total;
			$com_video[]             = $u;
		}
	}

	if ( 'videos' === $ordem ) {
		usort(
			$com_video,
			function ( $a, $b ) {
				return $b->tikporn_total_videos - $a->tikporn_total_videos;
			}
		);
	}

	return array_slice( $com_video, 0, (int) $limite );
}

/**
 * Formata número grande de forma curta (1200 -> "1,2 mil").
 */
function tikporn_numero_curto( $n ) {
	$n = (int) $n;
	if ( $n < 1000 ) {
		return (string) $n;
	}
	if ( $n < 1000000 ) {
		return number_format( $n / 1000, 1, ',', '.' ) . ' mil';
	}
	return number_format( $n / 1000000, 1, ',', '.' ) . ' mi';
}

/**
 * Formata número no estilo tube: 1234 -> "1.2k", 1500000 -> "1.5M".
 */
function tikporn_numero_k( $n ) {
	$n = (int) $n;
	if ( $n < 1000 ) {
		return (string) $n;
	}
	if ( $n < 1000000 ) {
		return rtrim( rtrim( number_format( $n / 1000, 1, '.', '' ), '0' ), '.' ) . 'k';
	}
	return rtrim( rtrim( number_format( $n / 1000000, 1, '.', '' ), '0' ), '.' ) . 'M';
}

/**
 * Capa (retrato) de um vídeo, com fallback para um cinza neutro.
 */
function tikporn_capa_url( $post_id, $tamanho = 'tikporn_capa' ) {
	$url = get_the_post_thumbnail_url( $post_id, $tamanho );
	return $url ? $url : '';
}

/**
 * Contador de visualizações de um vídeo.
 */
function tikporn_views( $post_id ) {
	return (int) get_post_meta( $post_id, '_tikporn_views', true );
}

/**
 * Registra uma visualização (1x por visitante/vídeo a cada 6h).
 */
function tikporn_registrar_view( $post_id ) {
	// Respeita a opção "Contar visualizações" (Opções do tema).
	if ( function_exists( 'tikporn_opcao' ) && ! tikporn_opcao( 'registrar_views' ) ) {
		return;
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'tp_view_' . md5( $ip . '_' . (int) $post_id );
	if ( get_transient( $key ) ) {
		return;
	}
	set_transient( $key, 1, 6 * HOUR_IN_SECONDS );
	update_post_meta( $post_id, '_tikporn_views', tikporn_views( $post_id ) + 1 );
}

/**
 * Mostra o player do vídeo (arquivo ou incorporado).
 *
 * @param int  $post_id  ID do vídeo.
 * @param bool $autoplay Autoplay mudo (feed vertical).
 * @param bool $controls Mostra controles nativos (página do vídeo) — sem mute/loop.
 */
function tikporn_player( $post_id, $autoplay = false, $controls = false ) {
	$url  = tikporn_get_video_url( $post_id );
	$tipo = tikporn_get_video_tipo( $post_id );
	$capa = get_the_post_thumbnail_url( $post_id, 'tikporn_capa' );

	if ( empty( $url ) ) {
		return;
	}

	if ( 'arquivo' === $tipo ) {
		$attrs = $controls
			? 'controls playsinline preload="metadata"'
			: ( $autoplay ? 'data-autoplay="1" ' : '' ) . 'muted loop playsinline preload="metadata"';
		printf(
			'<video class="tp-video" %s %s><source src="%s"></video>',
			$attrs, // phpcs:ignore WordPress.Security.EscapeOutput -- atributos fixos
			$capa ? 'poster="' . esc_url( $capa ) . '"' : '',
			esc_url( $url )
		);
	} else {
		// Link incorporado: usa o oEmbed do WordPress.
		echo '<div class="tp-embed">' . wp_oembed_get( esc_url( $url ) ) . '</div>'; // phpcs:ignore
	}
}
