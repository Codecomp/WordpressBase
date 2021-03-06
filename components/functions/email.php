<?php

/**
 * Builds a list of keys and values for all received items in a request
 *
 * @return string
 */
function build_email_content_from_request()
{
    $html = '';
    $ignore_keys = array('security', 'action');

    foreach ($_REQUEST as $key => $value) {
        if (in_array($key, $ignore_keys)) {
            continue;
        }

        $key = ucfirst($key);
        $key = str_replace(array('-', '_'), ' ', $key);

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        $html .= '<p><strong>' . $key . ':</strong> ' . $value . '</p>';
    }

    return $html;
}

/**
 * Send an email with the specified subject, message, to and from with either the local theme
 * template or WooCommerce email template.
 *
 * @param $subject
 * @param $message
 * @param $to
 * @param null $from
 * @return mixed
 */
function send_styled_email($subject, $message, $to, $from = null)
{
    if ($from == null) {
        $from = 'no-reply@' . clean_site_url();
    }

    if (!is_plugin_activated('woocommerce')) { // Send message via basic HTML
        $headers = "From: " . $from . "\r\n";
        $headers .= "Reply-To: " . $from . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $body = theme_email_header($subject) . $message . theme_email_footer();
        $result = wp_mail($to, $subject, $body, $headers);
    } else { // Send email using WooCommerce Template wrap to standardise emails
        global $woocommerce;

        ob_start();
        wc_get_template('emails/email-header.php', array('email_heading' => $subject));
        echo $message;
        wc_get_template('emails/email-footer.php');
        $body = ob_get_clean();
        $mailer = $woocommerce->mailer();
        $result = $mailer->send($to, $subject, $body);
    }

    return $result;
}

/**
 * Contact form handler for AJAX submissions
 */
function contact_email()
{
    // Validate Ajax Request
    if (!check_ajax_referer('ajax-nonce', 'security', false)) {
        respond_and_close(false, __('Security Incorrect', 'tmp'));
    }

    // Send email
    $to = (get_field('contact_email', 'options')) ? get_field('contact_email', 'options') : get_bloginfo('admin_email');
    $subject = (get_field('contact_subject', 'options')) ? get_field('contact_subject',
        'options') : __('New enquiry received', 'tmp');
    $message = '<h1>' . $subject . '</h1>';
    $message .= build_email_content_from_request();
    $email = send_styled_email($subject, $message, $to);

    // Return JSON success or failure
    if ($email) {
        respond_and_close(true, array('message' => __('Thank you for your interest', 'tmp')));
    } else {
        respond_and_close(false, array('message' => __('Mail failed to send, please try again later', 'tmp')));
    }
}

add_action('wp_ajax_contact_email', 'contact_email'); // for logged in user
add_action('wp_ajax_nopriv_contact_email', 'contact_email'); // if user not logged in

/**
 * Returns HTML for email wrap header
 *
 * @param $title
 * @return string
 */
function theme_email_header($title)
{
    $email_header = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html xmlns:v="urn:schemas-microsoft-com:vml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<title>' . $title . '</title>
		</head>

		<body marginheight="0" bgcolor="' . get_field('email_background', 'options') . '" style="margin:0px; padding:0px;">
		<table table style="width: 600px; margin: auto;" border="0" cellpadding="20" cellspacing="0" align="center" bgcolor="#FFFFFF">
			<tr>
				<td width="600">';

    $img = get_field('email_banner', 'options');

    if ($img) {
        $img_src = $img['sizes']['email-banner'];
        $img_width = $img['sizes']['email-banner-width'];
        $img_height = $img['sizes']['email-banner-height'];

        $email_header .= '<img src="' . $img_src . '" width="' . $img_width . '" height="' . $img_height . '" />';
    }

    return $email_header;
}

/**
 * Returns HTML for email wrap footer
 *
 * @return string
 */
function theme_email_footer()
{
    $email_footer = '</td>
	</tr>
	</table>
	</body>
	</html>';

    return $email_footer;
}
