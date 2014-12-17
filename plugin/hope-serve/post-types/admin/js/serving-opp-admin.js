//need to pass the $ as Wordpress calls noConflict mode
jQuery(document).ready(function ($) {

	//hide/show contact information based on what's selected
	$('.hcc_serving_opp_campus').each(function () {

		var whichCampus = $(this).attr('id');

		if ($(this).prop('checked')) {
			$('#hcc_serving_opp_campus_contact_primary_'+whichCampus).show();
			$('#hcc_serving_opp_campus_contact_secondary_'+whichCampus).show();
		} else {
			$('#hcc_serving_opp_campus_contact_primary_'+whichCampus).hide();
			$('#hcc_serving_opp_campus_contact_secondary_'+whichCampus).hide();
		}
	});

	//bind the actions for the campus checkboxes
	$('.hcc_serving_opp_campus').change(function() {
	
		var whichCampus = $(this).attr('id');
	
		if ($(this).prop('checked')) {
			//find the contact information div corresponding with the id
			//of the checkbox that was clicked
			$('#hcc_serving_opp_campus_contact_primary_'+whichCampus).show();
			$('#hcc_serving_opp_campus_contact_secondary_'+whichCampus).show();
		} else {
			$('#hcc_serving_opp_campus_contact_primary_'+whichCampus).hide();
			$('#hcc_serving_opp_campus_contact_secondary_'+whichCampus).hide();

		}
		
	});

});

