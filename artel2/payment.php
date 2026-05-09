<?php
// Removed session guard - No login required for demo project
include 'db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php"); exit();
}

// Fetch booking details
$stmt = $conn->prepare("SELECT id, name, plan, price, payment_status FROM bookings WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php"); exit();
}
$data = $result->fetch_assoc();

// Redirect if already paid
if ($data['payment_status'] === 'Paid') {
    header("Location: thank-you.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment – NetServe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f2f5, #e8eaf0);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 30px 15px;
        }
        .pay-card {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 8px 36px rgba(0,0,0,.1);
            width: 100%; max-width: 480px;
            overflow: hidden;
        }
        .pay-header {
            background: linear-gradient(135deg, #e60000, #8b0000);
            padding: 30px 32px;
            color: white;
            text-align: center;
        }
        .pay-header h3 { font-weight: 700; margin: 0 0 4px; }
        .pay-header p  { opacity: .8; margin: 0; font-size: .9rem; }
        .pay-body { padding: 32px; }
        .pay-detail {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 28px;
        }
        .pay-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            font-size: .9rem;
        }
        .pay-row:last-child { border-bottom: none; }
        .pay-row .label { color: #888; font-weight: 500; }
        .pay-row .value { color: #333; font-weight: 600; }
        .pay-row .value.amount { font-size: 1.3rem; color: #e60000; font-weight: 700; }
        .btn-paynow {
            display: block;
            text-align: center;
            text-decoration: none;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            border-radius: 12px;
            padding: 15px;
            font-weight: 700;
            font-size: 1rem;
            color: white;
            transition: all .25s;
            box-shadow: 0 4px 14px rgba(46,204,113,.35);
            margin-bottom: 12px;
        }
        .btn-paynow:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46,204,113,.45);
            color: white;
        }
        .btn-paylater {
            display: block;
            text-align: center;
            text-decoration: none;
            background: #fff;
            border: 2px solid #f0b429;
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            font-size: 1rem;
            color: #b7791f;
            transition: all .25s;
        }
        .btn-paylater:hover {
            background: #fffbeb;
            color: #92400e;
        }
        .secure-note {
            text-align: center;
            color: #bbb;
            font-size: .78rem;
            margin-top: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
    </style>
</head>
<body>
<div class="pay-card">
    <div class="pay-header">
        <div style="font-size:2.5rem;margin-bottom:10px;">💳</div>
        <h3>Complete Your Payment</h3>
        <p>Booking #<?php echo $data['id']; ?></p>
    </div>
    <div class="pay-body">
        <div class="pay-detail">
            <div class="pay-row">
                <span class="label"><i class="bi bi-person me-2"></i>Customer</span>
                <span class="value"><?php echo htmlspecialchars($data['name']); ?></span>
            </div>
            <div class="pay-row">
                <span class="label"><i class="bi bi-wifi me-2"></i>Plan</span>
                <span class="value"><?php echo htmlspecialchars($data['plan']); ?></span>
            </div>
            <div class="pay-row">
                <span class="label"><i class="bi bi-currency-rupee me-2"></i>Amount</span>
                <span class="value amount">₹<?php echo htmlspecialchars($data['price'] ?? '0'); ?></span>
            </div>
        </div>

        <a href="payment-success.php?id=<?php echo $data['id']; ?>" class="btn-paynow">
            <i class="bi bi-check-circle-fill me-2"></i>Pay Now — ₹<?php echo htmlspecialchars($data['price'] ?? '0'); ?>
        </a>
        <a href="payment-later.php?id=<?php echo $data['id']; ?>" class="btn-paylater">
            <i class="bi bi-clock me-2"></i>Pay Later
        </a>

        <div class="secure-note">
            <i class="bi bi-shield-lock-fill"></i>
            Demo payment — no real transaction occurs.
        </div>
    </div>
</div>
</body>
</html>
