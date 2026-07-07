/**
 * Player custom da página do vídeo (watch) — tikporn.
 * Autoplay mudo, tap play/pause, mute, barra de progresso, tempo e setas ↑/↓.
 */
( function () {
	'use strict';

	var wrap = document.querySelector( '[data-watch]' );
	if ( ! wrap ) {
		return;
	}

	var video = wrap.querySelector( '[data-video]' );
	var stage = wrap.querySelector( '[data-stage]' );
	var mute  = wrap.querySelector( '[data-mute]' );
	var seek  = wrap.querySelector( '[data-seek]' );
	var time  = wrap.querySelector( '[data-time]' );
	var pp    = wrap.querySelector( '[data-pp]' );

	function fmt( s ) {
		s = Math.floor( s || 0 );
		var m = Math.floor( s / 60 );
		var r = s % 60;
		return m + ':' + ( r < 10 ? '0' : '' ) + r;
	}

	// Setas ↑/↓ também no teclado.
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'ArrowUp' || e.key === 'ArrowDown' ) {
			var sel = e.key === 'ArrowUp' ? '.xf-watch__arrow:first-child' : '.xf-watch__arrow:last-child';
			var a = wrap.querySelector( '.xf-watch__nav ' + sel );
			if ( a && a.href ) {
				e.preventDefault();
				window.location.href = a.href;
			}
		}
	} );

	if ( ! video ) {
		return; // embed (iframe): sem player custom
	}

	// Autoplay mudo.
	video.muted = true;
	function syncMute() { wrap.classList.toggle( 'is-muted', video.muted ); }
	function syncPlay() { wrap.classList.toggle( 'is-paused', video.paused ); }

	video.play().catch( function () {} );

	// Tap no vídeo → play/pause.
	if ( stage ) {
		stage.addEventListener( 'click', function () {
			if ( video.paused ) { video.play().catch( function () {} ); } else { video.pause(); }
		} );
	}
	if ( pp ) {
		pp.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
			if ( video.paused ) { video.play().catch( function () {} ); } else { video.pause(); }
		} );
	}

	video.addEventListener( 'play', syncPlay );
	video.addEventListener( 'pause', syncPlay );

	// Mute.
	if ( mute ) {
		mute.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
			video.muted = ! video.muted;
			if ( ! video.muted && video.volume === 0 ) { video.volume = 1; }
			syncMute();
		} );
	}

	// Progresso + tempo.
	video.addEventListener( 'timeupdate', function () {
		if ( video.duration && seek ) {
			seek.value = ( video.currentTime / video.duration ) * 100;
			seek.style.setProperty( '--p', seek.value + '%' );
		}
		if ( time ) { time.textContent = fmt( video.currentTime ); }
	} );
	video.addEventListener( 'loadedmetadata', function () {
		if ( time ) { time.textContent = fmt( 0 ); }
	} );

	// Seek (arrastar a barra).
	if ( seek ) {
		seek.addEventListener( 'input', function ( e ) {
			e.stopPropagation();
			if ( video.duration ) { video.currentTime = ( seek.value / 100 ) * video.duration; }
			seek.style.setProperty( '--p', seek.value + '%' );
		} );
		seek.addEventListener( 'click', function ( e ) { e.stopPropagation(); } );
	}

	syncMute();
	syncPlay();
} )();
