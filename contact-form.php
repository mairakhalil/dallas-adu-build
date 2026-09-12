<?php

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'msg' => 'Invalid request method.'
    ]);
    exit;
}

// Get and sanitize form fields
$fullname       = trim($_POST['fullname'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$email          = trim($_POST['email'] ?? '');
$zipcode        = trim($_POST['zipcode'] ?? '');
$subject        = trim($_POST['subject'] ?? '');
$message        = trim($_POST['message'] ?? '');
$contact_method = trim($_POST['contact_method'] ?? '');
$contact_time   = trim($_POST['contact_time'] ?? '');

// Validate required fields
if ($fullname === '' || $phone === '' || $message === '') {
    echo json_encode([
        'status' => 'error',
        'msg' => 'Please fill in all required fields.'
    ]);
    exit;
}

// Validate email when provided
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'msg' => 'Please enter a valid email address.'
    ]);
    exit;
}

// Validate ZIP code when provided
if ($zipcode !== '' && !preg_match('/^\d{5}$/', $zipcode)) {
    echo json_encode([
        'status' => 'error',
        'msg' => 'Please enter a valid 5-digit ZIP code.'
    ]);
    exit;
}

// ADU service labels
$service_labels = [
    'garage-conversion-adu' => 'Garage Conversion ADU',
    'detached-adu'          => 'Detached ADU Construction',
    'attached-adu'          => 'Attached ADU Construction',
    'adu-design-planning'   => 'ADU Design & Planning',
    'adu-permitting'        => 'ADU Permitting & Permit Assistance',
    'multifamily-adu'       => 'Multifamily ADU Construction',
    'general'               => 'General ADU Inquiry'
];

$project_type = $service_labels[$subject] ?? 'Not specified';

// Escape user content for HTML email
$safe_fullname       = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');
$safe_phone          = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$safe_email          = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$safe_zipcode        = htmlspecialchars($zipcode, ENT_QUOTES, 'UTF-8');
$safe_project_type   = htmlspecialchars($project_type, ENT_QUOTES, 'UTF-8');
$safe_message        = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
$safe_contact_method = htmlspecialchars($contact_method, ENT_QUOTES, 'UTF-8');
$safe_contact_time   = htmlspecialchars($contact_time, ENT_QUOTES, 'UTF-8');

// Email settings
$to_email     = 'estimate@freequotepro.com';
$email_subject = 'New Dallas ADU Project Inquiry';

$email_body = "
<html>
<body>
    <h2>New ADU Project Inquiry</h2>

    <p>A new inquiry has been submitted through the Dallas ADU website.</p>

    <p>
        <strong>Full Name:</strong> {$safe_fullname}<br>
        <strong>Phone:</strong> {$safe_phone}<br>
        <strong>Email:</strong> {$safe_email}<br>
        <strong>ZIP Code:</strong> {$safe_zipcode}<br>
        <strong>Project Type:</strong> {$safe_project_type}<br>
        <strong>Preferred Contact Method:</strong> {$safe_contact_method}<br>
        <strong>Preferred Contact Time:</strong> {$safe_contact_time}
    </p>

    <p>
        <strong>Project Details:</strong><br>
        {$safe_message}
    </p>
</body>
</html>
";

// Use a domain-based From address.
// Make sure this mailbox/address is permitted by your hosting provider.
$from_email = 'noreply@dallasadubuild.com';

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: Dallas ADU Build Website <{$from_email}>\r\n";

// Only add Reply-To when the visitor supplied a valid email
if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $headers .= "Reply-To: {$email}\r\n";
}

if (mail($to_email, $email_subject, $email_body, $headers)) {

    echo json_encode([
        'status' => 'Success',
        'msg' => "Thank you, {$fullname}. Your ADU project inquiry has been received. We'll be in touch soon."
    ]);

} else {

    echo json_encode([
        'status' => 'error',
        'msg' => 'Your message could not be sent at this time. Please try again or call (682) 449-8199.'
    ]);
}

?>