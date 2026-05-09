<?php
/**
 * mailer.php — Reusable PHPMailer helper for NetServe
 * Include this after config.php has been loaded.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes (manual include — no Composer)
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

/**
 * Create a pre-configured PHPMailer instance ready for sending.
 *
 * @return PHPMailer
 */
function createMailer(): PHPMailer
{
    $mail = new PHPMailer(true); // true = throw exceptions

    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;          // smtp.gmail.com
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;          // Gmail address
    $mail->Password   = SMTP_PASS;          // App password (16-char)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // 'tls' via port 587
    $mail->Port       = SMTP_PORT;          // 587

    // Sender identity
    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);

    // Encoding
    $mail->CharSet  = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->isHTML(true);

    return $mail;
}

/**
 * Shared branded email wrapper.
 * Wraps $innerHtml inside the NetServe header/footer template.
 *
 * @param  string $innerHtml
 * @return string
 */
function wrapEmail(string $innerHtml): string
{
    $year = date('Y');
    return "
    <div style='font-family:Inter,Arial,sans-serif;max-width:560px;margin:0 auto;background:#f4f4f4;padding:20px;'>
      <div style='background:linear-gradient(135deg,#e60000,#8b0000);padding:30px 32px;border-radius:14px 14px 0 0;text-align:center;'>
        <h2 style='color:#ffffff;margin:0;font-size:1.5rem;font-weight:800;letter-spacing:1px;'>🌐 NetServe</h2>
        <p style='color:rgba(255,255,255,.8);margin:6px 0 0;font-size:.85rem;'>Your Trusted Broadband &amp; Telecom Partner</p>
      </div>
      <div style='background:#ffffff;padding:34px 32px;border-radius:0 0 14px 14px;border:1px solid #e8e8e8;'>
        {$innerHtml}
        <hr style='border:none;border-top:1px solid #eee;margin:28px 0 18px;'>
        <p style='color:#bbb;font-size:.78rem;text-align:center;margin:0;'>
          © {$year} NetServe Services. All rights reserved.<br>
          This is an automated message — please do not reply.
        </p>
      </div>
    </div>
    ";
}

/**
 * Send a booking confirmation email (triggered on booking creation).
 *
 * @param  string $toEmail
 * @param  string $toName
 * @param  string $plan
 * @param  string $price
 * @param  string $orderId
 * @return bool   true on success, false on failure
 */
function sendBookingConfirmation(string $toEmail, string $toName, string $plan, string $price, string $orderId): bool
{
    try {
        $mail = createMailer();
        $mail->addAddress($toEmail, $toName);
        $mail->Subject = "Booking Received – NetServe ({$orderId})";

        $inner = "
          <p style='color:#333;font-size:1rem;margin:0 0 12px;'>Hello <strong>" . htmlspecialchars($toName) . "</strong>,</p>
          <p style='color:#555;margin:0 0 20px;'>Thank you for booking with NetServe! We have received your request and will review it shortly.</p>

          <div style='background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:24px;'>
            <table style='width:100%;border-collapse:collapse;font-size:.92rem;'>
              <tr><td style='padding:8px 0;color:#888;'>Order ID</td><td style='text-align:right;font-weight:700;color:#e60000;'>{$orderId}</td></tr>
              <tr style='border-top:1px solid #eee;'><td style='padding:8px 0;color:#888;'>Plan</td><td style='text-align:right;font-weight:700;color:#333;'>" . htmlspecialchars($plan) . "</td></tr>
              <tr style='border-top:1px solid #eee;'><td style='padding:8px 0;color:#888;'>Amount</td><td style='text-align:right;font-weight:800;font-size:1.1rem;color:#2ecc71;'>₹{$price}</td></tr>
              <tr style='border-top:1px solid #eee;'><td style='padding:8px 0;color:#888;'>Status</td><td style='text-align:right;'><span style='background:#fff3cd;color:#856404;border-radius:6px;padding:2px 10px;font-size:.82rem;font-weight:700;'>⏳ Pending</span></td></tr>
            </table>
          </div>

          <p style='color:#555;margin:0 0 10px;'>Our team will review your booking and notify you once it is approved.</p>
          <p style='color:#888;font-size:.85rem;margin:0;'>If you have any questions, please contact our support team.</p>
        ";

        $mail->Body    = wrapEmail($inner);
        $mail->AltBody = "Hello {$toName},\n\nYour booking (Order ID: {$orderId}) for plan '{$plan}' (₹{$price}) has been received and is under review.\n\nThank you for choosing NetServe!";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("[NetServe Mailer] Booking confirmation failed for {$toEmail}: " . $e->getMessage());
        return false;
    }
}

/**
 * Send a payment confirmation email (triggered after successful payment).
 *
 * @param  string $toEmail
 * @param  string $toName
 * @param  string $plan
 * @param  string $price
 * @param  string $orderId
 * @return bool
 */
