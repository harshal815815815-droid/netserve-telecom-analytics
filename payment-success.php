<?php
/**
 * payment-success.php — Enhanced success page
 * Shows Razorpay payment details when available.
 * Keeps original design (green header, check circle, summary box).
 */
if (session_status() == PHP_SESSION_NONE) session_start();

require_once 'config.php';
include 'db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit(); }

// ── Fetch booking (include new Razorpay columns) ──────────────
$check = $conn->prepare(
    "SELECT id, name, email, plan, price, payment_status, payment_verified,
            razorpay_payment_id, payment_method, paid_at, created_at
     FROM bookings WHERE id=?"
);
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();
if ($result->num_rows === 0) { header("Location: index.php"); exit(); }
$b = $result->fetch_assoc();

// ── Access guard: must be paid ────────────────────────────────
if ($b['payment_status'] !== 'Paid') {
    header("Location: payment.php?id=" . $id);
    exit();
}

// ── Order ID (display) ────────────────────────────────────────
$orderDate = date('Ymd', strtotime($b['created_at']));
$orderId   = 'NS-' . $orderDate . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);

// ── Pull Razorpay info from session (set by verify-payment.php) ─
$rzpInfo    = $_SESSION['rzp_verified'] ?? [];
$paymentId  = $rzpInfo['payment_id']   ?? $b['razorpay_payment_id'] ?? null;
$rzpOrderId = $rzpInfo['order_id']     ?? null;
$method     = $rzpInfo['method']       ?? $b['payment_method']       ?? null;
$paidAt     = $rzpInfo['paid_at']      ?? $b['paid_at']              ?? null;

// Clear session to prevent re-showing "fresh" state on refresh
unset($_SESSION['rzp_verified']);

