<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Confirmed – NetServe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 30px 15px;
        }
        .ty-card {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 8px 36px rgba(0,0,0,.1);
            width: 100%; max-width: 460px;
            padding: 50px 40px;
            text-align: center;
        }
        .ty-icon {
            width: 88px; height: 88px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
            font-size: 2.4rem; color: white;
            box-shadow: 0 8px 24px rgba(46,204,113,.35);
            animation: pop .5s ease;
        }
        @keyframes pop {
            0%   { transform: scale(0.5); opacity: 0; }
            80%  { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
        .ty-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 10px;
        }
        .ty-subtitle {
            color: #777;
            font-size: .95rem;
            margin-bottom: 32px;
            line-height: 1.6;
        }
        .btn-home {
            background: linear-gradient(135deg, #e60000, #cc0000);
            border: none; border-radius: 12px; padding: 14px 30px;
            font-weight: 700; font-size: 1rem; color: white;
            transition: all .25s;
            box-shadow: 0 4px 15px rgba(230,0,0,.3);
            text-decoration: none; display: inline-block;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 22px rgba(230,0,0,.4);
            color: white;
        }
        .ty-note {
            margin-top: 22px;
            color: #bbb;
            font-size: .8rem;
        }
    </style>
</head>
<body>
<div class="ty-card">
    <div class="ty-icon">
        <i class="bi bi-check-lg"></i>
    </div>
    <h2 class="ty-title">Booking Confirmed!</h2>
    <p class="ty-subtitle">
        Thank you! Your service booking has been submitted successfully.<br>
        Our team will review and process your request shortly.
    </p>
    <a href="index.php" class="btn-home">
        <i class="bi bi-house-fill me-2"></i>Back to Home
    </a>
    <p class="ty-note">
        <i class="bi bi-envelope me-1"></i>
        You'll receive an email notification once your booking is approved.
    </p>
</div>
</body>
</html>
