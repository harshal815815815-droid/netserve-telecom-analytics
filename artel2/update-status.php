<?php
session_start();
require_once 'config.php';
include 'db.php';

// No login required for demo project

// ── PHPMailer ─────────────────────────────────────────────────
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ── Sanitize inputs ───────────────────────────────────────────
$id     = intval($_GET['id']     ?? 0);
$status = trim($_GET['status']   ?? '');

// Validate status is one of allowed values
if (!in_array($status, ['Approved', 'Rejected'])) {
    header("Location: view-booking.php");
    exit();
}
if ($id <= 0) {
    header("Location: view-booking.php");
    exit();
}

// ── Update booking status ─────────────────────────────────────
$stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
$stmt->bind_param("si", $status, $id);
$stmt->execute();

// ── Fetch booking for email ───────────────────────────────────
$stmt = $conn->prepare("SELECT name, email, plan FROM bookings WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header("Location: view-booking.php");
    exit();
}

$name  = $data['name'];
$email = $data['email'];
$plan  = $data['plan'];

// ── Send email notification via PHPMailer ─────────────────────
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress($email, $name);

    $mail->isHTML(true);
    $mail->Subject = "Your NetServe Booking has been {$status}";

    $statusColor  = $status === 'Approved' ? '#2ecc71' : '#e74c3c';
    $statusEmoji  = $status === 'Approved' ? '✅' : '❌';

    $mail->Body = "
    <div style='font-family:Inter,sans-serif;max-width:520px;margin:0 auto;'>
        <div style='background:linear-gradient(135deg,#e60000,#8b0000);padding:28px;border-radius:12px 12px 0 0;text-align:center;'>
            <h2 style='color:white;margin:0;font-size:1.4rem;'>NetServe Service</h2>
        </div>
        <div style='background:#fff;padding:32px;border:1px solid #eee;border-radius:0 0 12px 12px;'>
            <p style='color:#333;font-size:1rem;'>Hello <strong>{$name}</strong>,</p>
            <p style='color:#555;'>Your booking for the plan <strong>{$plan}</strong> has been:</p>
            <div style='background:{$statusColor}18;border:1.5px solid {$statusColor};border-radius:10px;padding:16px;text-align:center;margin:20px 0;'>
                <span style='font-size:1.5rem;font-weight:700;color:{$statusColor};'>{$statusEmoji} {$status}</span>
            </div>
            <p style='color:#888;font-size:.9rem;'>If you have any questions, please contact our support team.</p>
            <hr style='border:none;border-top:1px solid #eee;margin:20px 0;'>
            <p style='color:#bbb;font-size:.8rem;text-align:center;margin:0;'>© " . date('Y') . " NetServe Services</p>
        </div>
    </div>
    ";

    $mail->send();
} catch (Exception $e) {
    // Email failed — log but don't crash the admin flow
    error_log("PHPMailer Error: " . $mail->ErrorInfo);
}

header("Location: view-booking.php");
exit();
