const themejs = document.getElementById('themejs'), ajaxURL = themejs.dataset.ajax;

//Form JS Validation
// const validationForms = document.querySelectorAll('.form-validation');
// Array.from(validationForms).forEach(function(form) {
// 	form.addEventListener('submit', function(e){
// 		if( !form.checkValidity() ) {
// 			e.preventDefault();
// 			e.stopPropagation();
// 		}
// 		form.classList.add('was-validated')
// 	}, false);
// 	const inputs = form.querySelectorAll('input, textarea, select');
// 	Array.from(inputs).forEach(function(input) {
// 		input.oninvalid = function(e) {
// 			e.target.setCustomValidity('');
// 			if( !e.target.validity.valid && e.target.title.length > 0 ) {
// 				e.target.setCustomValidity( e.target.title );
// 			}
// 		}
// 		input.oninput = function(e) {
// 			e.target.setCustomValidity('');
// 		}
// 	});
// });

//OTP
async function arvandOtpAjax(action, nonce, data = null) {
	const formData = new FormData();
	formData.append('action', 'arvand_otp');
	formData.append('nonce', nonce);
	formData.append('method', action);
	
	for (const key in data) {
		if (data.hasOwnProperty(key)) {
			formData.append(key, data[key]);
		}
	}
	
	try {
		const res = await fetch(ajaxURL, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		});
		if( !res.ok ) {
			throw new Error(`HTTP error ${res.status}`);
		}
		const response = await res.json();
		return response;
	} catch (error) {
		return {
			success: false,
			data: {
				message: error.message || 'خطا در ارتباط با سرور'
			}
		};
	}
}

document.addEventListener('DOMContentLoaded', function() {
	//Splidejs
	document.querySelectorAll( '.splide' ).forEach((el) => {
		if( el.dataset.splide ) {
			new Splide( el ).mount();
		}
	});
});


if( typeof jQuery !== 'undefined' ){
	jQuery(document).ready(function($){
		//Login
		$(document).on('submit', '.form-arvandOTP', function(e){
			e.preventDefault();
			const nonce = $(this).find('[name="nonce"]').val();
			const method = $(this).find('[name="method"]').val();
			(async () => {
				let response = null;
				if( 'authcode' == method ){
					response = await arvandOtpAjax('checkcode', nonce, {authcode: $(this).find('[name="authcode"]').val() });
				} else {
					response = await arvandOtpAjax('sendcode', nonce, {username: $(this).find('[name="username"]').val()});
				}
				if( response.status === 'loggedin' ) {
					document.dispatchEvent(new CustomEvent("arvandOTP_loggedin", {
						detail: {
							user: response.user,
							message: response.message,
							target: this,
						},
					}));
				} else if( response.status ) {
					$(this).find('fieldset').fadeOut(function(){
						$(this).html( response.fragment ).fadeIn();
					});
				}
				if( response.message.length > 0 ){
					showToast(response.message, (response.status ? 'success' : 'error'), 5000);
				}
			})();
		});
		$(document).on('arvandOTP_loggedin', function(e){
			window.location.reload();
		});

		//Collapse 
		$(document).on('click', '.btn-collapse', function(e) {
			e.preventDefault();
			const $btn = $(this);
			const targetId = $btn.attr('aria-controls');
			if (!targetId) {
				console.error('خطا: aria-controls تعریف نشده است');
				return;
			}
			
			const $target = $('#' + targetId);
			if (!$target.length) {
				console.error(`خطا: المان با ID "${targetId}" یافت نشد`);
				return;
			}
			
			const $tablist = $btn.closest('.tablist');
			
			if ($tablist.length) {
				$tablist.find('.btn-collapse').attr('aria-selected', 'false');
				$tablist.find('[role="tabpanel"]').addClass('hidden');
				$btn.attr('aria-selected', 'true');
				$target.removeClass('hidden');
			} else {
				const isCurrentlyOpen = $btn.attr('aria-expanded') === 'true';
				const $accordion = $btn.closest('.accordion');
				if ($accordion.length && $accordion.hasClass('accordion-single')) {
					$accordion.find('.btn-collapse').not($btn).attr('aria-expanded', 'false');
					// $accordion.find('[role="region"]').not($target).addClass('hidden');
				}
				$btn.attr('aria-expanded', isCurrentlyOpen ? 'false' : 'true');
				$target.toggleClass('hidden', isCurrentlyOpen);
			}
		});
		
		//Like Button
		$(document).on('click', '[data-ilikeit] button', function(e){
			e.preventDefault();
			const $btn		= $(this);
			const $wrap		= $btn.closest('[data-ilikeit]');
			const params	= $wrap.data('ilikeit').split(':');

			if( $btn.hasClass('btn-like') ){
				params.push('like');
			} else {
				params.push('dislike');
			}

			$wrap.find('button').prop('disabled', true).find('.count').html('&hellip;');
			$.post(ajaxURL, {action: 'entry_user_reaction', params: params}, function(resp){
				if( resp.success ) {
					$wrap.find('.btn-like .count').html(resp.data.like);
					$wrap.find('.btn-dislike .count').html(resp.data.dislike);
					$btn.toggleClass('fill');
				} else {
					alert( resp.data );
				}
			});
			$wrap.find('button').prop('disabled', false);
		});

		//Scroll SPY
		document.querySelectorAll('[data-scrollspy]').forEach(element => {
			const wrapper = document.getElementById(element.dataset.scrollspy);
			if( ! wrapper ) {
				console.error('Scrollspy wrapper not found for:', element.dataset.scrollspy);
				return;
			}
			const items = [...element.querySelectorAll('a')];
			if( items.length === 0) {
				console.warn('No items found inside:', element);
				return;
			}

			const firstItem = items[0];
			firstItem.classList.add('is-active');
			let ticking = false;
			window.addEventListener('scroll', () => {
				if( !ticking ) {
					window.requestAnimationFrame(() => {
						const firstTarget = document.querySelector(firstItem.hash);
						if (!firstTarget) return;
						const spygap = firstTarget.offsetTop - wrapper.offsetHeight;
						if (window.pageYOffset <= spygap) {
							items.forEach(link => link.classList.remove('is-active'));
							firstItem.classList.add('is-active');
							element.scrollLeft = firstItem.offsetLeft - element.offsetLeft;
						} else {
							items.forEach(link => {
								const section = document.querySelector(link.hash);
								if (!section) return;

								const sectionTop = section.offsetTop - 100;
								const sectionBottom = sectionTop + section.offsetHeight;

								if (window.pageYOffset >= sectionTop && window.pageYOffset <= sectionBottom) {
									items.forEach(l => l.classList.remove('is-active'));
									link.classList.add('is-active');
									element.scrollLeft = link.offsetLeft - element.offsetLeft;
								}
							});
						}
						ticking = false;
					});
					ticking = true;
				}
			});
		});

		//ContactFrom
		$(document).on('submit', 'form.arvand-contactform', function(e){
			e.preventDefault();
			const $FORM = $(this);
			const params = $FORM.serializeArray();
			$.post(ajaxURL, {action: 'arvand_contact_form', form_data: params}, function(resp){
				if( resp.success ) {
					// TODO: Display resp.message;
				} else {
					alert( resp.message );
				}
			});
		});

		//Offcanvas mmenu
		// $('.collapse-menu li').click(function (e) {
		// 	if(this != e.target) return;
		// 	e.preventDefault();
		// 	$(this).toggleClass('item-opened').find('> ul').slideToggle();
		// });
	});
}