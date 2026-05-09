<?php
include 'db.php';

// ── Sanitize and validate booking ID ──────────────────────────
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// ── Verify booking exists ─────────────────────────────────────
$check = $conn->prepare("SELECT id FROM bookings WHERE id=?");
$check->bind_param("i", $id);
$check->execute();
if ($check->get_result()->num_rows == 0) {
    header("Location: index.php");
    exit();
}

// ── Update to Paid ────────────────────────────────────────────
$stmt = $conn->prepare("UPDATE bookings SET payment_status='Paid' WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: thank-you.php");
exit();
