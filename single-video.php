<?php
/**
 * Página do vídeo — FEED vertical estilo Reelix (scroll + pushState).
 * O feed é montado pelo feed.js a partir dos dados; cada vídeo empurra seu
 * permalink real na URL. Painel por vídeo no desktop; ações sobrepostas no mobile.
 *
 * @package tikporn
 */

get_header();

while ( have_posts() ) :
	the_post();

	$tp_id    = get_the_ID();
	$tp_first = tikporn_video_para_feed( $tp_id );

	tikporn_registrar_view( $tp_id );
	?>

	<div class="xf-watch" data-feed>
		<div class="xf-feed" data-feed-track<?php echo ( $tp_first && $tp_first['poster'] ) ? ' style="background-image:url(\'' . esc_url( $tp_first['poster'] ) . '\')"' : ''; ?>>
			<div class="xf-feed__spinner" data-feed-spinner><span></span></div>

			<noscript>
				<div class="xf-feed__noscript">
					<?php if ( $tp_first && 'arquivo' === $tp_first['tipo'] ) : ?>
						<video controls playsinline preload="metadata" <?php echo $tp_first['poster'] ? 'poster="' . esc_url( $tp_first['poster'] ) . '"' : ''; ?>><source src="<?php echo esc_url( $tp_first['src'] ); ?>"></video>
					<?php endif; ?>
					<h1><?php the_title(); ?></h1>
				</div>
			</noscript>
		</div>

		<aside class="xf-watch__panel" data-panel></aside>

		<div class="xf-watch__nav">
			<button type="button" class="xf-watch__arrow" data-feed-prev aria-label="<?php esc_attr_e( 'Anterior', 'tikporn' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 15l-6-6-6 6"/></svg>
			</button>
			<button type="button" class="xf-watch__arrow" data-feed-next aria-label="<?php esc_attr_e( 'Próximo', 'tikporn' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
			</button>
		</div>

		<a class="xf-watch__close" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Fechar', 'tikporn' ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
		</a>

		<!-- Topo do short (só mobile): logo + @autor à esquerda, busca à direita -->
		<div class="xf-watch__mtop">
			<div class="xf-watch__mtop-esq">
				<a class="xf-watch__mlogo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img src="<?php echo esc_url( TIKPORN_URI . '/assets/img/logo.png?v=' . TIKPORN_VERSION ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				</a>
				<a class="xf-watch__mhandle" data-mtop-handle href="<?php echo esc_url( $tp_first['autor']['url'] ?? home_url( '/' ) ); ?>">@<?php echo esc_html( $tp_first['autor']['handle'] ?? '' ); ?></a>
			</div>
			<a class="xf-watch__mbusca" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Buscar', 'tikporn' ); ?>">
				<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4 9a5 5 0 1110 0A5 5 0 014 9zm5-7a7 7 0 104.2 12.6.999.999 0 00.093.107l3 3a1 1 0 001.414-1.414l-3-3a.999.999 0 00-.107-.093A7 7 0 009 2z"/></svg>
			</a>
		</div>
	</div>

	<script type="application/json" data-feed-config>
		<?php echo wp_json_encode( array( 'first' => $tp_first, 'exclude' => $tp_id ) ); // phpcs:ignore ?>
	</script>

	<?php
endwhile;

get_footer();
