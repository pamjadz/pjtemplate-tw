const themejs = document.getElementById('themejs'), ajaxURL = themejs.dataset.ajax;

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

document.addEventListener('DOMContentLoaded', () => {
	//Splidejs
	document.querySelectorAll( '.splide' ).forEach(el => {
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

		//Offcanvas mmenu
		// $('.collapse-menu li').click(function (e) {
		// 	if(this != e.target) return;
		// 	e.preventDefault();
		// 	$(this).toggleClass('item-opened').find('> ul').slideToggle();
		// });
	});
}