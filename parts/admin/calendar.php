<div class="wrap">
	<div id="calendar" data-initial="<?php echo date('Y-m-d'); ?>" data-events='{"source":"<?php echo get_rest_url( null, 'arasta/v1/events/super' ); ?>","userid":<?php echo get_current_user_id(); ?>,"nonce":"<?php echo wp_create_nonce( 'astra_rest_getevents' ); ?>"}'></div>
</div>

<div id="eventThickbox" style="display:none;">
	<form id="eventThickboxContent" style="direction:rtl;text-align:start;">
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="date">تاریخ</label></th>
				<td><input type="text" name="date" id="date" value="" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="datetime_start">زمان شروع</label></th>
				<td><input type="time" name="datetime_start" dir="ltr" id="datetime_start" min="06:30" max="23:00" value=""></td>
			</tr>
			<tr>
				<th scope="row"><label for="datetime_end">زمان پایان</label></th>
				<td><input type="time" name="datetime_end" dir="ltr" id="datetime_end" min="06:30" max="23:00" value=""></td>
			</tr>
			<tr>
				<th scope="row"><label for="salon_id">مرکز زیبایی</label></th>
				<td><input type="text" name="salon_id" id="salon_id" value="" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="customer_id">مشتری</label></th>
				<td><input type="text" name="customer_id" id="customer_id" value="" class="regular-text"></td>
			</tr>
		</table>
		<button type="submit" class="button button-primary">TEST</button>
		<a href="#" class="button button-secondary">لغو نوبت</a>
	</form>
</div>

<script>
const $eventThick = jQuery('#eventThickboxContent');
const calendarEl = document.getElementById('calendar');
const calEvent = JSON.parse(calendarEl.dataset.events);
var calendar = new FullCalendar.Calendar(calendarEl, {
	height: '85vh',
	locale: 'fa',
	timeZone: 'local',
	direction: 'rtl',
	buttonText: {
		today: 'امروز',
		month: 'تقویم',
		week: 'هفته',
		day: 'روز',
		list: 'لیست',
	},
	customButtons: {
		saveDatesBTN: {
			text: 'ذخیره روز های تعطیل',
			click: function() {
				$('#calendar').submit();
			}
		}
	},
	headerToolbar: {
		left: 'today prev,next',
		center: 'title',
		right: 'listWeek,dayGridMonth'
	},
	initialView: 'dayGridMonth',
	initialDate: calendarEl.dataset.initial,
	expandRows: true,
	editable: true,
	selectable: true,
	nowIndicator: true,
	eventColor: '#378006',
	dateClick: (info) => {
		const today = new Date();
		const clicked = new Date(info.date);
		today.setHours(0,0,0,0);
		clicked.setHours(0,0,0,0);
		if (clicked < today) {
			alert("امکان افزودن رویداد در روزهای گذشته وجود ندارد.");
			return;
		}

		$eventThick.find('#salon_id').val( '' );
		$eventThick.find('#customer_id').val( '' );
		$eventThick.find('#datetime_start').val('');
		$eventThick.find('#datetime_End').val('');
		tb_show( 'افزودن رزرو', '/?TB_inline&inlineId=eventThickbox&width=540&height=350' );
	},
	events: {
		url: calEvent.source,
		method: 'GET',
		extraParams: () => {
			return {
				user_id: calEvent.userid,
				"X-WP-Nonce" : calEvent.nonce
			}
		},
		failure: () => {
			alert('خطا در دریافت رویدادها از REST API');
		}
    },
	eventClick: ( info ) => {
		const dateTimeStart = info.event.start ? `${info.event.start.getHours()}:${info.event.start.getMinutes()}` : '';
		const dateTimeEnd = info.event.end ? `${info.event.end.getHours()}:${info.event.end.getMinutes()}` : '';
		$eventThick.find('#salon_id').val( info.event.extendedProps.salon_id );
		$eventThick.find('#customer_id').val( info.event.extendedProps.salon_id );
		$eventThick.find('#datetime_start').val( dateTimeStart );
		$eventThick.find('#datetime_End').val( dateTimeEnd );

		tb_show( 'ویرایش رزرو', '/?TB_inline&inlineId=eventThickbox&width=540&height=350' );
	}
});
calendar.render();

$eventThick.on('submit', function(e){
	e.preventDefault();
	tb_remove();
});

</script>
