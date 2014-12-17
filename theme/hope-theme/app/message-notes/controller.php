<?php
	//include lib to send email through Mandrill
	require($_SERVER['DOCUMENT_ROOT'] . '/coreLib/mandrill-lib/Mandrill.php');

	//build email that goes to the user
	//instantiate Mandrill obj
	$mandrillObj = new Mandrill('XYmUvPTCRIgZ1obCZoYx5w');
	
	//set up message params
	$mandrillMessage = new Mandrill_Messages($mandrillObj);		
	
	//build the Mandrill message to be sent to the Student Ministries team
	$message = new stdClass();
	$message->subject = 'Message notes from ' . date('l, F j');
	$message->from_email = 'noreply@gethope.net';
	$message->from_name = 'Hope Community Church';
	$message->important = false;
	$message->track_opens = true;
	$message->track_clicks = true;			
	$message->async = false;
	$message->html = nl2br(stripslashes($_POST['userNotes'])) . '<p>' . $_POST['staticNotes'] . '</p>';
	
	$recipient = new stdClass();
	$recipient->email = $_POST['userEmail'];
	
	$message->to = array($recipient);	
	
	$emailSent = $mandrillMessage->send($message);	

	echo(json_encode($emailSent));


?>