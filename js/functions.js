jQuery(document).ready(function(){

	jQuery('.event_dates option:selected').each( 
		function() {
			var date_id = jQuery(this).attr('data-dateid');
			jQuery('ul[data-dateid="' + date_id + '"]').show();
		}
	);

	jQuery('a.show_full').toggle(

		function() {
			var event_id = jQuery(this).attr('data-eventdescription');
			jQuery('div.bpt_full_description[data-eventdescription="' + event_id + '"]').show();
			jQuery('a.hide_full').show();

			jQuery(this).text('Hide Full Description');
			
		},
		function() {
			var event_id = jQuery(this).attr('data-eventdescription');
			jQuery('div.bpt_full_description[data-eventdescription="' + event_id + '"]').hide();
			jQuery(this).text('Show Full Description');		
		}
	);

	
	// Change the price options to display on date change.
	jQuery('.event_dates').change(
		function() {

			var event_id = jQuery(this).parent().attr('data-eventid');
			console.log(event_id)

			var date_id = jQuery('option:selected', this).attr('data-dateid');
			console.log(date_id)

			jQuery('form.event_' + event_id + ' ul.bpt_price_list').hide();
			jQuery('ul.bpt_price_list[data-dateid="' + date_id + '"]').show();
		}
	);
});