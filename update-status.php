<?php
session_start();
require_once 'config.php';
include 'db.php';
include 'includes/mailer.php';

// No login required for demo project

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

// ── Send status notification email via shared mailer ──────────
if (!empty($data['email'])) {
    sendStatusUpdate($data['email'], $data['name'], $data['plan'], $status);
}

$flashVal = strtolower($status); // 'approved' or 'rejected'
header("Location: view-booking.php?flash=" . $flashVal);
exit();
