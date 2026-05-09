<?php
include 'db.php';

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $conn->prepare("UPDATE bookings SET payment_status='Paid' WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: view-booking.php");
exit();
