<?php
/**
 * Playlists do usuário.
 *
 * CPT "playlist" (dono = usuário). Público × privado = status do post:
 *   - publish  => pública  (URL /playlist/{slug}, visível a todos)
 *   - private  => privada  (só o dono/admin vê — garantido pelo WordPress)
 * Os vídeos ficam numa meta ordenada (_tikporn_playlist_videos).
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TIKPORN_PLAYLIST_META = '_tikporn_playlist_videos';

/**
 * Registra o CPT "playlist".
 */
function tikporn_registrar_playlist() {
	$labels = array(
		'name'          => __( 'Playlists', 'tikporn' ),
		'singular_name' => __( 'Playlist', 'tikporn' ),
		'add_new_item'  => __( 'Nova playlist', 'tikporn' ),
		'edit_item'     => __( 'Editar playlist', 'tikporn' ),
		'search_items'  => __( 'Buscar playlists', 'tikporn' ),
		'menu_name'     => __( 'Playlists', 'tikporn' ),
	);

	register_post_type(
		'playlist',
		array(
			'labels'          => $labels,
			'public'          => true,
			'show_in_rest'    => true,
			'menu_icon'       => 'dashicons-playlist-video',
			'menu_position'   => 27,
			'supports'        => array( 'title', 'author', 'thumbnail' ),
			'rewrite'         => array( 'slug' => 'playlist', 'with_front' => false ),
			'has_archive'     => false,
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'tikporn_registrar_playlist' );

/* ─────────────────────────  Helpers  ───────────────────────── */

/**
 * IDs dos vídeos de uma playlist (na ordem em que foram adicionados).
 */
function tikporn_playlist_videos( $playlist_id ) {
	$ids = get_post_meta( $playlist_id, TIKPORN_PLAYLIST_META, true );
	return array_values( array_filter( array_map( 'intval', (array) $ids ) ) );
}

/**
 * Capa da playlist: primeira capa de vídeo disponível.
 */
function tikporn_playlist_capa_url( $playlist_id ) {
	foreach ( tikporn_playlist_videos( $playlist_id ) as $vid ) {
		$url = tikporn_capa_url( $vid );
		if ( $url ) {
			return $url;
		}
	}
	return '';
}

/**
 * A playlist é pública?
 */
function tikporn_playlist_e_publica( $playlist_id ) {
	return 'publish' === get_post_status( $playlist_id );
}

/**
 * Dono da playlist.
 */
function tikporn_playlist_dono( $playlist_id ) {
	return (int) get_post_field( 'post_author', $playlist_id );
}

/**
 * O usuário atual pode editar esta playlist?
 */
function tikporn_pode_editar_playlist( $playlist_id ) {
	if ( ! is_user_logged_in() || 'playlist' !== get_post_type( $playlist_id ) ) {
		return false;
	}
	return tikporn_playlist_dono( $playlist_id ) === get_current_user_id()
		|| current_user_can( 'edit_others_posts' );
}

/**
 * Playlists de um usuário. $incluir_privadas só quando é o próprio dono/admin.
 *
 * @return WP_Post[]
 */
function tikporn_user_playlists( $user_id, $incluir_privadas = false ) {
	return get_posts(
		array(
			'post_type'      => 'playlist',
			'author'         => (int) $user_id,
			'post_status'    => $incluir_privadas ? array( 'publish', 'private' ) : array( 'publish' ),
			'posts_per_page' => 100,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		)
	);
}

/* ─────────────────────────  AJAX  ───────────────────────── */

/**
 * Exige login + nonce; devolve WP_Send_JSON de erro caso contrário.
 */
function tikporn_playlist_checar() {
	check_ajax_referer( 'tikporn_nonce', 'nonce' );
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'mensagem' => __( 'Entre para usar playlists.', 'tikporn' ) ), 401 );
	}
}

/**
 * Criar playlist.
 */
function tikporn_ajax_playlist_criar() {
	tikporn_playlist_checar();

	$titulo  = sanitize_text_field( wp_unslash( $_POST['titulo'] ?? '' ) );
	$publica = ! empty( $_POST['publica'] );

	if ( '' === $titulo ) {
		wp_send_json_error( array( 'mensagem' => __( 'Dê um nome à playlist.', 'tikporn' ) ), 400 );
	}

	$playlist_id = wp_insert_post(
		array(
			'post_type'   => 'playlist',
			'post_title'  => $titulo,
			'post_status' => $publica ? 'publish' : 'private',
			'post_author' => get_current_user_id(),
		),
		true
	);

	if ( is_wp_error( $playlist_id ) ) {
		wp_send_json_error( array( 'mensagem' => __( 'Não foi possível criar. Tente novamente.', 'tikporn' ) ), 400 );
	}

	// Já adiciona o vídeo, se veio junto.
	$video_id = absint( $_POST['video_id'] ?? 0 );
	if ( $video_id && 'video' === get_post_type( $video_id ) ) {
		update_post_meta( $playlist_id, TIKPORN_PLAYLIST_META, array( $video_id ) );
	}

	wp_send_json_success(
		array(
			'id'      => $playlist_id,
			'titulo'  => $titulo,
			'publica' => $publica,
			'url'     => get_permalink( $playlist_id ),
			'contem'  => (bool) $video_id,
		)
	);
}
add_action( 'wp_ajax_tikporn_playlist_criar', 'tikporn_ajax_playlist_criar' );

