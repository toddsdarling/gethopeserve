<?php
/*
Template Name: Serving Opportunity Listing page
*/
?>

<?php

	global $post;

	//get the slug
	$slug = get_the_permalink($post->ID);

	//check which serve listing you're on
	if (strstr($slug, 'serve/in-the-church')) {
		//you're in church serving opportunities, get that taxonomy
		$taxonomy = 'hcc_serving_opp_ministry';
	} else if (strstr($slug, 'serve/in-the-community')) {
		$taxonomy = 'hcc_community_ministry'; 
	} else if (strstr($slug, 'serve/in-the-world')) {
		$taxonomy = 'hcc_global_category';
	} else {
		$taxonomy = '';
	}

?>




<?php

if(!wpv_is_reduced_response()):
	get_header();
endif;
?>

<?php if(!wpv_is_reduced_response()): ?>
	<div class="row page-wrapper">
<?php endif; // reduced response ?>

<div class="page-content">

	<?php

	if ($taxonomy != '') {

		$terms = get_terms($taxonomy, array(
			'orderby'=>'default',
			'order' => 'ASC',
			'hide_empty' => false
		));

		foreach ($terms as $termObj) {

			//need to do custom post query here, instead of just objects in term
			$objectInTerm = get_objects_in_term($termObj->term_id, $taxonomy);

			if (count($objectInTerm) > 0) {
				/* print out the ministry title */
				print('<div class="sep-text single centered">');
				print('<span class="sep-text-before"><div class="sep-text-line"></div></span>');
				print('<div class="content">');
				print('<h2 class="regular-title-wrapper"><a name="' . $termObj->slug . '"></a>' . $termObj->name . '</h2>');
				print('</div>');
				print('<span class="sep-text-after"><div class="sep-text-line"></div></span>');
				print('</div>');

				//reset the midweek/weekend posts
				$weekendPosts = array();
				$midweekPosts = array();

				/* loop through all the posts and build the weekend/midweek arrays */
				foreach ($objectInTerm as $postID) {
					
					$postObj = get_post($postID);

					if (get_post_status($postID) == 'publish') {
						//get the availability for each opportunity
						$opp_availability = get_post_meta($postObj->ID, 'hcc_serving_opp_availability');

						//build seperate arrays for midweek and weekend serivng opps

						if (count($opp_availability) > 0) {
							if ($opp_availability[0]['availabilityName'] == 'Weekend') {
								array_push($weekendPosts, $postObj);
							} else if ($opp_availability[0]['availabilityName'] == 'Midweek') {
								array_push($midweekPosts, $postObj);
							}
						} else {
							array_push($weekendPosts, $postObj);
						}
					}

				}
			
				if (count($weekendPosts > 0) && count($midweekPosts > 0)) {
					//so merge the two arrays together, weekend posts first
					$servingOpps = array_merge($weekendPosts, $midweekPosts);
				} else if (count($weekendPosts > 0) && count($midweekPosts == 0)) {
					$servingOpps = $weekendPosts;
				} else if (count($midweekPosts > 0) && count($weekendPosts == 0)) {
					$servingOpps = $midweekPosts;
				}

				foreach ($servingOpps as $servingOpp) {

					print('<div class="hcc-serve-list servingopp-container">');

					print('<div class="hcc-serve-list servingopp-opportunity" data-rel="' . $servingOpp->ID . '">');

					echo('<h4><div class="inner"><strong>' . $servingOpp->post_title . '</strong></div></h4>');

					print('<p>' . $servingOpp->post_content . '</p>');

					if ($taxonomy == 'hcc_serving_opp_ministry') {
						//get the contacts, where the campus information is stored (contacts per campus)
						$opp_campus = get_post_meta($servingOpp->ID, 'hcc_serving_opp_contacts');

						if (count($opp_campus) > 0) {						
							$campusNameArr = array();

							foreach($opp_campus[0] as $key=>$value) {
								//get the campus name from the contact list
								$campusTerm = get_term($key, 'hcc_campus');								
								$campusNameArr[] = $campusTerm->name;
								
							}
							//print them out with a comma list
							print('<p class="hcc-serve-list-details campus"><span>Campus:</span> ' . implode(' , ',$campusNameArr) . '</p>');
						}					
					}

					//get the availability for each opportunity
					$opp_availability = get_post_meta($servingOpp->ID, 'hcc_serving_opp_availability');

					if (count($opp_availability) > 0) {						
						print('<p class="hcc-serve-list-details availability"><span>Availability:</span> ' . $opp_availability[0]['availabilityName'] . '</p>');
					}

					//global opportunities say something different on the button
					if ($taxonomy == 'hcc_global_category') {
						echo('<p>' . do_shortcode('[button id="" style="border" class="hcc-serve-button" link="" linkTarget="_self" bgColor="accent1" hover_color="accent1" font="12" icon="arrow-right9" icon_placement="right" icon_color=""]Get Started[/button]') . '</p>');
					} else {
						echo('<p>' . do_shortcode('[button id="" style="border" class="hcc-serve-button" link="" linkTarget="_self" bgColor="accent1" hover_color="accent1" font="12" icon="arrow-right9" icon_placement="right" icon_color=""]I\'ll Try It![/button]') . '</p>');
					}

					
					
					//close div for opportunity details
					print('</div>');

					//close opening DIV for each opp
					print('</div>');

				};

				}
		}

	}
	?>

	<?php 
	//add in a blank form here at the bottom that will be cloned and inserted into the DOM at the opportunity the user clicked on
	//build the campus select list
	if (taxonomy_exists('hcc_campus'))  {

		$campus_html .= '<div class="hcc-serve-list servingopp-form-campuscontainer">';
		$campus_html .= '<label for="campus">What campus do you attend?</label>';
		$campus_html .= '<select name="campus">';
		$campus_html .= '<option value="">select...</option>';

		$terms = get_terms('hcc_campus', array(
			'hide_empty'=>false
		));

		foreach ($terms as $termObj) {
			$campus_html .= '<option value="' . $termObj->term_id . '">' . $termObj->name . '</option>';
		}
		//close opening select and div tag
		$campus_html .= '</select></div>';

	}

	//get the vars for the nonce fields to be printed out in the form
	$nonce_fields = wp_nonce_field('hcc_process_opp','hcc_serving_opp_nonce', true, false);


	echo('<div class="hcc-serve-list servingopp-form-container hcc-serve-formClone">
		' . do_shortcode('[button id="" style="filled" class="hcc-serve-list servingopp-form-closebtn" align="" link="" linkTarget="_self" bgColor="accent3" hover_color="accent1" font="14" icon="" icon_placement="left" icon_color=""]Close[/button]') . '
		<form name="hcc-servingopp-form">
			<p><strong>Please fill out your information</strong><br />All fields are required.</p>
			<div class="hcc-serve-list servingopp-form-namecontainer inline">
				<label for="fname">First Name</label>
				<input type="text" id="fname" size="15" name="fname">
			</div>
			<div class="hcc-serve-list servingopp-form-namecontainer inline">
				<label for="lname">Last Name</label>
				<input type="text" id="lname" size="15" name="lname">
			</div>
			<div class="hcc-serve-list servingopp-form-phonecontainer">
				<label for="lname">Phone Number</label>
				<input type="text" class="inline" id="phone1" size="3" maxlength="3" name="phone1"> - <input type="text" maxlength="3" size="3" id="phone2" name="phone2"> - <input size="4" maxlength="4" type="text" id="phone3" name="phone3">
			</div>
			<div class="hcc-serve-list servingopp-form-emailcontainer">
				<label for="lname">Preferred email</label>
				<input type="text" id="email" size="15" name="email">
			</div> ' . $campus_html . $nonce_fields . '
			<p>' . do_shortcode('[button id="" style="filled" class="hcc-serve-list servingopp-form-submitbtn" align="" link="" linkTarget="_self" bgColor="accent1" hover_color="accent1" font="15" icon="" icon_placement="left" icon_color=""]Submit[/button]') . '</p>
		</form>
		</div>');
	?>

	<?php
		switch($taxonomy) {
			case 'hcc_serving_opp_ministry':
				//for "in the church" opportunities, show the "in the community" and "in the world" buttons at the bottom
				echo do_shortcode('[column width="1/2" title="" title_type="single" animation="none" implicit="true"]<a href="/serve/in-the-community/"><img class="alignnone wp-image-24180 size-full" src="/wp-content/uploads/2014/08/ServeCommunity.jpg" alt="" width="1280" height="720" /></a>[/column]');
				echo do_shortcode('[column width="1/2" last="true" title="" title_type="single" animation="none" implicit="true"]<a href="/serve/in-the-world/"><img class="alignnone wp-image-24178 size-full" src="/wp-content/uploads/2014/08/ServeWorld1.jpg" alt="Serve The World" width="1280" height="720" /></a>[/column]');
			break;

			case 'hcc_community_ministry':
				//for "in the community" opportunities, show the "in the church" and "in the world" buttons at the bottom
				echo do_shortcode('[column width="1/2" title="" title_type="single" animation="none" implicit="true"]<a href="/serve/in-the-church/"><img class="alignnone wp-image-24180 size-full" src="/wp-content/uploads/2014/08/ServeChurch.jpg" alt="" width="1280" height="720" /></a>[/column]');
				echo do_shortcode('[column width="1/2" last="true" title="" title_type="single" animation="none" implicit="true"]<a href="/serve/in-the-world/"><img class="alignnone wp-image-24178 size-full" src="/wp-content/uploads/2014/08/ServeWorld1.jpg" alt="Serve The World" width="1280" height="720" /></a>[/column]');
			break;

			case 'hcc_global_category':
				//for "in the world" opportunities, show the "in the community" and "in the church" buttons at the bottom
				echo do_shortcode('[column width="1/2" title="" title_type="single" animation="none" implicit="true"]<a href="/serve/in-the-church/"><img class="alignnone wp-image-24180 size-full" src="/wp-content/uploads/2014/08/ServeChurch.jpg" alt="" width="1280" height="720" /></a>[/column]');
				echo do_shortcode('[column width="1/2" last="true" title="" title_type="single" animation="none" implicit="true"]<a href="/serve/in-the-community/"><img class="alignnone wp-image-24178 size-full" src="/wp-content/uploads/2014/08/ServeCommunity.jpg" alt="Serve The Community" width="1280" height="720" /></a>[/column]');
			break;
		}


	?>








</div>