function sendPaymentConfirmation(string $toEmail, string $toName, string $plan, string $price, string $orderId): bool
{
    try {
        $mail = createMailer();
        $mail->addAddress($toEmail, $toName);
        $mail->Subject = "✅ Payment Successful – NetServe ({$orderId})";

        $inner = "
          <div style='text-align:center;margin-bottom:24px;'>
            <div style='display:inline-block;background:linear-gradient(135deg,#2ecc71,#27ae60);border-radius:50%;width:72px;height:72px;line-height:72px;font-size:2rem;color:#fff;'>✓</div>
            <h3 style='color:#1a1a1a;margin:14px 0 4px;font-size:1.4rem;'>Payment Successful!</h3>
            <p style='color:#888;margin:0;font-size:.9rem;'>Your payment has been processed successfully.</p>
          </div>

          <div style='background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;padding:20px;margin-bottom:24px;'>
            <table style='width:100%;border-collapse:collapse;font-size:.92rem;'>
              <tr><td style='padding:8px 0;color:#4b5563;'>Order ID</td><td style='text-align:right;font-weight:700;color:#16a34a;'>{$orderId}</td></tr>
              <tr style='border-top:1px solid #d1fae5;'><td style='padding:8px 0;color:#4b5563;'>Customer</td><td style='text-align:right;font-weight:700;color:#333;'>" . htmlspecialchars($toName) . "</td></tr>
              <tr style='border-top:1px solid #d1fae5;'><td style='padding:8px 0;color:#4b5563;'>Plan</td><td style='text-align:right;font-weight:700;color:#333;'>" . htmlspecialchars($plan) . "</td></tr>
              <tr style='border-top:1px solid #d1fae5;'><td style='padding:8px 0;color:#4b5563;'>Amount Paid</td><td style='text-align:right;font-weight:800;font-size:1.2rem;color:#15803d;'>₹{$price}</td></tr>
              <tr style='border-top:1px solid #d1fae5;'><td style='padding:8px 0;color:#4b5563;'>Status</td><td style='text-align:right;'><span style='background:#dcfce7;color:#15803d;border-radius:6px;padding:2px 10px;font-size:.82rem;font-weight:700;'>✅ Paid</span></td></tr>
            </table>
          </div>

          <p style='color:#555;margin:0 0 10px;'>Your booking is now confirmed. Our team will activate your service as soon as your booking is reviewed and approved by an admin.</p>
          <p style='color:#888;font-size:.85rem;margin:0;'>Thank you for choosing NetServe!</p>
        ";

        $mail->Body    = wrapEmail($inner);
        $mail->AltBody = "Hello {$toName},\n\nYour payment for Order ID: {$orderId} (Plan: {$plan}, Amount: ₹{$price}) has been received successfully. Your service will be activated soon.\n\nThank you for choosing NetServe!";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("[NetServe Mailer] Payment confirmation failed for {$toEmail}: " . $e->getMessage());
        return false;
    }
}

/**
 * Send a booking status update email (Approved / Rejected).
 *
 * @param  string $toEmail
 * @param  string $toName
 * @param  string $plan
 * @param  string $status   'Approved' or 'Rejected'
 * @return bool
 */
function sendStatusUpdate(string $toEmail, string $toName, string $plan, string $status): bool
{
    try {
        $mail = createMailer();
        $mail->addAddress($toEmail, $toName);
        $mail->Subject = "Your NetServe Booking has been {$status}";

        $isApproved   = ($status === 'Approved');
        $statusColor  = $isApproved ? '#2ecc71' : '#e74c3c';
        $statusBg     = $isApproved ? '#f0fdf4'  : '#fff5f5';
        $statusBorder = $isApproved ? '#bbf7d0'  : '#fecaca';
        $statusEmoji  = $isApproved ? '✅' : '❌';
        $statusMsg    = $isApproved
            ? 'Great news! Your service will be activated shortly by our team.'
            : 'Unfortunately your booking could not be approved at this time. Please contact support or re-apply.';

        $inner = "
          <p style='color:#333;font-size:1rem;margin:0 0 12px;'>Hello <strong>" . htmlspecialchars($toName) . "</strong>,</p>
          <p style='color:#555;margin:0 0 20px;'>Your booking for the plan <strong>" . htmlspecialchars($plan) . "</strong> has been:</p>

          <div style='background:{$statusBg};border:2px solid {$statusBorder};border-radius:12px;padding:20px;text-align:center;margin:0 0 24px;'>
            <span style='font-size:2rem;'>{$statusEmoji}</span>
            <div style='font-size:1.5rem;font-weight:800;color:{$statusColor};margin-top:8px;'>{$status}</div>
          </div>

          <p style='color:#555;margin:0 0 10px;'>{$statusMsg}</p>
          <p style='color:#888;font-size:.85rem;margin:0;'>If you have any questions, please contact our support team.</p>
        ";

        $mail->Body    = wrapEmail($inner);
        $mail->AltBody = "Hello {$toName},\n\nYour booking for plan '{$plan}' has been {$status}.\n\n{$statusMsg}\n\nThank you for choosing NetServe!";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("[NetServe Mailer] Status update failed for {$toEmail}: " . $e->getMessage());
        return false;
    }
}
