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
