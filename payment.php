<?php
/**
 * payment.php — Razorpay payment gateway integration
 * Keeps original design (header, step bar, detail box, Pay Later).
 * Replaces fake "Pay Now" with real Razorpay Checkout.
 */
require_once 'config.php';
require_once 'razorpay-config.php';
include 'db.php';

// ── Razorpay SDK (manual include — no Composer) ───────────────
require_once __DIR__ . '/Razorpay/Razorpay.php';
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

if (session_status() == PHP_SESSION_NONE) session_start();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: index.php"); exit(); }

// ── Fetch booking ─────────────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT id, name, email, plan, price, payment_status, created_at, razorpay_order_id
     FROM bookings WHERE id=?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) { header("Location: index.php"); exit(); }
$data = $result->fetch_assoc();

// Redirect if already paid
if ($data['payment_status'] === 'Paid') {
    header("Location: payment-success.php?id=" . $id); exit();
}

// ── Order ID (display) ────────────────────────────────────────
$orderDate = date('Ymd', strtotime($data['created_at']));
$orderId   = 'NS-' . $orderDate . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);

// ── Amount in paise (Razorpay requires smallest unit) ─────────
$priceINR  = (float)($data['price'] ?? 499);
$paise     = (int)round($priceINR * 100);

// ── Create / reuse Razorpay Order ─────────────────────────────
$rzpOrderId  = $data['razorpay_order_id'] ?? '';
$rzpOrderErr = '';

try {
    $api = new Api(RZP_KEY_ID, RZP_KEY_SECRET);

    // Only create a new Razorpay order if one doesn't already exist for this booking
    if (empty($rzpOrderId)) {
        $rzpOrder = $api->order->create([
            'amount'          => $paise,
            'currency'        => RZP_CURRENCY,
            'receipt'         => $orderId,
            'payment_capture' => 1,      // auto-capture
            'notes'           => [
                'booking_id'  => $id,
                'customer'    => $data['name'],
                'plan'        => $data['plan'],
            ],
        ]);
        $rzpOrderId = $rzpOrder['id'];

        // Save razorpay_order_id into DB immediately
        $upd = $conn->prepare("UPDATE bookings SET razorpay_order_id=? WHERE id=?");
        $upd->bind_param("si", $rzpOrderId, $id);
        $upd->execute();
    }
} catch (\Exception $e) {
    $rzpOrderErr = $e->getMessage();
    error_log("[NetServe RZP] Order creation failed for booking #{$id}: " . $e->getMessage());
}

