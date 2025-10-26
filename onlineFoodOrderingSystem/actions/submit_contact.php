<?php
// Include the database configuration file
require_once '../includes/config.php';

// Set the response header to JSON
header('Content-Type: application/json');

/*
================================================================================
IMPORTANT: EMAIL CONFIGURATION
================================================================================
Change the email address below to the one where you want to receive messages.
*/
$recipient_email = "johnaldriebaquiran51@gmail.com";



// Ensure the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get and sanitize form data
    $name = isset($_POST['contactName']) ? trim($_POST['contactName']) : '';
    $email = isset($_POST['contactEmail']) ? trim($_POST['contactEmail']) : '';
    $subject = isset($_POST['contactSubject']) ? trim($_POST['contactSubject']) : '';
    $message = isset($_POST['contactMessage']) ? trim($_POST['contactMessage']) : '';

    // --- Server-side Validation ---
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill out all required fields.'
        ]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide a valid email address.'
        ]);
        exit;
    }

    // --- 1. Database Insertion (for backup) ---
    $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $param_name, $param_email, $param_subject, $param_message);
        
        $param_name = $name;
        $param_email = $email;
        $param_subject = $subject;
        $param_message = $message;
        
        if ($stmt->execute()) {
            // --- 2. Send Email Notification ---
            // This part runs ONLY if the message was successfully saved to the database.
            
            $email_subject = "New Contact Message: " . $subject;
            
            // Construct the email body
            $email_body = "You have received a new message from your website contact form.\n\n";
            $email_body .= "--------------------------------------------------\n";
            $email_body .= "Name:    $name\n";
            $email_body .= "Email:   $email\n";
            $email_body .= "Subject: $subject\n";
            $email_body .= "--------------------------------------------------\n\n";
            $email_body .= "Message:\n$message\n";

            // Construct email headers to improve deliverability
            // Make sure to replace 'yourdomain.com' with your actual domain name
            $headers = "From: Bente Sais Lomihan <noreply@yourdomain.com>\r\n";
            $headers .= "Reply-To: $name <$email>\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            // Use @mail to suppress server warnings if email fails. 
            // The main success of this script is saving to the database.
            @mail($recipient_email, $email_subject, $email_body, $headers);

            // Respond with success since the message is safely stored.
            echo json_encode([
                'success' => true,
                'message' => 'Message sent successfully! We will get back to you soon.'
            ]);

        } else {
            // If database execution fails
            error_log("Error executing statement: " . $stmt->error);
            echo json_encode([
                'success' => false,
                'message' => 'Sorry, something went wrong on our end. Please try again later.'
            ]);
        }
        
        $stmt->close();
    } else {
        // If preparation fails
        error_log("Error preparing statement: " . $conn->error);
        echo json_encode([
            'success' => false,
            'message' => 'Sorry, a server error occurred. Please try again later.'
        ]);
    }
    
    $conn->close();

} else {
    // Handle cases where the request method is not POST
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>