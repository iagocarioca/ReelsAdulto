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

	document.addEventListener( 'DOMContentLoaded', function () {
		iniciarAutoplay();
		iniciarPlayToggle();
		iniciarCurtir();
		iniciarSeguir();
	} );
} )();
