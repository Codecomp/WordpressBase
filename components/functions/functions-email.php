<?php/********************************************************************* * Setup outbound emails *********************************************************************//** * Add contact form ACF options tabs * * @param $tabs * @return mixed */function theme_options_tabs_email( $tabs ) {    $tabs['Contact Emails'] = array(        array (            'name' => 'Contact Email',            'type' => 'text',            'instructions' => 'Email address where contact forms will be sent to.',        ),        array (            'name' => 'Email Banner',            'type' => 'image',            'instructions' => 'Image used for the banner on emails recieved from the site. Image should be 600px wide for best results. Larger images will be auto cropped.',        ),        array (            'name' => 'Email Background',            'type' => 'color_picker',            'instructions' => 'Colour used for the background on emails received from the site.',        )    );    return $tabs;}add_filter( 'theme_options_tabs', 'theme_options_tabs_email' );/** * Contact form handler for AJAX submissions */function contact_email(){	if (!check_ajax_referer( 'ajax-nonce', 'security', false ))		respond_and_close(false, __('Security Incorrect', 'tmp'));	//Email administrator	$to 	 = (get_field('contact_email', 'options')) ? get_field('contact_email', 'options') : get_bloginfo('admin_email');	$title 	 = __('Contact Enquiry', 'tmp');	$message = '<h1>' . $title . '</h1>';	foreach ($_REQUEST as $key => $value) {		if ($key == 'security' || $key == 'action')			continue;		$key = ucfirst($key);		$key = str_replace('-', ' ', $key);		if (is_array($value)) {			$value = implode(', ', $value);		}		$message.= '<p><strong>' . $key . ':</strong> ' . $value .'</p>';	}	$body 	  = email_header($title) . $message . email_footer();	$headers  = "From: " . strip_tags($_REQUEST['email-address']) . "\r\n";	$headers .= "Reply-To: ". strip_tags($_REQUEST['email-address']) . "\r\n";	$headers .= "MIME-Version: 1.0\r\n";	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";	//Send the email	$email = wp_mail($to, $title, $body, $headers);	//Depending on the email submission, echo the success or failure json data	if( $email )		respond_and_close(true);	else		respond_and_close(false, __('Mail failed to send', 'tmp'));}add_action('wp_ajax_contact_email', 		'contact_email'); // for logged in useradd_action('wp_ajax_nopriv_contact_email', 	'contact_email'); // if user not logged in/** * Returns HTML for email wrap header * * @param $title * @return string */function email_header($title){	$email_header = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">		<html xmlns:v="urn:schemas-microsoft-com:vml">		<head>			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">			<title>' . $title . '</title>		</head>		<body marginheight="0" bgcolor="' . get_field('email_background', 'options') . '" style="margin:0px; padding:0px;">		<table table style="width: 600px; margin: auto;" border="0" cellpadding="20" cellspacing="0" align="center" bgcolor="#FFFFFF">			<tr>				<td width="600">';	$img = get_field('email_banner', 'options');	if ($img) {		$img_src = $img['sizes']['email-banner'];		$img_width = $img['sizes']['email-banner-width'];		$img_height = $img['sizes']['email-banner-height'];		$email_header .= '<img src="' . $img_src . '" width="' . $img_width . '" height="' . $img_height . '" />';	}	return $email_header;}/** * Returns HTML for email wrap footer * * @return string */function email_footer(){	$email_footer = '</td>	</tr>	</table>	</body>	</html>';	return $email_footer;}