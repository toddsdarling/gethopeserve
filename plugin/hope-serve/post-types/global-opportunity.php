<?php

if(!class_exists('GlobalOpportunity'))
{

    class GlobalOpportunity
    {
        const POST_TYPE = "hcc_global_opp";
        
		public function __construct()
		{
		    // register actions
		    add_action('init', array(&$this, 'init'));
		    add_action('admin_init', array(&$this, 'admin_init'));
		    add_action('admin_init', array(&$this, 'add_admin_styles'));
		    add_action('admin_init', array(&$this, 'add_admin_js'));
		    add_action('save_post_hcc_global_opp', array(&$this, 'save_global_opp'));
		    add_action('admin_notices', array( &$this, 'display_validation_notices' ) );
		}


		public function init()
		{
		    // Initialize Serving Opportunity Post Type
			register_post_type('hcc_global_opp',
			    array(
			      'labels' => array(
			        'name' => __( 'Global Serving Opportunity' ),
			        'singular_name' => __( 'Global Serving Opportunity' )
			    ),
			    'description'=>'These are the serving opportunities that Hope is sponsoring/supporting globally.',
			    'public' => false,
			    'has_archive' => false,
			    'show_ui'=>true,
			    'supports'=>array(
			    	'title','editor','custom-fields'
			    )
			    )
			);

			//register the taxnonomies that go along with this post type
			register_taxonomy('hcc_global_category','hcc_global_opp',			
				array(
					'name'=>'Global Categories',
					'description'=>'These are the different categories of global serving opportunities',
					'show_ui'=>true,
					'hierarchical'=>true,
					'label'=>'Global Categories'
				)
			);


		} 
		
		public function admin_init()
		{           
		    // Add metaboxes		    
		    add_action('add_meta_boxes_hcc_global_opp', array(&$this, 'add_meta_boxes'));
		    
		}

		public function add_meta_boxes()
		{
		    
		    add_meta_box(
		    	'hcc_global_serving_opp_category',
		    	'Type of opportunity',
		    	array(&$this, 'add_category_box'),
		    	self::POST_TYPE,
		    	'normal'
		    );

		    add_meta_box(
		    	'hcc_global_serving_opp_campus',
		    	'Campus',
		    	array(&$this, 'add_campus_box'),
		    	self::POST_TYPE,
		    	'normal'
		    );


		}	

		/* add metabox for ministry for this post type */

		function add_category_box() {

			global $post;

			if (taxonomy_exists('hcc_global_category')) {

				$ministry = get_post_meta($post->ID, 'hcc_global_category');
				
				$html_string = '<p>Select the category this opportunity falls in. If this opportunity applies to multiple categories, you will need to create another opp in each category.</p>';

				$html_string .= '<select name="hcc_global_category">'; 
				$html_string .= '<option value="">select...</option>';

				$terms = get_terms('hcc_global_category', array(
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

				$html_string .= wp_nonce_field( basename( __FILE__ ), 'hcc_global_category_nonce');
	
				echo($html_string);

			}
		}

		/**
		 * This function adds the campus custom field based on the availability taxnomy we've set up
		 * Contacts for each opporutnity of who the email should go to is stored in an array with each campus
		 */				
		function add_campus_box() {

			global $post;

			$html_string = '<p>Select the campus that this opportunity applies to. This will determine who gets the contacted when someone wants to get involved with an opportunity. NOTE: If this opportunity has one contact for all campuses, just select any campus and enter contact information and the notifications will get sent there.</p>';
			
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

			$html_string .= wp_nonce_field( basename( __FILE__ ), 'hcc_global_opp_campus_nonce');

			echo($html_string);

		}		

		function add_admin_styles() {
	        wp_enqueue_style('serving-opp-admin-styles', plugin_dir_url(__FILE__) . '/' . '/admin/css/serving-opp-admin-styles.css');
		}

		function add_admin_js() {
			wp_enqueue_script('serving-opp-admin-script', plugin_dir_url(__FILE__) . '/' . '/admin/js/serving-opp-admin.js');
		}

		public function save_global_opp($post_id) {


			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
				return;
			}

			$msg_data = '<div class="error">';

			if ($_POST['hcc_global_category'] == '') {
				$msg_data .= '<p>Please select a category this opportunity applies to</p>';
			} else {
				//update the ministry
				update_post_meta($post_id, 'hcc_global_category', $_POST['hcc_global_category']);				
				wp_set_post_terms($post_id,$_POST['hcc_global_category'],'hcc_global_category',false);
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
						}

						if ($_POST['hcc_serving_opp_contact_name_secondary_' . $termObj->term_id] == '' || $_POST['hcc_serving_opp_contact_email_secondary_' . $termObj->term_id] == '' ) {
							$msg_data .= '<p>You selected this opportunity applies to the ' . $termObj->name . ' campus. Please fill out the secondary contact information for that campus';
						} else {
							$campusArray[$termObj->term_id]['secondary'] = array('campusName'=>$termObj->name,'contactName'=>$_POST['hcc_serving_opp_contact_name_secondary_' . $termObj->term_id], 'contactEmail'=>$_POST['hcc_serving_opp_contact_email_secondary_' . $termObj->term_id]);							
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



			//loop over campuses checking checkboxes for each in
			if ($msg_data != '<div class="error">') {
				//if anything has been added to the msg, that means we have an error to show
				//close the DIV
				$msg_data .= '</div>';
				//set the temporary data for WP
				set_transient('hcc_global_opp_' . $post_id, $msg_data, 80);							
			} 
		}

		public function display_validation_notices() {

			global $post;

			if (get_transient('hcc_global_opp_' . $post->ID)) {	
			    echo get_transient('hcc_global_opp_' . $post->ID);
			    delete_transient('hcc_global_opp_' . $post->ID);				
			}
		}
    } 
} 





?>