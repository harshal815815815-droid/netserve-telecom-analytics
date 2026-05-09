<?php
/**
 * verify-payment.php
 * ───────────────────
 * Server-side Razorpay payment verification.
 * Called via POST from the Razorpay checkout handler in payment.php.
 *
 * Security:
 *  - HMAC-SHA256 signature verification (Razorpay standard)
 *  - Booking ID validated from DB
 *  - Duplicate payment prevention (payment_verified check)
 *  - All DB writes use prepared statements
 */
if (session_status() == PHP_SESSION_NONE) session_start();

require_once 'config.php';
require_once 'razorpay-config.php';
include 'db.php';
include 'includes/mailer.php';

// ── Razorpay SDK ──────────────────────────────────────────────
require_once __DIR__ . '/Razorpay/Razorpay.php';
use Razorpay\Api\Api;

// ── Accept only POST ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

// ── Read & validate POST fields ───────────────────────────────
$rzpPaymentId = trim($_POST['razorpay_payment_id'] ?? '');
$rzpOrderId   = trim($_POST['razorpay_order_id']   ?? '');
$rzpSignature = trim($_POST['razorpay_signature']  ?? '');
$bookingId    = intval($_POST['booking_id']         ?? 0);

if (!$rzpPaymentId || !$rzpOrderId || !$rzpSignature || $bookingId <= 0) {
    header('Location: payment-failed.php?reason=' . urlencode('Invalid payment response received.'));
    exit();
}

// ── Fetch booking from DB ─────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT id, name, email, plan, price, payment_status, payment_verified, razorpay_order_id
     FROM bookings WHERE id=?"
);
$stmt->bind_param('i', $bookingId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header('Location: payment-failed.php?reason=' . urlencode('Booking not found.'));
    exit();
}
$booking = $res->fetch_assoc();

// ── Duplicate payment guard ───────────────────────────────────
if ($booking['payment_verified'] == 1 || $booking['payment_status'] === 'Paid') {
    // Already verified — just redirect to success safely
    header('Location: payment-success.php?id=' . $bookingId);
    exit();
}

// ── Verify that the Razorpay order_id matches what we stored ──
if ($booking['razorpay_order_id'] !== $rzpOrderId) {
    error_log("[NetServe RZP] Order ID mismatch for booking #{$bookingId}. Expected: {$booking['razorpay_order_id']}, Got: {$rzpOrderId}");
    header('Location: payment-failed.php?id=' . $bookingId . '&reason=' . urlencode('Order verification failed. Please contact support.'));
    exit();
}

// ── HMAC-SHA256 Signature Verification ───────────────────────
$expectedSignature = hash_hmac(
    'sha256',
    $rzpOrderId . '|' . $rzpPaymentId,
    RZP_KEY_SECRET
);

if (!hash_equals($expectedSignature, $rzpSignature)) {
    error_log("[NetServe RZP] Signature mismatch for booking #{$bookingId}. Payment ID: {$rzpPaymentId}");
    header('Location: payment-failed.php?id=' . $bookingId . '&reason=' . urlencode('Payment signature verification failed. Transaction rejected.'));
    exit();
}

// ── Fetch payment details from Razorpay API ───────────────────
try {
    $api     = new Api(RZP_KEY_ID, RZP_KEY_SECRET);
    $payment = $api->payment->fetch($rzpPaymentId);
    $method  = $payment['method'] ?? 'razorpay';   // card / upi / netbanking / wallet etc.
} catch (\Exception $e) {
    error_log("[NetServe RZP] Payment fetch failed for {$rzpPaymentId}: " . $e->getMessage());
    $method = 'razorpay';  // fallback — signature already verified so we trust it
}

// ── Update booking in DB ──────────────────────────────────────
$paidAt = date('Y-m-d H:i:s');

$upd = $conn->prepare(
    "UPDATE bookings
     SET payment_status='Paid',
         payment_verified=1,
         razorpay_payment_id=?,
         payment_method=?,
         paid_at=?
     WHERE id=? AND payment_verified=0"
    // The AND payment_verified=0 prevents double-update race condition
);
$upd->bind_param('sssi', $rzpPaymentId, $method, $paidAt, $bookingId);
$upd->execute();

// ── Order ID for email ────────────────────────────────────────
$orderDate = date('Ymd', strtotime($booking['created_at'] ?? 'now'));
// Re-fetch created_at since it wasn't in the SELECT above — or compute from booking id
$orderDate = date('Ymd');
$displayOrderId = 'NS-' . $orderDate . '-' . str_pad($bookingId, 4, '0', STR_PAD_LEFT);

