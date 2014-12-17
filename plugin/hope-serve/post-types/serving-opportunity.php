<?php

if(!class_exists('ServingOpportunity'))
{

    class ServingOpportunity
    {
        const POST_TYPE = "hcc_serving_opp";
        
		public function __construct()
		{
		    // register actions
		    add_action('init', array(&$this, 'init'));
		    add_action('admin_init', array(&$this, 'admin_init'));
		    add_action('admin_init', array(&$this, 'add_admin_styles'));
		    add_action('admin_init', array(&$this, 'add_admin_js'));
		    add_action('save_post_hcc_serving_opp', array(&$this, 'save_serving_opp'));
		    add_action('admin_notices', array( &$this, 'display_validation_notices' ));
		}


		public function init()
		{

		    // Initialize Serving Opportunity Post Type
			$hcc_serve_opp = register_post_type('hcc_serving_opp',
			    array(
			      'labels' => array(
			        'name' => __( 'Serving Opportunity' ),
			        'singular_name' => __( 'Serving Opportunity' )
			    ),
			    'description'=>'These are the serving opportunities available at Hope',
			    'public' => true,
			    'has_archive' => false,
			    'show_in_nav_menus'=>false,
			    'show_ui'=>true,
			    'supports'=>array(
			    	'title','editor','custom-fields'
			    )
			    )
			);

			//register the taxnonomies that go along with this post type
			register_taxonomy('hcc_serving_opp_availability','hcc_serving_opp',			
				array(
					'name'=>'Serving Opportunity Availability',
					'public'=>true,
					'description'=>'These are the different availabilities for serving opportunities',
					'show_ui'=>true,
					'hierarchical'=>false,
					'show_in_nav_menus'=>false,
					'label'=>'Availability'
				)
			);

			register_taxonomy('hcc_serving_opp_ministry','hcc_serving_opp',
					array(
						'name'=>'Hope Community Church Ministry',
						'public'=>true,
						'description'=>'These are the ministries for serving opportunities at Hope Community Church',
						'show_ui'=>true,
						'show_in_nav_menus'=>false,
						'hierarchical'=>true,
						'label'=>'Ministries'					
					)
			);


		} 

		/**
		 * This is run when the WP admin is initialized.  Basically just sets up the meta boxes (custom fields)
		 * for the post type
		 */		
		
		public function admin_init()
		{           
		    // Add metaboxes		    
		    add_action('add_meta_boxes_hcc_serving_opp', array(&$this, 'add_meta_boxes'));
		}

		/**
		 * Add meta boxes into the custom post type
		 */	

		public function add_meta_boxes()
		{

		    add_meta_box(
		    	'hcc_serving_opp_availability',
		    	'Availability',
		    	array(&$this, 'add_availability_box'),
		    	self::POST_TYPE,
		    	'normal'
		    );
		    
		    add_meta_box(
		    	'hcc_serving_opp_ministry',
		    	'Ministry',
		    	array(&$this, 'add_ministry_box'),
		    	self::POST_TYPE,
		    	'normal'
		    );		    

		    add_meta_box(
		    	'hcc_serving_opp_campus',
		    	'Campus',
		    	array(&$this, 'add_campus_box'),
		    	self::POST_TYPE,
		    	'normal'
		    );

		}

		/**
		 * This function adds the availability custom field based on the availability taxnomy we've set up
		 * It just echos out an html string to the admin screen
		 */	
		function add_availability_box() {

			global $post;

			$html_string = '<p>Select if this is a midweek or weekend serving opportunity. NOTE: Select the one that this falls MOSTLY under.</p>';
			
			if (taxonomy_exists('hcc_serving_opp_availability')) {

				$availability = get_post_meta($post->ID, 'hcc_serving_opp_availability');

				$terms = get_terms('hcc_serving_opp_availability', array(
					'hide_empty'=>false

				));

				$select = '<select name="hcc_serving_opp_availability">';
				$select .= '<option value="">select...</option>';

				foreach ($terms as $termObj) {
					//check which term comes out of the database and set that option be selected
					if ((count($availability) > 0) && ($availability[0]['availabilityID'] == $termObj->term_id)) {
						$select .= '<option value="'. $termObj->term_id . '" selected>' . $termObj->name .'</option>';
					} else {
						$select .= '<option value="'. $termObj->term_id . '">' . $termObj->name .'</option>';
					}
				}

				$select .= '</select>';

				$html_string .= $select;

				$html_string .= wp_nonce_field( basename( __FILE__ ), 'hcc_serving_opp_availability_nonce');

			}

			echo($html_string);

		}

		/**
		 * This function adds the campus custom field based on the availability taxnomy we've set up
		 * Contacts for each opporutnity of who the email should go to is stored in an array with each campus
		 */				
		function add_campus_box() {

			global $post;

			$html_string = '<p>Select the campus that this opportunity applies to.</p>';
			
			if (taxonomy_exists('hcc_campus')) {

				$contactsMeta = get_post_meta($post->ID, 'hcc_serving_opp_contacts');

				//var_dump($contactsMeta);

				//start new array to unnest the contact info
				$contactsArray = array();

				$terms = get_terms('hcc_campus', array(
					'hide_empty'=>false
				));

				foreach ($terms as $termObj) {
					$html_string .= '<p>';

					//set defaults for all the values
					$checked = '';
					$contact_name_primary = '';
					$contact_email_primary = '';
					$contact_name_secondary = '';
					$contact_email_secondary = '';

					//if there's an array here, there are contacts entered
					if (is_array($contactsMeta[0])) {

						foreach ($contactsMeta[0] as $key=>$value) {

							if ($key == $termObj->term_id) {

								//this key is in the array, there are contacts entered for this campus
								$checked = 'checked';

								$contact_name_primary = $value['primary']['contactName'];
								$contact_email_primary = $value['primary']['contactEmail'];
								$contact_name_secondary = $value['secondary']['contactName'];
								$contact_email_secondary = $value['secondary']['contactEmail'];								
							} 
						}
					} 

					$html_string .= '<label for="' . $termObj->term_id . '"><input class="hcc_serving_opp_campus" id="' . $termObj->term_id . '" type="checkbox" name="hcc_serving_opp_campus_' . $termObj->term_id . '" ' . $checked . '><strong>' . $termObj->name . '</strong></label>' . 
					'<br />
					<div id="hcc_serving_opp_campus_contact_primary_' . $termObj->term_id . '" class="hcc_serving_opp_contactinfo">
						Primary Contact name: <input type="text" name="hcc_serving_opp_contact_name_primary_' . $termObj->term_id . '" value="' . $contact_name_primary . '">&nbsp;&nbsp;Email: <input type="text" name="hcc_serving_opp_contact_email_primary_' . $termObj->term_id . '" value="' . $contact_email_primary .'">
					</div>
					<div id="hcc_serving_opp_campus_contact_secondary_' . $termObj->term_id . '" class="hcc_serving_opp_contactinfo">
						Secondary Contact name: <input type="text" name="hcc_serving_opp_contact_name_secondary_' . $termObj->term_id . '" value="' . $contact_name_secondary . '">&nbsp;&nbsp;Email: <input type="text" name="hcc_serving_opp_contact_email_secondary_' . $termObj->term_id . '" value="' . $contact_email_secondary .'">
					</div>

					</p>';
				}

			}

			$html_string .= wp_nonce_field( basename( __FILE__ ), 'hcc_serving_opp_campus_nonce');

			echo($html_string);

		}

		/**
		 * This function adds the ministry custom field based on the ministry taxnomy we've set up
		 * that allows you to select the ministry that this opportunity goes to		 
		 */			
		function add_ministry_box() {

			global $post;

			if (taxonomy_exists('hcc_serving_opp_ministry')) {

				$ministry = get_post_meta($post->ID, 'hcc_serving_opp_ministry');
				
				$html_string = '<p>Select the ministries that this serving opportunity applies to. If this opportunity applies to multiple ministries, you will need to create another one.</p>';

				$html_string .= '<select name="hcc_serving_opp_ministry">'; 
				$html_string .= '<option value="">select...</option>';

				$terms = get_terms('hcc_serving_opp_ministry', array(
					'hide_empty'=>false
				));

				foreach ($terms as $termObj) {

					if ((count($ministry[0]) > 0) && ($ministry[0] == $termObj->term_id)) {
						$html_string .= '<option value="'. $termObj->term_id . '" selected>' . $termObj->name .'</option>';
					} else {
						$html_string .= '<option value="'. $termObj->term_id . '">' . $termObj->name .'</option>';						
					}
				}

				$html_string .= '</select>';

				$html_string .= wp_nonce_field( basename( __FILE__ ), 'hcc_serving_opp_ministry_nonce');
	
				echo($html_string);

			}
		}	

		function add_admin_styles() {
	        wp_enqueue_style('serving-opp-admin-styles', plugin_dir_url(__FILE__) . '/' . '/admin/css/serving-opp-admin-styles.css');
		}

		function add_admin_js() {
			wp_enqueue_script('serving-opp-admin-script', plugin_dir_url(__FILE__) . '/' . '/admin/js/serving-opp-admin.js');
		}

		/**
		 * This is the single function that is called when the post is saved (not on autosave)
		 * which saves the availability, ministry, campus (with contacts).
		 * Just makes update_post_meta calls back to Wordpress
		 * If validation fails, it sets a transient piece of data so the admin knows to show
		 * the error message
		 */		

		public function save_serving_opp($post_id) {


			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
				return;
			}

			$msg_data = '<div class="error">';

			if ($_POST['hcc_serving_opp_availability'] == '') {	
				$msg_data .= '<p>Please fill out the availability</p>';			
			} else {
				//update the availability (get the name of the term)
				$termObj = get_term($_POST['hcc_serving_opp_availability'],'hcc_serving_opp_availability');
				update_post_meta($post_id, 'hcc_serving_opp_availability', array('availabilityName'=>$termObj->name,'availabilityID'=>$_POST['hcc_serving_opp_availability']));
			}

			if ($_POST['hcc_serving_opp_ministry'] == '') {
				$msg_data .= '<p>Please select a ministry this opportunity applies to</p>';
			} else {
				//update the ministry
				update_post_meta($post_id, 'hcc_serving_opp_ministry', $_POST['hcc_serving_opp_ministry']);
				wp_set_post_terms($post_id,$_POST['hcc_serving_opp_ministry'],'hcc_serving_opp_ministry',false);
			}

			//loop over campuses checking checkboxes for each in
			if (taxonomy_exists('hcc_campus')) {

				$campusTerms = get_terms('hcc_campus', array(
					'hide_empty'=>false
				));

				$campusNotChecked = array();
				$campusArray = array();

				foreach ($campusTerms as $termObj) {

					if ($_POST['hcc_serving_opp_campus_' . $termObj->term_id] == 'on') {

						$campusArray[$termObj->term_id] = array();

						//if you've checked the box for this campus, make sure there is a name and email
						if ($_POST['hcc_serving_opp_contact_name_primary_' . $termObj->term_id] == '' || $_POST['hcc_serving_opp_contact_email_primary_' . $termObj->term_id] == '' ) {
							$msg_data .= '<p>You selected this opportunity applies to the ' . $termObj->name . ' campus. Please fill out the primary contact information for that campus';
						} else {
							$campusArray[$termObj->term_id]['primary'] = array('campusName'=>$termObj->name,'contactName'=>$_POST['hcc_serving_opp_contact_name_primary_' . $termObj->term_id], 'contactEmail'=>$_POST['hcc_serving_opp_contact_email_primary_' . $termObj->term_id]);
							//array_push($campusArray[$termObj->term_id], array('type'=>'primary','campusName'=>$termObj->name,'contactName'=>$_POST['hcc_serving_opp_contact_name_primary_' . $termObj->term_id], 'contactEmail'=>$_POST['hcc_serving_opp_contact_email_primary_' . $termObj->term_id]));
						}

						if ($_POST['hcc_serving_opp_contact_name_secondary_' . $termObj->term_id] == '' || $_POST['hcc_serving_opp_contact_email_secondary_' . $termObj->term_id] == '' ) {
							$msg_data .= '<p>You selected this opportunity applies to the ' . $termObj->name . ' campus. Please fill out the secondary contact information for that campus';
						} else {
							$campusArray[$termObj->term_id]['secondary'] = array('campusName'=>$termObj->name,'contactName'=>$_POST['hcc_serving_opp_contact_name_secondary_' . $termObj->term_id], 'contactEmail'=>$_POST['hcc_serving_opp_contact_email_secondary_' . $termObj->term_id]);
							//array_push($campusArray[$termObj->term_id], array('type'=>'secondary','campusName'=>$termObj->name,'contactName'=>$_POST['hcc_serving_opp_contact_name_secondary_' . $termObj->term_id], 'contactEmail'=>$_POST['hcc_serving_opp_contact_email_secondary_' . $termObj->term_id]));
						}
					} else {
						array_push($campusNotChecked, $termObj->term_id);
					}
				}

				if (count($campusNotChecked) == count($campusTerms)) {
					//the campusNotChecked array has something in in it. Which means you selected NO campuses...add to error message
					//since you need to select at least one campus
					$msg_data .= 'You must select at least one campus this opportunity applies to';
				} else {
					//they selected at least one campus, update the post meta
					update_post_meta($post_id, 'hcc_serving_opp_contacts', $campusArray);
				}


			}

			if ($msg_data != '<div class="error">') {
				//if anything has been added to the msg, that means we have an error to show
				//close the DIV
				$msg_data .= '</div>';
				//set the temporary data for WP
				set_transient('hcc_serving_opp_' . $post_id, $msg_data, 80);							
			} 
		}

		/**
		* This function handles displaying the validation notices if the piece of transient data is set
		* The transient data holds the messages that should display
		*/
		public function display_validation_notices() {

			global $post;

			if (get_transient('hcc_serving_opp_' . $post->ID)) {	
			    echo get_transient('hcc_serving_opp_' . $post->ID);
			    delete_transient('hcc_serving_opp_' . $post->ID);				
			}
		}

		public function get_opp_form() {

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


			echo('<div class="hcc-serve-list servingopp-form-container">
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
					</div> ' . $campus_html . ' 
					<input type="hidden" name="hcc_serveopp_id" value="' . $_POST['oppID'] . '">' . 
					$nonce_fields . '
					<p>' . do_shortcode('[button id="" style="filled" class="hcc-serve-list servingopp-form-submitbtn" align="" link="" linkTarget="_self" bgColor="accent1" hover_color="accent1" font="15" icon="" icon_placement="left" icon_color=""]Submit[/button]') . '</p>
				</form>
				</div>');

				die();
			}

    } 
} 





?>