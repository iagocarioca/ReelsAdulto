<?php
/**
 * Opções do Tema — página de configurações no painel (Aparência → Opções do tema).
 *
 * Usa a Settings API do WordPress. Tudo fica guardado numa única opção
 * (array) chamada "tikporn_opcoes".
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valores padrão das opções.
 */
function tikporn_opcoes_padrao() {
	return array(
		// Aparência.
		'cor_destaque'         => '#FC30B7',
		// Página inicial (Home) — textos.
		'home_playlists_titulo' => 'Playlist & Chill',
		'home_playlists_link'   => 'Todas as Playlists',
		'home_tendencias_titulo' => 'Tendências porno móvel e vídeos de sexo',
		// Página inicial (Home) — quantidades.
		'home_qtd_playlists'   => 4,
		'home_qtd_videos'      => 18,
		'home_qtd_categorias'  => 24,
		// Textos gerais.
		'busca_placeholder'    => 'Pesquisar vídeos...',
		// Aurora5 (vídeo por UUID).
		'aurora5_secret'       => '',
		'aurora5_base'         => 'https://api.aurora5.com/secure-video/',
		'aurora5_ttl'          => 1800,
		// Geral.
		'registrar_views'      => 1,
	);
}

/**
 * Retorna uma opção do tema (com fallback no padrão).
 *
 * @param string $chave   Chave da opção.
 * @param mixed  $default Valor caso não exista (opcional).
 * @return mixed
 */
function tikporn_opcao( $chave, $default = null ) {
	$padroes = tikporn_opcoes_padrao();
	$opcoes  = get_option( 'tikporn_opcoes', array() );
	$opcoes  = is_array( $opcoes ) ? $opcoes : array();

	if ( isset( $opcoes[ $chave ] ) && '' !== $opcoes[ $chave ] ) {
		return $opcoes[ $chave ];
	}
	if ( null !== $default ) {
		return $default;
	}
	return isset( $padroes[ $chave ] ) ? $padroes[ $chave ] : '';
}

/**
 * Registra o menu no painel (Aparência → Opções do tema).
 */
function tikporn_opcoes_menu() {
	add_theme_page(
		__( 'Opções do tema', 'tikporn' ),
		__( 'Opções do tema', 'tikporn' ),
		'manage_options',
		'tikporn-opcoes',
		'tikporn_opcoes_pagina'
	);
}
add_action( 'admin_menu', 'tikporn_opcoes_menu' );

/**
 * Registra as configurações, seções e campos.
 */
