function fa2en(value) {
	const englishNumbers = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', ','], persianNumbers = ['۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '۰', '،'];
	for (let i = 0; i < 11; i++) {
		value = value.replace(new RegExp(persianNumbers[i], 'g'), englishNumbers[i]);
	}
	return value;
}

function number2persian(input) {
	const delimiter = ' و ';
	const letters = {
		ones: ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'],
		teens: ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده', 'بیست'],
		tens: ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'],
		hundreds: ['', 'یکصد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'],
		scales: ['', ' هزار', ' میلیون', ' میلیارد', ' بیلیون', ' بیلیارد', ' تریلیون', ' تریلیارد',' کوآدریلیون', ' کادریلیارد', ' کوینتیلیون', ' کوانتینیارد', ' سکستیلیون', ' سکستیلیارد', ' سپتیلیون', ' سپتیلیارد', ' اکتیلیون', ' اکتیلیارد', ' نانیلیون', ' نانیلیارد', ' دسیلیون', ' دسیلیارد'],
		decimals: ['', 'دهم', 'صدم', 'هزارم', 'ده‌هزارم', 'صد‌هزارم', 'میلیونوم', 'ده‌میلیونوم', 'صدمیلیونوم', 'میلیاردم', 'ده‌میلیاردم', 'صد‌‌میلیاردم']
	};

	const threeDigitsToWord = (n) => {
		n = parseInt(n, 10);
		if (!n) return '';
		if (n < 10) return letters.ones[n];
		if (n <= 20) return letters.teens[n - 10];
		if (n < 100) return letters.tens[Math.floor(n / 10)] + (n % 10 ? delimiter + letters.ones[n % 10] : '');
		const h = Math.floor(n / 100), t = Math.floor((n % 100) / 10), o = n % 10, out = [];
		if (h) out.push(letters.hundreds[h]);
		if (t * 10 + o) {
		if (t < 2) out.push(threeDigitsToWord(t * 10 + o));
		else out.push(letters.tens[t] + (o ? delimiter + letters.ones[o] : ''));
		}
		return out.join(delimiter);
	};

	let clean = fa2en(input.toString()).replace(/[^0-9.-]/g, '');
	let num = parseFloat(clean);
	if( isNaN(num) || num === 0) return false;

	let negative = num < 0;
	let [intPart, decPart = ''] = Math.abs(num).toString().split('.');

	if (intPart.length > 66) return 'خارج از محدوده';

	intPart = intPart.padStart(Math.ceil(intPart.length / 3) * 3, '0');
	const chunks = intPart.match(/\d{3}/g) || [];

	const words = chunks.map((chunk, i) => {
		const w = threeDigitsToWord(chunk);
		return w ? w + letters.scales[chunks.length - i - 1] : '';
	}).filter(Boolean);

	let decWords = '';
	if (decPart) {
		decWords = delimiter + threeDigitsToWord(decPart) + ' ' +
		(letters.decimals[decPart.length] || '');
	}
	return (negative ? 'منفی ' : '') + words.join(delimiter) + decWords;
}

jQuery(document).ready(function( $ ){
	$(document).on('input', '.price-field', function(){
		const price = number2persian( $(this).val() );
		$(this).parent().find('.price-field-letters').html((price ? `${price} تومان` : ''));
	});
	$(".metabox-holder").each(function(){
		var $holder = $(this);
		var $cols = $holder.find('.meta-box-sortables');
		$cols.sortable({
			connectWith: $cols,
			placeholder: 'sortable-placeholder',
			forcePlaceholderSize: true,
			items: '.postbox',
			start: function(e, ui){
				ui.placeholder.height(ui.item.outerHeight());
			},
			update: function(e, ui){
				$cols.each(function(){
					if($(this).children(".postbox").length === 0){
						$(this).addClass("empty-container");
					} else {
						$(this).removeClass("empty-container");
					}
				});
				if (this === ui.item.parent()[0]) {
					let $holder = $(this).closest('.metabox-holder');
					let project_id = $holder.attr('id');
					let order = {};
					$holder.find('.meta-box-sortables').each(function(){
						let listName = $(this).data('list');
						order[listName] = $(this).sortable('toArray');
					});
					$.post(ajaxurl, {
						action: 'arvand_update_project_tasks',
						project_id: project_id,
						order: order,
					});
				}
			}
		}).disableSelection();
	});
});
