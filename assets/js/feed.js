/**
 * Feed vertical estilo Reelix — tikporn.
 * Scroll-snap + IntersectionObserver: o vídeo ativo toca, o painel (desktop) e a
 * URL (pushState) acompanham. Scroll infinito, controles, e ações (curtir/seguir/
 * salvar) por delegação (funcionam nos itens criados dinamicamente).
 */
( function () {
	'use strict';

	var watch = document.querySelector( '[data-feed]' );
	if ( ! watch ) { return; }

	var track   = watch.querySelector( '[data-feed-track]' );
	var panel   = watch.querySelector( '[data-panel]' );
	var spinner = watch.querySelector( '[data-feed-spinner]' );
	var D       = window.tikpornDados || {};

	var cfg = {};
	try { cfg = JSON.parse( ( watch.parentNode.querySelector( '[data-feed-config]' ) || document.querySelector( '[data-feed-config]' ) ).textContent ); } catch ( e ) {}

	var cursor  = 0;
	var exclude = cfg.exclude || 0;
	var hasMore = true;
	var loading = false;
	var muted   = true;
	var active  = null;

	/* ── SVGs ── */
	var SVG = {
		play:  '<svg viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>',
		heart: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.6-4.9-10-9.2C.5 8.3 2.1 5 5.3 5 7.2 5 8.6 6.1 12 9.2 15.4 6.1 16.8 5 18.7 5 21.9 5 23.5 8.3 22 11.8 19.6 16.1 12 21 12 21z"/></svg>',
		save:  '<svg viewBox="0 0 30 30" fill="currentColor" aria-hidden="true"><path d="M4 5C3.446 5 3 5.446 3 6 3 6.554 3.446 7 4 7L19 7C19.554 7 20 6.554 20 6 20 5.446 19.554 5 19 5L4 5zM4 12C3.446 12 3 12.446 3 13 3 13.554 3.446 14 4 14L22 14C22.554 14 23 13.554 23 13 23 12.446 22.554 12 22 12L4 12zM21.949 17.004C21.606 17.004 21.272 17.037 20.949 17.104L20.949 20.955 17.1 20.955C17.034 21.278 17 21.612 17 21.955 17 22.298 17.034 22.632 17.1 22.955L20.949 22.955 20.949 26.805C21.272 26.871 21.606 26.904 21.949 26.904 22.292 26.904 22.626 26.871 22.949 26.805L22.949 22.955 26.801 22.955C26.867 22.632 26.9 22.298 26.9 21.955 26.9 21.612 26.867 21.278 26.801 20.955L22.949 20.955 22.949 17.104C22.626 17.037 22.292 17.004 21.949 17.004zM4 19C3.446 19 3 19.446 3 20 3 20.554 3.446 21 4 21L14 21C14.554 21 15 20.554 15 20 15 19.446 14.554 19 14 19L4 19z"/></svg>',
		share: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M16 6l-4-4-4 4"/><path d="M12 2v13"/></svg>',
		eye:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
		muted: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M23 9l-6 6M17 9l6 6"/></svg>',
		sound: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a9 9 0 0 1 0 14"/></svg>'
	};

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}
	function fmt( s ) { s = Math.floor( s || 0 ); var m = Math.floor( s / 60 ); var r = s % 60; return m + ':' + ( r < 10 ? '0' : '' ) + r; }

	function ajax( action, dados, metodo ) {
		var url = D.ajaxUrl + '?action=' + encodeURIComponent( action );
		var opt = { credentials: 'same-origin' };
		if ( metodo === 'POST' ) {
			var body = new URLSearchParams(); body.set( 'nonce', D.nonce || '' );
			Object.keys( dados || {} ).forEach( function ( k ) { body.set( k, dados[ k ] ); } );
			opt.method = 'POST'; opt.headers = { 'Content-Type': 'application/x-www-form-urlencoded' }; opt.body = body.toString();
		} else {
			Object.keys( dados || {} ).forEach( function ( k ) { url += '&' + k + '=' + encodeURIComponent( dados[ k ] ); } );
		}
		return fetch( url, opt ).then( function ( r ) { return r.json(); } ).catch( function () { return null; } );
	}

	/* ── Construção dos itens ── */
	function rail( d ) {
		return '<div class="xf-feed__rail">' +
			'<a class="xf-feed__av" href="' + esc( d.autor.url ) + '"><img src="' + esc( d.autor.avatar ) + '" alt="" loading="lazy"></a>' +
			'<button class="xf-rail-btn' + ( d.curtiu ? ' ativo' : '' ) + '" type="button" data-fb-like data-video-id="' + d.id + '">' + SVG.heart + '<span data-like-num>' + esc( d.likes ) + '</span></button>' +
			'<button class="xf-rail-btn" type="button" data-fb-save data-video-id="' + d.id + '">' + SVG.save + '</button>' +
			'<a class="xf-rail-btn" href="' + esc( d.permalink ) + '">' + SVG.share + '</a>' +
			'</div>';
	}
	function ui() {
		return '<div class="xf-feed__ui"><button class="xf-feed__mute" type="button" data-mute aria-label="Som"><span class="xf-ic-muted">' + SVG.muted + '</span><span class="xf-ic-sound">' + SVG.sound + '</span></button>' +
			'<input class="xf-feed__seek" type="range" min="0" max="100" value="0" step="0.1" data-seek aria-label="Progresso"><span class="xf-feed__time" data-time>0:00</span></div>';
	}
	function buildItem( d ) {
		var art = document.createElement( 'article' );
		art.className = 'xf-feed__item is-muted is-paused';
		art.dataset.id = d.id;
		art.dataset.permalink = d.permalink;
		art._data = d;

		var stage = 'arquivo' === d.tipo
			? '<div class="xf-feed__stage" data-stage><video class="xf-feed__video" playsinline muted loop preload="none"' + ( d.poster ? ' poster="' + esc( d.poster ) + '"' : '' ) + ' data-video><source src="' + esc( d.src ) + '"></video></div>'
			: '<div class="xf-feed__stage" data-stage><a class="xf-feed__embed-link" href="' + esc( d.permalink ) + '">' + ( d.poster ? '<img src="' + esc( d.poster ) + '" alt="">' : '' ) + '<span>' + SVG.play + '</span></a></div>';

		art.innerHTML =
			( d.poster ? '<span class="xf-feed__blur" style="background-image:url(\'' + esc( d.poster ) + '\')"></span>' : '' ) +
			stage +
			'<button class="xf-feed__pp" type="button" data-pp aria-label="Play/Pause">' + SVG.play + '</button>' +
			rail( d ) +
			'<div class="xf-feed__caption"><a href="' + esc( d.autor.url ) + '">@' + esc( d.autor.handle ) + '</a><p>' + esc( d.title ) + '</p></div>' +
			ui();
		return art;
	}

	function buildPanel( d ) {
		panel.innerHTML =
			'<div class="xf-watch__artist">' +
				'<a class="xf-watch__avatar" href="' + esc( d.autor.url ) + '"><img src="' + esc( d.autor.avatar ) + '" alt=""></a>' +
				'<div class="xf-watch__artist-info"><a class="xf-watch__artist-name" href="' + esc( d.autor.url ) + '">' + esc( d.autor.nome ) + '</a>' +
					'<span class="xf-watch__handle">@' + esc( d.autor.handle ) + ( d.autor.artista ? ' <span class="xf-badge">ARTISTA</span>' : '' ) + '</span></div>' +
				'<button class="xf-follow' + ( d.autor.segue ? ' ativo' : '' ) + '" type="button" data-fb-follow data-modelo-id="' + d.autor.id + '"><span class="tp-acao-legenda">' + ( d.autor.segue ? 'Seguindo' : 'Seguir' ) + '</span></button>' +
			'</div>' +
			'<div class="xf-watch__views">' + SVG.eye + '<strong>' + esc( d.views ) + '</strong> visualizações</div>' +
			'<h1 class="xf-watch__title">' + esc( d.title ) + '</h1>' +
			( d.desc ? '<div class="xf-watch__desc">' + esc( d.desc ) + '</div>' : '' ) +
			( d.cats && d.cats.length ? '<div class="xf-watch__tags">' + d.cats.map( function ( c ) { return '<a class="xf-chip" href="' + esc( c.url ) + '">' + esc( c.nome ) + '</a>'; } ).join( '' ) + '</div>' : '' ) +
			'<div class="xf-watch__actions">' +
				'<button class="xf-act tp-curtir' + ( d.curtiu ? ' ativo' : '' ) + '" type="button" data-fb-like data-video-id="' + d.id + '"><span class="xf-act__ic">' + SVG.heart + '</span><span data-like-num>' + esc( d.likes ) + '</span></button>' +
				'<a class="xf-act" href="' + esc( d.permalink ) + '"><span class="xf-act__ic">' + SVG.share + '</span><span>Compartilhar</span></a>' +
				'<div class="xf-act-save"><button class="xf-act" type="button" data-fb-save data-video-id="' + d.id + '"><span class="xf-act__ic">' + SVG.save + '</span><span>Salvar</span></button></div>' +
			'</div>';
	}

	/* ── Reprodução / ativo ── */
	function videoDe( item ) { return item ? item.querySelector( '[data-video]' ) : null; }

	function setActive( item ) {
		if ( active === item ) { return; }
		// pausa o anterior
		if ( active ) { var pv = videoDe( active ); if ( pv ) { pv.pause(); } }
		active = item;
		if ( ! item ) { return; }

		var v = videoDe( item );
		if ( v ) { v.muted = muted; v.play().catch( function () {} ); }
		item.classList.toggle( 'is-muted', muted );

		// URL + título + painel
		if ( item.dataset.permalink && window.history.replaceState ) {
			window.history.pushState( { id: item.dataset.id }, '', item.dataset.permalink );
		}
		if ( item._data ) { buildPanel( item._data ); document.title = item._data.title; }

		precarregarVizinhos( item );
	}

	function precarregarVizinhos( item ) {
		[ item.nextElementSibling, item.nextElementSibling && item.nextElementSibling.nextElementSibling ].forEach( function ( n ) {
			if ( n && n.classList && n.classList.contains( 'xf-feed__item' ) ) {
				var v = videoDe( n );
				if ( v && v.preload !== 'auto' ) { v.preload = 'auto'; try { v.load(); } catch ( e ) {} }
			}
		} );
	}

	var io = new IntersectionObserver( function ( entries ) {
		entries.forEach( function ( en ) {
			if ( en.isIntersecting && en.intersectionRatio >= 0.6 ) { setActive( en.target ); }
		} );
	}, { root: track, threshold: [ 0.6 ] } );

	function anexar( itens ) {
		var frag = document.createDocumentFragment();
		itens.forEach( function ( d ) { var el = buildItem( d ); frag.appendChild( el ); io.observe( el ); } );
		track.appendChild( frag );
	}

	/* ── Scroll infinito ── */
	function carregarMais() {
		if ( loading || ! hasMore ) { return; }
		loading = true;
		ajax( 'tikporn_feed', { cursor: cursor, exclude: exclude } ).then( function ( res ) {
			loading = false;
			if ( ! res || ! res.success ) { hasMore = false; return; }
			cursor  = res.data.next_cursor;
			hasMore = res.data.has_more;
			anexar( res.data.items || [] );
		} );
	}

	track.addEventListener( 'scroll', function () {
		if ( track.scrollTop + track.clientHeight > track.scrollHeight - track.clientHeight * 1.5 ) { carregarMais(); }
	}, { passive: true } );

	/* ── Controles (delegação) ── */
	function itemDe( el ) { return el.closest( '.xf-feed__item' ); }

	track.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '[data-fb-like],[data-fb-save],[data-fb-follow],.xf-feed__ui,.xf-feed__rail,.xf-feed__caption a' ) ) { return; }
		var st = e.target.closest( '[data-stage]' ) || e.target.closest( '[data-pp]' );
		if ( ! st ) { return; }
		var v = videoDe( itemDe( e.target ) );
		if ( ! v ) { return; }
		if ( v.paused ) { v.play().catch( function () {} ); } else { v.pause(); }
	} );

	track.addEventListener( 'play', function ( e ) { var it = itemDe( e.target ); if ( it ) { it.classList.remove( 'is-paused' ); } }, true );
	track.addEventListener( 'pause', function ( e ) { var it = itemDe( e.target ); if ( it ) { it.classList.add( 'is-paused' ); } }, true );

	track.addEventListener( 'timeupdate', function ( e ) {
		var it = itemDe( e.target ); if ( ! it ) { return; }
		var v = e.target, seek = it.querySelector( '[data-seek]' ), t = it.querySelector( '[data-time]' );
		if ( v.duration && seek ) { seek.value = ( v.currentTime / v.duration ) * 100; seek.style.setProperty( '--p', seek.value + '%' ); }
		if ( t ) { t.textContent = fmt( v.currentTime ); }
	}, true );

	track.addEventListener( 'input', function ( e ) {
		if ( ! e.target.matches( '[data-seek]' ) ) { return; }
		var v = videoDe( itemDe( e.target ) );
		if ( v && v.duration ) { v.currentTime = ( e.target.value / 100 ) * v.duration; }
		e.target.style.setProperty( '--p', e.target.value + '%' );
	} );

	// Mute global
	track.addEventListener( 'click', function ( e ) {
		var b = e.target.closest( '[data-mute]' );
		if ( ! b ) { return; }
		e.stopPropagation();
		muted = ! muted;
		track.querySelectorAll( '[data-video]' ).forEach( function ( v ) { v.muted = muted; if ( ! muted && v.volume === 0 ) { v.volume = 1; } } );
		track.querySelectorAll( '.xf-feed__item' ).forEach( function ( it ) { it.classList.toggle( 'is-muted', muted ); } );
	} );

	/* ── Ações (curtir / seguir / salvar) — delegadas no .xf-watch ── */
	watch.addEventListener( 'click', function ( e ) {
		var like = e.target.closest( '[data-fb-like]' );
		if ( like ) {
			e.preventDefault();
			if ( ! D.logado ) { window.location.href = D.loginUrl; return; }
			var vid = like.getAttribute( 'data-video-id' );
			ajax( 'tikporn_curtir', { video_id: vid }, 'POST' ).then( function ( res ) {
				if ( ! res || ! res.success ) { return; }
				watch.querySelectorAll( '[data-fb-like][data-video-id="' + vid + '"]' ).forEach( function ( btn ) {
					btn.classList.toggle( 'ativo', res.data.curtiu );
					var n = btn.querySelector( '[data-like-num]' ); if ( n ) { n.textContent = res.data.total; }
				} );
			} );
			return;
		}

		var follow = e.target.closest( '[data-fb-follow]' );
		if ( follow ) {
			e.preventDefault();
			if ( ! D.logado ) { window.location.href = D.loginUrl; return; }
			var mid = follow.getAttribute( 'data-modelo-id' );
			ajax( 'tikporn_seguir', { modelo_id: mid }, 'POST' ).then( function ( res ) {
				if ( ! res || ! res.success ) { return; }
				follow.classList.toggle( 'ativo', res.data.segue );
				var leg = follow.querySelector( '.tp-acao-legenda' ); if ( leg ) { leg.textContent = res.data.segue ? 'Seguindo' : 'Seguir'; }
			} );
			return;
		}

		var save = e.target.closest( '[data-fb-save]' );
		if ( save ) {
			e.preventDefault();
			e.stopPropagation();
			if ( ! D.logado ) { window.location.href = D.loginUrl; return; }
			abrirMenuSalvar( save );
			return;
		}
	} );

	// Fecha o menu ao clicar fora (o menu vive no body, então escuta no documento).
	document.addEventListener( 'click', function ( e ) {
		if ( document.querySelector( '.xf-plmenu' ) && ! e.target.closest( '.xf-plmenu' ) && ! e.target.closest( '[data-fb-save]' ) ) {
			fecharMenus();
		}
	} );

	/* ── Menu "Salvar" (dinâmico, por vídeo) ── */
	function fecharMenus() { document.querySelectorAll( '.xf-plmenu' ).forEach( function ( m ) { m.remove(); } ); }

	function posicionarMenu( menu, btn ) {
		var r = btn.getBoundingClientRect();
		var w = menu.offsetWidth || 264;
		menu.style.left = Math.min( Math.max( 8, r.left ), window.innerWidth - w - 8 ) + 'px';
		if ( r.top > 300 ) { // cabe acima do botão
			menu.style.top = 'auto';
			menu.style.bottom = ( window.innerHeight - r.top + 8 ) + 'px';
		} else { // abre abaixo
			menu.style.bottom = 'auto';
			menu.style.top = ( r.bottom + 8 ) + 'px';
		}
	}

	function abrirMenuSalvar( btn ) {
		if ( document.querySelector( '.xf-plmenu' ) ) { fecharMenus(); return; }
		var vid = btn.getAttribute( 'data-video-id' );
		var menu = document.createElement( 'div' );
		menu.className = 'xf-plmenu';
		menu.innerHTML = '<div class="xf-pl-menu__cab">Salvar em…</div><div class="xf-pl-menu__lista">…</div>' +
			'<form class="xf-pl-menu__nova"><input type="text" name="titulo" placeholder="Criar playlist…" maxlength="80" autocomplete="off"><button type="submit">+</button></form>';
		document.body.appendChild( menu );
		posicionarMenu( menu, btn );
		track.addEventListener( 'scroll', fecharMenus, { once: true, passive: true } );

		var lista = menu.querySelector( '.xf-pl-menu__lista' );
		function render( pls ) {
			lista.innerHTML = pls.length ? pls.map( function ( p ) {
				return '<button type="button" class="xf-pl-menu__item' + ( p.contem ? ' is-in' : '' ) + '" data-pl-item data-id="' + p.id + '"><span class="xf-pl-menu__check"></span><span class="xf-pl-menu__nome">' + esc( p.titulo ) + '</span>' + ( p.publica ? '' : '<span class="xf-pl-menu__lock">privada</span>' ) + '</button>';
			} ).join( '' ) : '<p class="xf-pl-menu__vazio">Crie sua primeira playlist abaixo.</p>';
		}
		function recarregar() { ajax( 'tikporn_playlist_listar', { video_id: vid }, 'POST' ).then( function ( r ) { if ( r && r.success ) { render( r.data.playlists ); } } ); }
		recarregar();

		lista.addEventListener( 'click', function ( e ) {
			var it = e.target.closest( '[data-pl-item]' ); if ( ! it ) { return; }
			ajax( 'tikporn_playlist_toggle_video', { playlist_id: it.getAttribute( 'data-id' ), video_id: vid }, 'POST' ).then( function ( r ) {
				if ( r && r.success ) { it.classList.toggle( 'is-in', r.data.contem ); }
			} );
		} );
		menu.querySelector( '.xf-pl-menu__nova' ).addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var campo = e.target.querySelector( 'input' ); var titulo = campo.value.trim(); if ( ! titulo ) { return; }
			ajax( 'tikporn_playlist_criar', { titulo: titulo, publica: 0, video_id: vid }, 'POST' ).then( function ( r ) { if ( r && r.success ) { campo.value = ''; recarregar(); } } );
		} );
	}

	/* ── Setas ↑/↓ ── */
	function irPara( dir ) {
		if ( ! active ) { return; }
		var alvo = dir < 0 ? active.previousElementSibling : active.nextElementSibling;
		if ( alvo && alvo.classList && alvo.classList.contains( 'xf-feed__item' ) ) { alvo.scrollIntoView( { behavior: 'smooth' } ); }
	}
	var prev = watch.querySelector( '[data-feed-prev]' ), next = watch.querySelector( '[data-feed-next]' );
	if ( prev ) { prev.addEventListener( 'click', function () { irPara( -1 ); } ); }
	if ( next ) { next.addEventListener( 'click', function () { irPara( 1 ); } ); }
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'ArrowUp' ) { e.preventDefault(); irPara( -1 ); }
		else if ( e.key === 'ArrowDown' ) { e.preventDefault(); irPara( 1 ); }
	} );

	/* ── Início ── */
	if ( cfg.first ) {
		var primeiro = buildItem( cfg.first );
		track.appendChild( primeiro );
		io.observe( primeiro );
		setActive( primeiro );
		exclude = cfg.first.id;
	}
	if ( spinner ) { spinner.remove(); }
	carregarMais();
} )();