/**
 * Adicionar / remover um vídeo da playlist (toggle).
 */
function tikporn_ajax_playlist_toggle_video() {
	tikporn_playlist_checar();

	$playlist_id = absint( $_POST['playlist_id'] ?? 0 );
	$video_id    = absint( $_POST['video_id'] ?? 0 );

	if ( ! tikporn_pode_editar_playlist( $playlist_id ) ) {
		wp_send_json_error( array( 'mensagem' => __( 'Playlist inválida.', 'tikporn' ) ), 403 );
	}
	if ( ! $video_id || 'video' !== get_post_type( $video_id ) ) {
		wp_send_json_error( array( 'mensagem' => __( 'Vídeo inválido.', 'tikporn' ) ), 400 );
	}

	$videos = tikporn_playlist_videos( $playlist_id );
	if ( in_array( $video_id, $videos, true ) ) {
		$videos = array_values( array_diff( $videos, array( $video_id ) ) );
		$contem = false;
	} else {
		$videos[] = $video_id;
		$contem   = true;
	}

	update_post_meta( $playlist_id, TIKPORN_PLAYLIST_META, $videos );
	wp_update_post( array( 'ID' => $playlist_id ) ); // atualiza "modified" p/ ordenar

	wp_send_json_success(
		array(
			'contem' => $contem,
			'total'  => count( $videos ),
		)
	);
}
add_action( 'wp_ajax_tikporn_playlist_toggle_video', 'tikporn_ajax_playlist_toggle_video' );

/**
 * Alternar visibilidade (pública/privada).
 */
function tikporn_ajax_playlist_visibilidade() {
	tikporn_playlist_checar();

	$playlist_id = absint( $_POST['playlist_id'] ?? 0 );
	if ( ! tikporn_pode_editar_playlist( $playlist_id ) ) {
		wp_send_json_error( array( 'mensagem' => __( 'Playlist inválida.', 'tikporn' ) ), 403 );
	}

	$publica = ! empty( $_POST['publica'] );
	wp_update_post(
		array(
			'ID'          => $playlist_id,
			'post_status' => $publica ? 'publish' : 'private',
		)
	);

	wp_send_json_success(
		array(
			'publica' => $publica,
			'url'     => get_permalink( $playlist_id ),
		)
	);
}
add_action( 'wp_ajax_tikporn_playlist_visibilidade', 'tikporn_ajax_playlist_visibilidade' );

/**
 * Excluir playlist (lixeira).
 */
function tikporn_ajax_playlist_excluir() {
	tikporn_playlist_checar();

	$playlist_id = absint( $_POST['playlist_id'] ?? 0 );
	if ( ! tikporn_pode_editar_playlist( $playlist_id ) ) {
		wp_send_json_error( array( 'mensagem' => __( 'Playlist inválida.', 'tikporn' ) ), 403 );
	}

	wp_trash_post( $playlist_id );
	wp_send_json_success( array( 'excluida' => true ) );
}
add_action( 'wp_ajax_tikporn_playlist_excluir', 'tikporn_ajax_playlist_excluir' );

/**
 * Lista as playlists do usuário (com flag "contém este vídeo") para o menu "Salvar".
 */
function tikporn_ajax_playlist_listar() {
	tikporn_playlist_checar();

	$video_id = absint( $_POST['video_id'] ?? 0 );
	$out      = array();

	foreach ( tikporn_user_playlists( get_current_user_id(), true ) as $pl ) {
		$out[] = array(
			'id'      => $pl->ID,
			'titulo'  => get_the_title( $pl ),
			'publica' => 'publish' === $pl->post_status,
			'contem'  => $video_id ? in_array( $video_id, tikporn_playlist_videos( $pl->ID ), true ) : false,
		);
	}

	wp_send_json_success( array( 'playlists' => $out ) );
}
add_action( 'wp_ajax_tikporn_playlist_listar', 'tikporn_ajax_playlist_listar' );

/**
 * Bloqueia acesso a playlist privada por quem não é o dono (defesa extra além do WP).
 */
function tikporn_proteger_playlist_privada() {
	if ( ! is_singular( 'playlist' ) ) {
		return;
	}
	$id = get_queried_object_id();
	if ( 'private' === get_post_status( $id ) && ! tikporn_pode_editar_playlist( $id ) ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
}
add_action( 'template_redirect', 'tikporn_proteger_playlist_privada' );