function tikporn_opcoes_registrar() {
	register_setting(
		'tikporn_opcoes_grupo',
		'tikporn_opcoes',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'tikporn_opcoes_sanitizar',
			'default'           => tikporn_opcoes_padrao(),
		)
	);

	// Seção: Aparência.
	add_settings_section(
		'tikporn_secao_aparencia',
		__( 'Aparência', 'tikporn' ),
		function () {
			echo '<p>' . esc_html__( 'Cores e visual do tema.', 'tikporn' ) . '</p>';
		},
		'tikporn-opcoes'
	);
	add_settings_field(
		'cor_destaque',
		__( 'Cor de destaque', 'tikporn' ),
		'tikporn_campo_cor_destaque',
		'tikporn-opcoes',
		'tikporn_secao_aparencia'
	);

	// Seção: Página inicial (Home).
	add_settings_section(
		'tikporn_secao_home',
		__( 'Página inicial', 'tikporn' ),
		function () {
			echo '<p>' . esc_html__( 'Títulos e quantidades exibidas na home. Deixe um título em branco para usar o texto padrão.', 'tikporn' ) . '</p>';
		},
		'tikporn-opcoes'
	);
	add_settings_field(
		'home_playlists_titulo',
		__( 'Título da seção de playlists', 'tikporn' ),
		'tikporn_campo_home_playlists_titulo',
		'tikporn-opcoes',
		'tikporn_secao_home'
	);
	add_settings_field(
		'home_playlists_link',
		__( 'Texto do link "ver todas"', 'tikporn' ),
		'tikporn_campo_home_playlists_link',
		'tikporn-opcoes',
		'tikporn_secao_home'
	);
	add_settings_field(
		'home_tendencias_titulo',
		__( 'Título da grade de vídeos', 'tikporn' ),
		'tikporn_campo_home_tendencias_titulo',
		'tikporn-opcoes',
		'tikporn_secao_home'
	);
	add_settings_field(
		'home_quantidades',
		__( 'Quantidades', 'tikporn' ),
		'tikporn_campo_home_quantidades',
		'tikporn-opcoes',
		'tikporn_secao_home'
	);

	// Seção: Textos gerais.
	add_settings_section(
		'tikporn_secao_textos',
		__( 'Textos gerais', 'tikporn' ),
		'__return_false',
		'tikporn-opcoes'
	);
	add_settings_field(
		'busca_placeholder',
		__( 'Texto do campo de busca', 'tikporn' ),
		'tikporn_campo_busca_placeholder',
		'tikporn-opcoes',
		'tikporn_secao_textos'
	);

	// Seção: Vídeo por UUID (Aurora5).
	add_settings_section(
		'tikporn_secao_aurora5',
		__( 'Vídeo por UUID (Aurora5)', 'tikporn' ),
		function () {
			echo '<p>' . esc_html__( 'Usado quando o vídeo é importado via REST informando apenas o UUID. O endereço final do MP4 é gerado e assinado na hora de tocar.', 'tikporn' ) . '</p>';
		},
		'tikporn-opcoes'
	);
	add_settings_field(
		'aurora5_secret',
		__( 'Secret', 'tikporn' ),
		'tikporn_campo_aurora5_secret',
		'tikporn-opcoes',
		'tikporn_secao_aurora5'
	);
	add_settings_field(
		'aurora5_base',
		__( 'URL base', 'tikporn' ),
		'tikporn_campo_aurora5_base',
		'tikporn-opcoes',
		'tikporn_secao_aurora5'
	);
	add_settings_field(
		'aurora5_ttl',
		__( 'Validade do link (segundos)', 'tikporn' ),
		'tikporn_campo_aurora5_ttl',
		'tikporn-opcoes',
		'tikporn_secao_aurora5'
	);

	// Seção: Geral.
	add_settings_section(
		'tikporn_secao_geral',
		__( 'Geral', 'tikporn' ),
		'__return_false',
		'tikporn-opcoes'
	);
	add_settings_field(
		'registrar_views',
		__( 'Contar visualizações', 'tikporn' ),
		'tikporn_campo_registrar_views',
		'tikporn-opcoes',
		'tikporn_secao_geral'
	);
}
add_action( 'admin_init', 'tikporn_opcoes_registrar' );

/**
 * Limpa e valida os valores antes de salvar.
 */
