<?php
// Pre-fill plan from URL
$plan  = htmlspecialchars(trim($_GET['plan']  ?? ''));
$price = htmlspecialchars(trim($_GET['price'] ?? '499'));

// Show server-side error if any
if (session_status() == PHP_SESSION_NONE) session_start();
$errorMsg = '';
if (!empty($_SESSION['form_error'])) {
    $errorMsg = $_SESSION['form_error'];
    unset($_SESSION['form_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Service – NetServe</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- SweetAlert2 — loaded BEFORE body so it's always available -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f2f5 0%, #e8eaf0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }
        .booking-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,.10);
            width: 100%;
            max-width: 560px;
        }
        .booking-header {
            background: linear-gradient(135deg, #e60000, #8b0000);
            padding: 28px 32px;
            color: white;
            border-radius: 20px 20px 0 0;
        }
        .booking-header h4 {
            font-weight: 700;
            font-size: 1.4rem;
            margin: 0 0 4px;
        }
        .booking-header p {
            margin: 0;
            opacity: .8;
            font-size: .9rem;
        }
        .booking-body { padding: 30px 32px; }
        .form-label {
            font-weight: 600;
            font-size: .875rem;
            color: #444;
            margin-bottom: 6px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px 12px 40px;
            border: 1.5px solid #e8e8e8;
            font-size: .92rem;
            transition: border-color .2s, box-shadow .2s;
        }
        textarea.form-control {
            padding-left: 15px;
            resize: none;
        }
        .form-control:focus {
            border-color: #e60000;
            box-shadow: 0 0 0 3px rgba(230,0,0,.12);
        }
        .icon-wrap {
            position: relative;
        }
        .icon-wrap .field-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #ccc;
            font-size: .95rem;
            pointer-events: none;
        }
        .plan-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #e3f2fd;
            color: #1565c0;
            border-radius: 10px;
            padding: 12px 16px;
            font-weight: 600;
            font-size: .9rem;
            border: 1.5px solid #bbdefb;
        }
        .btn-submit {
            background: linear-gradient(135deg, #e60000, #cc0000);
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-weight: 700;
            font-size: 1rem;
            color: white;
            width: 100%;
            cursor: pointer;
            transition: all .25s;
            box-shadow: 0 4px 15px rgba(230,0,0,.3);
            margin-top: 4px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(230,0,0,.4);
        }
        .back-link {
            text-align: center;
            margin-top: 16px;
        }
        .back-link a {
            color: #888;
            font-size: .85rem;
            text-decoration: none;
        }
        .back-link a:hover { color: #e60000; }
    </style>
</head>
<body>

<div class="booking-card">

    <!-- Header -->
    <div class="booking-header">
        <div class="d-flex align-items-center gap-3">
            <div style="width:46px;height:46px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;">
                <i class="bi bi-broadcast"></i>
            </div>
            <div>
                <h4>Book NetServe Service</h4>
                <p>Fill in your details to get started</p>
            </div>
        </div>
    </div>

    <!-- Body -->
    <div class="booking-body">

        <form id="bookingForm" action="submit-form.php" method="POST" onsubmit="return validateAndSubmit()">

            <!-- Name -->
            <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <div class="icon-wrap">
                    <i class="bi bi-person-fill field-icon"></i>
                    <input type="text" name="name" id="fName" class="form-control"
                           placeholder="e.g. Rahul Sharma">
                </div>
            </div>

            <!-- Mobile -->
            <div class="mb-3">
                <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                <div class="icon-wrap">
                    <i class="bi bi-telephone-fill field-icon"></i>
                    <input type="tel" name="mobile" id="fMobile" class="form-control"
                           placeholder="10-digit number starting with 6–9"
                           maxlength="10">
                </div>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                <div class="icon-wrap">
                    <i class="bi bi-envelope-fill field-icon"></i>
                    <input type="text" name="email" id="fEmail" class="form-control"
                           placeholder="e.g. rahul@example.com">
                </div>
            </div>

            <!-- Address -->
            <div class="mb-3">
                <label class="form-label">Service Address <span class="text-danger">*</span></label>
                <textarea name="address" id="fAddress" class="form-control"
                          rows="3"
                          placeholder="Full address for service installation"></textarea>
            </div>

            <div class="mb-4">
                <label class="form-label">Selected Plan</label>
                <div class="plan-badge">
                    <i class="bi bi-wifi"></i>
                    <span><?php echo $plan ?: 'No plan selected'; ?></span>
                </div>
                <input type="hidden" name="plan"   value="<?php echo $plan; ?>">
                <input type="hidden" name="price"  value="<?php echo $price; ?>">
                <input type="hidden" name="source" value="service">
            </div>

            <!-- Submit button -->
        <button type="submit" id="btnSubmit" class="btn-submit">
            <i class="bi bi-arrow-right-circle-fill me-2"></i>Submit Booking
        </button>

        </form>

        <div class="back-link">
            <a href="index.php"><i class="bi bi-arrow-left me-1"></i>Back to Home</a>
        </div>
    </div>

</div>

<!-- ── Server-side error (from redirect) ─── -->
<?php if ($errorMsg): ?>
<script>
    // Runs after page loads
    window.onload = function() {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: <?php echo json_encode($errorMsg); ?>,
            confirmButtonColor: '#e60000'
        });
    };
</script>
<?php endif; ?>

<!-- ── Validation function ──────────────────────────────────── -->
<script>
function validateAndSubmit() {

    // Read values
    var name    = document.getElementById('fName').value.trim();
    var mobile  = document.getElementById('fMobile').value.trim();
    var email   = document.getElementById('fEmail').value.trim();
    var address = document.getElementById('fAddress').value.trim();

    // ── Validation checks ────────────────────────────────────

    // 1. Name
    if (name === '') {
        showErr('Name is required!', 'Please enter your full name.');
        return false;
    }
    if (!/^[a-zA-Z\s]+$/.test(name)) {
        showErr('Invalid Name!', 'Name must contain letters only — no numbers or symbols.');
        return false;
    }
    if (name.length < 3) {
        showErr('Name Too Short!', 'Please enter at least 3 characters.');
        return false;
    }

    // 2. Mobile
    if (mobile === '') {
        showErr('Mobile Required!', 'Please enter your 10-digit mobile number.');
        return false;
    }
    if (!/^[6-9][0-9]{9}$/.test(mobile)) {
        showErr('Invalid Mobile Number!', 'Enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.');
        return false;
    }

    // 3. Email
    if (email === '') {
        showErr('Email Required!', 'Please enter your email address.');
        return false;
    }
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        showErr('Invalid Email!', 'Please enter a valid email — e.g. name@example.com');
        return false;
    }

    // 4. Address
    if (address === '') {
        showErr('Address Required!', 'Please enter your service address.');
        return false;
    }
    if (address.length < 10) {
        showErr('Address Too Short!', 'Please provide a complete address (at least 10 characters).');
        return false;
    }

    // ── All valid — disable button to prevent double-submit, allow form to submit ──
    var btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Submitting...';
    return true;
}

function showErr(title, message) {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonColor: '#e60000',
        confirmButtonText: 'OK, Fix It',
        backdrop: true
    });
}
</script>

</body>
</html>
