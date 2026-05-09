<?php
/**
 * payment-failed.php — Professional payment failure page
 */
include 'db.php';

$id     = intval($_GET['id']     ?? 0);
$reason = trim($_GET['reason']   ?? 'Your payment could not be completed.');

// Fetch booking name if we have an ID
$bookingName = '';
if ($id > 0) {
    $stmt = $conn->prepare("SELECT name FROM bookings WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $r = $stmt->get_result();
    if ($row = $r->fetch_assoc()) $bookingName = $row['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Failed – NetServe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fff5f5, #fee2e2);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 30px 15px;
        }
        .fail-card {
            background: #fff; border-radius: 24px;
            box-shadow: 0 8px 36px rgba(0,0,0,.1);
            width: 100%; max-width: 460px; overflow: hidden;
        }
        .fail-header {
            background: linear-gradient(135deg, #e60000, #8b0000);
            padding: 36px 32px; text-align: center; color: #fff;
        }
        .x-circle {
            width: 80px; height: 80px;
            background: rgba(255,255,255,.2); border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 2.4rem; color: #fff;
            animation: shake .5s ease;
        }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-8px); }
            75%      { transform: translateX(8px); }
        }
        .fail-header h3 { font-weight: 800; margin: 0 0 6px; font-size: 1.6rem; }
        .fail-header p  { opacity: .85; margin: 0; font-size: .92rem; }
        .fail-body { padding: 28px 32px; }
        .reason-box {
            background: #fff5f5; border: 1.5px solid #fecaca;
            border-radius: 14px; padding: 18px 20px; margin-bottom: 24px;
        }
        .reason-box .reason-title { font-weight: 700; color: #991b1b; margin-bottom: 6px; display: flex; align-items: center; gap: 7px; }
        .reason-box .reason-text  { color: #7f1d1d; font-size: .88rem; margin: 0; }
        .tips-box {
            background: #f9fafb; border: 1px solid #e5e7eb;
            border-radius: 12px; padding: 16px 18px; margin-bottom: 24px;
        }
        .tips-box h6 { font-size: .85rem; font-weight: 700; color: #374151; margin-bottom: 10px; }
        .tips-box ul { list-style: none; padding: 0; margin: 0; }
        .tips-box ul li { font-size: .83rem; color: #6b7280; padding: 4px 0; display: flex; align-items: center; gap: 7px; }
        .tips-box ul li i { color: #9ca3af; }
        .btn-retry {
            display: block; text-align: center; text-decoration: none;
            background: linear-gradient(135deg, #e60000, #8b0000);
            border-radius: 12px; padding: 15px; font-weight: 700; font-size: 1rem;
            color: white; transition: all .25s; box-shadow: 0 4px 14px rgba(230,0,0,.35);
            margin-bottom: 10px;
        }
        .btn-retry:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(230,0,0,.45); color: white; }
        .btn-paylater {
            display: block; text-align: center; text-decoration: none;
            background: #fff; border: 2px solid #f0b429; border-radius: 12px;
            padding: 12px; font-weight: 700; font-size: .95rem;
            color: #b7791f; transition: all .25s; margin-bottom: 10px;
        }
        .btn-paylater:hover { background: #fffbeb; color: #92400e; }
        .btn-home {
            display: block; text-align: center; text-decoration: none;
            background: #fff; border: 2px solid #e5e7eb; border-radius: 12px;
            padding: 12px; font-weight: 700; font-size: .9rem;
            color: #6b7280; transition: all .25s;
        }
        .btn-home:hover { background: #f9fafb; color: #374151; }
        .note { text-align: center; color: #bbb; font-size: .78rem; margin-top: 16px; display: flex; align-items: center; justify-content: center; gap: 5px; }
    </style>
</head>
<body>
<div class="fail-card">

    <!-- Header -->
    <div class="fail-header">
        <div class="x-circle"><i class="bi bi-x-lg"></i></div>
        <h3>Payment Failed</h3>
        <p><?php echo $bookingName ? 'Sorry, ' . htmlspecialchars($bookingName) . '!' : 'We could not process your payment.'; ?></p>
    </div>

    <!-- Body -->
    <div class="fail-body">

        <!-- Reason -->
        <div class="reason-box">
            <div class="reason-title"><i class="bi bi-exclamation-triangle-fill"></i> What went wrong?</div>
            <p class="reason-text"><?php echo htmlspecialchars($reason); ?></p>
        </div>

        <!-- Tips -->
        <div class="tips-box">
            <h6>Possible fixes:</h6>
            <ul>
                <li><i class="bi bi-check-circle"></i>Check that your card / UPI details are correct</li>
                <li><i class="bi bi-check-circle"></i>Ensure sufficient balance in your account</li>
                <li><i class="bi bi-check-circle"></i>Try a different payment method (UPI / Net Banking)</li>
                <li><i class="bi bi-check-circle"></i>Disable VPN if active and retry</li>
                <li><i class="bi bi-check-circle"></i>Contact your bank if the issue persists</li>
            </ul>
        </div>

        <!-- Actions -->
        <?php if ($id > 0): ?>
        <a href="payment.php?id=<?php echo $id; ?>" class="btn-retry">
            <i class="bi bi-arrow-clockwise me-2"></i>Retry Payment
        </a>
        <a href="payment-later.php?id=<?php echo $id; ?>" class="btn-paylater">
            <i class="bi bi-clock me-2"></i>Pay Later Instead
        </a>
        <?php endif; ?>

        <a href="index.php" class="btn-home">
            <i class="bi bi-house-fill me-2"></i>Back to Home
        </a>

        <p class="note">
            <i class="bi bi-headset"></i>
            Need help? Contact our support team.
        </p>
    </div>
</div>
</body>
</html>
