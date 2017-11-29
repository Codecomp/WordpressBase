<?php/********************************************************************* * Setup outbound emails *********************************************************************//** * Add contact form ACF options tabs * * @param $tabs * @return mixed */function theme_options_tabs_email( $tabs ) {    $tabs['Contact Emails'] = array(        array (            'name' => 'Contact Email',            'type' => 'text',            'instructions' => 'Email address where contact forms will be sent to.',        ),        array (            'name' => 'Contact Subject',            'type' => 'text',            'instructions' => 'Subject title for contact form email.',            'default' => 'New enquiry received'        )    );    // If WooCommerce is not installed add extra fields for our email styling    if( !is_plugin_activated('woocommerce') ){        $tabs['Contact Emails'][] = array (            'name' => 'Email Banner',            'type' => 'image',            'instructions' => 'Image used for the banner on emails recieved from the site. Image should be 600px wide for best results. Larger images will be auto cropped.',        );        $tabs['Contact Emails'][] = array (            'name' => 'Email Background',            'type' => 'color_picker',            'instructions' => 'Colour used for the background on emails received from the site.',        );    }    return $tabs;}add_filter( 'theme_options_tabs', 'theme_options_tabs_email' );/** * Builds a list of keys and values for all received items in a request * * @return string */function build_email_content_from_request(){    $html = '';    foreach ($_REQUEST as $key => $value) {        if ($key == 'security' || $key == 'action')            continue;        $key = ucfirst($key);        $key = str_replace('-', ' ', $key);        if (is_array($value)) {            $value = implode(', ', $value);        }        $html .= '<p><strong>' . $key . ':</strong> ' . $value .'</p>';    }    return $html;}function send_styled_email($subject, $message, $to, $from = null){    if( $from == null )        $from = 'no-reply@' . clean_site_url();    if( !is_plugin_activated('woocommerce') ){ // Send message via basic HTML        $headers  = "From: " . $from . "\r\n";        $headers .= "Reply-To: ". $from. "\r\n";        $headers .= "MIME-Version: 1.0\r\n";        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";        $body 	  = theme_email_header($subject) . $message . theme_email_footer();        $result   = wp_mail($to, $subject, $body, $headers);    } else { // Send email using WooCommerce Temlate wrap to standardise emails        global $woocommerce;        ob_start();        wc_get_template( 'emails/email-header.php', array( 'email_heading' => $subject ) );        echo $message;        wc_get_template( 'emails/email-footer.php' );        $body     = ob_get_clean();        $mailer   = $woocommerce->mailer();        $result   = $mailer->send( $to, $subject, $body);    }    return $result;}/** * Contact form handler for AJAX submissions */function contact_email(){    // Validate Ajax Request	if (!check_ajax_referer( 'ajax-nonce', 'security', false ))		respond_and_close(false, __('Security Incorrect', 'tmp'));	// Send email	$to 	 = (get_field('contact_email', 'options')) ? get_field('contact_email', 'options') : get_bloginfo('admin_email');    $subject = (get_field('contact_subject', 'options')) ? get_field('contact_subject', 'options') : __('New enquiry received', 'tmp');	$message = '<h1>' . $subject . '</h1>';	$message.= build_email_content_from_request();    $email   = send_styled_email($subject, $message, $to);    // Return JSON success or failure	if( $email )		respond_and_close(true);	else		respond_and_close(false, __('Mail failed to send', 'tmp'));}add_action('wp_ajax_contact_email', 		'contact_email'); // for logged in useradd_action('wp_ajax_nopriv_contact_email', 	'contact_email'); // if user not logged in/** * Returns HTML for email wrap header * * @param $title * @return string */function theme_email_header($title){	$email_header = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">		<html xmlns:v="urn:schemas-microsoft-com:vml">		<head>			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">			<title>' . $title . '</title>		</head>		<body marginheight="0" bgcolor="' . get_field('email_background', 'options') . '" style="margin:0px; padding:0px;">		<table table style="width: 600px; margin: auto;" border="0" cellpadding="20" cellspacing="0" align="center" bgcolor="#FFFFFF">			<tr>				<td width="600">';	$img = get_field('email_banner', 'options');	if ($img) {		$img_src = $img['sizes']['email-banner'];		$img_width = $img['sizes']['email-banner-width'];		$img_height = $img['sizes']['email-banner-height'];		$email_header .= '<img src="' . $img_src . '" width="' . $img_width . '" height="' . $img_height . '" />';	}	return $email_header;}/** * Returns HTML for email wrap footer * * @return string */function theme_email_footer(){	$email_footer = '</td>	</tr>	</table>	</body>	</html>';	return $email_footer;}