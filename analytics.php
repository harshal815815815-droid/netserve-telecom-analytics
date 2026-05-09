<?php
// Session for compatibility only (no login required for demo)
if (session_status() == PHP_SESSION_NONE) session_start();
require_once 'config.php';
include 'db.php';

// ── Page variables (for sidebar) ────────────────────────────────
$activePage = 'analytics';
$pageTitle  = 'Data Analytics';

// ── Run Python script to regenerate charts ──────────────────────
$scriptPath = __DIR__ . '/analytics/generate_charts.py';
$output     = shell_exec('python "' . $scriptPath . '" 2>&1');
if ($output && (strpos($output, "not recognized") !== false || strpos($output, "No such file") !== false)) {
    $output = shell_exec('python3 "' . $scriptPath . '" 2>&1');
}

// ── PHP-side live stats ─────────────────────────────────────────
$total    = (int)$conn->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c'];
$paid     = (int)$conn->query("SELECT COUNT(*) c FROM bookings WHERE payment_status='Paid'")->fetch_assoc()['c'];
$pending  = (int)$conn->query("SELECT COUNT(*) c FROM bookings WHERE payment_status='Pending'")->fetch_assoc()['c'];
$payLater = (int)$conn->query("SELECT COUNT(*) c FROM bookings WHERE payment_status='Pay Later'")->fetch_assoc()['c'];
$approved = (int)$conn->query("SELECT COUNT(*) c FROM bookings WHERE status='Approved'")->fetch_assoc()['c'];
$rejected = (int)$conn->query("SELECT COUNT(*) c FROM bookings WHERE status='Rejected'")->fetch_assoc()['c'];

$topPlanRow = $conn->query("SELECT plan, COUNT(*) c FROM bookings GROUP BY plan ORDER BY c DESC LIMIT 1")->fetch_assoc();
$topPlan    = $topPlanRow ? $topPlanRow['plan'] : 'N/A';
$topPlanCnt = $topPlanRow ? (int)$topPlanRow['c'] : 0;
$paidPct    = $total > 0 ? round(($paid / $total) * 100) : 0;

// Today's bookings
$today      = (int)$conn->query("SELECT COUNT(*) c FROM bookings WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['c'];

// ── ML predictions ──────────────────────────────────────────────
$jsonPath      = __DIR__ . '/analytics/ml_predictions.json';
$ml            = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];
$generatedAt   = $ml['generated_at']           ?? 'Not generated yet';
$planPreds     = $ml['plan_predictions']        ?? [];
$payPreds      = $ml['payment_predictions']     ?? [];
$forecastAvg   = $ml['forecast_avg_daily']      ?? 'N/A';
$forecastPrd   = $ml['forecast_period']         ?? 'N/A';
$planAcc       = $ml['plan_model_accuracy']     ?? 'N/A';
$payAcc        = $ml['payment_model_accuracy']  ?? 'N/A';

// Helper: check chart exists
function chart(string $name): ?string {
    $path = __DIR__ . '/analytics/' . $name;
    return file_exists($path) ? 'analytics/' . $name . '?v=' . time() : null;
}

// ── Chart.js live data ─────────────────────────────────────────
// Bar chart — Plan Popularity
$planRows = $conn->query("SELECT plan, COUNT(*) AS cnt FROM bookings GROUP BY plan ORDER BY cnt DESC");
$cjsPlanLabels = [];
$cjsPlanCounts = [];
if ($planRows) {
    while ($r = $planRows->fetch_assoc()) {
        $cjsPlanLabels[] = $r['plan'];
        $cjsPlanCounts[] = (int)$r['cnt'];
    }
}

// Pie chart — Payment Status Distribution
$payRows = $conn->query("SELECT payment_status, COUNT(*) AS cnt FROM bookings GROUP BY payment_status");
$cjsPayLabels = [];
$cjsPayCounts = [];
if ($payRows) {
    while ($r = $payRows->fetch_assoc()) {
        $cjsPayLabels[] = $r['payment_status'];
        $cjsPayCounts[] = (int)$r['cnt'];
    }
}

// Line chart — Booking Trend (last 30 days)
$trendRows = $conn->query("SELECT DATE(created_at) AS day, COUNT(*) AS cnt FROM bookings WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY day ORDER BY day ASC");
$cjsTrendLabels = [];
$cjsTrendCounts = [];
if ($trendRows) {
    while ($r = $trendRows->fetch_assoc()) {
        $cjsTrendLabels[] = $r['day'];
        $cjsTrendCounts[] = (int)$r['cnt'];
    }
}
?>
<?php include 'includes/sidebar.php'; ?>

