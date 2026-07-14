<?php
/**
 * Card de vídeo na grade (layout tube). Usar dentro do loop.
 *
 * @package tikporn
 */

$tp_id   = get_the_ID();
$tp_capa = tikporn_capa_url( $tp_id );
// Métrica exibida no badge: visualizações do vídeo.
$tp_num  = tikporn_numero_k( tikporn_views( $tp_id ) );

// Preview no hover: só para vídeos de arquivo (mp4 direto).
$tp_prev = '';
if ( function_exists( 'tikporn_get_video_tipo' ) && 'arquivo' === tikporn_get_video_tipo( $tp_id ) ) {
	$tp_prev = tikporn_get_video_url( $tp_id );
}

// Categoria como hashtag ao lado do título (#sexogostoso) — ignora a padrão.
$tp_hash   = '';
$tp_termos = get_the_terms( $tp_id, TIKPORN_TAX_CAT );
if ( $tp_termos && ! is_wp_error( $tp_termos ) ) {
	$tp_cat_padrao = (int) get_option( 'default_category' );
	foreach ( $tp_termos as $tp_termo_cat ) {
		if ( (int) $tp_termo_cat->term_id !== $tp_cat_padrao ) {
			$tp_hash = '#' . str_replace( '-', '', $tp_termo_cat->slug );
			break;
		}
	}
}
?>
<a class="xf-card" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>"<?php echo $tp_prev ? ' data-preview="' . esc_url( $tp_prev ) . '"' : ''; ?>>
	<div class="xf-card__thumb">
		<?php if ( $tp_capa ) : ?>
			<img src="<?php echo esc_url( $tp_capa ); ?>" alt="" loading="lazy" decoding="async">
		<?php else : ?>
			<span class="xf-card__semcapa" aria-hidden="true"></span>
		<?php endif; ?>

		<span class="xf-card__stat">
			<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
			<?php echo esc_html( $tp_num ); ?>
		</span>
	</div>
	<span class="xf-card__titulo"><?php echo esc_html( wp_trim_words( get_the_title(), 8 ) ); ?><?php echo $tp_hash ? ' <b class="xf-card__cat">' . esc_html( $tp_hash ) . '</b>' : ''; ?></span>
</a>
