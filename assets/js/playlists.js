/**
 * Playlists — tikporn.
 * Menu "Salvar em playlist" no vídeo, criar/gerir na página Minhas playlists,
 * e visibilidade/exclusão na página da playlist.
 */
( function () {
	'use strict';

	var D = window.tikpornDados || {};

	function post( action, dados ) {
		var corpo = new URLSearchParams();
		corpo.set( 'action', action );
		corpo.set( 'nonce', D.nonce || '' );
		Object.keys( dados || {} ).forEach( function ( k ) { corpo.set( k, dados[ k ] ); } );
		return fetch( D.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: corpo.toString(),
		} ).then( function ( r ) { return r.json(); } ).catch( function () { return null; } );
	}

	function precisaLogin() {
		window.location.href = D.loginUrl || '/entrar/';
	}

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	/* ── Menu "Salvar" no vídeo ── */
	function initSalvar() {
		var wrap = document.querySelector( '[data-pl-save]' );
		if ( ! wrap ) { return; }

		var btn   = wrap.querySelector( '[data-pl-save-btn]' );
		var menu  = wrap.querySelector( '[data-pl-menu]' );
		var lista = wrap.querySelector( '[data-pl-menu-lista]' );
		var nova  = wrap.querySelector( '[data-pl-menu-nova]' );
		var videoId = menu ? menu.getAttribute( 'data-video-id' ) : 0;
		var carregado = false;

		function render( playlists ) {
			if ( ! playlists.length ) {
				lista.innerHTML = '<p class="xf-pl-menu__vazio">Crie sua primeira playlist abaixo.</p>';
				return;
			}
			lista.innerHTML = playlists.map( function ( p ) {
				return '<button type="button" class="xf-pl-menu__item' + ( p.contem ? ' is-in' : '' ) +
					'" data-pl-item data-id="' + p.id + '">' +
					'<span class="xf-pl-menu__check" aria-hidden="true"></span>' +
					'<span class="xf-pl-menu__nome">' + esc( p.titulo ) + '</span>' +
					( p.publica ? '' : '<span class="xf-pl-menu__lock"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>privada</span>' ) +
					'</button>';
			} ).join( '' );
		}

		function carregar() {
			if ( carregado ) { return; }
			post( 'tikporn_playlist_listar', { video_id: videoId } ).then( function ( res ) {
				if ( res && res.success ) { render( res.data.playlists ); carregado = true; }
			} );
		}

		btn.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
			if ( ! D.logado ) { precisaLogin(); return; }
			var abrir = menu.hasAttribute( 'hidden' );
			if ( abrir ) { menu.removeAttribute( 'hidden' ); carregar(); } else { menu.setAttribute( 'hidden', '' ); }
		} );

		// Toggle add/remove ao clicar num item.
		lista.addEventListener( 'click', function ( e ) {
			var item = e.target.closest( '[data-pl-item]' );
			if ( ! item ) { return; }
			var id = item.getAttribute( 'data-id' );
			post( 'tikporn_playlist_toggle_video', { playlist_id: id, video_id: videoId } ).then( function ( res ) {
				if ( res && res.success ) { item.classList.toggle( 'is-in', res.data.contem ); }
			} );
		} );

		// Criar nova playlist (já com este vídeo).
		if ( nova ) {
			nova.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var campo = nova.querySelector( 'input[name="titulo"]' );
				var titulo = campo.value.trim();
				if ( ! titulo ) { return; }
				post( 'tikporn_playlist_criar', { titulo: titulo, publica: 0, video_id: videoId } ).then( function ( res ) {
					if ( res && res.success ) {
						campo.value = '';
						carregado = false;
						carregar();
					}
				} );
			} );
		}

		// Fecha ao clicar fora.
		document.addEventListener( 'click', function ( e ) {
			if ( menu && ! menu.hasAttribute( 'hidden' ) && ! wrap.contains( e.target ) ) {
				menu.setAttribute( 'hidden', '' );
			}
		} );
	}

	/* ── Página "Minhas playlists": abrir form + criar ── */
	function initMinhas() {
		var abrir = document.querySelector( '[data-pl-nova-abrir]' );
		var form  = document.querySelector( '[data-pl-nova]' );
		if ( abrir && form ) {
			abrir.addEventListener( 'click', function () {
				form.toggleAttribute( 'hidden' );
				var c = form.querySelector( 'input[name="titulo"]' );
				if ( c && ! form.hasAttribute( 'hidden' ) ) { c.focus(); }
			} );
		}
		if ( form ) {
			form.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var titulo = form.querySelector( 'input[name="titulo"]' ).value.trim();
				var publica = form.querySelector( 'input[name="publica"]' ).checked ? 1 : 0;
				if ( ! titulo ) { return; }
				post( 'tikporn_playlist_criar', { titulo: titulo, publica: publica } ).then( function ( res ) {
					if ( res && res.success ) { window.location.href = res.data.url; }
				} );
			} );
		}
	}

	/* ── Página da playlist: visibilidade + excluir ── */
	function initGerir() {
		var box = document.querySelector( '[data-playlist-manage]' );
		if ( ! box ) { return; }
		var id = box.getAttribute( 'data-playlist-id' );

		var vis = box.querySelector( '[data-pl-visibilidade]' );
		if ( vis ) {
			vis.addEventListener( 'click', function () {
				var novaPublica = vis.getAttribute( 'data-publica' ) === '1' ? 0 : 1;
				post( 'tikporn_playlist_visibilidade', { playlist_id: id, publica: novaPublica } ).then( function ( res ) {
					if ( res && res.success ) { window.location.reload(); }
				} );
			} );
		}

		var del = box.querySelector( '[data-pl-excluir]' );
		if ( del ) {
			del.addEventListener( 'click', function () {
				if ( ! window.confirm( 'Excluir esta playlist?' ) ) { return; }
				post( 'tikporn_playlist_excluir', { playlist_id: id } ).then( function ( res ) {
					if ( res && res.success ) { window.location.href = D.playlistsUrl || '/minhas-playlists/'; }
				} );
			} );
		}
	}

	/* ── Página "Minha conta": abas ── */
	function initConta() {
		var nav = document.querySelector( '.xf-conta__nav' );
		if ( ! nav ) { return; }
		var abas  = nav.querySelectorAll( 'a' );
		var secoes = document.querySelectorAll( '[data-conta-sec]' );

		function mostrar( alvo ) {
			secoes.forEach( function ( s ) { s.hidden = ( '#' + s.id ) !== alvo; } );
			abas.forEach( function ( a ) { a.classList.toggle( 'is-active', a.getAttribute( 'href' ) === alvo ); } );
		}

		abas.forEach( function ( a ) {
			a.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				mostrar( a.getAttribute( 'href' ) );
				if ( history.replaceState ) { history.replaceState( null, '', a.getAttribute( 'href' ) ); }
			} );
		} );

		if ( location.hash && document.querySelector( location.hash + '[data-conta-sec]' ) ) {
			mostrar( location.hash );
		}
	}

	/* ── Sidebar: expandir/recolher categorias ── */
	function initSidebar() {
		document.querySelectorAll( '[data-cats-toggle]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var side = btn.closest( '[data-sidebar]' );
				if ( ! side ) { return; }
				var aberto = side.classList.toggle( 'is-expanded' );
				btn.setAttribute( 'aria-expanded', aberto ? 'true' : 'false' );
			} );
		} );
	}

	/* ── Navegação mobile: drawer + painel de categorias ── */
	function initMobileNav() {
		var drawer = document.querySelector( '[data-drawer]' );
		var cats   = document.querySelector( '[data-cats-panel]' );

		function abrir( el ) {
			if ( ! el ) { return; }
			el.classList.add( 'is-open' );
			el.setAttribute( 'aria-hidden', 'false' );
			document.body.classList.add( 'xf-nav-open' );
		}
		function fechar( el ) {
			if ( ! el ) { return; }
			el.classList.remove( 'is-open' );
			el.setAttribute( 'aria-hidden', 'true' );
			if ( ! document.querySelector( '.is-open[data-drawer], .is-open[data-cats-panel]' ) ) {
				document.body.classList.remove( 'xf-nav-open' );
			}
		}

		document.addEventListener( 'click', function ( e ) {
			if ( e.target.closest( '[data-drawer-open]' ) ) { abrir( drawer ); }
			else if ( e.target.closest( '[data-drawer-close]' ) ) { fechar( drawer ); }
			else if ( e.target.closest( '[data-cats-open]' ) ) { abrir( cats ); }
			else if ( e.target.closest( '[data-cats-close]' ) ) { fechar( cats ); }
		} );
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) { fechar( drawer ); fechar( cats ); }
		} );
	}

	/* ── Busca expansível + sugestões ── */
	function initBusca() {
		var form = document.querySelector( '[data-busca]' );
		if ( ! form ) { return; }
		var toggle = form.querySelector( '[data-busca-toggle]' );
		var input  = form.querySelector( '[data-busca-input]' );
		var box    = form.querySelector( '[data-busca-sugestoes]' );
		var close  = form.querySelector( '[data-busca-close]' );
		var timer, ultimo = '';

		function ehMobile() { return window.matchMedia( '(max-width: 760px)' ).matches; }
		function abrir() { form.classList.add( 'is-open' ); setTimeout( function () { input.focus(); }, 80 ); }
		function fechar() { form.classList.remove( 'is-open' ); box.hidden = true; }

		if ( close ) {
			close.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				if ( input.value ) { input.value = ''; ultimo = ''; box.hidden = true; input.focus(); }
				else { fechar(); }
			} );
		}

		toggle.addEventListener( 'click', function () {
			if ( ! ehMobile() ) { if ( input.value.trim() ) { form.submit(); } return; }
			if ( form.classList.contains( 'is-open' ) ) {
				if ( input.value.trim() ) { form.submit(); } else { fechar(); }
			} else { abrir(); }
		} );

		function render( items ) {
			if ( ! items.length ) { box.hidden = true; box.innerHTML = ''; return; }
			box.innerHTML = items.map( function ( it ) {
				return '<a class="xf-sug" href="' + esc( it.url ) + '">' +
					'<span class="xf-sug__thumb"' + ( it.poster ? ' style="background-image:url(\'' + esc( it.poster ) + '\')"' : '' ) + '></span>' +
					'<span class="xf-sug__txt"><span class="xf-sug__title">' + esc( it.title ) + '</span>' +
					'<span class="xf-sug__views">' + esc( it.views ) + ' visualizações</span></span></a>';
			} ).join( '' );
			box.hidden = false;
		}

		input.addEventListener( 'input', function () {
			var q = input.value.trim();
			clearTimeout( timer );
			if ( q.length < 2 ) { box.hidden = true; return; }
			if ( q === ultimo ) { return; }
			timer = setTimeout( function () {
				ultimo = q;
				fetch( D.ajaxUrl + '?action=tikporn_busca&q=' + encodeURIComponent( q ), { credentials: 'same-origin' } )
					.then( function ( r ) { return r.json(); } )
					.then( function ( res ) { if ( res && res.success ) { render( res.data.items ); } } )
					.catch( function () {} );
			}, 220 );
		} );

		document.addEventListener( 'click', function ( e ) {
			if ( ! form.contains( e.target ) ) { box.hidden = true; if ( ehMobile() ) { fechar(); } }
		} );
		document.addEventListener( 'keydown', function ( e ) { if ( e.key === 'Escape' ) { fechar(); } } );
	}

	/* ── Menu de conta (avatar) ── */
	function initContaMenu() {
		var wrap = document.querySelector( '[data-conta-menu]' );
		if ( ! wrap ) { return; }
		var btn  = wrap.querySelector( '[data-conta-toggle]' );
		var menu = wrap.querySelector( '[data-conta-dropdown]' );

		btn.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
			menu.hidden = ! menu.hidden;
		} );
		document.addEventListener( 'click', function ( e ) {
			if ( ! menu.hidden && ! wrap.contains( e.target ) ) { menu.hidden = true; }
		} );
		document.addEventListener( 'keydown', function ( e ) { if ( e.key === 'Escape' ) { menu.hidden = true; } } );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initSalvar();
		initMinhas();
		initGerir();
		initConta();
		initSidebar();
		initMobileNav();
		initBusca();
		initContaMenu();
	} );
} )();
