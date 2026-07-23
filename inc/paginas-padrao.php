<?php
/**
 * Cria automaticamente as páginas do tema quando ele é ativado,
 * e define a página inicial como o feed.
 *
 * @package tikporn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lista de páginas a criar: slug => [título, modelo de página].
 */
function tikporn_paginas_do_tema() {
	return array(
		'entrar'      => array( __( 'Entrar', 'tikporn' ), 'page-templates/login.php' ),
		'cadastro'    => array( __( 'Cadastro', 'tikporn' ), 'page-templates/cadastro.php' ),
		'area-modelo' => array( __( 'Área da modelo', 'tikporn' ), 'page-templates/area-modelo.php' ),
		'buscar'      => array( __( 'Buscar', 'tikporn' ), 'page-templates/buscar.php' ),
		'minhas-playlists' => array( __( 'Minhas playlists', 'tikporn' ), 'page-templates/minhas-playlists.php' ),
		'playlists'        => array( __( 'Playlists', 'tikporn' ), 'page-templates/playlists.php' ),
		'minha-conta'      => array( __( 'Minha conta', 'tikporn' ), 'page-templates/minha-conta.php' ),
		'categorias'       => array( __( 'Categorias', 'tikporn' ), 'page-templates/categorias.php' ),
		// Páginas legais: criadas já com um texto-base (editável no painel).
		'dmca'             => array( __( 'DMCA', 'tikporn' ), 'page-templates/legal.php', 'dmca' ),
		// Slug com prefixo: o WP não aceita slug só numérico ("2257" colidiria com IDs.)
		'usc-2257'         => array( __( '18 U.S.C. 2257', 'tikporn' ), 'page-templates/legal.php', '2257' ),
		'contato'          => array( __( 'Contato', 'tikporn' ), 'page-templates/legal.php', 'contato' ),
	);
}

/**
 * Texto-base das páginas legais. Serve de ponto de partida — o conteúdo
 * é livremente editável no painel depois de criado.
 *
 * @param string $chave dmca | 2257 | contato
 * @return string HTML do conteúdo.
 */
function tikporn_conteudo_legal( $chave ) {
	$site  = get_bloginfo( 'name' );
	$email = get_option( 'admin_email' );

	switch ( $chave ) {
		case 'dmca':
			return sprintf(
				'<p>O %1$s respeita os direitos de propriedade intelectual e responde a notificações de violação de direitos autorais de acordo com o Digital Millennium Copyright Act (DMCA).</p>

<h2>Como enviar uma notificação</h2>
<p>Se você é o titular dos direitos autorais (ou seu representante autorizado) e acredita que algum conteúdo publicado aqui viola seus direitos, envie uma notificação para <a href="mailto:%2$s">%2$s</a> contendo:</p>
<ol>
<li>Sua assinatura física ou eletrônica, como titular dos direitos ou representante autorizado;</li>
<li>Identificação da obra protegida que você alega ter sido violada;</li>
<li>O endereço (URL) exato do conteúdo que deve ser removido, para que possamos localizá-lo;</li>
<li>Seus dados de contato: nome completo, endereço, telefone e e-mail;</li>
<li>Uma declaração de que você acredita, de boa-fé, que o uso do material não foi autorizado pelo titular dos direitos, seu agente ou pela lei;</li>
<li>Uma declaração de que as informações da notificação são exatas e, sob pena de perjúrio, de que você é o titular dos direitos ou está autorizado a agir em seu nome.</li>
</ol>

<h2>Nosso procedimento</h2>
<p>Ao receber uma notificação completa e válida, removemos ou desabilitamos o acesso ao conteúdo indicado em até 72 horas úteis e notificamos quem o publicou. Contas com violações reincidentes são encerradas.</p>

<h2>Contra-notificação</h2>
<p>Se o seu conteúdo foi removido e você acredita que isso foi um engano ou identificação incorreta, envie uma contra-notificação para o mesmo e-mail, com sua identificação, a URL removida e uma declaração, sob pena de perjúrio, de que a remoção foi equivocada.</p>

<p><strong>Contato do agente DMCA:</strong> <a href="mailto:%2$s">%2$s</a></p>',
				esc_html( $site ),
				esc_attr( $email )
			);

		case '2257':
			return sprintf(
				'<h2>Declaração de conformidade — 18 U.S.C. §2257</h2>
<p>Todos os modelos, atores, atrizes e demais pessoas que aparecem em qualquer representação visual de conduta sexualmente explícita publicada neste site tinham <strong>18 anos de idade ou mais</strong> no momento da criação de tais imagens.</p>

<h2>Conteúdo de terceiros</h2>
<p>O %1$s atua, em parte, como plataforma que exibe conteúdo enviado por usuários e por sites parceiros. Em relação a esse material, o %1$s não é o produtor (primário ou secundário) no sentido do 18 U.S.C. §2257. A responsabilidade pelos registros exigidos pelo §2257 é do produtor original de cada conteúdo, que declara mantê-los conforme a legislação aplicável.</p>

<h2>Registros</h2>
<p>Os registros exigidos pela legislação, referentes ao conteúdo produzido diretamente por esta plataforma, são mantidos pelo custodiante de registros e podem ser solicitados por autoridades competentes através do e-mail <a href="mailto:%2$s">%2$s</a>.</p>

<h2>Política de tolerância zero</h2>
<p>Este site aplica política de tolerância zero contra pornografia infantil e conteúdo não consensual. Qualquer material dessa natureza é removido imediatamente e reportado às autoridades. Para denunciar conteúdo ilegal, escreva para <a href="mailto:%2$s">%2$s</a>.</p>',
				esc_html( $site ),
				esc_attr( $email )
			);

		case 'contato':
			return sprintf(
				'<p>Fale com a equipe do %1$s. Respondemos em até 72 horas úteis.</p>

<h2>E-mail</h2>
<p><a href="mailto:%2$s">%2$s</a></p>

<h2>Assuntos</h2>
<ul>
<li><strong>Direitos autorais / remoção de conteúdo:</strong> use a página <a href="%3$s">DMCA</a>, que lista as informações necessárias para agilizar a análise.</li>
<li><strong>Denúncia de conteúdo ilegal ou não consensual:</strong> escreva com o assunto “URGENTE — Denúncia” e a URL do conteúdo. Essas mensagens têm prioridade.</li>
<li><strong>Parcerias e publicidade:</strong> envie a proposta com dados da empresa e formatos de interesse.</li>
<li><strong>Suporte da conta:</strong> descreva o problema e, se possível, envie capturas de tela.</li>
</ul>

<p>Ao entrar em contato, inclua sempre a URL da página relacionada — isso acelera bastante a resposta.</p>',
				esc_html( $site ),
				esc_attr( $email ),
				esc_url( site_url( '/dmca/' ) )
			);
	}

	return '';
}

/**
 * Cria as páginas ao ativar o tema.
 */
function tikporn_criar_paginas() {
	foreach ( tikporn_paginas_do_tema() as $slug => $dados ) {
		$existente = get_page_by_path( $slug );
		if ( $existente ) {
			continue;
		}

		// 3º item (opcional): chave do texto-base da página legal.
		$conteudo = isset( $dados[2] ) ? tikporn_conteudo_legal( $dados[2] ) : '';

		$page_id = wp_insert_post(
			array(
				'post_title'   => $dados[0],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => $conteudo,
			)
		);

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', $dados[1] );
		}
	}

	// Página inicial passa a ser o feed (front-page.php cuida do visual).
	update_option( 'show_on_front', 'posts' );

	// Recria as regras de links (por causa do novo tipo de conteúdo).
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'tikporn_criar_paginas' );
