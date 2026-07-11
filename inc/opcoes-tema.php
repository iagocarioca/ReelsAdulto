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
	$valor = sanitize_hex_color( tikporn_opcao( 'cor_destaque' ) ) ?: '#FC30B7';
	?>
	<div class="tp-cor" data-tp-cor>
		<label class="tp-cor__swatch">
			<input type="color" value="<?php echo esc_attr( $valor ); ?>" data-tp-cor-picker aria-label="<?php esc_attr_e( 'Escolher cor', 'tikporn' ); ?>" />
		</label>
		<input type="text" name="tikporn_opcoes[cor_destaque]" value="<?php echo esc_attr( $valor ); ?>"
			class="tp-cor__hex" data-tp-cor-hex maxlength="7" spellcheck="false" aria-label="<?php esc_attr_e( 'Código da cor', 'tikporn' ); ?>" />
		<button type="button" class="tp-cor__reset" data-tp-cor-reset title="<?php esc_attr_e( 'Voltar ao padrão', 'tikporn' ); ?>">
			<?php esc_html_e( 'Padrão', 'tikporn' ); ?>
		</button>
	</div>
	<p class="description"><?php esc_html_e( 'Cor principal do tema (botões, links, destaques).', 'tikporn' ); ?></p>
	<?php
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
	// Estilo próprio (não depende mais do color picker do WP).
	wp_register_style( 'tikporn-opcoes', false, array(), TIKPORN_VERSION );
	wp_enqueue_style( 'tikporn-opcoes' );
	wp_add_inline_style( 'tikporn-opcoes', tikporn_opcoes_css() );

	// JS depende só do jQuery (sempre presente no admin).
	wp_add_inline_script( 'jquery-core', tikporn_opcoes_js() );
}
add_action( 'admin_enqueue_scripts', 'tikporn_opcoes_assets' );

/**
 * CSS do painel moderno (injetado só na página de opções).
 * Usa tokens, respeita o modo escuro do admin e é responsivo.
 */