function tikporn_opcoes_sanitizar( $entrada ) {
	$entrada = is_array( $entrada ) ? $entrada : array();
	$saida   = tikporn_opcoes_padrao();

	// Cor de destaque (hex).
	if ( isset( $entrada['cor_destaque'] ) ) {
		$cor = sanitize_hex_color( $entrada['cor_destaque'] );
		if ( $cor ) {
			$saida['cor_destaque'] = $cor;
		}
	}

	// Página inicial — textos (vazio = usa o padrão via tikporn_opcao).
	foreach ( array( 'home_playlists_titulo', 'home_playlists_link', 'home_tendencias_titulo', 'busca_placeholder' ) as $chave_txt ) {
		if ( isset( $entrada[ $chave_txt ] ) ) {
			$saida[ $chave_txt ] = sanitize_text_field( wp_unslash( $entrada[ $chave_txt ] ) );
		}
	}

	// Página inicial — quantidades (limites sensatos).
	if ( isset( $entrada['home_qtd_playlists'] ) ) {
		$saida['home_qtd_playlists'] = min( 12, max( 0, absint( $entrada['home_qtd_playlists'] ) ) );
	}
	if ( isset( $entrada['home_qtd_videos'] ) ) {
		$saida['home_qtd_videos'] = min( 60, max( 1, absint( $entrada['home_qtd_videos'] ) ) );
	}
	if ( isset( $entrada['home_qtd_categorias'] ) ) {
		$saida['home_qtd_categorias'] = min( 60, max( 0, absint( $entrada['home_qtd_categorias'] ) ) );
	}

	// Aurora5.
	if ( isset( $entrada['aurora5_secret'] ) ) {
		$saida['aurora5_secret'] = sanitize_text_field( $entrada['aurora5_secret'] );
	}
	if ( isset( $entrada['aurora5_base'] ) ) {
		$saida['aurora5_base'] = esc_url_raw( trim( $entrada['aurora5_base'] ) );
	}
	if ( isset( $entrada['aurora5_ttl'] ) ) {
		$saida['aurora5_ttl'] = max( 60, absint( $entrada['aurora5_ttl'] ) );
	}

	// Geral.
	$saida['registrar_views'] = empty( $entrada['registrar_views'] ) ? 0 : 1;

	return $saida;
}

/* -------------------------------------------------------------------------
 * Campos do formulário.
 * ---------------------------------------------------------------------- */

function tikporn_campo_cor_destaque() {
	$valor = tikporn_opcao( 'cor_destaque' );
	printf(
		'<input type="text" name="tikporn_opcoes[cor_destaque]" value="%s" class="tikporn-cor" data-default-color="#FC30B7" />',
		esc_attr( $valor )
	);
	echo '<p class="description">' . esc_html__( 'Cor principal do tema (botões, links, destaques).', 'tikporn' ) . '</p>';
}

function tikporn_campo_home_playlists_titulo() {
	printf(
		'<input type="text" name="tikporn_opcoes[home_playlists_titulo]" value="%s" class="regular-text" placeholder="%s" />',
		esc_attr( tikporn_opcao( 'home_playlists_titulo' ) ),
		esc_attr__( 'Playlist & Chill', 'tikporn' )
	);
	echo '<p class="description">' . esc_html__( 'Título da fileira de playlists no topo da home.', 'tikporn' ) . '</p>';
}

function tikporn_campo_home_playlists_link() {
	printf(
		'<input type="text" name="tikporn_opcoes[home_playlists_link]" value="%s" class="regular-text" placeholder="%s" />',
		esc_attr( tikporn_opcao( 'home_playlists_link' ) ),
		esc_attr__( 'Todas as Playlists', 'tikporn' )
	);
	echo '<p class="description">' . esc_html__( 'Texto do link à direita do título de playlists.', 'tikporn' ) . '</p>';
}

function tikporn_campo_home_tendencias_titulo() {
	printf(
		'<input type="text" name="tikporn_opcoes[home_tendencias_titulo]" value="%s" class="large-text" placeholder="%s" />',
		esc_attr( tikporn_opcao( 'home_tendencias_titulo' ) ),
		esc_attr__( 'Tendências porno móvel e vídeos de sexo', 'tikporn' )
	);
	echo '<p class="description">' . esc_html__( 'Título da grade principal de vídeos da home.', 'tikporn' ) . '</p>';
}

