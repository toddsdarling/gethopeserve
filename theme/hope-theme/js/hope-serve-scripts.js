jQuery(document).ready(function($) {

	$('.hcc-serve-button').click(function(e) {

		e.preventDefault();

		//click the close button for any form that might be open
		$('.servingopp-form-closebtn').click();

		//clone blank form sitting at the bottom of the page
		var clonedForm = jQuery('.hcc-serve-formClone').clone();

		//append a hidden field with the opp ID to submit back to the server
		$(clonedForm).find('form[name="hcc-servingopp-form"]').append('<input type="hidden" name="hcc_serveopp_id" value="' + $(this).parents('.servingopp-opportunity').attr('data-rel') + '">');

		//take off the cloneform class
		$(clonedForm).removeClass('hcc-serve-formClone');

		//inser the form right after the opportunity container
		$(this).parents('.servingopp-opportunity').after(clonedForm);

		//get the height of the form
		var formHeight = $(clonedForm).height();

		//set the height of the parent (which will animate through CSS)
		//get the padding of the container
		var containerPadding = $(this).parents('.servingopp-container').css('padding-bottom').replace('px','');
		var totalPadding = formHeight + Number(containerPadding);
		$(this).parents('.servingopp-container').css('height', totalPadding + 'px');

		//hide the opp
		//add CSS class to slide the opportunity off
		$(this).parents('.servingopp-opportunity').addClass('slideoff');

		//show the form
		$(clonedForm).addClass('slideon');

		//wire up form "close" button
		$(clonedForm).find('.servingopp-form-closebtn').click(function(e) {

			//measure height of the form element
			var oppHeight = jQuery(this).parents('.servingopp-container').find('.servingopp-opportunity').css('height').replace('px','');
			var paddingBottom = jQuery(this).parents('.servingopp-container').css('padding-bottom').replace('px','');
			//these are strings, so need to cast them to numbers
			var restoredHeight = Number(oppHeight) + Number(paddingBottom);

			//set the height of the parent back to just the container (which will animate through CSS)
			jQuery(this).parents('.servingopp-container').css('height', restoredHeight + 'px');
			//remove the class on the form so it will slide off
			jQuery(this).parents('.servingopp-form-container').removeClass('slideon');

			//remove slideoff class on the container so it will slide on
			jQuery(this).parents('.servingopp-form-container').prev().removeClass('slideoff')

			jQuery(this).parents('.servingopp-form-container').on('webkitAnimationEnd oanimationend msAnimationEnd animationend',   
			    function(e) {		 
			    // code to execute after animation ends
			    this.remove();
			});
		});

		//wire up form submit
		$(clonedForm).find('.servingopp-form-submitbtn').click(function(e) {

			e.preventDefault();

			var formObj = jQuery(this).parents('form');

			jQuery(formObj).find('input,select').each(function() {
				
				if (jQuery(this).val() == '') {
					jQuery(this).addClass('error');	
				} else {
					jQuery(this).removeClass('error');	
				}			
			});	

			if (jQuery(formObj).find('input.error').length == 0) {

				var submitBtn = jQuery(formObj).find('a.servingopp-form-submitbtn .btext');

				if (jQuery(submitBtn).text != 'Sending...') {

					jQuery(formObj).find('a.servingopp-form-submitbtn .btext').text('Sending...');
					var formData = jQuery(formObj).serialize() + '&action=hcc_process_serving_opp_form';

					//ajax here to send the response
					jQuery.ajax({
						//ajaxVars is an object written out from localize_script Wordpress function
						url: ajaxVars.ajaxUrl,
						method: 'POST',
						data: formData,
						success: processFormSuccess,
						error: processFormError, 
						context: formObj,
						type:"JSON"
					});
				}
			}
		});							



		/*
		var formData = {
			oppID: $(this).parents('.servingopp-opportunity').attr('data-rel'),
			action: 'hcc_process_get_serving_opp_form'
		};

		$.ajax({
			//ajaxVars is an object written out from localize_script Wordpress function
			url: ajaxVars.ajaxUrl,
			method: 'POST',
			data: formData,
			success: getFormSuccess,
			error: getFormError, 
			type:"JSON",
			//send the context as the parent container
			context:$(this).parents('.servingopp-opportunity')
		});	*/
	});

});

