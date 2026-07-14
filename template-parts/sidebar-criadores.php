<?php
/**
 * Sidebar de Criadores (modelos) — cards com thumb, nome, stats e "Ver perfil".
 * Estilo inspirado nas listas de criadores (thumb grande + overlay).
 *
 * @package tikporn
 */

$tp_titulo    = tikporn_opcao( 'criadores_titulo' );
$tp_qtd       = (int) tikporn_opcao( 'criadores_qtd' );
$tp_criadores = function_exists( 'tikporn_criadores' ) ? tikporn_criadores( $tp_qtd, 'videos' ) : array();

if ( empty( $tp_criadores ) ) {
	return;
}
?>
<section class="xf-criadores">
	<div class="xf-criadores__cab">
		<h2 class="xf-criadores__titulo"><?php echo esc_html( $tp_titulo ); ?></h2>
	</div>

	<div class="xf-criadores__lista">
		<?php
		foreach ( $tp_criadores as $tp_c ) :
			$tp_url    = tikporn_url_perfil( $tp_c->ID );
			$tp_nome   = $tp_c->display_name;
			$tp_videos = isset( $tp_c->tikporn_total_videos ) ? (int) $tp_c->tikporn_total_videos : tikporn_total_videos( $tp_c->ID );
			$tp_seg    = tikporn_seguidores( $tp_c->ID );

			// Thumb grande: foto de perfil (medium) ou avatar.
			$tp_foto_id = get_user_meta( $tp_c->ID, 'tikporn_foto_id', true );
			$tp_thumb   = $tp_foto_id ? wp_get_attachment_image_url( $tp_foto_id, 'medium' ) : '';
			if ( ! $tp_thumb ) {
				$tp_thumb = get_avatar_url( $tp_c->ID, array( 'size' => 300 ) );
			}
			?>
			<a class="xf-criador" href="<?php echo esc_url( $tp_url ); ?>">
				<span class="xf-criador__thumb" style="background-image:url('<?php echo esc_url( $tp_thumb ); ?>')"></span>
				<span class="xf-criador__info">
					<span class="xf-criador__nome"><?php echo esc_html( $tp_nome ); ?></span>
					<span class="xf-criador__stats">
						<span class="xf-criador__stat">
							<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.6-4.9-10-9.2C.5 8.3 2.1 5 5.3 5 7.2 5 8.6 6.1 12 9.2 15.4 6.1 16.8 5 18.7 5 21.9 5 23.5 8.3 22 11.8 19.6 16.1 12 21 12 21z"/></svg>
							<?php echo esc_html( tikporn_numero_k( $tp_seg ) ); ?>
						</span>
						<span class="xf-criador__stat">
							<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 4h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm6 4v8l6-4-6-4z"/></svg>
							<?php echo esc_html( $tp_videos ); ?>
						</span>
					</span>
				</span>
				<span class="xf-criador__btn"><?php esc_html_e( 'Ver perfil', 'tikporn' ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