function tikporn_campo_home_quantidades() {
	$pl  = (int) tikporn_opcao( 'home_qtd_playlists' );
	$vid = (int) tikporn_opcao( 'home_qtd_videos' );
	$cat = (int) tikporn_opcao( 'home_qtd_categorias' );
	echo '<div class="tikporn-qtd-grid">';
	printf(
		'<label>%s<input type="number" min="0" max="12" name="tikporn_opcoes[home_qtd_playlists]" value="%d" class="small-text" /></label>',
		esc_html__( 'Playlists', 'tikporn' ),
		$pl
	);
	printf(
		'<label>%s<input type="number" min="1" max="60" name="tikporn_opcoes[home_qtd_videos]" value="%d" class="small-text" /></label>',
		esc_html__( 'Vídeos na grade', 'tikporn' ),
		$vid
	);
	printf(
		'<label>%s<input type="number" min="0" max="60" name="tikporn_opcoes[home_qtd_categorias]" value="%d" class="small-text" /></label>',
		esc_html__( 'Categorias na barra lateral', 'tikporn' ),
		$cat
	);
	echo '</div>';
	echo '<p class="description">' . esc_html__( 'Quantos itens exibir em cada seção da home.', 'tikporn' ) . '</p>';
}

function tikporn_campo_busca_placeholder() {
	printf(
		'<input type="text" name="tikporn_opcoes[busca_placeholder]" value="%s" class="regular-text" placeholder="%s" />',
		esc_attr( tikporn_opcao( 'busca_placeholder' ) ),
		esc_attr__( 'Pesquisar vídeos...', 'tikporn' )
	);
	echo '<p class="description">' . esc_html__( 'Texto de exemplo dentro do campo de busca do cabeçalho.', 'tikporn' ) . '</p>';
}

function tikporn_campo_aurora5_secret() {
	$valor = tikporn_opcao( 'aurora5_secret' );
	printf(
		'<input type="password" name="tikporn_opcoes[aurora5_secret]" value="%s" class="regular-text" autocomplete="new-password" />',
		esc_attr( $valor )
	);
	echo '<p class="description">' . esc_html__( 'Chave secreta para assinar os links de vídeo. Não compartilhe.', 'tikporn' ) . '</p>';
	if ( defined( 'TIKPORN_AURORA5_SECRET' ) && TIKPORN_AURORA5_SECRET ) {
		echo '<p class="description" style="color:#b26a00;">' . esc_html__( 'Obs.: a constante TIKPORN_AURORA5_SECRET está definida no wp-config e tem prioridade sobre este campo.', 'tikporn' ) . '</p>';
	}
}

function tikporn_campo_aurora5_base() {
	$valor = tikporn_opcao( 'aurora5_base' );
	printf(
		'<input type="url" name="tikporn_opcoes[aurora5_base]" value="%s" class="regular-text" placeholder="https://api.aurora5.com/secure-video/" />',
		esc_attr( $valor )
	);
	echo '<p class="description">' . esc_html__( 'Endereço base do servidor de vídeo seguro.', 'tikporn' ) . '</p>';
}

function tikporn_campo_aurora5_ttl() {
	$valor = tikporn_opcao( 'aurora5_ttl' );
	printf(
		'<input type="number" min="60" step="60" name="tikporn_opcoes[aurora5_ttl]" value="%s" class="small-text" />',
		esc_attr( $valor )
	);
	echo '<p class="description">' . esc_html__( 'Por quanto tempo cada link de vídeo é válido (mínimo 60s).', 'tikporn' ) . '</p>';
}

function tikporn_campo_registrar_views() {
	$valor = (int) tikporn_opcao( 'registrar_views' );
	printf(
		'<label><input type="checkbox" name="tikporn_opcoes[registrar_views]" value="1" %s /> %s</label>',
		checked( 1, $valor, false ),
		esc_html__( 'Contar uma visualização cada vez que um vídeo é assistido.', 'tikporn' )
	);
}

/**
 * Carrega o seletor de cor (color picker) na página de opções.
 */
