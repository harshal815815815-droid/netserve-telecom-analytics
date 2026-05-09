<?php
// payment-later.php — Upgraded "Pay Later" confirmation page
include 'db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit(); }

// Verify booking exists and fetch details
$check = $conn->prepare("SELECT id, name, plan, price, created_at FROM bookings WHERE id=?");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();
if ($result->num_rows === 0) { header("Location: index.php"); exit(); }
$b = $result->fetch_assoc();

// Update to Pay Later
$stmt = $conn->prepare("UPDATE bookings SET payment_status='Pay Later' WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Generate Order ID using booking's created_at date (consistent across all pages)
$orderDate = date('Ymd', strtotime($b['created_at']));
$orderId   = 'NS-' . $orderDate . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pay Later Confirmed – NetServe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 30px 15px;
        }
        .pl-card {
            background: #fff; border-radius: 24px;
            box-shadow: 0 8px 36px rgba(0,0,0,.1);
            width: 100%; max-width: 460px; overflow: hidden;
        }
        .pl-header {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            padding: 36px 32px; text-align: center; color: #fff;
        }
        .clock-circle {
            width: 80px; height: 80px;
            background: rgba(255,255,255,.2); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 2.4rem;
            animation: pop .5s ease;
        }
        @keyframes pop {
            0%   { transform: scale(.4); opacity: 0; }
            80%  { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
        .pl-header h3 { font-weight: 800; margin: 0 0 6px; font-size: 1.6rem; }
        .pl-header p  { opacity: .85; margin: 0; font-size: .92rem; }
        .order-id-tag {
            display: inline-block;
            background: rgba(255,255,255,.2); border-radius: 20px;
            padding: 5px 16px; font-size: .8rem; font-weight: 700; margin-top: 10px;
        }
        /* Body */
        .pl-body { padding: 28px 32px; }
        .info-banner {
            background: #fffbeb; border: 1.5px solid #fde68a;
            border-radius: 14px; padding: 14px 18px; margin-bottom: 24px;
            display: flex; align-items: flex-start; gap: 12px;
        }
        .info-banner i   { font-size: 1.3rem; color: #d97706; flex-shrink: 0; margin-top: 2px; }
        .info-banner p   { color: #92400e; font-size: .87rem; margin: 0; line-height: 1.55; }
        /* Summary */
        .summary-box {
            background: #fffbeb; border: 1.5px solid #fde68a;
            border-radius: 14px; padding: 18px 20px; margin-bottom: 24px;
        }
        .sum-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 9px 0; border-bottom: 1px solid #fde68a; font-size: .9rem;
        }
        .sum-row:last-child { border-bottom: none; }
        .sum-label { color: #78350f; font-weight: 500; display: flex; align-items: center; gap: 7px; }
        .sum-label i { color: #d97706; }
        .sum-value  { color: #1c1917; font-weight: 700; }
        .sum-price  { font-size: 1.2rem; color: #b45309; font-weight: 800; }
        /* Buttons */
        .btn-pay-now-link {
            display: block; text-align: center; text-decoration: none;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-radius: 12px; padding: 14px;
            font-weight: 700; font-size: 1rem; color: #fff;
            transition: all .25s; box-shadow: 0 4px 14px rgba(245,158,11,.35);
            margin-bottom: 10px;
        }
        .btn-pay-now-link:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245,158,11,.45); color: #fff; }
        .btn-home-link {
            display: block; text-align: center; text-decoration: none;
            background: #fff; border: 2px solid #fde68a; border-radius: 12px;
            padding: 13px; font-weight: 700; font-size: .95rem;
            color: #b45309; transition: all .25s;
        }
        .btn-home-link:hover { background: #fffbeb; color: #92400e; }
        .btn-mybookings-link {
            display: block; text-align: center; text-decoration: none;
            background: #f0f4ff; border: 1.5px solid #c7d9ff; border-radius: 12px;
            padding: 11px; font-weight: 600; font-size: .88rem; color: #1a6bff;
            transition: all .2s; margin-top: 10px;
        }
        .btn-mybookings-link:hover { background: #e0ebff; }
        .note { text-align: center; color: #bbb; font-size: .78rem; margin-top: 16px; display: flex; align-items: center; justify-content: center; gap: 5px; }
    </style>
</head>
<body>
<div class="pl-card">

    <!-- Header -->
    <div class="pl-header">
        <div class="clock-circle"><i class="bi bi-clock-fill"></i></div>
        <h3>Pay Later Confirmed</h3>
        <p>Your booking has been saved.</p>
        <div class="order-id-tag"><i class="bi bi-hash me-1"></i><?php echo $orderId; ?></div>
    </div>

    <!-- Body -->
    <div class="pl-body">

        <!-- Info banner -->
        <div class="info-banner">
            <i class="bi bi-exclamation-circle-fill"></i>
            <p>Your booking is <strong>reserved</strong>. Complete payment anytime by using the <em>Pay Now Instead</em> button below or revisiting this page. Your service will activate once payment is received.</p>
        </div>

        <!-- Summary -->
        <div class="summary-box">
            <div class="sum-row">
                <span class="sum-label"><i class="bi bi-person-fill"></i> Customer</span>
                <span class="sum-value"><?php echo htmlspecialchars($b['name']); ?></span>
            </div>
            <div class="sum-row">
                <span class="sum-label"><i class="bi bi-wifi"></i> Plan</span>
                <span class="sum-value">
                    <span style="background:#fef3c7;color:#b45309;border-radius:8px;padding:2px 10px;font-size:.82rem;">
                        <?php echo htmlspecialchars($b['plan']); ?>
                    </span>
                </span>
            </div>
            <div class="sum-row">
                <span class="sum-label"><i class="bi bi-receipt"></i> Order ID</span>
                <span class="sum-value" style="color:#d97706;"><?php echo $orderId; ?></span>
            </div>
            <div class="sum-row">
                <span class="sum-label"><i class="bi bi-currency-rupee"></i> Amount Due</span>
                <span class="sum-value sum-price">₹<?php echo htmlspecialchars($b['price']); ?></span>
            </div>
        </div>

        <!-- Actions -->
        <a href="payment.php?id=<?php echo $id; ?>" class="btn-pay-now-link">
            <i class="bi bi-credit-card-fill me-2"></i>Pay Now Instead
        </a>
        <a href="index.php" class="btn-home-link">
            <i class="bi bi-house-fill me-2"></i>Back to Home
        </a>


        <p class="note">
            <i class="bi bi-shield-check"></i>
            Demo mode — no real transaction.
        </p>
    </div>
</div>
</body>
</html>
