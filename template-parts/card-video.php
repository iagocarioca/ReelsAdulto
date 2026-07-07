<?php
/**
 * Card de um vídeo no feed vertical.
 *
 * @package tikporn
 */

$tp_id      = get_the_ID();
$tp_autor   = (int) get_post_field( 'post_author', $tp_id );
$tp_nome    = get_the_author_meta( 'display_name', $tp_autor );
$tp_curtiu  = tikporn_usuario_curtiu( $tp_id );
$tp_seguindo = tikporn_usuario_segue( $tp_autor );
?>
<section class="tp-card" data-video-id="<?php echo esc_attr( $tp_id ); ?>">

	<div class="tp-card-video">
		<?php tikporn_player( $tp_id, true ); ?>
		<button class="tp-play-toggle" type="button" aria-label="<?php esc_attr_e( 'Tocar ou pausar', 'tikporn' ); ?>"></button>
	</div>

	<div class="tp-card-info">
		<a class="tp-card-autor" href="<?php echo esc_url( tikporn_url_perfil( $tp_autor ) ); ?>">
			@<?php echo esc_html( $tp_nome ); ?>
		</a>
		<h2 class="tp-card-titulo"><?php the_title(); ?></h2>
		<?php if ( get_the_content() ) : ?>
			<p class="tp-card-legenda"><?php echo esc_html( wp_trim_words( get_the_content(), 24 ) ); ?></p>
		<?php endif; ?>
	</div>

	<div class="tp-card-acoes">
		<a class="tp-acao tp-acao-avatar" href="<?php echo esc_url( tikporn_url_perfil( $tp_autor ) ); ?>">
			<?php echo tikporn_foto_perfil( $tp_autor, 48 ); // phpcs:ignore ?>
		</a>

		<button class="tp-acao tp-curtir <?php echo $tp_curtiu ? 'ativo' : ''; ?>"
			type="button"
			data-video-id="<?php echo esc_attr( $tp_id ); ?>">
			<span class="tp-acao-icone" aria-hidden="true">&#9829;</span>
			<span class="tp-acao-num"><?php echo esc_html( tikporn_numero_curto( tikporn_curtidas( $tp_id ) ) ); ?></span>
		</button>

		<button class="tp-acao tp-seguir <?php echo $tp_seguindo ? 'ativo' : ''; ?>"
			type="button"
			data-modelo-id="<?php echo esc_attr( $tp_autor ); ?>">
			<span class="tp-acao-icone" aria-hidden="true"><?php echo $tp_seguindo ? '&#10003;' : '&#43;'; ?></span>
			<span class="tp-acao-legenda"><?php echo $tp_seguindo ? esc_html__( 'Seguindo', 'tikporn' ) : esc_html__( 'Seguir', 'tikporn' ); ?></span>
		</button>

		<a class="tp-acao tp-compartilhar" href="<?php the_permalink(); ?>">
			<span class="tp-acao-icone" aria-hidden="true">&#10150;</span>
			<span class="tp-acao-legenda"><?php esc_html_e( 'Ver', 'tikporn' ); ?></span>
		</a>
	</div>

</section>
