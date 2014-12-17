<?php

require($_SERVER['DOCUMENT_ROOT'] . '/coreLib/mandrill-lib/Mandrill.php');

defined('ABSPATH') or die("No script kiddies please!");

/**
 * Plugin Name: Hope Community Church Serving Opportunities
 * Plugin URI: http://www.gethope.net/serve
 * Description: This shows the serving opportunities in the church, community and world for Hope Community Church.
 * Version: 1.0
 * Author: Todd Darling
 * Author URI: http://www.gethope.net
 * License: GPL2
 */

if (!class_exists('HCC_Serve')) {

	class HCC_Serve {

		public function __construct()
		{
		    // register actions

			require_once(sprintf("%s/post-types/serving-opportunity.php", dirname(__FILE__)));
			require_once(sprintf("%s/post-types/community-opportunity.php", dirname(__FILE__)));
			require_once(sprintf("%s/post-types/global-opportunity.php", dirname(__FILE__)));
			$PostTypeTemplate = new ServingOpportunity();
			$CommunityPostTypeTemplate = new CommunityOpportunity();
			$GlobalPostTypeTemplate = new GlobalOpportunity();

			//add actions
		    //register action from form on front-end to process the form when user wants to try an opportunity		    
		    add_action('wp_ajax_nopriv_hcc_process_serving_opp_form', array(&$this,'processOppForm'));
		    add_action('wp_ajax_hcc_process_serving_opp_form', array(&$this,'processOppForm'));

		} 

		/**
		 * Activate the plugin
		 */
		public static function activate()
		{

			//set up the database tables in Wordpress to store serve entries
			global $wpdb;

			$table_name = $wpdb->prefix . 'hcc_serve_tracking';

			/*
			 * We'll set the default character set and collation for this table.
			 * If we don't do this, some characters could end up being converted 
			 * to just ?'s when saved in our table.
			 */
			$charset_collate = '';

			if ( ! empty( $wpdb->charset ) ) {
			  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			}

			if ( ! empty( $wpdb->collate ) ) {
			  $charset_collate .= " COLLATE {$wpdb->collate}";
			}

			//check if the tracking table is already there.  If not, don't create another one
			$does_tracking_exist = $wpdb->query('SHOW TABLES LIKE "' . $table_name . '"');

			if ($does_tracking_exist == 0) {
				$sql = "CREATE TABLE $table_name (
				  id mediumint(9) NOT NULL AUTO_INCREMENT,
				  dat_timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				  txt_fname varchar(255),
				  txt_lname varchar(255),
				  txt_email varchar(255),
				  txt_campus varchar(255),
				  int_job_id mediumint(9) DEFAULT '0' NOT NULL,
				  txt_job_name varchar(255) DEFAULT '' NOT NULL,
				  UNIQUE KEY id (id)
				) $charset_collate;";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
			}
		} 

		/**
		 * Deactivate the plugin
		 */     
		public static function deactivate()
		{
		    // Do nothing
		}

		/**
		* This function handles displaying the validation notices if the piece of transient data is set
		* The transient data holds the messages that should display
		*/
		public function processOppForm() {
			global $wpdb;
			
			//verify the nonce fields
			if (!isset($_POST['hcc_serving_opp_nonce']) || !wp_verify_nonce($_POST['hcc_serving_opp_nonce'],'hcc_process_opp')) {		
				return false;
			} 
				//process form here, we passed verification

				//get the post info
				$job_info = get_post($_POST['hcc_serveopp_id']);

				//send email through Mandrill
				$mandrillObj = new Mandrill('XXXX');
				//set up message params
				$mandrillMessage = new Mandrill_Messages($mandrillObj);

				$ministryTemplateContent = array();

				/**** EMAIL TO MINISTRY HERE ****/
				$templatePiece = new stdClass();
				$templatePiece->name = "introContent";
				$templatePiece->content = $_POST['fname'] . ' ' . $_POST['lname'] . ' has searched the finder and indicated they would like to try your opportunity.  Their information is below. Please follow up with them within 3 business days.';

				array_push($ministryTemplateContent,$templatePiece);

				//fill in the template pieces
				$templatePiece = new stdClass();
				$templatePiece->name = "oppName";
				$templatePiece->content = $job_info->post_title;

				array_push($ministryTemplateContent,$templatePiece);

				$templatePiece = new stdClass();
				$templatePiece->name = "userName";
				$templatePiece->content = $_POST['fname'] . ' ' . $_POST['lname'];

				array_push($ministryTemplateContent,$templatePiece);				

				$templatePiece = new stdClass();
				$templatePiece->name = "userPhone";
				$templatePiece->content = $_POST['phone1'] . '-' . $_POST['phone2'] . '-' . $_POST['phone3'];

				array_push($ministryTemplateContent,$templatePiece);

				$templatePiece = new stdClass();
				$templatePiece->name = "userEmail";
				$templatePiece->content = $_POST['email'];				

				array_push($ministryTemplateContent,$templatePiece);

				$templatePiece = new stdClass();
				$templatePiece->name = "userCampus";

				//campus is sent by ID, so need to get the term out of the taxonomy here to get the name of the campus
				$campusTermObj = get_term($_POST['campus'],'hcc_campus');
				$campusName = $campusTermObj->name;
				$templatePiece->content = $campusName;

				array_push($ministryTemplateContent,$templatePiece);

				//build the ministry confirmation email and send
				$ministryMessage = new stdClass();
				$ministryMessage->subject = 'Someone wants to try serving as a ' . $job_info->post_title;
				$ministryMessage->from_email = 'serveteam@gethope.net';
				$ministryMessage->from_name = 'Hope Community Church';
				$ministryMessage->important = false;
				$ministryMessage->track_opens = true;
				$ministryMessage->track_clicks = true;
				$ministryMessage->async = false;

				//need to find which contact this email should go to, based on campus
				$campusContacts = get_post_meta($job_info->ID, 'hcc_serving_opp_contacts');

				//set defaults
				$contactNamePrimary = '';
				$contactEmailPrimary = '';
				$contactNameSecondary = '';
				$contactEmailSecondary = '';

				foreach ($campusContacts[0] as $key=>$value) {
					//the contacts are an array keyed by campus ID, so match it to the campus ID
					//set in the POST arr
					if ($key == $_POST['campus']) {

						$contactNamePrimary = $campusContacts[0][$key]['primary']['contactName'];
						$contactEmailPrimary = $campusContacts[0][$key]['primary']['contactEmail'];	
						$contactNameSecondary= $campusContacts[0][$key]['secondary']['contactName'];
						$contactEmailSecondary = $campusContacts[0][$key]['secondary']['contactEmail'];											
					}
				}

				if ($contactNamePrimary == '' || $contactEmailPrimary == '') {
					//if no match found, grab the first contact (there will always be one as per validation)
					//just so it gets sent somewhere
					$firstContact = reset($campusContacts[0]);

					$contactNamePrimary = $firstContact['primary']['contactName'];
					$contactEmailPrimary = $firstContact['primary']['contactEmail'];	
					$contactNameSecondary= $firstContact['secondary']['contactName'];
					$contactEmailSecondary = $firstContact['secondary']['contactEmail'];
				}

				$ministryRecipients = array();

				//set the values in the recipient array
				$recipient = new stdClass();
				//all emails go to Todd for now
				//$recipient->email = 'todd@gethope.net';
				//$recipient->name = 'Todd Darling';
				$recipient->email = $contactEmailPrimary;
				$recipient->name = $contactNamePrimary;				
				//add it to the receipients
				array_push($ministryRecipients, $recipient);
				//cc the secondary contact
				//bill is test secondary contact
				$recipient = new stdClass();
				//$recipient->email = 'billm@gethope.net';
				//$recipient->name = 'Bill Morrison';
				$recipient->email = $contactEmailSecondary;
				$recipient->name = $contactNameSecondary;
				array_push($ministryRecipients, $recipient);

				$ministryMessage->to = $ministryRecipients;
				$ministryMessage->bcc_address = 'servehub@gethope.net';

				$emailSent = $mandrillMessage->sendTemplate('serve-finder-email-to-ministry', $ministryTemplateContent, $ministryMessage);	

				//build email to user
				$usrMsg = new stdClass();
				$usrMsg->subject = 'Your serving confirmation';
				$usrMsg->from_email = 'serveteam@gethope.net';
				$usrMsg->from_name = 'Hope Community Church';
				$usrMsg->important = false;
				$usrMsg->track_opens = true;
				$usrMsg->track_clicks = true;			
				$usrMsg->async = false;
				
				$recipient = new stdClass();
				$recipient->email = $_POST['email'];
				$recipient->name = $_POST['fname'] . ' ' . $_POST['lname'];
				//set the recipients
				$usrMsg->to = array($recipient);

				//fill in the template
				$usrTemplateContent = array();

				$templatePiece = new stdClass();
				$templatePiece->name = "oppName";
				$templatePiece->content = $job_info->post_title;

				array_push($usrTemplateContent,$templatePiece);

				//send the email
				$usrEmailSent = $mandrillMessage->sendTemplate('serve-finder-email-to-user', $usrTemplateContent, $usrMsg);


				//track into database
				$table_name = $wpdb->prefix . 'hcc_serve_tracking';

				$tracked = $wpdb->insert( 
					$table_name, 
					array( 
						'dat_timestamp' => date('Y-m-d H:i:s'), 
						'txt_fname' => $_POST['fname'], 
						'txt_lname' => $_POST['lname'],
						'txt_email' => $_POST['email'],
						'txt_campus' => $campusName,
						'int_job_id' => $job_info->ID,
						'txt_job_name' => $job_info->post_title
					) 
				);

		}



		
	}

    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('HCC_Serve', 'activate'));
    register_deactivation_hook(__FILE__, array('HCC_Serve', 'deactivate'));

    // instantiate the plugin class
    $hcc_serve = new HCC_Serve();

}

?>