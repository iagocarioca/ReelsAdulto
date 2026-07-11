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
		// Sidebar de criadores.
		'criadores_titulo'     => 'Criadores que você pode gostar',
		'criadores_qtd'        => 6,
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
	add_settings_field(
		'criadores',
		__( 'Sidebar de criadores', 'tikporn' ),
		'tikporn_campo_criadores',
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
	foreach ( array( 'home_playlists_titulo', 'home_playlists_link', 'home_tendencias_titulo', 'busca_placeholder', 'criadores_titulo' ) as $chave_txt ) {
		if ( isset( $entrada[ $chave_txt ] ) ) {
			$saida[ $chave_txt ] = sanitize_text_field( wp_unslash( $entrada[ $chave_txt ] ) );
		}
	}

	// Sidebar de criadores — quantidade (0 = esconde a seção).
	if ( isset( $entrada['criadores_qtd'] ) ) {
		$saida['criadores_qtd'] = min( 12, max( 0, absint( $entrada['criadores_qtd'] ) ) );
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

function tikporn_campo_criadores() {
	printf(
		'<input type="text" name="tikporn_opcoes[criadores_titulo]" value="%s" class="large-text" placeholder="%s" />',
		esc_attr( tikporn_opcao( 'criadores_titulo' ) ),
		esc_attr__( 'Criadores que você pode gostar', 'tikporn' )
	);
	echo '<div class="tikporn-qtd-grid" style="margin-top:12px;">';
	printf(
		'<label>%s<input type="number" min="0" max="12" name="tikporn_opcoes[criadores_qtd]" value="%d" class="small-text" /></label>',
		esc_html__( 'Quantos criadores', 'tikporn' ),
		(int) tikporn_opcao( 'criadores_qtd' )
	);
	echo '</div>';
	echo '<p class="description">' . esc_html__( 'Cards de criadores (modelos com vídeos) na barra lateral. Use 0 para esconder.', 'tikporn' ) . '</p>';
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

	wp_add_inline_style( 'wp-color-picker', tikporn_opcoes_css() );
	wp_add_inline_script( 'wp-color-picker', tikporn_opcoes_js() );
}
add_action( 'admin_enqueue_scripts', 'tikporn_opcoes_assets' );

/**
 * CSS do painel moderno (injetado só na página de opções).
 * Usa tokens, respeita o modo escuro do admin e é responsivo.
 */
function tikporn_opcoes_css() {
	return <<<CSS
/* ---- Tokens ---- */
.tp-opt {
	--tp-bg: #f6f7fb;
	--tp-surface: #ffffff;
	--tp-surface-2: #f9fafc;
	--tp-border: #e5e7ef;
	--tp-text: #1c1f2b;
	--tp-muted: #6b7280;
	--tp-radius: 14px;
	--tp-shadow: 0 1px 2px rgba(16,24,40,.05), 0 8px 24px -12px rgba(16,24,40,.18);
	--tp-accent-ink: #fff;
	max-width: 1080px; margin: 20px 20px 0 2px;
}
@media (prefers-color-scheme: dark) {
	.tp-opt {
		--tp-bg: #14161d; --tp-surface: #1c1f2a; --tp-surface-2: #22252f;
		--tp-border: #2e323d; --tp-text: #e7e9ef; --tp-muted: #9aa0ac;
		--tp-shadow: 0 1px 2px rgba(0,0,0,.4), 0 10px 30px -14px rgba(0,0,0,.6);
	}
}
.tp-opt * { box-sizing: border-box; }

/* Remove notices/whitespace herdados do WP dentro do wrap */
.tp-opt .notice { display: none; }

/* ---- Hero ---- */
.tp-opt__hero {
	position: relative; overflow: hidden;
	display: flex; align-items: center; justify-content: space-between; gap: 16px;
	padding: 26px 28px; margin-bottom: 22px;
	border-radius: 18px; color: #fff;
	background:
		radial-gradient(1200px 200px at 0% 0%, rgba(255,255,255,.18), transparent 60%),
		linear-gradient(120deg, var(--tp-accent), color-mix(in srgb, var(--tp-accent) 55%, #7b2ff7));
	box-shadow: 0 18px 40px -18px color-mix(in srgb, var(--tp-accent) 70%, #000);
}
.tp-opt__hero-brand { display: flex; align-items: center; gap: 16px; }
.tp-opt__logo {
	display: grid; place-items: center; width: 52px; height: 52px; flex: 0 0 auto;
	border-radius: 14px; background: rgba(255,255,255,.18);
	backdrop-filter: blur(6px); box-shadow: inset 0 0 0 1px rgba(255,255,255,.25);
}
.tp-opt__logo svg { width: 26px; height: 26px; }
.tp-opt__title { margin: 0; font-size: 22px; font-weight: 800; letter-spacing: -.02em; color: #fff; }
.tp-opt__subtitle { margin: 3px 0 0; font-size: 13px; opacity: .9; }
.tp-opt__hero-status {
	display: inline-flex; align-items: center; gap: 6px;
	padding: 8px 14px; border-radius: 999px; font-weight: 700; font-size: 13px;
	background: rgba(255,255,255,.2); backdrop-filter: blur(6px);
	animation: tpFade .3s ease;
}
.tp-opt__hero-status .dashicons { font-size: 18px; width: 18px; height: 18px; }
@keyframes tpFade { from { opacity: 0; transform: translateY(-4px); } }

/* ---- Layout ---- */
.tp-opt__layout { display: grid; grid-template-columns: 232px 1fr; gap: 22px; align-items: start; }

/* ---- Navegação lateral ---- */
.tp-opt__nav {
	position: sticky; top: 42px;
	display: flex; flex-direction: column; gap: 4px;
	padding: 8px; border-radius: var(--tp-radius);
	background: var(--tp-surface); border: 1px solid var(--tp-border); box-shadow: var(--tp-shadow);
}
.tp-opt__nav-item {
	display: flex; align-items: center; gap: 11px; width: 100%;
	padding: 11px 13px; border: 0; cursor: pointer; text-align: left;
	border-radius: 10px; background: transparent; color: var(--tp-text);
	font-size: 13.5px; font-weight: 600; line-height: 1.2;
	transition: background .15s ease, color .15s ease, transform .1s ease;
}
.tp-opt__nav-item:hover { background: var(--tp-surface-2); }
.tp-opt__nav-item:active { transform: scale(.99); }
.tp-opt__nav-item .dashicons { font-size: 19px; width: 19px; height: 19px; color: var(--tp-muted); transition: color .15s; }
.tp-opt__nav-item.is-active {
	background: color-mix(in srgb, var(--tp-accent) 12%, transparent);
	color: color-mix(in srgb, var(--tp-accent) 72%, var(--tp-text));
}
.tp-opt__nav-item.is-active .dashicons { color: var(--tp-accent); }

/* ---- Painéis ---- */
.tp-opt__panel { animation: tpSlide .25s ease; }
@keyframes tpSlide { from { opacity: 0; transform: translateY(6px); } }
.tp-opt__panel-head { margin: 2px 0 16px; }
.tp-opt__panel-head h2 { margin: 0; font-size: 18px; font-weight: 800; color: var(--tp-text); letter-spacing: -.01em; }
.tp-opt__panel-head p { margin: 4px 0 0; font-size: 13px; color: var(--tp-muted); }

.tp-opt__cards { display: flex; flex-direction: column; gap: 12px; }
.tp-opt__field {
	display: grid; grid-template-columns: 240px 1fr; gap: 18px; align-items: start;
	padding: 18px 20px; border-radius: var(--tp-radius);
	background: var(--tp-surface); border: 1px solid var(--tp-border); box-shadow: var(--tp-shadow);
	transition: border-color .15s ease;
}
.tp-opt__field:focus-within { border-color: color-mix(in srgb, var(--tp-accent) 45%, var(--tp-border)); }
.tp-opt__field-label { font-size: 13.5px; font-weight: 700; color: var(--tp-text); padding-top: 8px; }
.tp-opt__field-control { min-width: 0; }
.tp-opt__field-control .description { margin: 8px 0 0; color: var(--tp-muted); font-size: 12.5px; font-style: normal; }

/* ---- Inputs (sobrescreve o visual padrão do WP) ---- */
.tp-opt input[type=text], .tp-opt input[type=url], .tp-opt input[type=password],
.tp-opt input[type=number], .tp-opt input[type=search] {
	width: 100%; max-width: 460px; margin: 0;
	padding: 10px 13px; border-radius: 10px;
	border: 1.5px solid var(--tp-border); background: var(--tp-surface-2); color: var(--tp-text);
	font-size: 14px; line-height: 1.3; box-shadow: none;
	transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
}
.tp-opt input[type=number] { width: 96px; max-width: 96px; }
.tp-opt input:focus {
	outline: 0; background: var(--tp-surface);
	border-color: var(--tp-accent);
	box-shadow: 0 0 0 4px color-mix(in srgb, var(--tp-accent) 18%, transparent);
}
.tp-opt input::placeholder { color: var(--tp-muted); opacity: .7; }

/* Grid de quantidades: pílulas com stepper */
.tikporn-qtd-grid { display: flex; flex-wrap: wrap; gap: 14px; }
.tikporn-qtd-grid label {
	display: flex; flex-direction: column; gap: 7px;
	font-size: 12.5px; font-weight: 700; color: var(--tp-text);
}

/* Toggle switch para checkbox */
.tp-opt__field-control label { display: inline-flex; align-items: center; gap: 12px; font-size: 14px; color: var(--tp-text); cursor: pointer; }
.tp-opt__field-control input[type=checkbox] {
	appearance: none; -webkit-appearance: none; position: relative; flex: 0 0 auto;
	width: 46px; height: 26px; margin: 0; border-radius: 999px; cursor: pointer;
	background: var(--tp-border); border: 0; transition: background .2s ease;
}
.tp-opt__field-control input[type=checkbox]::after {
	content: ""; position: absolute; top: 3px; left: 3px; width: 20px; height: 20px;
	border-radius: 999px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.3);
	transition: transform .2s cubic-bezier(.2,.8,.2,1);
}
.tp-opt__field-control input[type=checkbox]:checked { background: var(--tp-accent); }
.tp-opt__field-control input[type=checkbox]:checked::after { transform: translateX(20px); }
.tp-opt__field-control input[type=checkbox]:focus-visible { box-shadow: 0 0 0 4px color-mix(in srgb, var(--tp-accent) 22%, transparent); }

/* Color picker do WP integrado ao tema */
.tp-opt .wp-picker-container { display: inline-block; }
.tp-opt .wp-color-result.button { border-radius: 10px; height: 42px; border: 1.5px solid var(--tp-border); }

/* ---- Rodapé de ações (sticky) ---- */
.tp-opt__actions {
	position: sticky; bottom: 0; z-index: 5;
	display: flex; justify-content: flex-end; gap: 12px;
	margin-top: 22px; padding: 16px 4px;
	background: linear-gradient(to top, var(--tp-bg) 55%, transparent);
}
.tp-opt .tp-opt__save.button-primary {
	height: auto; padding: 11px 26px; border-radius: 11px;
	font-size: 14px; font-weight: 700; border: 0;
	background: var(--tp-accent); box-shadow: 0 8px 20px -8px var(--tp-accent);
	transition: transform .1s ease, filter .15s ease;
}
.tp-opt .tp-opt__save.button-primary:hover { filter: brightness(1.06); transform: translateY(-1px); }
.tp-opt .tp-opt__save.button-primary:active { transform: translateY(0); }

/* ---- Responsivo ---- */
@media (max-width: 782px) {
	.tp-opt { margin-right: 12px; }
	.tp-opt__layout { grid-template-columns: 1fr; }
	.tp-opt__nav {
		position: static; flex-direction: row; overflow-x: auto; gap: 6px;
		scrollbar-width: none;
	}
	.tp-opt__nav::-webkit-scrollbar { display: none; }
	.tp-opt__nav-item { flex: 0 0 auto; }
	.tp-opt__nav-label { white-space: nowrap; }
	.tp-opt__field { grid-template-columns: 1fr; gap: 10px; }
	.tp-opt__field-label { padding-top: 0; }
	.tp-opt__hero { flex-direction: column; align-items: flex-start; }
}
CSS;
}

/**
 * JS do painel: navegação por abas + preview da cor em tempo real.
 */
function tikporn_opcoes_js() {
	return <<<'JS'
jQuery(function ($) {
	var $wrap = $('.tp-opt');
	if (!$wrap.length) { return; }

	/* Color picker com preview ao vivo no hero */
	$('.tikporn-cor').wpColorPicker({
		change: function (event, ui) {
			var c = ui.color.toString();
			$wrap.css('--tp-accent', c);
		},
		clear: function () {
			$wrap.css('--tp-accent', '#FC30B7');
		}
	});

	/* Navegação por abas (persiste a aba ativa em sessionStorage) */
	function ativar(id) {
		$('.tp-opt__nav-item').each(function () {
			var on = $(this).data('tp-tab') === id;
			$(this).toggleClass('is-active', on).attr('aria-selected', on ? 'true' : 'false');
		});
		$('.tp-opt__panel').each(function () {
			var on = $(this).data('tp-panel') === id;
			$(this).toggleClass('is-active', on).prop('hidden', !on);
		});
		try { sessionStorage.setItem('tpOptTab', id); } catch (e) {}
	}

	$('.tp-opt__nav-item').on('click', function () { ativar($(this).data('tp-tab')); });

	/* Restaura a última aba (útil após salvar, que recarrega a página) */
	var saved;
	try { saved = sessionStorage.getItem('tpOptTab'); } catch (e) {}
	if (saved && $('.tp-opt__nav-item[data-tp-tab="' + saved + '"]').length) { ativar(saved); }

	/* Aviso "Salvo" após redirect com settings-updated */
	if (/settings-updated=true/.test(window.location.search)) {
		$('[data-tp-saved]').prop('hidden', false);
		setTimeout(function () { $('[data-tp-saved]').fadeOut(300); }, 2600);
	}
});
JS;
}

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
 * Estrutura das abas do painel: id, rótulo, ícone (dashicon) e campos.
 * Cada campo aponta para o callback de render já existente.
 */
function tikporn_opcoes_abas() {
	return array(
		'aparencia' => array(
			'label' => __( 'Aparência', 'tikporn' ),
			'icon'  => 'admin-customizer',
			'desc'  => __( 'Cor e identidade visual do tema.', 'tikporn' ),
			'campos' => array(
				array( 'label' => __( 'Cor de destaque', 'tikporn' ), 'cb' => 'tikporn_campo_cor_destaque' ),
			),
		),
		'home' => array(
			'label' => __( 'Página inicial', 'tikporn' ),
			'icon'  => 'admin-home',
			'desc'  => __( 'Títulos, quantidades e seções exibidas na home.', 'tikporn' ),
			'campos' => array(
				array( 'label' => __( 'Título da seção de playlists', 'tikporn' ), 'cb' => 'tikporn_campo_home_playlists_titulo' ),
				array( 'label' => __( 'Texto do link "ver todas"', 'tikporn' ), 'cb' => 'tikporn_campo_home_playlists_link' ),
				array( 'label' => __( 'Título da grade de vídeos', 'tikporn' ), 'cb' => 'tikporn_campo_home_tendencias_titulo' ),
				array( 'label' => __( 'Quantidades', 'tikporn' ), 'cb' => 'tikporn_campo_home_quantidades' ),
				array( 'label' => __( 'Sidebar de criadores', 'tikporn' ), 'cb' => 'tikporn_campo_criadores' ),
			),
		),
		'textos' => array(
			'label' => __( 'Textos', 'tikporn' ),
			'icon'  => 'editor-textcolor',
			'desc'  => __( 'Textos avulsos exibidos na interface.', 'tikporn' ),
			'campos' => array(
				array( 'label' => __( 'Texto do campo de busca', 'tikporn' ), 'cb' => 'tikporn_campo_busca_placeholder' ),
			),
		),
		'aurora5' => array(
			'label' => __( 'Vídeo (Aurora5)', 'tikporn' ),
			'icon'  => 'shield',
			'desc'  => __( 'Assinatura de vídeos importados via REST por UUID.', 'tikporn' ),
			'campos' => array(
				array( 'label' => __( 'Secret', 'tikporn' ), 'cb' => 'tikporn_campo_aurora5_secret' ),
				array( 'label' => __( 'URL base', 'tikporn' ), 'cb' => 'tikporn_campo_aurora5_base' ),
				array( 'label' => __( 'Validade do link (segundos)', 'tikporn' ), 'cb' => 'tikporn_campo_aurora5_ttl' ),
			),
		),
		'geral' => array(
			'label' => __( 'Geral', 'tikporn' ),
			'icon'  => 'admin-settings',
			'desc'  => __( 'Comportamento geral do site.', 'tikporn' ),
			'campos' => array(
				array( 'label' => __( 'Contar visualizações', 'tikporn' ), 'cb' => 'tikporn_campo_registrar_views' ),
			),
		),
	);
}

/**
 * Renderiza a página de opções — painel moderno com navegação por abas.
 */
function tikporn_opcoes_pagina() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$abas    = tikporn_opcoes_abas();
	$cor     = sanitize_hex_color( tikporn_opcao( 'cor_destaque' ) ) ?: '#FC30B7';
	$primeira = key( $abas );
	?>
	<div class="wrap tp-opt" style="--tp-accent: <?php echo esc_attr( $cor ); ?>;">

		<!-- Cabeçalho -->
		<header class="tp-opt__hero">
			<div class="tp-opt__hero-brand">
				<span class="tp-opt__logo" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none"><path d="M8 5v14l11-7z" fill="currentColor"/></svg>
				</span>
				<div>
					<h1 class="tp-opt__title"><?php esc_html_e( 'Opções do tema', 'tikporn' ); ?></h1>
					<p class="tp-opt__subtitle"><?php echo esc_html( sprintf( __( 'tikporn • versão %s', 'tikporn' ), defined( 'TIKPORN_VERSION' ) ? TIKPORN_VERSION : '' ) ); ?></p>
				</div>
			</div>
			<div class="tp-opt__hero-status" data-tp-saved hidden>
				<span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Salvo', 'tikporn' ); ?>
			</div>
		</header>

		<form action="options.php" method="post" class="tp-opt__form">
			<?php settings_fields( 'tikporn_opcoes_grupo' ); ?>

			<div class="tp-opt__layout">

				<!-- Navegação lateral -->
				<nav class="tp-opt__nav" role="tablist" aria-label="<?php esc_attr_e( 'Seções', 'tikporn' ); ?>">
					<?php foreach ( $abas as $id => $aba ) : ?>
						<button type="button" class="tp-opt__nav-item<?php echo $id === $primeira ? ' is-active' : ''; ?>"
							role="tab" data-tp-tab="<?php echo esc_attr( $id ); ?>"
							aria-selected="<?php echo $id === $primeira ? 'true' : 'false'; ?>">
							<span class="dashicons dashicons-<?php echo esc_attr( $aba['icon'] ); ?>"></span>
							<span class="tp-opt__nav-label"><?php echo esc_html( $aba['label'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</nav>

				<!-- Painéis -->
				<div class="tp-opt__panels">
					<?php foreach ( $abas as $id => $aba ) : ?>
						<section class="tp-opt__panel<?php echo $id === $primeira ? ' is-active' : ''; ?>"
							role="tabpanel" data-tp-panel="<?php echo esc_attr( $id ); ?>"
							<?php echo $id === $primeira ? '' : 'hidden'; ?>>
							<div class="tp-opt__panel-head">
								<h2><?php echo esc_html( $aba['label'] ); ?></h2>
								<p><?php echo esc_html( $aba['desc'] ); ?></p>
							</div>
							<div class="tp-opt__cards">
								<?php foreach ( $aba['campos'] as $campo ) : ?>
									<div class="tp-opt__field">
										<label class="tp-opt__field-label"><?php echo esc_html( $campo['label'] ); ?></label>
										<div class="tp-opt__field-control">
											<?php call_user_func( $campo['cb'] ); ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</section>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Rodapé fixo -->
			<div class="tp-opt__actions">
				<?php submit_button( __( 'Salvar alterações', 'tikporn' ), 'primary tp-opt__save', 'submit', false ); ?>
			</div>
		</form>
	</div>
	<?php
}
