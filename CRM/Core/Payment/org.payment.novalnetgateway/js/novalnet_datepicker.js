cj(function() {
cj( ".date_picker" ).datepicker({
  changeMonth: true,
  changeYear: true,
  dateFormat: 'yy-mm-dd',
  yearRange: '1940:'+(new Date).getFullYear()
});
});
