<?php
// receipt.php — Print-friendly payment receipt
include 'db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit(); }

$stmt = $conn->prepare("SELECT id, name, mobile, email, plan, price, payment_status, status, created_at FROM bookings WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { header("Location: index.php"); exit(); }
$b = $result->fetch_assoc();

// Generate Order ID (deterministic, based on date + booking id)
$orderDate = date('Ymd', strtotime($b['created_at']));
$orderId   = 'NS-' . $orderDate . '-' . str_pad($b['id'], 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt #<?php echo $orderId; ?> – NetServe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4ff;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 30px 15px;
        }
        .receipt-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,.1);
            width: 100%; max-width: 500px;
            overflow: hidden;
        }
        .receipt-header {
            background: linear-gradient(135deg, #1a6bff, #003087);
            padding: 28px 32px; text-align: center; color: #fff;
        }
        .receipt-header .logo-icon {
            width: 56px; height: 56px; background: rgba(255,255,255,.15);
            border-radius: 14px; display: flex; align-items: center; justify-content: center;
            margin: 0 auto 12px; font-size: 24px;
        }
        .receipt-header h3 { font-weight: 800; margin: 0 0 4px; font-size: 1.4rem; }
        .receipt-header p  { opacity: .75; margin: 0; font-size: .85rem; }
        .receipt-body { padding: 28px 32px; }
        .receipt-order-id {
            background: #f0f4ff; border: 1.5px dashed #93c5fd;
            border-radius: 12px; padding: 14px 18px; margin-bottom: 24px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .order-id-label { font-size: .75rem; color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: .8px; }
        .order-id-val   { font-size: 1.15rem; font-weight: 800; color: #1a6bff; }
        .receipt-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 11px 0; border-bottom: 1px solid #f0f0f0; font-size: .9rem;
        }
        .receipt-row:last-child { border-bottom: none; }
        .receipt-row .rl { color: #888; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .receipt-row .rv { color: #333; font-weight: 600; }
        .receipt-row .rv.amount { font-size: 1.2rem; color: #1a6bff; font-weight: 800; }
        .receipt-footer {
            background: #f8faff; border-top: 1px solid #e8f0fe;
            padding: 18px 32px; text-align: center;
        }
        .receipt-footer p { color: #aaa; font-size: .78rem; margin: 0 0 12px; }
        .btn-print {
            background: linear-gradient(135deg, #1a6bff, #003087);
            border: none; border-radius: 10px; padding: 10px 22px;
            font-weight: 700; color: #fff; cursor: pointer; font-size: .9rem;
            transition: all .25s; box-shadow: 0 3px 12px rgba(26,107,255,.3);
        }
        .btn-print:hover { transform: translateY(-1px); }
        .btn-back {
            background: #f0f4ff; border: 1.5px solid #c7d9ff;
            border-radius: 10px; padding: 9px 20px;
            font-weight: 600; color: #1a6bff; font-size: .9rem;
            text-decoration: none; transition: all .2s;
        }
        .btn-back:hover { background: #e0ebff; color: #003087; }
        .status-paid {
            display: inline-flex; align-items: center; gap: 5px;
            background: #e8f5e9; color: #2e7d32; border-radius: 20px;
            padding: 4px 12px; font-size: .78rem; font-weight: 600;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt-footer .btn-print, .receipt-footer .btn-back { display: none; }
            .receipt-card { box-shadow: none; border-radius: 0; }
        }
    </style>
</head>
<body>
<div class="receipt-card">
    <div class="receipt-header">
        <div class="logo-icon"><i class="bi bi-receipt"></i></div>
        <h3>Payment Receipt</h3>
        <p>NetServe Internet Services</p>
    </div>
    <div class="receipt-body">
        <!-- Order ID -->
        <div class="receipt-order-id">
            <div>
                <div class="order-id-label">Order ID</div>
                <div class="order-id-val"><?php echo $orderId; ?></div>
            </div>
            <span class="status-paid">
                <i class="bi bi-check-circle-fill"></i>
                <?php echo htmlspecialchars($b['payment_status']); ?>
            </span>
        </div>

        <!-- Details -->
        <div class="receipt-row">
            <span class="rl"><i class="bi bi-person-fill"></i> Customer</span>
            <span class="rv"><?php echo htmlspecialchars($b['name']); ?></span>
        </div>
        <div class="receipt-row">
            <span class="rl"><i class="bi bi-envelope-fill"></i> Email</span>
            <span class="rv" style="font-size:.85rem;"><?php echo htmlspecialchars($b['email']); ?></span>
        </div>
        <div class="receipt-row">
            <span class="rl"><i class="bi bi-phone-fill"></i> Mobile</span>
            <span class="rv"><?php echo htmlspecialchars($b['mobile']); ?></span>
        </div>
        <div class="receipt-row">
            <span class="rl"><i class="bi bi-wifi"></i> Plan</span>
            <span class="rv">
                <span style="background:#e3f2fd;color:#1565c0;border-radius:8px;padding:2px 10px;font-size:.82rem;">
                    <?php echo htmlspecialchars($b['plan']); ?>
                </span>
            </span>
        </div>
        <div class="receipt-row">
            <span class="rl"><i class="bi bi-calendar-check"></i> Booking Status</span>
            <span class="rv"><?php echo htmlspecialchars($b['status']); ?></span>
        </div>
        <div class="receipt-row">
            <span class="rl"><i class="bi bi-clock-history"></i> Date</span>
            <span class="rv" style="font-size:.85rem;"><?php echo date('d M Y, h:i A', strtotime($b['created_at'])); ?></span>
        </div>
        <div class="receipt-row" style="border-top: 2px solid #e8f0fe; margin-top: 8px; padding-top: 16px;">
            <span class="rl" style="font-size:1rem;font-weight:700;color:#333;"><i class="bi bi-currency-rupee"></i> Total Amount</span>
            <span class="rv amount">₹<?php echo htmlspecialchars($b['price']); ?></span>
        </div>
    </div>
    <div class="receipt-footer">
        <p><i class="bi bi-shield-check me-1"></i>This is a simulated receipt. No real transaction occurred.</p>
        <div class="d-flex gap-2 justify-content-center">
            <button class="btn-print" onclick="window.print()">
                <i class="bi bi-printer-fill me-1"></i>Print
            </button>
            <a href="index.php" class="btn-back">
                <i class="bi bi-house-fill me-1"></i>Back to Home
            </a>
        </div>
    </div>
</div>
</body>
</html>