function getFormSuccess(data,textStatus,obj) {
 	//insert the form into the DOM immediately after the container
 	this.after(data);

	//copy it into another container and measure the height
	if (jQuery('.ghostForm').length == 0) {
		var formClone = this.next('.servingopp-form-container').clone().addClass('hcc-serve-formClone');
		jQuery('body').append(formClone);			
		var formHeight = jQuery('.hcc-serve-formClone').height();
		jQuery('.ghostForm').remove();			
	}

	 
	//set the height of the parent (which will animate through CSS)
	//get the padding of the container
	var containerPadding = this.parents('.servingopp-container').css('padding-bottom').replace('px','');
	var totalPadding = formHeight + Number(containerPadding);
	this.parents('.servingopp-container').css('height', totalPadding + 'px');

	//hide the opp
	//add CSS class to slide the opportunity off
	this.addClass('slideoff');

	//show the form
	this.next('.servingopp-form-container').addClass('slideon');

	//wire up form "close" button
	this.next('.servingopp-form-container').find('.servingopp-form-closebtn').click(function(e) {

		//measure height of the form element
		var oppHeight = jQuery(this).parents('.servingopp-container').find('.servingopp-opportunity').css('height').replace('px','');
		var paddingBottom = jQuery(this).parents('.servingopp-container').css('padding-bottom').replace('px','');
		//these are strings, so need to cast them to numbers
		var restoredHeight = Number(oppHeight) + Number(paddingBottom);

		//set the height of the parent back to just the container (which will animate through CSS)
		jQuery(this).parents('.servingopp-container').css('height', restoredHeight + 'px');
		//remove the class on the form so it will slide off
		jQuery(this).parents('.servingopp-form-container').removeClass('slideon');

		//remove slideoff class on the container so it will slide on
		jQuery(this).parents('.servingopp-form-container').prev().removeClass('slideoff')

		jQuery(this).parents('.servingopp-form-container').on('webkitAnimationEnd oanimationend msAnimationEnd animationend',   
		    function(e) {		 
		    // code to execute after animation ends
		    this.remove();
		});
	}); 

	//wire up form submit
	this.next('.servingopp-form-container').find('.servingopp-form-submitbtn').click(function(e) {

		e.preventDefault();

		var formObj = jQuery(this).parents('form');

		jQuery(formObj).find('input,select').each(function() {
			
			if (jQuery(this).val() == '') {
				jQuery(this).addClass('error');	
			} else {
				jQuery(this).removeClass('error');	
			}			
		});	

		if (jQuery(formObj).find('input.error').length == 0) {

			var submitBtn = jQuery(formObj).find('a.servingopp-form-submitbtn .btext');

			if (jQuery(submitBtn).text != 'Sending...') {

				jQuery(formObj).find('a.servingopp-form-submitbtn .btext').text('Sending...');
				var formData = jQuery(formObj).serialize() + '&action=hcc_process_serving_opp_form';

				//ajax here to send the response
				jQuery.ajax({
					//ajaxVars is an object written out from localize_script Wordpress function
					url: ajaxVars.ajaxUrl,
					method: 'POST',
					data: formData,
					success: processFormSuccess,
					error: processFormError, 
					context: formObj,
					type:"JSON"
				});
			}
		}
	});	


}

function processFormSuccess(data, textStatus, obj) {
	//add the success message
	jQuery(this).after('<p class="hcc-servingopp-successMessage">Thank you for taking this next step!<br />We will be in touch with you within 3 business days.</p>');
	var parentContainer = jQuery(this).parents('.servingopp-container');
	//get the height of the whole box minus the form
	var containerPadding = jQuery(this).parents('.servingopp-container').css('padding-bottom').replace('px','');
	var msgHeight = jQuery('.hcc-servingopp-successMessage').css('height').replace('px','');
	var oppHeight = Number(msgHeight) + Number(containerPadding);
	//remove the form
	jQuery(this).remove();
	//set height of element to (container + message height)
	jQuery(parentContainer).css('height', oppHeight + 'px');
	setTimeout(function() {
		jQuery(parentContainer).find('a.servingopp-form-closebtn').click();
	}, 2000);
} 

function processFormError(data, textStatus, obj) {
	//reset the text on the button back to submit
	jQuery(this).find('a.servingopp-form-submitbtn .btext').text('Submit');

}

function getFormError() {

}





