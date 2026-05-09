<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once 'config.php';
include 'db.php';
include 'includes/mailer.php';

// ── Helper: redirect back with error ─────────────────────────
// Redirects to the page that submitted the form:
//   source=index  → back to index.php (modal was open there)
//   source=service → back to service.php standalone form
function redirectError(string $msg): void {
    $_SESSION['form_error'] = $msg;
    $source = trim($_POST['source'] ?? 'service');
    if ($source === 'index') {
        header("Location: index.php");
    } else {
        header("Location: service.php" . (!empty($_POST['plan']) ? '?plan=' . urlencode($_POST['plan']) . '&price=' . urlencode($_POST['price'] ?? '499') : ''));
    }
    exit();
}

// ── Read and sanitize inputs ──────────────────────────────────
$name    = trim($_POST['name']    ?? '');
$mobile  = trim($_POST['mobile']  ?? '');
$email   = trim($_POST['email']   ?? '');
$address = trim($_POST['address'] ?? '');
$plan    = trim($_POST['plan']    ?? '');
$price   = trim($_POST['price']   ?? '499');

// ── Server-side validation (safety net after client validation) ─
if (empty($name)) {
    redirectError("Name is required.");
}
if (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
    redirectError("Name must contain only letters and spaces.");
}
if (strlen($name) < 2) {
    redirectError("Name must be at least 2 characters.");
}

if (empty($mobile)) {
    redirectError("Mobile number is required.");
}
if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
    redirectError("Please enter a valid 10-digit Indian mobile number starting with 6–9.");
}

if (empty($email)) {
    redirectError("Email address is required.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectError("Please enter a valid email address.");
}

if (empty($address)) {
    redirectError("Service address is required.");
}
if (strlen($address) < 10) {
    redirectError("Please provide a more complete address (at least 10 characters).");
}

if (empty($plan)) {
    redirectError("No plan was selected. Please go back and choose a plan.");
}

// Sanitize price — must be a positive number
$price = preg_replace('/[^0-9.]/', '', $price);
if (empty($price) || !is_numeric($price) || (float)$price <= 0) {
    $price = '499';
}

// ── Insert into database ──────────────────────────────────────
$stmt = $conn->prepare(
    "INSERT INTO bookings (name, mobile, email, address, plan, price, payment_status, status)
     VALUES (?, ?, ?, ?, ?, ?, 'Pending', 'Pending')"
);
$stmt->bind_param("ssssss", $name, $mobile, $email, $address, $plan, $price);

if ($stmt->execute()) {
    $booking_id = $stmt->insert_id;

    // ── Generate consistent Order ID ─────────────────────────
    $created_at = date('Y-m-d H:i:s'); // current time (just inserted)
    $orderDate  = date('Ymd');
    $orderId    = 'NS-' . $orderDate . '-' . str_pad($booking_id, 4, '0', STR_PAD_LEFT);

    // ── Send booking confirmation email ───────────────────────
    sendBookingConfirmation($email, $name, $plan, $price, $orderId);

    header("Location: payment.php?id=" . intval($booking_id));
    exit();
} else {
    redirectError("Something went wrong while saving your booking. Please try again.");
}