// ── Was this a "just paid" via Razorpay? ─────────────────────
$isRazorpayPaid = !empty($paymentId) && str_starts_with($paymentId, 'pay_');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Successful – NetServe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 30px 15px;
        }
        .success-card {
            background: #fff; border-radius: 24px;
            box-shadow: 0 8px 36px rgba(0,0,0,.1);
            width: 100%; max-width: 500px; overflow: hidden;
        }
        .success-header {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            padding: 36px 32px; text-align: center; color: #fff;
        }
        .check-circle {
            width: 80px; height: 80px;
            background: rgba(255,255,255,.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 2.4rem; color: #fff;
            animation: pop .5s ease;
        }
        @keyframes pop {
            0%   { transform: scale(.4); opacity: 0; }
            80%  { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
        .success-header h3 { font-weight: 800; margin: 0 0 6px; font-size: 1.6rem; }
        .success-header p  { opacity: .85; margin: 0; font-size: .92rem; }
        .order-id-tag {
            display: inline-block;
            background: rgba(255,255,255,.2); border-radius: 20px;
            padding: 5px 16px; font-size: .8rem; font-weight: 700; margin-top: 10px;
        }
        /* Step bar */
        .step-bar {
            display: flex; align-items: center; justify-content: center;
            padding: 16px 32px; background: #f0fdf4; border-bottom: 1px solid #d1fae5;
        }
        .s-circle { width: 28px; height: 28px; border-radius: 50%; background: #2ecc71; color: #fff; display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; }
        .s-line   { flex: 1; height: 2px; background: #2ecc71; margin: 0 6px; }
        .s-label  { font-size: .68rem; color: #16a34a; font-weight: 600; text-align: center; margin-top: 4px; text-transform: uppercase; letter-spacing: .5px; }
        .s-step   { display: flex; flex-direction: column; align-items: center; flex: 1; }
        /* Summary */
        .success-body { padding: 26px 32px; }
        .summary-box {
            background: #f0fdf4; border: 1.5px solid #bbf7d0;
            border-radius: 14px; padding: 18px 20px; margin-bottom: 20px;
        }
        .sum-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 9px 0; border-bottom: 1px solid #d1fae5; font-size: .9rem;
        }
        .sum-row:last-child { border-bottom: none; }
        .sum-label { color: #4b5563; font-weight: 500; display: flex; align-items: center; gap: 7px; }
        .sum-label i { color: #16a34a; }
        .sum-value { color: #111827; font-weight: 700; }
        .sum-amount { font-size: 1.25rem; color: #15803d; font-weight: 800; }
        .sum-value-sm { font-size: .82rem; color: #374151; font-weight: 600; word-break: break-all; text-align: right; max-width: 55%; }
        /* Verified badge */
        .verified-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: #dcfce7; color: #15803d; border-radius: 6px;
            padding: 2px 10px; font-size: .8rem; font-weight: 700;
        }
        /* Email badge */
        .email-badge {
            background: #f0fdf4; border: 1.5px solid #bbf7d0;
            border-radius: 10px; padding: 10px 16px; margin-bottom: 16px;
            display: flex; align-items: center; gap: 10px; font-size: .85rem; color: #16a34a;
        }
        .email-badge i { font-size: 1.1rem; flex-shrink: 0; }
        /* Buttons */
        .btn-receipt {
            display: block; text-align: center; text-decoration: none;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            border-radius: 12px; padding: 14px;
            font-weight: 700; font-size: 1rem; color: #fff;
            transition: all .25s; box-shadow: 0 4px 14px rgba(46,204,113,.35);
            margin-bottom: 10px;
        }
        .btn-receipt:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(46,204,113,.45); color: #fff; }
        .btn-home {
            display: block; text-align: center; text-decoration: none;
            background: #fff; border: 2px solid #bbf7d0; border-radius: 12px;
            padding: 13px; font-weight: 700; font-size: .95rem;
            color: #16a34a; transition: all .25s; margin-bottom: 10px;
        }
        .btn-home:hover { background: #f0fdf4; color: #15803d; }
        .note { text-align: center; color: #bbb; font-size: .78rem; margin-top: 16px; display: flex; align-items: center; justify-content: center; gap: 5px; }
    </style>
</head>
<body>
<div class="success-card">

    <!-- Header -->
    <div class="success-header">
        <div class="check-circle"><i class="bi bi-check-lg"></i></div>
        <h3>Payment Successful!</h3>
        <p>Your service booking has been confirmed.</p>
        <div class="order-id-tag"><i class="bi bi-hash me-1"></i><?php echo $orderId; ?></div>
    </div>

    <!-- Step bar (all green) -->
    <div class="step-bar">
        <div class="s-step">
            <div class="s-circle"><i class="bi bi-check"></i></div>
            <div class="s-label">Order</div>
        </div>
        <div class="s-line"></div>
        <div class="s-step">
            <div class="s-circle"><i class="bi bi-check"></i></div>
            <div class="s-label">Payment</div>
        </div>
        <div class="s-line"></div>
        <div class="s-step">
            <div class="s-circle"><i class="bi bi-check"></i></div>
            <div class="s-label">Confirmed</div>
        </div>
    </div>

    <!-- Body -->
    <div class="success-body">

        <!-- Summary -->
        <div class="summary-box">
            <div class="sum-row">
                <span class="sum-label"><i class="bi bi-person-fill"></i> Customer</span>
                <span class="sum-value"><?php echo htmlspecialchars($b['name']); ?></span>
            </div>
            <div class="sum-row">
                <span class="sum-label"><i class="bi bi-wifi"></i> Plan</span>
                <span class="sum-value">
                    <span style="background:#dcfce7;color:#16a34a;border-radius:8px;padding:2px 10px;font-size:.82rem;">
                        <?php echo htmlspecialchars($b['plan']); ?>
                    </span>
                </span>
            </div>
            <div class="sum-row">
                <span class="sum-label"><i class="bi bi-receipt"></i> Order ID</span>
                <span class="sum-value" style="color:#16a34a;"><?php echo $orderId; ?></span>
            </div>
            <?php if ($paymentId): ?>
            <div class="sum-row">
                <span class="sum-label"><i class="bi bi-credit-card-2-front-fill"></i> Payment ID</span>
                <span class="sum-value-sm"><?php echo htmlspecialchars($paymentId); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($method): ?>
            <div class="sum-row">
                <span class="sum-label"><i class="bi bi-cash-stack"></i> Method</span>
                <span class="sum-value"><?php echo htmlspecialchars(ucfirst($method)); ?></span>
            </div>
            <?php endif; ?>
            <div class="sum-row">
                <span class="sum-label"><i class="bi bi-currency-rupee"></i> Amount Paid</span>
                <span class="sum-value sum-amount">₹<?php echo htmlspecialchars($b['price'] ?? '0'); ?></span>
            </div>
            <?php if ($paidAt): ?>
            <div class="sum-row">
                <span class="sum-label"><i class="bi bi-clock-fill"></i> Paid At</span>
                <span class="sum-value" style="font-size:.85rem;"><?php echo date('d M Y, h:i A', strtotime($paidAt)); ?></span>
            </div>
            <?php endif; ?>
            <div class="sum-row">
                <span class="sum-label"><i class="bi bi-shield-check-fill"></i> Verified</span>
                <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Razorpay Verified</span>
            </div>
        </div>

        <!-- Email confirmation notice -->
        <?php if ($isRazorpayPaid && !empty($b['email'])): ?>
        <div class="email-badge">
            <i class="bi bi-envelope-check-fill"></i>
            <span>Payment receipt sent to <strong><?php echo htmlspecialchars($b['email']); ?></strong></span>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <a href="receipt.php?id=<?php echo $id; ?>" class="btn-receipt">
            <i class="bi bi-receipt me-2"></i>View / Print Receipt
        </a>
        <a href="index.php" class="btn-home">
            <i class="bi bi-house-fill me-2"></i>Back to Home
        </a>

        <p class="note">
            <i class="bi bi-shield-check"></i>
            Your booking will be activated once approved by admin.
        </p>
    </div>
</div>
</body>
</html>