function tikporn_opcoes_assets( $hook ) {
	if ( 'appearance_page_tikporn-opcoes' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_add_inline_script(
		'wp-color-picker',
		'jQuery(function($){ $(".tikporn-cor").wpColorPicker(); });'
	);

	// Visual da página de opções (cartões por seção + grid de quantidades).
	$css = '
		.tikporn-opcoes-wrap { max-width: 820px; }
		.tikporn-opcoes-wrap h1 { display: flex; align-items: center; gap: 10px; }
		.tikporn-opcoes-wrap h1 .dashicons { color: #FC30B7; font-size: 30px; width: 30px; height: 30px; }
		.tikporn-opcoes-wrap form > h2 {
			margin: 30px 0 0; padding: 16px 20px 0; font-size: 15px; font-weight: 600;
			background: #fff; border: 1px solid #e2e4e7; border-bottom: 0;
			border-radius: 10px 10px 0 0;
		}
		.tikporn-opcoes-wrap form > p {
			margin: 0; padding: 4px 20px 0; color: #646970; font-size: 13px;
			background: #fff; border-left: 1px solid #e2e4e7; border-right: 1px solid #e2e4e7;
		}
		.tikporn-opcoes-wrap form > .form-table {
			margin-top: 0; padding: 8px 20px 16px;
			background: #fff; border: 1px solid #e2e4e7; border-top: 0;
			border-radius: 0 0 10px 10px;
		}
		.tikporn-opcoes-wrap .form-table th { padding-left: 0; }
		.tikporn-qtd-grid { display: flex; flex-wrap: wrap; gap: 18px; }
		.tikporn-qtd-grid label {
			display: flex; flex-direction: column; gap: 5px;
			font-weight: 600; font-size: 13px; color: #1d2327;
		}
		.tikporn-qtd-grid input { width: 90px; }
		.tikporn-opcoes-wrap .submit { margin-top: 22px; }
	';
	wp_add_inline_style( 'wp-color-picker', $css );
}
add_action( 'admin_enqueue_scripts', 'tikporn_opcoes_assets' );

/**
 * Clareia uma cor hex misturando-a com branco.
 *
 * @param string $hex     Cor no formato #rrggbb.
 * @param float  $percent 0 = cor original, 1 = branco.
 * @return string Cor hex resultante.
 */
function tikporn_clarear_cor( $hex, $percent ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( 6 !== strlen( $hex ) ) {
		return '#' . $hex;
	}

	$percent = max( 0, min( 1, (float) $percent ) );
	$r       = hexdec( substr( $hex, 0, 2 ) );
	$g       = hexdec( substr( $hex, 2, 2 ) );
	$b       = hexdec( substr( $hex, 4, 2 ) );

	$r = (int) round( $r + ( 255 - $r ) * $percent );
	$g = (int) round( $g + ( 255 - $g ) * $percent );
	$b = (int) round( $b + ( 255 - $b ) * $percent );

	return sprintf( '#%02x%02x%02x', $r, $g, $b );
}

/**
 * Monta o CSS que sobrescreve a cor de destaque do tema.
 *
 * Gera as três variantes usadas no tube.css a partir da cor escolhida:
 *  --xf-roxo (base), --xf-roxo-2 (mais clara), --xf-roxo-suave (bem clara).
 *
 * @return string CSS pronto para injeção (vazio se for a cor padrão).
 */
function tikporn_css_cor_destaque() {
	$cor = sanitize_hex_color( tikporn_opcao( 'cor_destaque' ) );
	if ( ! $cor ) {
		return '';
	}
	// Se for igual ao padrão do CSS, não precisa injetar nada.
	if ( strtoupper( $cor ) === '#FC30B7' ) {
		return '';
	}

	$clara = tikporn_clarear_cor( $cor, 0.28 );
	$suave = tikporn_clarear_cor( $cor, 0.90 );

	return sprintf(
		':root{--xf-roxo:%s;--xf-roxo-2:%s;--xf-roxo-suave:%s;}',
		$cor,
		$clara,
		$suave
	);
}

/**
 * Renderiza a página de opções.
 */
function tikporn_opcoes_pagina() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap tikporn-opcoes-wrap">
		<h1><span class="dashicons dashicons-admin-appearance"></span> <?php echo esc_html__( 'Opções do tema', 'tikporn' ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'tikporn_opcoes_grupo' );
			do_settings_sections( 'tikporn-opcoes' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
