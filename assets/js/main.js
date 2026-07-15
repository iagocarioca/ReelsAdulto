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

	/* --------- Login com Google (GIS) --------- */
	function iniciarGoogle() {
		var cfg = window.tikpornGoogle;
		var botao = document.querySelector( '[data-google-btn]' );
		if ( ! cfg || ! cfg.clientId || ! botao ) {
			return;
		}

		// Callback global chamado pelo Google com o ID token (credential).
		window.tikpornGoogleCallback = function ( resposta ) {
			if ( ! resposta || ! resposta.credential ) {
				return;
			}
			botao.classList.add( 'is-carregando' );
			var params = new URLSearchParams();
			params.append( 'action', 'tikporn_google_login' );
			params.append( 'credential', resposta.credential );
			fetch( cfg.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: params.toString(),
			} ).then( function ( r ) { return r.json(); } ).then( function ( res ) {
				if ( res && res.success ) {
					window.location.href = res.data.redirect || '/';
				} else {
					botao.classList.remove( 'is-carregando' );
					alert( ( res && res.data && res.data.msg ) || 'Não foi possível entrar com o Google.' );
				}
			} ).catch( function () {
				botao.classList.remove( 'is-carregando' );
			} );
		};

		function iniciarGis() {
			if ( ! window.google || ! window.google.accounts || ! window.google.accounts.id ) {
				return setTimeout( iniciarGis, 200 );
			}
			window.google.accounts.id.initialize( {
				client_id: cfg.clientId,
				callback: window.tikpornGoogleCallback,
			} );
			botao.addEventListener( 'click', function () {
				// Abre o fluxo do Google (One Tap / seletor de conta).
				window.google.accounts.id.prompt();
			} );
		}
		iniciarGis();
	}

	/* --------- Compartilhar (perfil): share nativo ou copiar link --------- */
	function iniciarCompartilhar() {
		document.addEventListener( 'click', function ( e ) {
			var botao = e.target.closest( '[data-compartilhar]' );
			if ( ! botao ) {
				return;
			}
			e.preventDefault();
			var dados = { title: document.title, url: window.location.href };
			if ( navigator.share ) {
				navigator.share( dados ).catch( function () {} );
				return;
			}
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( dados.url ).then( function () {
					botao.classList.add( 'is-copiado' );
					setTimeout( function () {
						botao.classList.remove( 'is-copiado' );
					}, 1500 );
				} );
			}
		} );
	}

	/* --------- Preview no hover das thumbs (1s do início, meio e fim) --------- */
	function iniciarPreviews() {
		// Só em dispositivos com mouse (hover real).
		if ( ! window.matchMedia || ! window.matchMedia( '(hover: hover)' ).matches ) {
			return;
		}

		var atual = null; // { card, video, timers }

		function parar() {
			if ( ! atual ) {
				return;
			}
			atual.timers.forEach( clearTimeout );
			clearInterval( atual.intervalo );
			if ( atual.video ) {
				atual.video.pause();
				atual.video.removeAttribute( 'src' );
				try { atual.video.load(); } catch ( e ) {}
				atual.video.remove();
			}
			atual.card.classList.remove( 'is-previewing' );
			atual = null;
		}

		function comecar( card ) {
			var src = card.getAttribute( 'data-preview' );
			var thumb = card.querySelector( '.xf-card__thumb' );
			if ( ! src || ! thumb ) {
				return;
			}
			var v = document.createElement( 'video' );
			v.className = 'xf-card__prev';
			v.muted = true;
			v.playsInline = true;
			v.preload = 'metadata';
			v.src = src;
			thumb.appendChild( v );

			var estado = { card: card, video: v, timers: [], intervalo: 0 };
			atual = estado;

			v.addEventListener( 'loadedmetadata', function () {
				if ( atual !== estado ) {
					return;
				}
				var dur = v.duration;
				// Trechos de ~1s: início, meio e fim.
				var pontos = ( dur && isFinite( dur ) && dur > 4 )
					? [ 0, dur / 2, Math.max( 0, dur - 1.3 ) ]
					: [ 0 ];
				var i = 0;
				v.currentTime = pontos[ 0 ];
				v.play().then( function () {
					card.classList.add( 'is-previewing' );
				} ).catch( function () {} );
				if ( pontos.length > 1 ) {
					estado.intervalo = setInterval( function () {
						i = ( i + 1 ) % pontos.length;
						try { v.currentTime = pontos[ i ]; } catch ( e ) {}
					}, 1000 );
				}
			} );
			v.addEventListener( 'error', function () {
				if ( atual === estado ) { parar(); }
			} );
		}

		document.addEventListener( 'mouseover', function ( e ) {
			var card = e.target.closest( '[data-preview]' );
			if ( ! card ) {
				return;
			}
			if ( atual && atual.card === card ) {
				return;
			}
			parar();
			// Pequeno atraso: não baixa vídeo em passadas rápidas do mouse.
			var t = setTimeout( function () { comecar( card ); }, 180 );
			card.addEventListener( 'mouseleave', function ao_sair() {
				clearTimeout( t );
				card.removeEventListener( 'mouseleave', ao_sair );
				if ( atual && atual.card === card ) { parar(); }
			} );
		} );
	}

	/* --------- Scroll infinito das grades (home, autor, categoria, busca, playlist) --------- */
	function iniciarScrollInfinito() {
		var D = window.tikpornDados || {};
		document.querySelectorAll( '[data-grade-inf]' ).forEach( function ( grade ) {
			if ( grade.getAttribute( 'data-tem-mais' ) !== '1' ) {
				return;
			}
			var pagina     = parseInt( grade.getAttribute( 'data-pagina' ) || '1', 10 );
			var carregando = false;

			var sentinela = document.createElement( 'div' );
			sentinela.className = 'xf-sentinela';
			sentinela.innerHTML = '<span></span>';
			grade.parentNode.insertBefore( sentinela, grade.nextSibling );

			var io = new IntersectionObserver( function ( entradas ) {
				if ( ! entradas[ 0 ].isIntersecting || carregando ) {
					return;
				}
				carregando = true;
				sentinela.classList.add( 'is-carregando' );

				var params = new URLSearchParams();
				params.append( 'action', 'tikporn_grade' );
				params.append( 'pagina', pagina + 1 );
				[ 'tipo', 'autor', 'tax', 'term', 'busca', 'playlist', 'ordem', 'qtd' ].forEach( function ( k ) {
					var v = grade.getAttribute( 'data-' + k );
					if ( v ) { params.append( k, v ); }
				} );

				fetch( D.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: params.toString(),
				} ).then( function ( r ) { return r.json(); } ).then( function ( res ) {
					carregando = false;
					sentinela.classList.remove( 'is-carregando' );
					if ( ! res || ! res.success || ! res.data ) {
						io.disconnect();
						sentinela.remove();
						return;
					}
					if ( res.data.html ) {
						grade.insertAdjacentHTML( 'beforeend', res.data.html );
					}
					pagina++;
					if ( ! res.data.tem_mais ) {
						io.disconnect();
						sentinela.remove();
					}
				} ).catch( function () {
					carregando = false;
					sentinela.classList.remove( 'is-carregando' );
				} );
			}, { rootMargin: '600px 0px' } );

			io.observe( sentinela );
		} );
	}

	/* --------- Nome do arquivo no upload custom (Minha conta) --------- */
	function iniciarArquivo() {
		document.addEventListener( 'change', function ( e ) {
			if ( ! e.target.matches( '[data-arquivo]' ) ) {
				return;
			}
			var alvo = e.target.closest( '.xf-arquivo' );
			var nome = alvo && alvo.querySelector( '[data-arquivo-nome]' );
			if ( nome && e.target.files && e.target.files[ 0 ] ) {
				nome.textContent = e.target.files[ 0 ].name;
			}
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		iniciarAutoplay();
		iniciarPlayToggle();
		iniciarCurtir();
		iniciarSeguir();
		iniciarVerMais();
		centralizarCatsTab();
		iniciarGoogle();
		iniciarCompartilhar();
		iniciarScrollInfinito();
		iniciarPreviews();
		iniciarArquivo();
	} );
} )();
