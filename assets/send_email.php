<?php

// use phpmailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(plugin_dir_path(__FILE__) . '../lib/PHPMailer/src/Exception.php');
require_once(plugin_dir_path(__FILE__) . '../lib/PHPMailer/src/PHPMailer.php');
require_once(plugin_dir_path(__FILE__) . '../lib/PHPMailer/src/SMTP.php');

require_once(plugin_dir_path(__FILE__) . '../config/config.php');
require_once(plugin_dir_path(__FILE__) . 'language.php');

function send_booking_email() {
    // Ensure default language is nl_NL if not provided
    $lang = isset($_POST['lang']) ? $_POST['lang'] : 'nl_NL';
    loadLang($lang);

    // Get the form data and validate
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $size = filter_input(INPUT_POST, 'size', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $aantal = filter_input(INPUT_POST, 'bezoekers', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $info = filter_input(INPUT_POST, 'info', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $slot = filter_input(INPUT_POST, 'slot', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

    // Validate required fields
    if (!$name || !$email || !$telephone || !$city || !$size || !$aantal || !$date || !$slot) {
        echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
        exit();
    }

    // Ensure $to is set correctly
    global $to, $toname, $smtp_server, $smtp_username, $smtp_password, $smtp_port;
    if (empty($to)) {
        echo json_encode(["status" => "error", "message" => "Invalid address: (to): $to"]);
        exit();
    }

    $fmtDate = new IntlDateFormatter($lang == 'en_EN' ? 'en_GB' : 'nl_NL', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    $fmtTime = new IntlDateFormatter($lang == 'en_EN' ? 'en_GB' : 'nl_NL', IntlDateFormatter::NONE, IntlDateFormatter::SHORT);
    $subject = Language::translate("email-subject");
    // variable email data 1
    $headercontent = Language::translate("new-booking-request");
    $footercontent = "Bookme - Uit-liefde";
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { background-color: #f9f9f9; padding: 20px; border-radius: 10px; }
            .header { background-color: #c40079; padding: 10px; border-radius: 10px 10px 0 0; }
            .header h1 { margin: 0; color: #ffffff; }
            .content { padding: 20px; }
            .content p { margin: 10px 0; }
            .footer { background-color: #c40079; padding: 10px; border-radius: 0 0 10px 10px; text-align: center; color: #ffffff; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>$headercontent</h1>
            </div>
            <div class='content'>
                <p><strong>" . Language::translate("date") . ":</strong> " . $fmtDate->format(new DateTime($slot['start'])) . "</p>
                <p><strong>" . Language::translate("time-slot") . ":</strong> " . $fmtTime->format(new DateTime($slot['start'])) . " - " . $fmtTime->format(new DateTime($slot['end'])) . " </p>
                <p><strong>" . Language::translate("name") . ":</strong> $name</p>
                <p><strong>" . Language::translate("email") . ":</strong> $email</p>
                <p><strong>" . Language::translate("telephone") . ":</strong> $telephone</p>
                <p><strong>" . Language::translate("city") . ":</strong> $city</p>
                <p><strong>" . Language::translate("size") . ":</strong> $size</p>
                <p><strong>" . Language::translate("visitors") . ":</strong> $aantal</p>
                <p><strong>" . Language::translate("additional-info") . ":</strong> $info</p>
            </div>
            <div class='footer'>
                <p>$footercontent</p>
            </div>
        </div>
    </body>
    </html>";
    $altbody = Language::translate("name") . ": $name\n" . Language::translate("email") . ": $email\n" . Language::translate("telephone") . ": $telephone\n" . Language::translate("city") . ": $city\n" . Language::translate("size") . ": $size\n" . Language::translate("visitors") . ": $aantal\n" . Language::translate("additional-info") . ": $info\n" . Language::translate("date") . ": $date\n" . Language::translate("time-slot") . ": " . $fmtDate->format(new DateTime($slot['start'])) . " (" . $fmtTime->format(new DateTime($slot['start'])) . " - " . $fmtTime->format(new DateTime($slot['end'])) . ")";

    // guest = sender
    $send_from_address= $email; 
    $send_from_name = $name;

    // booking manager = receiver
    $send_to_address = $to;
    $send_to_name = $toname;

    $mail = new PHPMailer(true);

    // Server settings
    $mail->SMTPDebug = 2;                      // Enable verbose debug output
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = $smtp_server;                    // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = $smtp_username;                     // SMTP username
    $mail->Password   = $smtp_password;                               // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  //'tls';       // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port       = $smtp_port;         // 587;                           // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
    // Recipients
    $mail->setFrom($send_from_address, $send_from_name);
    $mail->addAddress($send_to_address, $send_to_name);     // Add a recipient
    //$mail->addAddress('ellen@example.com');               // Name is optional
    //$mail->addReplyTo('support@webdesigndenhaag.net', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    // Content
    $mail->isHTML(true);            // Set email format to HTML
    $mail->Subject = $subject;      //'Here is the subject';
    $mail->Body    = $body;         //'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = $altbody; //'This is the body in plain text for non-HTML mail clients';

    // Send the email
    if(!$mail->send()){
        echo json_encode(["status" => "error", "message" => "E-mail verzenden mislukt..."]); // $mail->ErrorInfo;
        exit();
    } 
    // variable email data 2
    $headercontent = Language::translate("booking-in-progress");
    $footercontent = "Bookme - Uit-liefde";
    $from = $to; // see config
    $fromname = $toname; // see config
    // $subject = "Nieuwe afspraak aanvraag"; // see config
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { background-color: #f9f9f9; padding: 20px; border-radius: 10px; }
            .header { background-color: #c40079; padding: 10px; border-radius: 10px 10px 0 0; }
            .header h1 { margin: 0; color: #ffffff; }
            .content { padding: 20px; }
            .content p { margin: 10px 0; }
            .footer { background-color: #c40079; padding: 10px; border-radius: 0 0 10px 10px; text-align: center; color: #ffffff; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>$headercontent</h1>
            </div>
            <div class='content'>
                <p>" . Language::translate("greeting") . " $name,</p>
                <p>" . Language::translate("booking-details") . "</p>
                <p><strong>" . Language::translate("date") . ":</strong> " . $fmtDate->format(new DateTime($slot['start'])) . "</p>
                <p><strong>" . Language::translate("time-slot") . ":</strong> " . $fmtTime->format(new DateTime($slot['start'])) . " - " . $fmtTime->format(new DateTime($slot['end'])) . " </p>
                <p><strong>" . Language::translate("name") . ":</strong> $name</p>
                <p><strong>" . Language::translate("email") . ":</strong> $email</p>
                <p><strong>" . Language::translate("telephone") . ":</strong> $telephone</p>
                <p><strong>" . Language::translate("city") . ":</strong> $city</p>
                <p><strong>" . Language::translate("size") . ":</strong> $size</p>
                <p><strong>" . Language::translate("visitors") . ":</strong> $aantal</p>
                <p><strong>" . Language::translate("additional-info") . ":</strong> $info</p>
            </div>
            <div class='footer'>
                <p>$footercontent</p>
            </div>
        </div>
    </body>
    </html>";
    $altbody = Language::translate("greeting") . " $name,\n" . Language::translate("booking-details") . "\n" . Language::translate("date") . ": " . $fmtDate->format(new DateTime($slot['start'])) . "\n" . Language::translate("time-slot") . ": " . $fmtTime->format(new DateTime($slot['start'])) . " - " . $fmtTime->format(new DateTime($slot['end'])) . "\n" . Language::translate("name") . ": $name\n" . Language::translate("email") . ": $email\n" . Language::translate("telephone") . ": $telephone\n" . Language::translate("city") . ": $city\n" . Language::translate("size") . ": $size\n" . Language::translate("visitors") . ": $aantal\n" . Language::translate("additional-info") . ": $info\n";
    
    // guest = receiver
    $send_from_address= $from; 
    $send_from_name = $fromname;
    // booking manager = sender
    $send_to_address = $email;
    $send_to_name = $name;
    
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->SMTPDebug = 2;                      // Enable verbose debug output
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = $smtp_server;                    // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = $smtp_username;                     // SMTP username
    $mail->Password   = $smtp_password;                               // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  //'tls';       // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port       = $smtp_port;         // 587;                           // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
    // Recipients
    $mail->setFrom($send_from_address, $send_from_name);
    $mail->addAddress($send_to_address, $send_to_name);     // Add a recipient
    //$mail->addAddress('ellen@example.com');               // Name is optional
    //$mail->addReplyTo('support@webdesigndenhaag.net', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    // Content
    $mail->isHTML(true);            // Set email format to HTML
    $mail->Subject = $subject;      //'Here is the subject';
    $mail->Body    = $body;         //'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = $altbody; //'This is the body in plain text for non-HTML mail clients';

    // Send the email
    if(!$mail->send()){
        echo json_encode(["status" => "error", "message" => "E-mail verzenden mislukt..."]); // $mail->ErrorInfo;
    } else {
        echo json_encode(["status" => "success", "message" => "E-mails succesvol verzonden naar $to en $email..."]);
    }
}

add_action('wp_ajax_send_booking_email', 'send_booking_email');
add_action('wp_ajax_nopriv_send_booking_email', 'send_booking_email');
?>
