/* tikporn — comportamento do feed, curtidas e seguir. */
( function () {
	'use strict';

	var dados = window.tikpornDados || {};

	/* --------- Toca/pausa vídeos conforme entram na tela --------- */
	function iniciarAutoplay() {
		var videos = document.querySelectorAll( '.tp-video[data-autoplay]' );
		if ( ! videos.length || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		var observador = new IntersectionObserver(
			function ( entradas ) {
				entradas.forEach( function ( e ) {
					var v = e.target;
					if ( e.isIntersecting && e.intersectionRatio > 0.6 ) {
						v.play().catch( function () {} );
					} else {
						v.pause();
					}
				} );
			},
			{ threshold: [ 0, 0.6, 1 ] }
		);

		videos.forEach( function ( v ) {
			observador.observe( v );
		} );
	}

	/* --------- Toque na tela pausa/retoma o vídeo --------- */
	function iniciarPlayToggle() {
		document.querySelectorAll( '.tp-play-toggle' ).forEach( function ( botao ) {
			botao.addEventListener( 'click', function () {
				var card = botao.closest( '.tp-card' );
				var v = card && card.querySelector( '.tp-video' );
				if ( ! v ) {
					return;
				}
				if ( v.paused ) {
					v.play();
				} else {
					v.pause();
				}
			} );
		} );
	}

	/* --------- Requisição AJAX simples --------- */
	function enviar( acao, corpo ) {
		corpo.action = acao;
		corpo.nonce = dados.nonce;
		var params = new URLSearchParams();
		Object.keys( corpo ).forEach( function ( k ) {
			params.append( k, corpo[ k ] );
		} );
		return fetch( dados.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: params.toString(),
		} ).then( function ( r ) {
			return r.json();
		} );
	}

	function precisaLogin() {
		if ( dados.loginUrl ) {
			window.location.href = dados.loginUrl;
		}
	}

	/* --------- Curtir --------- */
	function iniciarCurtir() {
		document.querySelectorAll( '.tp-curtir' ).forEach( function ( botao ) {
			botao.addEventListener( 'click', function () {
				if ( ! dados.logado ) {
					precisaLogin();
					return;
				}
				var id = botao.getAttribute( 'data-video-id' );
				enviar( 'tikporn_curtir', { video_id: id } ).then( function ( res ) {
					if ( ! res || ! res.success ) {
						return;
					}
					botao.classList.toggle( 'ativo', res.data.curtiu );
					var num = botao.querySelector( '.tp-acao-num' );
					if ( num ) {
						num.textContent = res.data.total;
					}
				} );
			} );
		} );
	}

	/* --------- Seguir --------- */
	function iniciarSeguir() {
		document.querySelectorAll( '.tp-seguir' ).forEach( function ( botao ) {
			botao.addEventListener( 'click', function () {
				if ( ! dados.logado ) {
					precisaLogin();
					return;
				}
				var id = botao.getAttribute( 'data-modelo-id' );
				enviar( 'tikporn_seguir', { modelo_id: id } ).then( function ( res ) {
					if ( ! res || ! res.success ) {
						return;
					}
					botao.classList.toggle( 'ativo', res.data.segue );
					var legenda = botao.querySelector( '.tp-acao-legenda' );
					if ( legenda ) {
						legenda.textContent = res.data.segue ? 'Seguindo' : 'Seguir';
					}
				} );
			} );
		} );
	}

	/* --------- Descrição recolhível ("ver mais / ver menos") --------- */
	function iniciarVerMais() {
		document.querySelectorAll( '[data-ver-mais]' ).forEach( function ( bloco ) {
			var texto = bloco.querySelector( '.xf-plhead__desc-texto' );
			var botao = bloco.querySelector( '[data-ver-mais-btn]' );
			if ( ! texto || ! botao ) {
				return;
			}

			// Só mostra o botão se o texto realmente ultrapassa o clamp.
			function avaliar() {
				var estava = bloco.classList.contains( 'is-aberto' );
				bloco.classList.remove( 'is-aberto' );
				var excede = texto.scrollHeight - texto.clientHeight > 2;
				bloco.classList.toggle( 'tem-mais', excede );
				if ( estava ) {
					bloco.classList.add( 'is-aberto' );
				}
			}

			avaliar();

			botao.addEventListener( 'click', function () {
				bloco.classList.toggle( 'is-aberto' );
			} );

			// Reavalia se a largura muda (troca de layout mobile/desktop).
			var t;
			window.addEventListener( 'resize', function () {
				clearTimeout( t );
				t = setTimeout( avaliar, 150 );
			} );
		} );
	}

	/* --------- Centraliza a aba "Categorias" na altura da thumb (mobile) --------- */
	function centralizarCatsTab() {
		var tab   = document.querySelector( '.xf-cats-tab' );
		var thumb = document.querySelector( '.xf-playlist__thumb' )
			|| document.querySelector( '.xf-playlists' )
			|| document.querySelector( '.xf-grade' );
		if ( ! tab || ! thumb ) {
			return;
		}

		function ajustar() {
			// Só no mobile (aba visível). Usa o centro da thumb relativo ao offsetParent da aba.
			if ( getComputedStyle( tab ).display === 'none' ) {
				return;
			}
			var pai = tab.offsetParent || document.body;
			var paiTop = pai.getBoundingClientRect().top;
			var r = thumb.getBoundingClientRect();
			var centro = ( r.top - paiTop ) + r.height / 2;
			tab.style.setProperty( '--xf-cats-top', Math.round( centro ) + 'px' );
		}

		ajustar();
		// Reajusta ao carregar imagens/rotacionar/redimensionar.
		window.addEventListener( 'load', ajustar );
		var t;
		window.addEventListener( 'resize', function () {
			clearTimeout( t );
			t = setTimeout( ajustar, 120 );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		iniciarAutoplay();
		iniciarPlayToggle();
		iniciarCurtir();
		iniciarSeguir();
		iniciarVerMais();
		centralizarCatsTab();
	} );
} )();
