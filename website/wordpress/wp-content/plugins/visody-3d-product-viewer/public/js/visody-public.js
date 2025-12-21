var waitForEl = function (selector, callback) {
	if (jQuery(selector).length) {
		setTimeout(function () {
			callback();
		}, visody_check_interval);
	} else {
		setTimeout(function () {
			waitForEl(selector, callback);
		}, visody_check_interval);
	}
};

(function ($) {
	"use strict";

	/**
	 * Load
	 */

	$(document).ready(function () {

		bindViewerEventHandlers();

		if ($(visody_gallery + ' .vsd-model-viewer-overlay').length) {
			if ($(visody_gallery_slide).length > 1) {
				waitForEl(visody_gallery_active_slide, function () {
					var $thumbnail = $(visody_gallery_active_slide);

					$(visody_gallery + ' .vsd-model-viewer').width($thumbnail.width());
					$(visody_gallery + ' .vsd-model-viewer').height($thumbnail.height());
					$(visody_gallery + ' .vsd-model-viewer-overlay').addClass('loaded');
				});
			} else if ($(visody_gallery_slide).length) {
				waitForEl(visody_gallery_slide, function () {
					var $thumbnail = $(visody_gallery_slide);

					$(visody_gallery + ' .vsd-model-viewer').width($thumbnail.width());
					$(visody_gallery + ' .vsd-model-viewer').height($thumbnail.height());
					$(visody_gallery + ' .vsd-model-viewer-overlay').addClass('loaded');
				});
			}

			$(window).on('resize', function () {
				var isInFullScreen = (document.fullScreenElement && document.fullScreenElement !== null) || (document.mozFullScreen || document.webkitIsFullScreen);
				if (!isInFullScreen) {
					if ($(visody_gallery_slide).length > 1) {
						var $thumbnail = $(visody_gallery_active_slide);
					} else {
						var $thumbnail = $(visody_gallery_slide);
					}

					$(visody_gallery + ' .vsd-model-viewer').width($thumbnail.width());
					$(visody_gallery + ' .vsd-model-viewer').height($thumbnail.height());
				}
			});
		}

		if ($(visody_gallery).length ) {
			var control_button_html = '';
			if ( $('.vsd-model-viewer-control-buttons.control-buttons-gallery.hidden').length ) {
				control_button_html = $('.vsd-model-viewer-control-buttons.control-buttons-gallery.hidden').get(0).outerHTML;
				$('.vsd-model-viewer-control-buttons.control-buttons-gallery.hidden').remove();
			}

			if ($(visody_gallery_slide).length > 1) {
				waitForEl(visody_gallery_active_slide, function () {
					bindViewerEventHandlers();
					$(visody_gallery).append(control_button_html);
					$('.vsd-model-viewer-control-buttons.control-buttons-gallery').removeClass('hidden');
					$('.vsd-model-viewer-inline model-viewer').trigger('load');
				});
			} else {
				waitForEl(visody_gallery_slide, function () {
					bindViewerEventHandlers();
					$(visody_gallery).append(control_button_html);
					$('.vsd-model-viewer-control-buttons.control-buttons-gallery').removeClass('hidden');
					$('.vsd-model-viewer-inline model-viewer').trigger('load');
				});
			}
		}

		// // Check WebXR support in user agent
		// if ( $('.vsd-model-viewer model-viewer').length ) {
		// 	(async function() {
		// 		if (navigator.xr && await navigator.xr.isSessionSupported("immersive-ar")) {
		// 			// All is well
		// 		} else {
		// 			// Browser no support for AR. Disable buttons immidiately
		// 			if ( $(window).width() < 922 ) {
		// 				$('.vsd-model-viewer-ar-desktop').attr('disabled', 'disabled');
		// 			}
		// 			$('.vsd-model-viewer-ar-button').attr('disabled', 'disabled');
		// 		}
		// 	})();
		// }

		if ($(visody_gallery + ' .vsd-model-viewer-last-slide').length) {
			if ($(visody_gallery_slide).length > 1) {
				// Only move when slide count > 1
				waitForEl(visody_gallery_slide, function () {
					var viewerHtml = $(visody_gallery + ' .vsd-model-viewer-last-slide').get(0).outerHTML;

					$(visody_gallery + ' .vsd-model-viewer-last-slide').remove();
					$(visody_gallery_active_slide).removeClass('vsd-model-viewer__image');

					$(visody_gallery_slide + ':last-child').addClass('vsd-model-viewer__image');
					$(visody_gallery_slide + ':last-child').append(viewerHtml);

					// Rebind event handlers to keep viewer active.
					bindViewerEventHandlers();
				});
			}
		}

		$(document).on('click', '.vsd-model-viewer-ar-button', function() {
			// Show loading indicator that button is clicked to give user feedback.
			if ( $(this).parents('.vsd-model-viewer-control-buttons').length ) {
				if ( ! $(this).parents('.vsd-model-viewer-control-buttons').hasClass('loaded') ) {
					$(this).addClass('loading');
				}
			} else {
				if ( ! $(this).parents('.vsd-model-viewer').hasClass('loaded') ) {
					$(this).addClass('loading');
				}
			}
		});

		$(document).on('click', '.vsd-model-viewer .vsd-model-viewer-zoom-in-button', function(e) {
			e.preventDefault();
			$(this).closest('.vsd-model-viewer').find('model-viewer').get(0).zoom(visody_zoom_factor);
		});

		$(document).on('click', '.vsd-model-viewer .vsd-model-viewer-zoom-out-button', function(e) {
			e.preventDefault();
			$(this).closest('.vsd-model-viewer').find('model-viewer').get(0).zoom(-visody_zoom_factor);
		});

		$(document).on('click', visody_gallery + ' .vsd-model-viewer-3d-button', function (e) {
			e.preventDefault();
			$(visody_gallery + ' .vsd-model-viewer').addClass('active');
			if ($(visody_gallery_trigger).length) {
				$(visody_gallery_trigger).hide();
			}
		});

		$(document).on('click', visody_gallery + ' .vsd-model-viewer .vsd-model-viewer-fs-button', function (e) {
			e.preventDefault();
			toggleFullScreen($('.vsd-model-viewer').get(0));
		});

		$(document).on('click', visody_gallery + ' .vsd-model-viewer.fullscreen .vsd-model-viewer-close-button', function (e) {
			e.preventDefault();
			document.activeElement.blur();
			toggleFullScreen($('.vsd-model-viewer').get(0));
		});

		$(document).on('click', visody_gallery + ' .vsd-model-viewer-close-button', function (e) {
			e.preventDefault();
			var isInFullScreen = (document.fullScreenElement && document.fullScreenElement !== null) || (document.mozFullScreen || document.webkitIsFullScreen);
			if (!isInFullScreen) {
				$(visody_gallery + ' .vsd-model-viewer').removeClass('active');

				if ($(visody_gallery_trigger).length) {
					$(visody_gallery_trigger).show();
				}
			}
		});

		$(document).on('click', '.vsd-model-viewer .vsd-model-viewer-cam-button', function (e) {
			e.preventDefault();
			var title = $(this).data('title') || 'capture';
			var dataUrl = $(this).closest('.vsd-model-viewer').find('model-viewer').get(0).toDataURL();
			var anchor = document.createElement('a');
			anchor.style.display = 'none';
			document.body.appendChild(anchor);
			anchor.href = dataUrl;
			anchor.download = title + '.png';
			anchor.click();
			window.setTimeout(() => {
				document.body.removeChild(anchor);
			}, 100);
		});

		$(document).on('click', '.vsd-model-viewer-control-buttons.control-buttons-gallery .vsd-model-viewer-3d-button', function (e) {
			e.preventDefault();
			$(visody_gallery + ' .vsd-model-viewer').addClass('active');
			if ($(visody_gallery_trigger).length) {
				$(visody_gallery_trigger).hide();
			}
		});

		$(document).on('click', '.vsd-model-viewer-control-buttons.control-buttons-gallery .vsd-model-viewer-ar-button', function (e) {
			e.preventDefault();
			$(visody_gallery + ' .vsd-model-viewer').addClass('active');
			$(visody_gallery + ' .vsd-model-viewer .vsd-model-viewer-ar-button').trigger('click');
			$(visody_gallery + ' .vsd-model-viewer').removeClass('active');
		});

		$(document).on('click touchstart', visody_gallery_control_thumbs_item, function (e) {
			e.preventDefault();

			var $thumbnail = $(visody_gallery_active_slide);
			$(visody_gallery + ' .vsd-model-viewer-overlay').width($thumbnail.width());
			$(visody_gallery + ' .vsd-model-viewer-overlay').height($thumbnail.height());

			if ($(visody_gallery_trigger).length) {
				if ($(visody_gallery_active_slide + ' .vsd-model-viewer-inline').length) {
					$(visody_gallery_trigger).hide();
				} else {
					$(visody_gallery_trigger).show();
				}
			}
		});

		$(document).keyup(function (e) {
			var isInFullScreen = (document.fullScreenElement && document.fullScreenElement !== null) || (document.mozFullScreen || document.webkitIsFullScreen);
			if (e.key === "Escape" && !isInFullScreen) { // escape key maps to keycode `27`
				$(visody_gallery + ' .vsd-model-viewer').removeClass('active');
			}
		});

		/**
		 * YITH Quick View support.
		 */
		$(document).on('qv_loader_stop', function() {
			if ( $(document).find('#yith-quick-view-content .vsd-model-viewer').length ) {
				bindViewerEventHandlers();
			}
		});

		/**
		 * Variation Gallery Images support
		 */
		var viewerHtml = '';
		if ( $('.wpgs-for').length ) {
			waitForEl('.wpgs-for .vsd-model-viewer', function() {
				viewerHtml = $('.wpgs-for .vsd-model-viewer').get(0).outerHTML;
			});
		}
		if ( $('.wcgs-carousel').length ) {
			waitForEl('.wcgs-carousel .vsd-model-viewer', function() {
				viewerHtml = $('.wcgs-carousel .vsd-model-viewer').get(0).outerHTML;
			});
		}
		if ( $('.woo-variation-product-gallery').length ) {
			waitForEl('.woo-variation-product-gallery .vsd-model-viewer', function() {
				viewerHtml = $('.woo-variation-product-gallery .vsd-model-viewer').get(0).outerHTML;
			});
		}
		if ( $('.theme-woodmart .woocommerce-product-gallery .owl-carousel').length ) {
			waitForEl('.woocommerce-product-gallery .owl-carousel', function() {
				viewerHtml = $('.woocommerce-product-gallery .owl-carousel .vsd-model-viewer').get(0).outerHTML;
			});
		}

		$( document ).on( "show_variation", ".variations_form", function ( event, variation ) {
			if ( $('.wpgs_image').length ) {
				waitForEl('.wpgs_image.woocommerce-product-gallery__image', function() {
					$('.wpgs_image .vsd-model-viewer').remove();
					$('.wpgs_image.woocommerce-product-gallery__image').append(viewerHtml);
					bindViewerEventHandlers();
				});
			}

			if ( $('.wvg-gallery-image').length ) {
				waitForEl('.wvg-gallery-image', function() {
					$('.wvg-gallery-image .vsd-model-viewer').remove();
					$('.wvg-gallery-image').append(viewerHtml);
					bindViewerEventHandlers();
				});
			}

			if ( $('.theme-woodmart .woocommerce-product-gallery .owl-item .product-image-wrap').length ) {
				$('.theme-woodmart .woocommerce-product-gallery .owl-item:first-child .product-image-wrap .vsd-model-viewer').remove();
				$('.theme-woodmart .woocommerce-product-gallery .owl-item:first-child .product-image-wrap').append(viewerHtml);
				bindViewerEventHandlers();
			}
		});

		$( document ).on( "reset_data", ".variations_form", function (e) {
			if ( $('.wpgs_image').length ) {
				setTimeout(function() {
					$('.wpgs_image').append(viewerHtml);
					bindViewerEventHandlers();
				}, visody_check_interval);
			}

			if ( $('.wvg-gallery-image').length ) {
				setTimeout(function() {
					$('.wvg-gallery-image').append(viewerHtml);
					bindViewerEventHandlers();
				}, visody_check_interval);
			}
			
			if ( $('.theme-woodmart .woocommerce-product-gallery .owl-item .product-image-wrap').length ) {
				setTimeout(function() {
					$('.theme-woodmart .woocommerce-product-gallery .owl-item:first-child .product-image-wrap').append(viewerHtml);
					bindViewerEventHandlers();
				}, visody_check_interval);
			}
		});
	});

	window.visodyBindViewerEventHandlers = () => {
		bindViewerEventHandlers();
	}

	function bindViewerEventHandlers() {
		$('.vsd-model-viewer model-viewer').bind('progress', function (event) {
			$(this).find('.vsd-model-viewer-loader-progress').css('width', parseInt(event.detail.totalProgress * 100) + '%');
			$(this).find('.vsd-model-viewer-loader-value').text(parseInt(event.detail.totalProgress * 100) + '%');
		});

		$('.vsd-model-viewer model-viewer').bind('ar-status', function (event) {
			if (event.detail.status === 'failed') {
				// Failed. Show error.
				var error = $(event.target).find(".vsd-ar-failed");
				error.removeClass('hide');
				error.bind('transitionend', (event) => {
					setTimeout(function() {
						error.addClass('hide');
					}, 1000);
				});

				// Make AR buttons disabled.
				$('.vsd-model-viewer-ar-desktop').attr('disabled', 'disabled');
				$('.vsd-model-viewer-ar-button').attr('disabled', 'disabled');
			} else {
				// Not failed. Remove loading indicators.
				$('.vsd-model-viewer-ar-button').removeClass('loading');
			}
		});

		$('.vsd-model-viewer model-viewer').bind('load', function (event) {
			$(this).find('.vsd-model-viewer-loader').hide();
			$(this).parents('.vsd-model-viewer').addClass('loaded');

			var idref = $(this).parents('.vsd-model-viewer').attr('id');
			$('.vsd-model-viewer-control-buttons[data-viewer-id="' + idref + '"]').addClass('loaded');

			if ( ! $(this).parents('.vsd-model-viewer').hasClass('vsd-model-viewer-loop') ) {
				if ($(this).parents('.vsd-model-viewer').hasClass('vsd-model-viewer-inline') && $(visody_gallery_trigger).length) {
					$(visody_gallery_trigger).hide();
				}
			}

			$('.vsd-model-viewer-ar-button').removeClass('loading');
		});

		if ( $('.vsd-model-viewer .vsd-model-viewer-zoom-in-button').length || $('.vsd-model-viewer model-viewer[data-zoom="manual"]').length ) {
			$('.vsd-model-viewer model-viewer').each(function(key,mv){
				mv.addEventListener("wheel", (e) => {
					e.stopPropagation();
				}, true);
			});
		}
	}

	document.addEventListener('fullscreenchange', exitHandler);
	document.addEventListener('webkitfullscreenchange', exitHandler);
	document.addEventListener('mozfullscreenchange', exitHandler);
	document.addEventListener('MSFullscreenChange', exitHandler);

	function exitHandler() {
		if (!document.fullscreenElement && !document.webkitIsFullScreen && !document.mozFullScreen && !document.msFullscreenElement) {
			$(visody_gallery + ' .vsd-model-viewer').removeClass('fullscreen');
		}
	}

	function toggleFullScreen(el) {
		if (!el) {
			el = document.body; // Make the body go full screen.
		}
		var isInFullScreen = (document.fullScreenElement && document.fullScreenElement !== null) || (document.mozFullScreen || document.webkitIsFullScreen);
		if (isInFullScreen) {
			$(visody_gallery + ' .vsd-model-viewer').removeClass('fullscreen');
			cancelFullScreen();
		} else {
			$(visody_gallery + ' .vsd-model-viewer').addClass('fullscreen');
			requestFullScreen(el);
		}
		return false;
	}

	function cancelFullScreen() {
		var el = document;
		var requestMethod = el.cancelFullScreen || el.webkitCancelFullScreen || el.mozCancelFullScreen || el.exitFullscreen || el.webkitExitFullscreen;
		if (requestMethod) { // cancel full screen.
			requestMethod.call(el);
		} else if (typeof window.ActiveXObject !== "undefined") { // Older IE.
			var wscript = new ActiveXObject("WScript.Shell");
			if (wscript !== null) {
				wscript.SendKeys("{F11}");
			}
		}
	}

	function requestFullScreen(el) {
		// Supports most browsers and their versions.
		var requestMethod = el.requestFullScreen || el.webkitRequestFullScreen || el.mozRequestFullScreen || el.msRequestFullscreen;
		if (requestMethod) { // Native full screen.
			requestMethod.call(el);
		} else if (typeof window.ActiveXObject !== "undefined") { // Older IE.
			var wscript = new ActiveXObject("WScript.Shell");
			if (wscript !== null) {
				wscript.SendKeys("{F11}");
			}
		}
		return false;
	}
}(jQuery));