// ── Send payment confirmation email ──────────────────────────
if (!empty($booking['email'])) {
    sendRazorpayConfirmation(
        $booking['email'],
        $booking['name'],
        $booking['plan'],
        $booking['price'],
        $displayOrderId,
        $rzpPaymentId,
        ucfirst($method)
    );
}

// ── Store verified payment info in session for success page ──
$_SESSION['rzp_verified'] = [
    'booking_id'  => $bookingId,
    'payment_id'  => $rzpPaymentId,
    'order_id'    => $rzpOrderId,
    'method'      => ucfirst($method),
    'paid_at'     => $paidAt,
];

// ── Redirect to success page ──────────────────────────────────
header('Location: payment-success.php?id=' . $bookingId);
exit();

// ════════════════════════════════════════════════════════════════
// Helper: Enhanced payment confirmation email (Razorpay version)
// ════════════════════════════════════════════════════════════════
function sendRazorpayConfirmation(
    string $toEmail,
    string $toName,
    string $plan,
    string $price,
    string $orderId,
    string $paymentId,
    string $method
): bool {
    try {
        $mail = createMailer();
        $mail->addAddress($toEmail, $toName);
        $mail->Subject = "✅ Payment Successful – NetServe ({$orderId})";

        $inner = "
          <div style='text-align:center;margin-bottom:24px;'>
            <div style='display:inline-block;background:linear-gradient(135deg,#2ecc71,#27ae60);border-radius:50%;width:72px;height:72px;line-height:72px;font-size:2rem;color:#fff;'>✓</div>
            <h3 style='color:#1a1a1a;margin:14px 0 4px;font-size:1.4rem;'>Payment Successful!</h3>
            <p style='color:#888;margin:0;font-size:.9rem;'>Your payment via Razorpay has been processed.</p>
          </div>

          <div style='background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;padding:20px;margin-bottom:24px;'>
            <table style='width:100%;border-collapse:collapse;font-size:.92rem;'>
              <tr><td style='padding:8px 0;color:#4b5563;'>Order ID</td><td style='text-align:right;font-weight:700;color:#16a34a;'>{$orderId}</td></tr>
              <tr style='border-top:1px solid #d1fae5;'><td style='padding:8px 0;color:#4b5563;'>Payment ID</td><td style='text-align:right;font-weight:600;color:#333;font-size:.85rem;'>{$paymentId}</td></tr>
              <tr style='border-top:1px solid #d1fae5;'><td style='padding:8px 0;color:#4b5563;'>Customer</td><td style='text-align:right;font-weight:700;color:#333;'>" . htmlspecialchars($toName) . "</td></tr>
              <tr style='border-top:1px solid #d1fae5;'><td style='padding:8px 0;color:#4b5563;'>Plan</td><td style='text-align:right;font-weight:700;color:#333;'>" . htmlspecialchars($plan) . "</td></tr>
              <tr style='border-top:1px solid #d1fae5;'><td style='padding:8px 0;color:#4b5563;'>Amount Paid</td><td style='text-align:right;font-weight:800;font-size:1.2rem;color:#15803d;'>₹{$price}</td></tr>
              <tr style='border-top:1px solid #d1fae5;'><td style='padding:8px 0;color:#4b5563;'>Method</td><td style='text-align:right;font-weight:600;color:#333;'>{$method}</td></tr>
              <tr style='border-top:1px solid #d1fae5;'><td style='padding:8px 0;color:#4b5563;'>Status</td><td style='text-align:right;'><span style='background:#dcfce7;color:#15803d;border-radius:6px;padding:2px 10px;font-size:.82rem;font-weight:700;'>✅ Verified</span></td></tr>
            </table>
          </div>

          <p style='color:#555;margin:0 0 10px;'>Your booking is confirmed. Our team will activate your service shortly.</p>
          <p style='color:#888;font-size:.85rem;margin:0;'>Thank you for choosing NetServe!</p>
        ";

        $mail->Body    = wrapEmail($inner);
        $mail->AltBody = "Hello {$toName},\n\nPayment Successful!\nOrder ID: {$orderId}\nPayment ID: {$paymentId}\nPlan: {$plan}\nAmount: ₹{$price}\nMethod: {$method}\n\nThank you for choosing NetServe!";

        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log("[NetServe Mailer] Razorpay confirmation failed for {$toEmail}: " . $e->getMessage());
        return false;
    }
}
