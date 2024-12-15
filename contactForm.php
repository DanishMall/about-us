<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'db_connect.php';

// Check if form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    // Validation checks
    $errors = [];

    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Please provide a valid name.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    if (empty($message) || strlen($message) < 10) {
        $errors[] = "Message must be at least 10 characters long.";
    }

    // If no errors, proceed with submission
    if (empty($errors)) {
        try {
            // Prepare SQL to insert message
            $stmt = $pdo->prepare("INSERT INTO contact_submissions 
                (name, email, message, submission_date) 
                VALUES (:name, :email, :message, NOW())");

            // Execute the statement
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':message' => $message
            ]);

            // Optional: Send email notification
            $to = "dinidanish008@gmail.com";
            $subject = "New Contact Form Submission";
            $emailBody = "Name: $name\nEmail: $email\n\nMessage:\n$message";
            $headers = "From: https://danishmall.github.io/about-us/";

            mail($to, $subject, $emailBody, $headers);

            // Prepare success response
            $response = [
                'status' => 'success',
                'message' => 'Your message has been sent successfully!'
            ];
        } catch(PDOException $e) {
            $response = [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    } else {
        // If validation fails
        $response = [
            'status' => 'error',
            'errors' => $errors
        ];
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>