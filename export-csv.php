<?php
// No login required for demo project
if (session_status() == PHP_SESSION_NONE) session_start();

include 'db.php';

// ── Accept the same filters as view-booking.php ───────────────────
$search        = trim($_GET['search']         ?? '');
$filterPayment = trim($_GET['payment_status'] ?? '');
$filterStatus  = trim($_GET['status']         ?? '');
$filterPlan    = trim($_GET['plan']           ?? '');
$dateFrom      = trim($_GET['date_from']      ?? '');
$dateTo        = trim($_GET['date_to']        ?? '');
$sortOrder     = in_array($_GET['sort'] ?? '', ['ASC','DESC']) ? $_GET['sort'] : 'DESC';

// ── Build WHERE clause (mirrors view-booking.php logic) ───────────
$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[]  = '(name LIKE ? OR mobile LIKE ? OR email LIKE ?)';
    $s = "%$search%";
    $params[] = $s; $params[] = $s; $params[] = $s;
    $types   .= 'sss';
}
if ($filterPayment !== '') {
    $where[]  = 'payment_status = ?';
    $params[] = $filterPayment;
    $types   .= 's';
}
if ($filterStatus !== '') {
    $where[]  = 'status = ?';
    $params[] = $filterStatus;
    $types   .= 's';
}
if ($filterPlan !== '') {
    $where[]  = 'plan LIKE ?';
    $params[] = "%$filterPlan%";
    $types   .= 's';
}
if ($dateFrom !== '') {
    $where[]  = 'DATE(created_at) >= ?';
    $params[] = $dateFrom;
    $types   .= 's';
}
if ($dateTo !== '') {
    $where[]  = 'DATE(created_at) <= ?';
    $params[] = $dateTo;
    $types   .= 's';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ── Fetch filtered bookings ───────────────────────────────────────
$sql = "SELECT id, name, mobile, email, address, plan, price, payment_status, status, created_at
        FROM bookings $whereSQL ORDER BY id $sortOrder";

if ($types) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

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