function tikporn_opcoes_css() {
	return <<<CSS
/* =========================================================================
 * Painel de Opções — Design System (tokens: primitivo → semântico → componente)
 * ===================================================================== */

/* -------- 1) Tokens PRIMITIVOS + escalas fixas (não mudam com o tema) ---- */
.tp-opt {
	/* Cor primitiva — cinzas */
	--tp-gray-0: #ffffff;  --tp-gray-50: #f7f8fb; --tp-gray-100: #eef0f6;
	--tp-gray-200: #e3e6ef; --tp-gray-300: #cfd3e0; --tp-gray-400: #9aa0b2;
	--tp-gray-500: #6b7280; --tp-gray-600: #4b5163; --tp-gray-700: #343a49;
	--tp-gray-800: #232733; --tp-gray-900: #171a22; --tp-gray-950: #0e1017;
	/* Cor primitiva — feedback */
	--tp-green-500: #22c55e; --tp-amber-500: #f59e0b; --tp-red-500: #ef4444;

	/* Tipografia */
	--tp-font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
	--tp-text-xs: 12px; --tp-text-sm: 13px; --tp-text-base: 14px;
	--tp-text-lg: 16px; --tp-text-xl: 18px; --tp-text-2xl: 22px;
	--tp-weight-medium: 500; --tp-weight-semibold: 600; --tp-weight-bold: 700; --tp-weight-black: 800;
	--tp-leading-tight: 1.2; --tp-leading-normal: 1.5;
	--tp-tracking-tight: -0.02em;

	/* Espaçamento (escala 4px) */
	--tp-space-1: 4px; --tp-space-2: 8px; --tp-space-3: 12px; --tp-space-4: 16px;
	--tp-space-5: 20px; --tp-space-6: 24px; --tp-space-7: 28px; --tp-space-8: 32px;

	/* Raio */
	--tp-radius-sm: 8px; --tp-radius-md: 10px; --tp-radius-lg: 14px; --tp-radius-xl: 18px; --tp-radius-full: 999px;

	/* Movimento */
	--tp-duration-fast: 120ms; --tp-duration-normal: 180ms; --tp-duration-slow: 260ms;
	--tp-ease: cubic-bezier(0.2, 0.8, 0.2, 1);

	/* Camadas */
	--tp-z-sticky: 200;
}

/* -------- 2) Tokens SEMÂNTICOS — tema claro (padrão) -------------------- */
.tp-opt {
	--tp-bg: var(--tp-gray-50);
	--tp-surface: var(--tp-gray-0);
	--tp-surface-raised: var(--tp-gray-50);
	--tp-border: var(--tp-gray-200);
	--tp-border-strong: var(--tp-gray-300);
	--tp-text: var(--tp-gray-900);
	--tp-text-muted: var(--tp-gray-500);
	--tp-text-on-accent: #ffffff;
	--tp-shadow-sm: 0 1px 2px rgba(16,24,40,.05);
	--tp-shadow-md: 0 1px 2px rgba(16,24,40,.05), 0 8px 24px -12px rgba(16,24,40,.18);
	--tp-shadow-accent: 0 8px 20px -8px var(--tp-accent);

	/* --tp-accent é injetado inline no wrap (cor de destaque do tema) */
	--tp-accent-hover: color-mix(in srgb, var(--tp-accent) 88%, #000);
	--tp-accent-soft: color-mix(in srgb, var(--tp-accent) 12%, transparent);
	--tp-accent-ring: color-mix(in srgb, var(--tp-accent) 20%, transparent);
	--tp-success: var(--tp-green-500);
	--tp-warning: var(--tp-amber-500);

	max-width: 860px; margin: var(--tp-space-6) var(--tp-space-5) 0 0;
	font-family: var(--tp-font-sans);
	/* Pinta o próprio painel: o dark mode do painel vale mesmo se o admin for claro */
	background: var(--tp-bg); color: var(--tp-text);
	padding: var(--tp-space-7) var(--tp-space-8) var(--tp-space-8);
	border-radius: var(--tp-radius-xl);
	transition: background var(--tp-duration-normal) var(--tp-ease), color var(--tp-duration-normal) var(--tp-ease);
}

/* -------- 3) Tema escuro — override semântico -------------------------- */
@media (prefers-color-scheme: dark) {
	.tp-opt:not([data-tp-theme="light"]) {
		--tp-bg: var(--tp-gray-950);
		--tp-surface: var(--tp-gray-900);
		--tp-surface-raised: var(--tp-gray-800);
		--tp-border: var(--tp-gray-700);
		--tp-border-strong: var(--tp-gray-600);
		--tp-text: var(--tp-gray-50);
		--tp-text-muted: var(--tp-gray-400);
		--tp-shadow-sm: 0 1px 2px rgba(0,0,0,.4);
		--tp-shadow-md: 0 1px 2px rgba(0,0,0,.4), 0 10px 30px -14px rgba(0,0,0,.6);
	}
}
.tp-opt[data-tp-theme="dark"] {
	--tp-bg: var(--tp-gray-950);
	--tp-surface: var(--tp-gray-900);
	--tp-surface-raised: var(--tp-gray-800);
	--tp-border: var(--tp-gray-700);
	--tp-border-strong: var(--tp-gray-600);
	--tp-text: var(--tp-gray-50);
	--tp-text-muted: var(--tp-gray-400);
	--tp-shadow-sm: 0 1px 2px rgba(0,0,0,.4);
	--tp-shadow-md: 0 1px 2px rgba(0,0,0,.4), 0 10px 30px -14px rgba(0,0,0,.6);
}

.tp-opt, .tp-opt * { box-sizing: border-box; }
.tp-opt .notice { display: none; }

/* Acessibilidade: respeita quem prefere menos movimento */
@media (prefers-reduced-motion: reduce) {
	.tp-opt *, .tp-opt *::before, .tp-opt *::after {
		animation-duration: .01ms !important; transition-duration: .01ms !important;
	}
}

/* -------- Componente: HEADER (discreto, sem gradiente) ----------------- */
.tp-opt__head {
	display: flex; align-items: flex-start; justify-content: space-between; gap: var(--tp-space-4);
	padding: 0 var(--tp-space-1) var(--tp-space-5);
	margin-bottom: var(--tp-space-6);
	border-bottom: 1px solid var(--tp-border);
}
.tp-opt__head-brand { display: flex; align-items: center; gap: var(--tp-space-4); }
.tp-opt__logo {
	display: grid; place-items: center; width: 44px; height: 44px; flex: 0 0 auto;
	border-radius: var(--tp-radius-lg); color: var(--tp-accent);
	background: var(--tp-accent-soft);
}
.tp-opt__logo svg { width: 22px; height: 22px; }
.tp-opt__title { margin: 0; font-size: var(--tp-text-2xl); font-weight: var(--tp-weight-bold); letter-spacing: var(--tp-tracking-tight); color: var(--tp-text); line-height: 1.15; }
.tp-opt__subtitle { margin: 4px 0 0; font-size: var(--tp-text-sm); color: var(--tp-text-muted); }
.tp-opt__head-side { display: flex; align-items: center; gap: var(--tp-space-2); padding-top: 2px; }

/* Chip "Salvo" discreto */
.tp-opt__saved {
	display: inline-flex; align-items: center; gap: 6px;
	padding: 6px 12px; border-radius: var(--tp-radius-full);
	font-weight: var(--tp-weight-semibold); font-size: var(--tp-text-xs);
	color: var(--tp-success); background: color-mix(in srgb, var(--tp-success) 12%, transparent);
	animation: tpFade var(--tp-duration-normal) var(--tp-ease);
}
.tp-opt__saved[hidden] { display: none; }
.tp-opt__saved svg { width: 15px; height: 15px; }
@keyframes tpFade { from { opacity: 0; transform: translateY(-4px); } }

/* Botão de tema — outline discreto */
.tp-opt__theme {
	display: grid; place-items: center; width: 38px; height: 38px; flex: 0 0 auto;
	border: 1px solid var(--tp-border); cursor: pointer; border-radius: var(--tp-radius-md);
	color: var(--tp-text-muted); background: var(--tp-surface);
	transition: color var(--tp-duration-fast) var(--tp-ease), border-color var(--tp-duration-fast) var(--tp-ease), background var(--tp-duration-fast) var(--tp-ease);
}
.tp-opt__theme:hover { color: var(--tp-text); background: var(--tp-surface-raised); }
.tp-opt__theme:active { transform: scale(.94); }
.tp-opt__theme:focus-visible { outline: 2px solid var(--tp-accent); outline-offset: 2px; }
.tp-opt__theme svg { width: 18px; height: 18px; }
.tp-opt__theme-light { display: block; }
.tp-opt__theme-dark { display: none; }
.tp-opt[data-tp-scheme="dark"] .tp-opt__theme-light { display: none; }
.tp-opt[data-tp-scheme="dark"] .tp-opt__theme-dark { display: block; }

/* -------- Componente: LAYOUT + NAV (segmentada, leve) ------------------ */
.tp-opt__layout { display: grid; grid-template-columns: 210px 1fr; gap: var(--tp-space-8); align-items: start; }

.tp-opt__nav {
	position: sticky; top: 42px; z-index: var(--tp-z-sticky);
	display: flex; flex-direction: column; gap: 2px;
}
.tp-opt__nav-item {
	display: flex; align-items: center; gap: 11px; width: 100%;
	padding: 9px 12px; border: 0; cursor: pointer; text-align: left;
	border-radius: var(--tp-radius-md); background: transparent; color: var(--tp-text-muted);
	font-size: var(--tp-text-sm); font-weight: var(--tp-weight-medium); line-height: var(--tp-leading-tight);
	transition: background var(--tp-duration-fast) var(--tp-ease), color var(--tp-duration-fast) var(--tp-ease);
}
.tp-opt__nav-item:hover { background: var(--tp-surface-raised); color: var(--tp-text); }
.tp-opt__nav-item:focus-visible { outline: 2px solid var(--tp-accent); outline-offset: 2px; }
.tp-opt__nav-item .dashicons { font-size: 18px; width: 18px; height: 18px; opacity: .85; }
.tp-opt__nav-item.is-active {
	background: var(--tp-surface); color: var(--tp-text); font-weight: var(--tp-weight-semibold);
	box-shadow: var(--tp-shadow-sm), inset 0 0 0 1px var(--tp-border);
}
.tp-opt__nav-item.is-active .dashicons { color: var(--tp-accent); opacity: 1; }

/* -------- Componente: PAINEL ------------------------------------------- */
.tp-opt__panel { animation: tpSlide var(--tp-duration-slow) var(--tp-ease); }
@keyframes tpSlide { from { opacity: 0; transform: translateY(8px); } }
.tp-opt__panel-head { margin: 0 0 var(--tp-space-5); }
.tp-opt__panel-head h2 { margin: 0; font-size: var(--tp-text-xl); font-weight: var(--tp-weight-bold); color: var(--tp-text); letter-spacing: -0.01em; }
.tp-opt__panel-head p { margin: 5px 0 0; font-size: var(--tp-text-sm); color: var(--tp-text-muted); }

/* -------- Componente: CARD DE CAMPO (flutuante, macio) ----------------- */
.tp-opt__cards { display: flex; flex-direction: column; gap: var(--tp-space-4); }
.tp-opt__field {
	display: grid; grid-template-columns: 1fr; gap: var(--tp-space-3);
	padding: var(--tp-space-5) var(--tp-space-6); border-radius: var(--tp-radius-xl);
	background: var(--tp-surface); border: 1px solid var(--tp-border);
	box-shadow: 0 1px 2px rgba(16,24,40,.04), 0 12px 28px -18px rgba(16,24,40,.28);
	transition: box-shadow var(--tp-duration-normal) var(--tp-ease), transform var(--tp-duration-normal) var(--tp-ease);
}
.tp-opt__field:hover { box-shadow: 0 2px 6px rgba(16,24,40,.06), 0 18px 40px -20px rgba(16,24,40,.34); transform: translateY(-1px); }
.tp-opt__field-top { display: flex; align-items: center; gap: var(--tp-space-3); }
.tp-opt__field-ico {
	display: grid; place-items: center; width: 34px; height: 34px; flex: 0 0 auto;
	border-radius: var(--tp-radius-md); color: var(--tp-accent); background: var(--tp-accent-soft);
}
.tp-opt__field-ico .dashicons { font-size: 18px; width: 18px; height: 18px; }
.tp-opt__field-label { font-size: var(--tp-text-base); font-weight: var(--tp-weight-semibold); color: var(--tp-text); }
.tp-opt__field-control { min-width: 0; }
.tp-opt__field-control .description { margin: var(--tp-space-2) 0 0; color: var(--tp-text-muted); font-size: var(--tp-text-xs); font-style: normal; line-height: var(--tp-leading-normal); }

/* -------- Componente: INPUTS ------------------------------------------ */
.tp-opt input[type=text], .tp-opt input[type=url], .tp-opt input[type=password],
.tp-opt input[type=number], .tp-opt input[type=search] {
	width: 100%; margin: 0;
	padding: 11px 14px; border-radius: var(--tp-radius-md);
	border: 1px solid var(--tp-border); background: var(--tp-surface-raised); color: var(--tp-text);
	font-size: var(--tp-text-base); line-height: 1.3; box-shadow: none;
	transition: border-color var(--tp-duration-fast) var(--tp-ease), box-shadow var(--tp-duration-fast) var(--tp-ease), background var(--tp-duration-fast) var(--tp-ease);
}
.tp-opt input[type=number] { width: 88px; text-align: center; }
.tp-opt input:focus {
	outline: 0; background: var(--tp-surface);
	border-color: var(--tp-accent); box-shadow: 0 0 0 3px var(--tp-accent-ring);
}
.tp-opt input::placeholder { color: var(--tp-text-muted); opacity: .65; }

.tikporn-qtd-grid { display: flex; flex-wrap: wrap; gap: var(--tp-space-4); }
.tikporn-qtd-grid label {
	display: flex; flex-direction: column; gap: 8px;
	font-size: var(--tp-text-xs); font-weight: var(--tp-weight-semibold); color: var(--tp-text-muted);
	text-transform: uppercase; letter-spacing: .04em;
}

/* -------- Componente: TOGGLE ------------------------------------------ */
.tp-opt__field-control label { display: inline-flex; align-items: center; gap: var(--tp-space-3); font-size: var(--tp-text-base); color: var(--tp-text); cursor: pointer; }
.tp-opt__field-control input[type=checkbox] {
	appearance: none; -webkit-appearance: none; position: relative; flex: 0 0 auto;
	width: 44px; height: 25px; margin: 0; border-radius: var(--tp-radius-full); cursor: pointer;
	background: var(--tp-border-strong); border: 0; transition: background var(--tp-duration-normal) var(--tp-ease);
}
.tp-opt__field-control input[type=checkbox]::after {
	content: ""; position: absolute; top: 3px; left: 3px; width: 19px; height: 19px;
	border-radius: var(--tp-radius-full); background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.3);
	transition: transform var(--tp-duration-normal) var(--tp-ease);
}
.tp-opt__field-control input[type=checkbox]:checked { background: var(--tp-accent); }
.tp-opt__field-control input[type=checkbox]:checked::after { transform: translateX(19px); }
.tp-opt__field-control input[type=checkbox]:focus-visible { box-shadow: 0 0 0 3px var(--tp-accent-ring); }

/* -------- Componente: SELETOR DE COR ---------------------------------- */
.tp-cor { --tp-cor: var(--tp-accent); display: inline-flex; align-items: center; gap: var(--tp-space-3); }
.tp-cor__swatch {
	position: relative; display: block; width: 44px; height: 44px; flex: 0 0 auto;
	border-radius: var(--tp-radius-md); cursor: pointer; overflow: hidden;
	background: var(--tp-cor); box-shadow: inset 0 0 0 1px rgba(0,0,0,.12), 0 1px 2px rgba(16,24,40,.1);
	transition: transform var(--tp-duration-fast) var(--tp-ease);
}
.tp-cor__swatch:hover { transform: scale(1.05); }
.tp-cor__swatch input[type=color] {
	position: absolute; inset: -4px; width: calc(100% + 8px); height: calc(100% + 8px);
	border: 0; padding: 0; margin: 0; cursor: pointer; opacity: 0;
}
.tp-cor__hex {
	width: 108px !important; max-width: 108px !important; text-align: left;
	font-family: var(--tp-font-mono, ui-monospace, "SFMono-Regular", Menlo, monospace);
	text-transform: lowercase; letter-spacing: .02em;
}
.tp-cor__reset {
	border: 1px solid var(--tp-border); background: var(--tp-surface); color: var(--tp-text-muted);
	border-radius: var(--tp-radius-md); padding: 9px 14px; cursor: pointer;
	font-size: var(--tp-text-sm); font-weight: var(--tp-weight-medium);
	transition: color var(--tp-duration-fast) var(--tp-ease), border-color var(--tp-duration-fast) var(--tp-ease), background var(--tp-duration-fast) var(--tp-ease);
}
.tp-cor__reset:hover { color: var(--tp-text); background: var(--tp-surface-raised); }
.tp-cor__reset:focus-visible { outline: 2px solid var(--tp-accent); outline-offset: 2px; }

/* -------- Componente: AÇÕES (rodapé sticky) --------------------------- */
.tp-opt__actions {
	position: sticky; bottom: 0; z-index: var(--tp-z-sticky);
	display: flex; justify-content: flex-end; gap: var(--tp-space-3);
	margin-top: var(--tp-space-6); padding: var(--tp-space-4) var(--tp-space-1);
	background: linear-gradient(to top, var(--tp-bg) 60%, transparent);
}
.tp-opt .tp-opt__save.button-primary {
	height: auto; padding: 11px var(--tp-space-6); border-radius: var(--tp-radius-md);
	font-size: var(--tp-text-base); font-weight: var(--tp-weight-semibold); border: 0;
	background: var(--tp-accent); color: var(--tp-text-on-accent);
	box-shadow: 0 2px 8px -2px var(--tp-accent);
	transition: transform var(--tp-duration-fast) var(--tp-ease), background var(--tp-duration-fast) var(--tp-ease);
}
.tp-opt .tp-opt__save.button-primary:hover { background: var(--tp-accent-hover); transform: translateY(-1px); }
.tp-opt .tp-opt__save.button-primary:active { transform: translateY(0); }
.tp-opt .tp-opt__save.button-primary:focus-visible { outline: 2px solid var(--tp-accent); outline-offset: 3px; }

/* -------- Responsivo -------------------------------------------------- */
@media (max-width: 782px) {
	.tp-opt { margin-right: var(--tp-space-3); }
	.tp-opt__layout { grid-template-columns: 1fr; gap: var(--tp-space-5); }
	.tp-opt__nav {
		position: static; flex-direction: row; overflow-x: auto; gap: var(--tp-space-1);
		scrollbar-width: none; padding-bottom: 4px;
	}
	.tp-opt__nav::-webkit-scrollbar { display: none; }
	.tp-opt__nav-item { flex: 0 0 auto; }
	.tp-opt__nav-label { white-space: nowrap; }
	.tp-opt__head { flex-direction: column; }
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

	/* Seletor de cor nativo: swatch + campo hex sincronizados, preview ao vivo */
	(function () {
		var box = document.querySelector('[data-tp-cor]');
		if (!box) { return; }
		var picker = box.querySelector('[data-tp-cor-picker]');
		var hex    = box.querySelector('[data-tp-cor-hex]');
		var reset  = box.querySelector('[data-tp-cor-reset]');
		var PADRAO = '#FC30B7';

		function normalizar(v) {
			v = String(v || '').trim();
			if (v[0] !== '#') { v = '#' + v; }
			return /^#[0-9a-fA-F]{6}$/.test(v) ? v.toLowerCase() : null;
		}
		function aplicar(v, origem) {
			var c = normalizar(v);
			if (!c) { return; }
			$wrap[0].style.setProperty('--tp-accent', c);
			box.style.setProperty('--tp-cor', c);
			if (origem !== 'picker') { picker.value = c; }
			if (origem !== 'hex') { hex.value = c; }
		}
		box.style.setProperty('--tp-cor', hex.value || PADRAO);
		picker.addEventListener('input', function () { aplicar(picker.value, 'picker'); });
		hex.addEventListener('input', function () { aplicar(hex.value, 'hex'); });
		hex.addEventListener('blur', function () { if (!normalizar(hex.value)) { aplicar(picker.value, 'hex'); } });
		reset.addEventListener('click', function () { aplicar(PADRAO); });
	})();

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

	/* Alternância de tema do painel (claro → escuro → sistema), persistida.
	   Segue o padrão de theming da design system: data-tp-theme + prefers-color-scheme. */
	var wrapEl = $wrap[0];
	var mqDark = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : { matches: false, addEventListener: function () {} };

	function aplicarTema(modo) {
		if (modo === 'light' || modo === 'dark') {
			wrapEl.setAttribute('data-tp-theme', modo);
		} else {
			modo = 'system';
			wrapEl.removeAttribute('data-tp-theme'); // segue o SO
		}
		// Esquema efetivo (resolve 'system' pelo SO) — controla o ícone do botão.
		var efetivo = modo === 'system' ? (mqDark.matches ? 'dark' : 'light') : modo;
		wrapEl.setAttribute('data-tp-scheme', efetivo);
		try { localStorage.setItem('tpOptTheme', modo); } catch (e) {}
	}

	var temaSalvo = 'system';
	try { temaSalvo = localStorage.getItem('tpOptTheme') || 'system'; } catch (e) {}
	aplicarTema(temaSalvo);

	// Se estiver em 'system', reage à troca do SO em tempo real.
	mqDark.addEventListener && mqDark.addEventListener('change', function () {
		var modo = wrapEl.getAttribute('data-tp-theme') || 'system';
		if (modo === 'system') { aplicarTema('system'); }
	});

	$('[data-tp-theme-toggle]').on('click', function () {
		var atual = wrapEl.getAttribute('data-tp-theme') || 'system';
		var proximo = atual === 'light' ? 'dark' : (atual === 'dark' ? 'system' : 'light');
		aplicarTema(proximo);
	});

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
				array( 'label' => __( 'Cor de destaque', 'tikporn' ), 'icon' => 'art', 'cb' => 'tikporn_campo_cor_destaque' ),
			),
		),
		'home' => array(
			'label' => __( 'Página inicial', 'tikporn' ),
			'icon'  => 'admin-home',
			'desc'  => __( 'Títulos, quantidades e seções exibidas na home.', 'tikporn' ),
			'campos' => array(
				array( 'label' => __( 'Título da seção de playlists', 'tikporn' ), 'icon' => 'playlist-video', 'cb' => 'tikporn_campo_home_playlists_titulo' ),
				array( 'label' => __( 'Texto do link "ver todas"', 'tikporn' ), 'icon' => 'admin-links', 'cb' => 'tikporn_campo_home_playlists_link' ),
				array( 'label' => __( 'Título da grade de vídeos', 'tikporn' ), 'icon' => 'grid-view', 'cb' => 'tikporn_campo_home_tendencias_titulo' ),
				array( 'label' => __( 'Quantidades', 'tikporn' ), 'icon' => 'sort', 'cb' => 'tikporn_campo_home_quantidades' ),
				array( 'label' => __( 'Sidebar de criadores', 'tikporn' ), 'icon' => 'groups', 'cb' => 'tikporn_campo_criadores' ),
			),
		),
		'textos' => array(
			'label' => __( 'Textos', 'tikporn' ),
			'icon'  => 'editor-textcolor',
			'desc'  => __( 'Textos avulsos exibidos na interface.', 'tikporn' ),
			'campos' => array(
				array( 'label' => __( 'Texto do campo de busca', 'tikporn' ), 'icon' => 'search', 'cb' => 'tikporn_campo_busca_placeholder' ),
			),
		),
		'aurora5' => array(
			'label' => __( 'Vídeo (Aurora5)', 'tikporn' ),
			'icon'  => 'shield',
			'desc'  => __( 'Assinatura de vídeos importados via REST por UUID.', 'tikporn' ),
			'campos' => array(
				array( 'label' => __( 'Secret', 'tikporn' ), 'icon' => 'lock', 'cb' => 'tikporn_campo_aurora5_secret' ),
				array( 'label' => __( 'URL base', 'tikporn' ), 'icon' => 'admin-site-alt3', 'cb' => 'tikporn_campo_aurora5_base' ),
				array( 'label' => __( 'Validade do link (segundos)', 'tikporn' ), 'icon' => 'clock', 'cb' => 'tikporn_campo_aurora5_ttl' ),
			),
		),
		'geral' => array(
			'label' => __( 'Geral', 'tikporn' ),
			'icon'  => 'admin-settings',
			'desc'  => __( 'Comportamento geral do site.', 'tikporn' ),
			'campos' => array(
				array( 'label' => __( 'Contar visualizações', 'tikporn' ), 'icon' => 'visibility', 'cb' => 'tikporn_campo_registrar_views' ),
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

		<!-- Cabeçalho discreto -->
		<header class="tp-opt__head">
			<div class="tp-opt__head-brand">
				<span class="tp-opt__logo" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none"><path d="M8 5v14l11-7z" fill="currentColor"/></svg>
				</span>
				<div>
					<h1 class="tp-opt__title"><?php esc_html_e( 'Opções do tema', 'tikporn' ); ?></h1>
					<p class="tp-opt__subtitle"><?php echo esc_html( sprintf( __( 'tikporn • versão %s', 'tikporn' ), defined( 'TIKPORN_VERSION' ) ? TIKPORN_VERSION : '' ) ); ?></p>
				</div>
			</div>
			<div class="tp-opt__head-side">
				<span class="tp-opt__saved" data-tp-saved hidden>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
					<?php esc_html_e( 'Salvo', 'tikporn' ); ?>
				</span>
				<button type="button" class="tp-opt__theme" data-tp-theme-toggle
					aria-label="<?php esc_attr_e( 'Alternar tema claro/escuro', 'tikporn' ); ?>" title="<?php esc_attr_e( 'Alternar tema', 'tikporn' ); ?>">
					<svg class="tp-opt__theme-light" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
					<svg class="tp-opt__theme-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
				</button>
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
										<div class="tp-opt__field-top">
											<span class="tp-opt__field-ico" aria-hidden="true">
												<span class="dashicons dashicons-<?php echo esc_attr( $campo['icon'] ?? 'admin-generic' ); ?>"></span>
											</span>
											<span class="tp-opt__field-label"><?php echo esc_html( $campo['label'] ); ?></span>
										</div>
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
