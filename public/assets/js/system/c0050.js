$(function(){

	$('a[id^=w_holiday]').click(function(e) {
		var year 	= $(this).attr('data-year');
		var month 	= $(this).attr('data-month');
		var no 		= $(this).attr('data-no');

		return setHolidayWeek(year,month,no);
	});

	$('a[id=days]').click(function(e) {
		var year 	= $(this).attr('data-year');
		var month 	= $(this).attr('data-month');
		var day 	= $(this).attr('data-day');
		var mode 	= $(this).attr('data-mode');

		return setHoliday(year,month,day,mode);
	});

	function setHoliday(year,month,day,mode){

		var w_day = year + '/' + month + '/' + day;

		$("input[id=upDay]").val(w_day);
		$("input[id=holidayMode]").val(mode);

		// document.calendar.upDay.value = year + '/' + month + '/' + day;
		// document.calendar.holidayMode.value = mode;

		$('form[id=calendar]').submit();
	};
	function setHolidayWeek(year,month,week){

		var w_day = year + '/' + month + '/01';

		$("input[id=upDay]").val(w_day);
		$("input[id=upWeek]").val(week);

		// document.calendar.upDay.value = year + '/' + month + '/01';
		// document.calendar.upWeek.value = week;
		$('form[id=calendar]').submit();
	};
});