// ── Failure scenario (?fail=1) ────────────────────────────────
$showFail = isset($_GET['fail']);
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
            box-shadow: 0 8px 36px rgba(0,0,0,.12);
            width: 100%; max-width: 500px;
            overflow: hidden;
        }
        /* Header */
        .pay-header {
            background: linear-gradient(135deg, #e60000, #8b0000);
            padding: 26px 32px; color: white; text-align: center;
        }
        .pay-header h3 { font-weight: 700; margin: 0 0 4px; }
        .pay-header p  { opacity: .8; margin: 0; font-size: .9rem; }
        .pay-order-id {
            display: inline-block;
            background: rgba(255,255,255,.15);
            border-radius: 20px; padding: 3px 14px;
            font-size: .78rem; font-weight: 600;
            margin-top: 8px; letter-spacing: .5px;
        }
        /* Step indicator */
        .pay-steps {
            display: flex; align-items: center; justify-content: center;
            gap: 0; padding: 18px 32px 0; background: #fafafa;
            border-bottom: 1px solid #f0f0f0;
        }
        .pay-step { display: flex; flex-direction: column; align-items: center; flex: 1; }
        .step-circle {
            width: 34px; height: 34px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .8rem; font-weight: 700; margin-bottom: 6px;
        }
        .step-done   { background: #e8f5e9; color: #2e7d32; border: 2px solid #2e7d32; }
        .step-active { background: linear-gradient(135deg, #e60000, #8b0000); color: #fff; border: 2px solid #8b0000; }
        .step-todo   { background: #f5f5f5; color: #bbb; border: 2px solid #e0e0e0; }
        .step-label  { font-size: .7rem; font-weight: 600; color: #aaa; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 12px; }
        .step-label.active { color: #e60000; }
        .step-line   { flex: 1; height: 2px; background: #e0e0e0; margin: 0 6px; align-self: center; margin-bottom: 18px; }
        .step-line.done { background: #2e7d32; }
        /* Body */
        .pay-body { padding: 28px 32px; }
        /* Failure banner */
        .fail-banner {
            background: #fff0f0; border: 1.5px solid #fca5a5; border-radius: 14px;
            padding: 14px 18px; margin-bottom: 22px;
            display: flex; align-items: flex-start; gap: 12px;
        }
        .fail-banner i    { font-size: 1.4rem; color: #dc2626; margin-top: 2px; flex-shrink: 0; }
        .fail-banner h6   { color: #991b1b; font-weight: 700; margin-bottom: 2px; }
        .fail-banner p    { color: #c0392b; font-size: .83rem; margin: 0; }
        /* Detail box */
        .pay-detail {
            background: #f8f9fa; border-radius: 14px;
            padding: 18px 20px; margin-bottom: 24px;
        }
        .pay-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; border-bottom: 1px solid #eee; font-size: .9rem;
        }
        .pay-row:last-child { border-bottom: none; }
        .pay-row .label { color: #888; font-weight: 500; display: flex; align-items: center; gap: 6px; }
        .pay-row .value { color: #333; font-weight: 600; }
        .pay-row .value.amount { font-size: 1.3rem; color: #e60000; font-weight: 800; }
        /* Razorpay badge */
        .rzp-badge {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 10px;
            padding: 10px 16px; margin-bottom: 20px; font-size: .82rem; color: #555;
        }
        .rzp-badge img { height: 20px; }
        /* Action buttons */
        .btn-paynow {
            display: block; text-align: center;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            border-radius: 12px; padding: 15px; font-weight: 700; font-size: 1rem;
            color: white; transition: all .25s; box-shadow: 0 4px 14px rgba(46,204,113,.35);
            margin-bottom: 10px; border: none; width: 100%; cursor: pointer;
        }
        .btn-paynow:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(46,204,113,.45); color: white; }
        .btn-paynow:disabled { opacity: .65; pointer-events: none; }
        .btn-paylater {
            display: block; text-align: center; text-decoration: none;
            background: #fff; border: 2px solid #f0b429; border-radius: 12px;
            padding: 12px; font-weight: 700; font-size: .95rem;
            color: #b7791f; transition: all .25s;
        }
        .btn-paylater:hover { background: #fffbeb; color: #92400e; }
        .secure-note {
            text-align: center; color: #bbb; font-size: .78rem;
            margin-top: 16px; display: flex; align-items: center; justify-content: center; gap: 5px;
        }
        /* Error card */
        .err-card {
            background: #fff0f0; border: 1.5px solid #fca5a5; border-radius: 14px;
            padding: 18px 20px; margin-bottom: 20px; text-align: center;
        }
        .err-card h6 { color: #991b1b; font-weight: 700; }
        .err-card p  { color: #c0392b; font-size: .84rem; margin: 0; }
    </style>
</head>
<body>
<div class="pay-card">

    <!-- Header -->
    <div class="pay-header">
        <div style="font-size:2.2rem;margin-bottom:8px;">💳</div>
        <h3>Complete Your Payment</h3>
        <p>Booking #<?php echo $data['id']; ?></p>
        <div class="pay-order-id"><i class="bi bi-hash me-1"></i><?php echo $orderId; ?></div>
    </div>

    <!-- Step Indicators -->
    <div class="pay-steps">
        <div class="pay-step">
            <div class="step-circle step-done"><i class="bi bi-check"></i></div>
            <div class="step-label">Order</div>
        </div>
        <div class="step-line done"></div>
        <div class="pay-step">
            <div class="step-circle step-active"><i class="bi bi-credit-card"></i></div>
            <div class="step-label active">Payment</div>
        </div>
        <div class="step-line"></div>
        <div class="pay-step">
            <div class="step-circle step-todo"><i class="bi bi-check-all"></i></div>
            <div class="step-label">Confirmed</div>
        </div>
    </div>

    <div class="pay-body">

        <?php if ($showFail): ?>
        <div class="fail-banner">
            <i class="bi bi-x-circle-fill"></i>
            <div>
                <h6>Payment Failed</h6>
                <p>Your payment could not be processed. Please try again or choose Pay Later.</p>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($rzpOrderErr): ?>
        <div class="err-card">
            <h6><i class="bi bi-exclamation-triangle-fill me-1"></i>Gateway Error</h6>
            <p>Could not initiate payment. Please refresh the page or contact support.</p>
            <small class="text-muted" style="font-size:.75rem;">Technical: <?php echo htmlspecialchars($rzpOrderErr); ?></small>
        </div>
        <?php endif; ?>

        <!-- Booking Details -->
        <div class="pay-detail">
            <div class="pay-row">
                <span class="label"><i class="bi bi-person"></i> Customer</span>
                <span class="value"><?php echo htmlspecialchars($data['name']); ?></span>
            </div>
            <div class="pay-row">
                <span class="label"><i class="bi bi-wifi"></i> Plan</span>
                <span class="value"><?php echo htmlspecialchars($data['plan']); ?></span>
            </div>
            <div class="pay-row">
                <span class="label"><i class="bi bi-currency-rupee"></i> Amount</span>
                <span class="value amount">₹<?php echo htmlspecialchars($data['price'] ?? '0'); ?></span>
            </div>
        </div>

        <!-- Razorpay trust badge -->
        <div class="rzp-badge">
            <i class="bi bi-shield-lock-fill text-success"></i>
            <span>Secured by <strong>Razorpay</strong> — UPI, Cards, Net Banking, Wallets</span>
        </div>

        <!-- Pay Now via Razorpay -->
        <button id="payNowBtn" class="btn-paynow"
                <?php echo $rzpOrderErr ? 'disabled' : ''; ?>
                onclick="openRazorpay()">
            <i class="bi bi-lightning-charge-fill me-2"></i>Pay Now — ₹<?php echo htmlspecialchars($data['price'] ?? '0'); ?>
        </button>

        <!-- Pay Later -->
        <a href="payment-later.php?id=<?php echo $data['id']; ?>" class="btn-paylater">
            <i class="bi bi-clock me-2"></i>Pay Later
        </a>

        <div class="secure-note">
            <i class="bi bi-shield-lock-fill"></i>
            256-bit SSL encryption — Your payment is 100% secure
        </div>
    </div>
</div>

<!-- Razorpay Checkout SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function openRazorpay() {
    var btn = document.getElementById('payNowBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Opening Payment…';

    var options = {
        key:         '<?php echo RZP_KEY_ID; ?>',
        amount:      '<?php echo $paise; ?>',
        currency:    '<?php echo RZP_CURRENCY; ?>',
        name:        '<?php echo addslashes(RZP_COMPANY); ?>',
        description: '<?php echo addslashes(htmlspecialchars($data['plan'])); ?>',
        image:       '<?php echo RZP_LOGO; ?>',
        order_id:    '<?php echo $rzpOrderId; ?>',

        handler: function(response) {
            // Build a hidden form and POST to verify-payment.php (server-side)
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'verify-payment.php';

            var fields = {
                razorpay_payment_id:  response.razorpay_payment_id,
                razorpay_order_id:    response.razorpay_order_id,
                razorpay_signature:   response.razorpay_signature,
                booking_id:           '<?php echo $id; ?>'
            };

            Object.keys(fields).forEach(function(k) {
                var inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = k;
                inp.value = fields[k];
                form.appendChild(inp);
            });

            document.body.appendChild(form);
            form.submit();
        },

        prefill: {
            name:  '<?php echo addslashes(htmlspecialchars($data['name'])); ?>',
            email: '<?php echo addslashes(htmlspecialchars($data['email'])); ?>'
        },

        notes: {
            booking_id: '<?php echo $id; ?>',
            order_ref:  '<?php echo $orderId; ?>'
        },

        theme: { color: '#e60000' },

        modal: {
            ondismiss: function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-lightning-charge-fill me-2"></i>Pay Now — ₹<?php echo htmlspecialchars($data["price"] ?? "0"); ?>';
            }
        }
    };

    var rzp = new Razorpay(options);

    rzp.on('payment.failed', function(response) {
        window.location.href = 'payment-failed.php?id=<?php echo $id; ?>&reason=' + encodeURIComponent(response.error.description);
    });

    rzp.open();
}
</script>
</body>
</html>