<!-- ── Analytics-specific styles ──────────────────────────────── -->
<style>
/* ── Hero Banner ── */
.an-hero {
    background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
    border-radius: 20px;
    padding: 32px 36px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
}
.an-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 260px; height: 260px;
    background: rgba(230,0,0,0.12);
    border-radius: 50%;
}
.an-hero::after {
    content: '';
    position: absolute;
    bottom: -80px; left: 40%;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
}
.an-hero-title {
    font-size: 1.55rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 6px;
    letter-spacing: -0.3px;
}
.an-hero-sub {
    font-size: 0.87rem;
    color: rgba(255,255,255,0.55);
    margin-bottom: 0;
}
.an-hero-badge {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.7);
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 0.78rem;
    backdrop-filter: blur(4px);
}

/* ── Section Heading ── */
.an-section-heading {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    margin-top: 8px;
}
.an-section-heading h5 {
    font-size: 1.05rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0;
    letter-spacing: -0.2px;
}
.an-section-line {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, #e0e0e0, transparent);
}
.an-section-badge {
    background: #fff0f0;
    color: #e60000;
    border-radius: 20px;
    padding: 3px 12px;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    border: 1px solid #ffd0d0;
}
.an-section-icon {
    width: 34px; height: 34px;
    background: linear-gradient(135deg, #e60000, #8b0000);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 0.95rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(230,0,0,0.3);
}

/* ── KPI Summary Cards ── */
.an-kpi-card {
    background: #fff;
    border-radius: 18px;
    padding: 22px 24px 20px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    position: relative;
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    border: 1px solid #f5f5f5;
    height: 100%;
}
.an-kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.12);
}
.an-kpi-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 4px;
    border-radius: 18px 18px 0 0;
}
.kpi-blue::after   { background: linear-gradient(90deg, #4a90e2, #357abd); }
.kpi-green::after  { background: linear-gradient(90deg, #2ecc71, #27ae60); }
.kpi-orange::after { background: linear-gradient(90deg, #f39c12, #e67e22); }
.kpi-red::after    { background: linear-gradient(90deg, #e74c3c, #c0392b); }
.kpi-purple::after { background: linear-gradient(90deg, #9b59b6, #8e44ad); }
.kpi-teal::after   { background: linear-gradient(90deg, #1abc9c, #16a085); }

.an-kpi-label {
    font-size: 0.73rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #aaa;
    margin-bottom: 10px;
}
.an-kpi-value {
    font-size: 2.2rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 6px;
}
.kpi-blue .an-kpi-value   { color: #4a90e2; }
.kpi-green .an-kpi-value  { color: #2ecc71; }
.kpi-orange .an-kpi-value { color: #f39c12; }
.kpi-red .an-kpi-value    { color: #e74c3c; }
.kpi-purple .an-kpi-value { color: #9b59b6; }
.kpi-teal .an-kpi-value   { color: #1abc9c; }

.an-kpi-sub {
    font-size: 0.78rem;
    color: #bbb;
}
.an-kpi-icon-bg {
    position: absolute;
    right: 18px; top: 50%;
    transform: translateY(-50%);
    font-size: 3rem;
    opacity: 0.06;
}

/* ── Popular Plan Highlight Card ── */
.an-plan-card {
    background: linear-gradient(135deg, #c0392b 0%, #8e044e 100%);
    border-radius: 18px;
    padding: 26px 28px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(192,57,43,0.35);
}
.an-plan-card::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 160px; height: 160px;
    background: rgba(255,255,255,0.07);
    border-radius: 50%;
}
.an-plan-card .trophy-bg {
    position: absolute;
    right: 22px; bottom: 10px;
    font-size: 5rem;
    opacity: 0.12;
    color: #fff;
}
.an-plan-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: rgba(255,255,255,0.6);
    margin-bottom: 6px;
}
.an-plan-name {
    font-size: 1.35rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 4px;
}
.an-plan-count {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.75);
}

/* ── Quick Metric Cards ── */
.an-metric-card {
    background: #fff;
    border-radius: 16px;
    padding: 22px 24px;
    box-shadow: 0 2px 14px rgba(0,0,0,0.06);
    height: 100%;
    border: 1px solid #f0f0f0;
}
.an-metric-label {
    font-size: 0.78rem;
    color: #999;
    font-weight: 600;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.an-metric-value {
    font-size: 1.9rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 10px;
}
.an-metric-sub {
    font-size: 0.75rem;
    color: #bbb;
}
.an-progress { height: 6px; border-radius: 6px; background: #f0f0f0; }
.an-progress-bar { height: 100%; border-radius: 6px; }

/* ── Chart Cards ── */
.an-chart-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    border: 1px solid #f0f0f0;
    height: 100%;
}
.an-chart-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.11);
}
.an-chart-header {
    background: #fafafa;
    border-bottom: 1px solid #f0f0f0;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.an-chart-header-icon {
    width: 28px; height: 28px;
    background: linear-gradient(135deg, #e60000, #8b0000);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 0.8rem;
    flex-shrink: 0;
}
.an-chart-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}
.an-chart-tag {
    margin-left: auto;
    background: #f5f5f5;
    color: #888;
    border-radius: 12px;
    padding: 2px 10px;
    font-size: 0.7rem;
    font-weight: 600;
}
.an-chart-img {
    width: 100%;
    height: auto;
    display: block;
    padding: 12px;
}
.an-chart-placeholder {
    background: #f8f9fa;
    border: 2px dashed #e0e0e0;
    border-radius: 12px;
    margin: 14px;
    padding: 50px 20px;
    text-align: center;
    color: #ccc;
}
.an-chart-placeholder i { font-size: 2.5rem; display: block; margin-bottom: 12px; }
.an-chart-placeholder p { font-size: 0.85rem; margin: 0; }

/* ── ML Accuracy Badges ── */
.an-acc-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #15803d;
    border-radius: 24px;
    padding: 7px 16px;
    font-size: 0.82rem;
    font-weight: 600;
}
.an-acc-pill i { color: #16a34a; }

/* ── Prediction Table Rows ── */
.an-pred-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-radius: 10px;
    background: #fafafa;
    border: 1px solid #f0f0f0;
    margin-bottom: 8px;
    transition: background 0.2s;
}
.an-pred-row:hover { background: #fff5f5; border-color: #ffd0d0; }
.an-pred-day {
    font-size: 0.85rem;
    font-weight: 700;
    color: #1a1a2e;
}
.an-pred-plan {
    font-size: 0.8rem;
    color: #555;
    max-width: 60%;
    text-align: right;
}
.an-pred-badge {
    display: inline-block;
    border-radius: 20px;
    padding: 3px 12px;
    font-size: 0.72rem;
    font-weight: 700;
}
.badge-paid     { background: #dcfce7; color: #16a34a; }
.badge-pending  { background: #fef9c3; color: #ca8a04; }
.badge-paylater { background: #ffedd5; color: #c2410c; }

/* ── Forecast Info Strip ── */
.an-forecast-strip {
    background: linear-gradient(135deg, #1e3a5f, #1a1a2e);
    border-radius: 16px;
    padding: 22px 26px;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
.an-forecast-icon {
    width: 52px; height: 52px;
    background: rgba(255,255,255,0.1);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: #60a5fa;
    font-size: 1.5rem;
    flex-shrink: 0;
}
.an-forecast-label { font-size: 0.78rem; color: rgba(255,255,255,0.5); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 1px; }
.an-forecast-val   { font-size: 1.4rem; font-weight: 800; color: #fff; line-height: 1; }
.an-forecast-sub   { font-size: 0.75rem; color: rgba(255,255,255,0.4); margin-top: 3px; }
.an-forecast-divider { width: 1px; height: 40px; background: rgba(255,255,255,0.1); }

/* ── Suggestion Cards ── */
.an-suggest-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 14px rgba(0,0,0,0.06);
    border: 1px solid #f0f0f0;
    height: 100%;
    transition: transform 0.25s, box-shadow 0.25s;
}
.an-suggest-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}
.an-suggest-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
    margin-bottom: 14px;
    flex-shrink: 0;
}
.an-suggest-title { font-size: 0.9rem; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; }
.an-suggest-desc  { font-size: 0.8rem; color: #888; line-height: 1.5; margin: 0; }

/* ── Console ── */
.an-console {
    background: #0d1117;
    color: #7ee787;
    font-family: 'Courier New', monospace;
    font-size: 0.8rem;
    border-radius: 14px;
    padding: 18px;
    max-height: 200px;
    overflow-y: auto;
    white-space: pre-wrap;
    border: 1px solid #21262d;
    box-shadow: inset 0 2px 8px rgba(0,0,0,0.3);
}
.an-console-header {
    background: #161b22;
    border-radius: 14px 14px 0 0;
    padding: 10px 18px;
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #21262d;
    border-bottom: none;
}
.an-console-dot { width: 10px; height: 10px; border-radius: 50%; }
.an-console + .an-console { border-radius: 0 0 14px 14px; }

/* ── Divider ── */
.an-divider { border: none; border-top: 1px solid #f0f0f0; margin: 32px 0; }

/* Animation */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.an-animate { animation: fadeInUp 0.4s ease both; }
.an-delay-1 { animation-delay: 0.05s; }
.an-delay-2 { animation-delay: 0.10s; }
.an-delay-3 { animation-delay: 0.15s; }
.an-delay-4 { animation-delay: 0.20s; }
</style>

<!-- ═══════════════════════════════════════════════
     ①  HERO BANNER
═══════════════════════════════════════════════ -->
<div class="an-hero an-animate">
    <div class="row align-items-center g-3">
        <div class="col">
            <div class="an-hero-title">
                <i class="bi bi-bar-chart-line-fill me-2" style="color:#f87171;"></i>Analytics &amp; ML Predictions
            </div>
            <p class="an-hero-sub">
                Exploratory Data Analysis + Machine Learning on booking data
            </p>
            <span class="an-hero-badge mt-2 d-inline-block">
                <i class="bi bi-clock-history me-1"></i>Last run: <?php echo htmlspecialchars($generatedAt); ?>
            </span>
        </div>
        <div class="col-auto d-flex gap-2 flex-wrap">
            <a href="?refresh=1" class="btn btn-sm px-3 py-2 fw-600" style="background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.2);border-radius:10px;font-size:0.83rem;backdrop-filter:blur(4px);">
                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
            </a>
            <a href="view-booking.php" class="btn btn-sm px-3 py-2" style="background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.7);border:1px solid rgba(255,255,255,0.12);border-radius:10px;font-size:0.83rem;">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     ②  ANALYTICS OVERVIEW — Section Heading
═══════════════════════════════════════════════ -->
<div class="an-section-heading an-animate an-delay-1">
    <div class="an-section-icon"><i class="bi bi-grid-fill"></i></div>
    <h5>Analytics Overview</h5>
    <span class="an-section-line"></span>
    <span class="an-section-badge">Live Data</span>
</div>

<!-- Popular Plan + Quick Metrics Row -->
<div class="row g-3 mb-4 an-animate an-delay-1">
    <!-- Most Popular Plan (wide card) -->
    <div class="col-xl-4 col-lg-5">
        <div class="an-plan-card h-100">
            <div class="an-plan-label">🏆 Most Popular Plan</div>
            <div class="an-plan-name"><?php echo htmlspecialchars($topPlan); ?></div>
            <div class="an-plan-count"><?php echo $topPlanCnt; ?> bookings out of <?php echo $total; ?> total</div>
            <div class="trophy-bg"><i class="bi bi-trophy-fill"></i></div>
        </div>
    </div>

    <!-- Payment Success Rate -->
    <div class="col-xl-4 col-lg-3 col-md-6">
        <div class="an-metric-card">
            <div class="an-metric-label"><i class="bi bi-pie-chart-fill text-success"></i> Payment Success Rate</div>
            <div class="an-metric-value" style="color:#16a34a;"><?php echo $paidPct; ?>%</div>
            <div class="an-progress mb-2">
                <div class="an-progress-bar" style="width:<?php echo $paidPct; ?>%;background:linear-gradient(90deg,#2ecc71,#27ae60);"></div>
            </div>
            <div class="an-metric-sub"><?php echo $paid; ?> paid out of <?php echo $total; ?> total bookings</div>
        </div>
    </div>

    <!-- Today's Bookings -->
    <div class="col-xl-2 col-lg-2 col-md-6">
        <div class="an-metric-card text-center">
            <div class="an-metric-label justify-content-center"><i class="bi bi-calendar-check text-primary"></i> Today</div>
            <div class="an-metric-value" style="color:#4a90e2;"><?php echo $today; ?></div>
            <div class="an-metric-sub">New bookings today</div>
        </div>
    </div>

    <!-- ML Forecast -->
    <div class="col-xl-2 col-lg-2 col-md-6">
        <div class="an-metric-card text-center">
            <div class="an-metric-label justify-content-center"><i class="bi bi-graph-up-arrow text-danger"></i> ML Forecast</div>
            <div class="an-metric-value" style="color:#e60000;"><?php echo is_numeric($forecastAvg) ? $forecastAvg : '—'; ?></div>
            <div class="an-metric-sub">avg bookings/day</div>
        </div>
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-5 an-animate an-delay-2">
    <?php
    $kpiCards = [
        ['kpi-blue',   'bi-journal-text',       'Total Bookings',   $total,    'All-time records'],
        ['kpi-green',  'bi-check-circle-fill',  'Paid',             $paid,     'Completed payments'],
        ['kpi-orange', 'bi-clock-history',      'Pending Payment',  $pending,  'Awaiting settlement'],
        ['kpi-purple', 'bi-wallet2',            'Pay Later',        $payLater, 'Deferred billing'],
        ['kpi-teal',   'bi-patch-check-fill',   'Approved',         $approved, 'Service activated'],
        ['kpi-red',    'bi-x-circle-fill',      'Rejected',         $rejected, 'Declined requests'],
    ];
    foreach ($kpiCards as [$cls, $icon, $label, $val, $sub]): ?>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="an-kpi-card <?php echo $cls; ?>">
            <div class="an-kpi-label"><?php echo $label; ?></div>
            <div class="an-kpi-value"><?php echo number_format($val); ?></div>
            <div class="an-kpi-sub"><?php echo $sub; ?></div>
            <i class="bi <?php echo $icon; ?> an-kpi-icon-bg"></i>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<hr class="an-divider">

<!-- ═══════════════════════════════════════════════
     ③  CHARTS — Section Heading
═══════════════════════════════════════════════ -->
<div class="an-section-heading an-animate an-delay-2">
    <div class="an-section-icon"><i class="bi bi-graph-up"></i></div>
    <h5>Live Charts</h5>
    <span class="an-section-line"></span>
    <span class="an-section-badge">Chart.js · Live Data</span>
</div>

<div class="row g-4 mb-5 an-animate an-delay-3">

    <!-- ① Bar Chart — Plan Popularity -->
    <div class="col-lg-6">
        <div class="an-chart-card">
            <div class="an-chart-header">
                <div class="an-chart-header-icon"><i class="bi bi-bar-chart-fill"></i></div>
                <div>
                    <div class="an-chart-title">Plan Popularity</div>
                    <div style="font-size:.72rem;color:#bbb;margin-top:1px;">Booking count per plan</div>
                </div>
                <span class="an-chart-tag">Bar · Live</span>
            </div>
            <div style="padding:16px;">
                <canvas id="chartBar" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- ② Pie/Doughnut Chart — Payment Distribution -->
    <div class="col-lg-6">
        <div class="an-chart-card">
            <div class="an-chart-header">
                <div class="an-chart-header-icon"><i class="bi bi-pie-chart-fill"></i></div>
                <div>
                    <div class="an-chart-title">Payment Status Distribution</div>
                    <div style="font-size:.72rem;color:#bbb;margin-top:1px;">Paid / Pending / Pay Later</div>
                </div>
                <span class="an-chart-tag">Pie · Live</span>
            </div>
            <div style="padding:16px;max-width:340px;margin:0 auto;">
                <canvas id="chartPie" height="220"></canvas>
            </div>
        </div>
    </div>

    <!-- ③ Line Chart — Booking Trend (last 30 days) -->
    <div class="col-lg-6">
        <div class="an-chart-card">
            <div class="an-chart-header">
                <div class="an-chart-header-icon"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <div class="an-chart-title">Daily Booking Trend</div>
                    <div style="font-size:.72rem;color:#bbb;margin-top:1px;">Last 30 days</div>
                </div>
                <span class="an-chart-tag">Line · Live</span>
            </div>
            <div style="padding:16px;">
                <canvas id="chartLine" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- ④ Monthly Growth — Static PNG (if available) -->
    <?php $mgSrc = chart('monthly_growth.png'); ?>
    <div class="col-lg-6">
        <div class="an-chart-card">
            <div class="an-chart-header">
                <div class="an-chart-header-icon"><i class="bi bi-calendar3"></i></div>
                <div>
                    <div class="an-chart-title">Monthly Booking Growth</div>
                    <div style="font-size:.72rem;color:#bbb;margin-top:1px;">Month-on-month comparison</div>
                </div>
                <span class="an-chart-tag">EDA</span>
            </div>
            <?php if ($mgSrc): ?>
                <img src="<?php echo $mgSrc; ?>" alt="Monthly Booking Growth" class="an-chart-img" loading="lazy">
            <?php else: ?>
                <div class="an-chart-placeholder">
                    <i class="bi bi-image-alt"></i>
                    <p>Chart not yet generated.<br><strong>Click Refresh</strong> to generate.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<hr class="an-divider">

<!-- ═══════════════════════════════════════════════
     ④  PREDICTIONS — Section Heading
═══════════════════════════════════════════════ -->
<div class="an-section-heading an-animate an-delay-3">
    <div class="an-section-icon"><i class="bi bi-robot"></i></div>
    <h5>Predictions</h5>
    <span class="an-section-line"></span>
    <span class="an-section-badge">ML · 3 Models</span>
</div>

<!-- Model Accuracy Strip -->
<div class="d-flex flex-wrap gap-2 mb-4 an-animate an-delay-3">
    <span class="an-acc-pill">
        <i class="bi bi-tree"></i>
        Plan Predictor — RandomForest
        <?php echo is_numeric($planAcc) ? " · <strong>{$planAcc}%</strong> accuracy" : ''; ?>
    </span>
    <span class="an-acc-pill">
        <i class="bi bi-diagram-3"></i>
        Payment Predictor — LogisticReg
        <?php echo is_numeric($payAcc) ? " · <strong>{$payAcc}%</strong> accuracy" : ''; ?>
    </span>
    <span class="an-acc-pill">
        <i class="bi bi-graph-up"></i>
        Volume Forecast — LinearRegression
        <?php echo is_numeric($forecastAvg) ? " · avg <strong>{$forecastAvg}</strong> bookings/day" : ''; ?>
    </span>
</div>

<!-- Forecast Info Strip -->
<div class="an-forecast-strip mb-4 an-animate an-delay-3">
    <div class="an-forecast-icon"><i class="bi bi-lightning-charge-fill"></i></div>
    <div>
        <div class="an-forecast-label">Avg Daily Forecast</div>
        <div class="an-forecast-val"><?php echo is_numeric($forecastAvg) ? $forecastAvg : '—'; ?> bookings/day</div>
        <div class="an-forecast-sub">LinearRegression model</div>
    </div>
    <div class="an-forecast-divider d-none d-md-block"></div>
    <div>
        <div class="an-forecast-label">Forecast Period</div>
        <div class="an-forecast-val" style="font-size:1.1rem;"><?php echo htmlspecialchars($forecastPrd); ?></div>
        <div class="an-forecast-sub">Next 30 days projection</div>
    </div>
    <div class="an-forecast-divider d-none d-md-block"></div>
    <div>
        <div class="an-forecast-label">Plan Model Accuracy</div>
        <div class="an-forecast-val" style="color:#60a5fa;"><?php echo is_numeric($planAcc) ? $planAcc.'%' : '—'; ?></div>
        <div class="an-forecast-sub">RandomForest Classifier</div>
    </div>
    <div class="an-forecast-divider d-none d-md-block"></div>
    <div>
        <div class="an-forecast-label">Payment Accuracy</div>
        <div class="an-forecast-val" style="color:#34d399;"><?php echo is_numeric($payAcc) ? $payAcc.'%' : '—'; ?></div>
        <div class="an-forecast-sub">Logistic Regression</div>
    </div>
</div>

<!-- ML Chart Cards -->
<div class="row g-4 mb-4 an-animate an-delay-4">
    <?php
    $mlCharts = [
        ['ml_plan_prediction.png',    'bi-bar-chart-steps',      'Predicted Plan Demand by Day',          'scikit-learn', 'col-lg-6'],
        ['ml_payment_prediction.png', 'bi-credit-card-2-front',  'Payment Behaviour Prediction per Plan', 'scikit-learn', 'col-lg-6'],
        ['ml_booking_forecast.png',   'bi-graph-up-arrow',       '30-Day Booking Volume Forecast',        'scikit-learn', 'col-12'],
    ];
    foreach ($mlCharts as [$file, $icon, $title, $tag, $col]):
        $src = chart($file); ?>
    <div class="<?php echo $col; ?>">
        <div class="an-chart-card">
            <div class="an-chart-header">
                <div class="an-chart-header-icon"><i class="bi <?php echo $icon; ?>"></i></div>
                <div>
                    <div class="an-chart-title"><?php echo $title; ?></div>
                    <div style="font-size:.72rem;color:#bbb;margin-top:1px;">Machine Learning</div>
                </div>
                <span class="an-chart-tag"><?php echo $tag; ?></span>
            </div>
            <?php if ($src): ?>
                <img src="<?php echo $src; ?>" alt="<?php echo $title; ?>" class="an-chart-img" loading="lazy">
            <?php else: ?>
                <div class="an-chart-placeholder">
                    <i class="bi bi-cpu"></i>
                    <p>ML chart not generated yet.<br>Click <strong>Refresh</strong>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ML Prediction Tables (from JSON) -->
<?php if (!empty($planPreds) || !empty($payPreds)): ?>
<div class="row g-4 mb-5 an-animate an-delay-4">
    <?php if (!empty($planPreds)): ?>
    <div class="col-lg-6">
        <div class="an-chart-card">
            <div class="an-chart-header">
                <div class="an-chart-header-icon"><i class="bi bi-calendar-week"></i></div>
                <div><div class="an-chart-title">Predicted Top Plan by Day of Week</div></div>
                <span class="an-chart-tag">JSON</span>
            </div>
            <div class="p-3">
                <?php foreach ($planPreds as $day => $plan): ?>
                <div class="an-pred-row">
                    <span class="an-pred-day"><i class="bi bi-calendar-day me-2 text-danger"></i><?php echo htmlspecialchars($day); ?></span>
                    <span class="an-pred-plan"><?php echo htmlspecialchars($plan); ?></span>
                    <span class="an-pred-badge" style="background:#fff0f0;color:#e60000;margin-left:8px;">Predicted</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($payPreds)): ?>
    <div class="col-lg-6">
        <div class="an-chart-card">
            <div class="an-chart-header">
                <div class="an-chart-header-icon"><i class="bi bi-credit-card"></i></div>
                <div><div class="an-chart-title">Predicted Payment Behaviour by Plan</div></div>
                <span class="an-chart-tag">JSON</span>
            </div>
            <div class="p-3">
                <?php
                $bColors = ['Paid'=>'badge-paid','Pending'=>'badge-pending','Pay Later'=>'badge-paylater'];
                foreach ($payPreds as $plan => $beh):
                    $bc = $bColors[$beh] ?? 'badge-pending'; ?>
                <div class="an-pred-row">
                    <span class="an-pred-day" style="max-width:65%;word-break:break-word;font-weight:500;color:#444;">
                        <?php echo htmlspecialchars($plan); ?>
                    </span>
                    <span class="an-pred-badge <?php echo $bc; ?>"><?php echo htmlspecialchars($beh); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<hr class="an-divider">

<!-- ═══════════════════════════════════════════════
     ⑤  Python Output (collapsible)
═══════════════════════════════════════════════ -->
<div class="an-section-heading mb-3">
    <div class="an-section-icon" style="background:linear-gradient(135deg,#1e3a5f,#0d1117);"><i class="bi bi-terminal"></i></div>
    <h5>Python Script Output</h5>
    <span class="an-section-line"></span>
    <button class="btn btn-sm px-3" onclick="toggleConsole()"
        style="background:#f5f5f5;border:1px solid #e0e0e0;border-radius:8px;font-size:0.8rem;color:#555;">
        <i class="bi bi-eye me-1"></i>Toggle Console
    </button>
</div>
<div id="consoleWrap" class="mb-5 d-none">
    <div class="an-console-header">
        <div class="an-console-dot" style="background:#ff5f56;"></div>
        <div class="an-console-dot" style="background:#ffbd2e;"></div>
        <div class="an-console-dot" style="background:#27c93f;"></div>
        <span style="margin-left:8px;font-size:0.75rem;color:#586069;">generate_charts.py — output</span>
    </div>
    <div class="an-console" style="border-radius:0 0 14px 14px;"><?php echo htmlspecialchars($output ?? 'No output captured.'); ?></div>
</div>

<!-- ═══════════════════════════════════════════════
     ⑥  Future ML Enhancements — ROADMAP
═══════════════════════════════════════════════ -->
<div class="an-section-heading">
    <div class="an-section-icon" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);"><i class="bi bi-lightbulb"></i></div>
    <h5>Future ML Enhancements</h5>
    <span class="an-section-line"></span>
    <span class="an-section-badge" style="background:#f3f0ff;color:#6d28d9;border-color:#ddd6fe;">Roadmap</span>
</div>

<div class="row g-3 mb-5">
    <?php
    $suggestions = [
        ['bi-cpu-fill',        '#4a90e2', 'Churn Prediction',       'Predict which customers are unlikely to renew their plans.'],
        ['bi-diagram-3-fill',  '#2ecc71', 'Plan Recommendation',    'Suggest best plan for new users based on patterns.'],
        ['bi-reception-4',     '#e67e22', 'Demand Forecasting',     'ARIMA/LSTM for accurate long-range booking forecasting.'],
        ['bi-shield-lock-fill','#e74c3c', 'Fraud Detection',        'Detect suspicious booking patterns with Isolation Forest.'],
        ['bi-people-fill',     '#9b59b6', 'Customer Segmentation',  'K-Means clustering for targeted promotions.'],
        ['bi-graph-up-arrow',  '#1abc9c', 'Revenue Prediction',     'Predict next-month revenue from plan mix and history.'],
    ];
    foreach ($suggestions as [$icon, $color, $title, $desc]): ?>
    <div class="col-md-4 col-sm-6">
        <div class="an-suggest-card">
            <div class="an-suggest-icon" style="background:<?php echo $color; ?>18;">
                <i class="bi <?php echo $icon; ?>" style="color:<?php echo $color; ?>;"></i>
            </div>
            <div class="an-suggest-title"><?php echo $title; ?></div>
            <p class="an-suggest-desc"><?php echo $desc; ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
function toggleConsole() {
    document.getElementById('consoleWrap').classList.toggle('d-none');
}

// ── Chart.js Data (PHP → JSON) ────────────────────────────────
const cjsPlanLabels  = <?php echo json_encode($cjsPlanLabels,  JSON_UNESCAPED_UNICODE); ?>;
const cjsPlanCounts  = <?php echo json_encode($cjsPlanCounts); ?>;
const cjsPayLabels   = <?php echo json_encode($cjsPayLabels,   JSON_UNESCAPED_UNICODE); ?>;
const cjsPayCounts   = <?php echo json_encode($cjsPayCounts); ?>;
const cjsTrendLabels = <?php echo json_encode($cjsTrendLabels, JSON_UNESCAPED_UNICODE); ?>;
const cjsTrendCounts = <?php echo json_encode($cjsTrendCounts); ?>;

// ── ① Bar Chart — Plan Popularity ────────────────────────────
(function() {
    const ctx = document.getElementById('chartBar');
    if (!ctx || !cjsPlanLabels.length) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: cjsPlanLabels,
            datasets: [{
                label: 'Bookings',
                data: cjsPlanCounts,
                backgroundColor: [
                    'rgba(230,0,0,0.75)',
                    'rgba(139,0,0,0.75)',
                    'rgba(74,144,226,0.75)',
                    'rgba(46,204,113,0.75)',
                    'rgba(243,156,18,0.75)',
                    'rgba(155,89,182,0.75)',
                ],
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} bookings`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    ticks: { font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });
})();

// ── ② Pie/Doughnut Chart — Payment Distribution ───────────────
(function() {
    const ctx = document.getElementById('chartPie');
    if (!ctx || !cjsPayLabels.length) return;
    const colorMap = {
        'Paid':      'rgba(46,204,113,0.85)',
        'Pending':   'rgba(243,156,18,0.85)',
        'Pay Later': 'rgba(155,89,182,0.85)',
    };
    const bgColors = cjsPayLabels.map(l => colorMap[l] || 'rgba(149,165,166,0.8)');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: cjsPayLabels,
            datasets: [{
                data: cjsPayCounts,
                backgroundColor: bgColors,
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 12 }, padding: 14 }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed}`
                    }
                }
            }
        }
    });
})();

// ── ③ Line Chart — Daily Booking Trend ───────────────────────
(function() {
    const ctx = document.getElementById('chartLine');
    if (!ctx) return;
    // Show "No data" message if empty
    if (!cjsTrendLabels.length) {
        ctx.parentElement.innerHTML = '<div style="text-align:center;padding:40px 20px;color:#bbb;"><i class="bi bi-graph-up" style="font-size:2rem;display:block;margin-bottom:10px;"></i><p style="font-size:.85rem;">No booking data in the last 30 days.</p></div>';
        return;
    }
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: cjsTrendLabels,
            datasets: [{
                label: 'Bookings',
                data: cjsTrendCounts,
                fill: true,
                backgroundColor: 'rgba(230,0,0,0.08)',
                borderColor: 'rgba(230,0,0,0.85)',
                borderWidth: 2.5,
                pointBackgroundColor: 'rgba(230,0,0,1)',
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.35,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} booking${ctx.parsed.y !== 1 ? 's' : ''}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    ticks: { font: { size: 10 }, maxRotation: 45 },
                    grid: { display: false }
                }
            }
        }
    });
})();
</script>

<?php include 'includes/sidebar_end.php'; ?>
