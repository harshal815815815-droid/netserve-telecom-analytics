<?php
// No login required for demo project
if (session_status() == PHP_SESSION_NONE) session_start();

include 'db.php';

// ── Fetch all bookings ────────────────────────────────────────
$result = $conn->query("SELECT id, name, mobile, email, address, plan, price, payment_status, status, created_at FROM bookings ORDER BY id DESC");

if (!$result) {
    die("Query failed: " . $conn->error);
}

// ── Set CSV headers ────────────────────────────────────────────
$filename = 'netserve_bookings_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// ── Write CSV ─────────────────────────────────────────────────
$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Column headers
fputcsv($output, [
    'ID',
    'Customer Name',
    'Mobile',
    'Email',
    'Address',
    'Plan',
    'Price (₹)',
    'Payment Status',
    'Booking Status',
    'Booking Date'
]);

// Data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['mobile'],
        $row['email'],
        $row['address'],
        $row['plan'],
        $row['price'] ?? '0',
        $row['payment_status'],
        $row['status'],
        date('d M Y, h:i A', strtotime($row['created_at']))
    ]);
}

fclose($output);
exit();
