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
?>
<?php include 'includes/sidebar.php'; ?>

<!-- ── Top Hero Banner ─────────────────────────────────────────── -->
<div class="ns-analytics-hero mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="fw-bold mb-1" style="color:#1a1a1a;">
                <i class="bi bi-bar-chart-line-fill text-danger me-2"></i>Data Analytics & ML Predictions
            </h2>
            <p class="text-muted mb-0" style="font-size:0.9rem;">
                Exploratory Data Analysis + Machine Learning on booking data &nbsp;|&nbsp;
                <span class="badge bg-light text-muted border">
                    <i class="bi bi-clock me-1"></i>Last run: <?php echo htmlspecialchars($generatedAt); ?>
                </span>
            </p>
        </div>
        <div class="col-auto">
            <a href="?refresh=1" class="btn btn-outline-danger btn-sm me-2">
                <i class="bi bi-arrow-clockwise me-1"></i>Refresh Charts
            </a>
            <a href="view-booking.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
        </div>
    </div>
</div>

<!-- ── Most Popular Plan Banner ───────────────────────────────── -->
<div class="ns-top-plan-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <p class="mb-1 text-white" style="font-size:0.75rem;letter-spacing:1.5px;text-transform:uppercase;opacity:.7;">
                🏆 Most Booked Plan
            </p>
            <h3 class="fw-bold text-white mb-1"><?php echo htmlspecialchars($topPlan); ?></h3>
            <p class="mb-0 text-white" style="opacity:.8;">
                <?php echo $topPlanCnt; ?> bookings out of <?php echo $total; ?> total
            </p>
        </div>
        <div style="font-size:3.5rem;opacity:.2;color:white;">
            <i class="bi bi-trophy-fill"></i>
        </div>
    </div>
</div>

<!-- ── Summary Stats ───────────────────────────────────────────── -->
<div class="ns-section-header mb-3">
    <span class="ns-section-title"><i class="bi bi-grid-fill text-danger me-2"></i>Summary Overview</span>
    <span class="ns-section-pill">LIVE DATA</span>
</div>

<div class="row g-3 mb-5">
    <?php
    $statCards = [
        ['sc-blue',   'bi-journal-text',       'Total Bookings',  $total],
        ['sc-green',  'bi-check-circle-fill',  'Paid',            $paid],
        ['sc-orange', 'bi-clock-history',      'Pending Payment', $pending],
        ['sc-amber',  'bi-wallet2',            'Pay Later',       $payLater],
        ['sc-teal',   'bi-patch-check-fill',   'Approved',        $approved],
        ['sc-red',    'bi-x-circle-fill',      'Rejected',        $rejected],
    ];
    foreach ($statCards as [$cls,$icon,$label,$val]): ?>
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="ns-stat-card <?php echo $cls; ?>">
            <div class="ns-stat-label"><?php echo $label; ?></div>
            <div class="ns-stat-value"><?php echo number_format($val); ?></div>
            <i class="bi <?php echo $icon; ?> ns-stat-icon"></i>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Quick Stats Row ─────────────────────────────────────────── -->
<div class="row g-3 mb-5">
    <div class="col-md-4">
        <div class="ns-quick-stat-card">
            <div class="ns-qs-label">📊 Payment Success Rate</div>
            <div class="ns-qs-value"><?php echo $paidPct; ?>%</div>
            <div class="progress mt-2" style="height:6px;border-radius:3px;">
                <div class="progress-bar bg-success" style="width:<?php echo $paidPct; ?>%;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ns-quick-stat-card">
            <div class="ns-qs-label">🤖 ML Forecast (avg/day)</div>
            <div class="ns-qs-value" style="color:#4a90e2;">
                <?php echo is_numeric($forecastAvg) ? $forecastAvg : '—'; ?>
            </div>
            <div class="ns-qs-sub">Next 30 days prediction</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="ns-quick-stat-card">
            <div class="ns-qs-label">🎯 Plan Model Accuracy</div>
            <div class="ns-qs-value" style="color:#2ecc71;">
                <?php echo is_numeric($planAcc) ? $planAcc.'%' : '—'; ?>
            </div>
            <div class="ns-qs-sub">RandomForest Classifier</div>
        </div>
    </div>
</div>

<!-- ── EDA Charts ──────────────────────────────────────────────── -->
<div class="ns-section-header mb-3">
    <span class="ns-section-title"><i class="bi bi-graph-up text-danger me-2"></i>Exploratory Data Analysis</span>
    <span class="ns-section-pill">4 CHARTS</span>
</div>

<div class="row g-4 mb-5">
    <?php
    $edaCharts = [
        ['plan_popularity.png',      'bi-bar-chart-fill',  'Plan Popularity'],
        ['payment_distribution.png', 'bi-pie-chart-fill',  'Payment Status Distribution'],
        ['booking_trend.png',        'bi-graph-up-arrow',  'Daily Booking Trend'],
        ['monthly_growth.png',       'bi-calendar3',       'Monthly Booking Growth'],
    ];
    foreach ($edaCharts as [$file,$icon,$title]):
        $src = chart($file); ?>
    <div class="col-lg-6">
        <div class="ns-chart-card h-100">
            <div class="ns-chart-header">
                <i class="bi <?php echo $icon; ?> text-danger me-2"></i><?php echo $title; ?>
            </div>
            <?php if ($src): ?>
                <img src="<?php echo $src; ?>" alt="<?php echo $title; ?>" class="ns-chart-img" loading="lazy">
            <?php else: ?>
                <div class="ns-chart-placeholder">
                    <i class="bi bi-image-alt"></i>
                    <p>Chart not yet generated.<br>Click <strong>Refresh Charts</strong> above.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── ML Predictions ─────────────────────────────────────────── -->
<div class="ns-section-header mb-3">
    <span class="ns-section-title"><i class="bi bi-robot text-danger me-2"></i>Machine Learning Predictions</span>
    <span class="ns-section-pill">3 MODELS</span>
</div>

<!-- Model accuracy badges -->
<div class="d-flex gap-3 flex-wrap mb-4">
    <span class="ns-acc-badge">
        <i class="bi bi-tree me-1"></i>
        Plan Predictor — RandomForest
        <?php echo is_numeric($planAcc) ? " · Accuracy: {$planAcc}%" : ''; ?>
    </span>
    <span class="ns-acc-badge">
        <i class="bi bi-diagram-3 me-1"></i>
        Payment Predictor — LogisticReg
        <?php echo is_numeric($payAcc) ? " · Accuracy: {$payAcc}%" : ''; ?>
    </span>
    <span class="ns-acc-badge">
        <i class="bi bi-graph-up me-1"></i>
        Volume Forecast — LinearRegression
        <?php echo is_numeric($forecastAvg) ? " · avg {$forecastAvg} bookings/day" : ''; ?>
    </span>
</div>

<!-- 3 ML chart cards -->
<div class="row g-4 mb-5">
    <?php
    $mlCharts = [
        ['ml_plan_prediction.png',    'bi-bar-chart-steps',       'Predicted Plan Demand by Day',         'scikit-learn', 'col-lg-6'],
        ['ml_payment_prediction.png', 'bi-credit-card-2-front',   'Payment Behaviour Prediction per Plan', 'scikit-learn', 'col-lg-6'],
        ['ml_booking_forecast.png',   'bi-graph-up-arrow',        '30-Day Booking Volume Forecast',        'scikit-learn', 'col-12'],
    ];
    foreach ($mlCharts as [$file,$icon,$title,$tag,$col]):
        $src = chart($file); ?>
    <div class="<?php echo $col; ?>">
        <div class="ns-chart-card h-100">
            <div class="ns-chart-header">
                <i class="bi <?php echo $icon; ?> text-danger me-2"></i><?php echo $title; ?>
                <span class="badge ms-2" style="background:#f5f5f5;color:#666;font-size:.72rem;"><?php echo $tag; ?></span>
            </div>
            <?php if ($src): ?>
                <img src="<?php echo $src; ?>" alt="<?php echo $title; ?>" class="ns-chart-img" loading="lazy">
            <?php else: ?>
                <div class="ns-chart-placeholder">
                    <i class="bi bi-cpu"></i>
                    <p>ML chart not generated yet.<br>Click <strong>Refresh Charts</strong>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ML Prediction tables -->
<?php if (!empty($planPreds) || !empty($payPreds)): ?>
<div class="row g-4 mb-5">
    <?php if (!empty($planPreds)): ?>
    <div class="col-lg-6">
        <div class="ns-chart-card">
            <div class="ns-chart-header">
                <i class="bi bi-calendar-week text-danger me-2"></i>Predicted Top Plan by Day of Week
            </div>
            <div class="p-3">
                <?php foreach ($planPreds as $day => $plan): ?>
                <div class="ns-pred-box d-flex justify-content-between align-items-center">
                    <span class="ns-pred-day"><i class="bi bi-calendar-day me-1"></i><?php echo htmlspecialchars($day); ?></span>
                    <span><?php echo htmlspecialchars($plan); ?>
                        <span class="ns-pred-tag">Predicted</span>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($payPreds)): ?>
    <div class="col-lg-6">
        <div class="ns-chart-card">
            <div class="ns-chart-header">
                <i class="bi bi-credit-card text-danger me-2"></i>Predicted Payment Behaviour by Plan
            </div>
            <div class="p-3">
                <?php
                $behaviorColors = ['Paid'=>'#2ecc71','Pending'=>'#f39c12','Pay Later'=>'#e67e22'];
                foreach ($payPreds as $plan => $beh):
                    $bc = $behaviorColors[$beh] ?? '#999';
                ?>
                <div class="ns-pred-box d-flex justify-content-between align-items-center">
                    <span class="ns-pred-day" style="max-width:60%;word-break:break-word;">
                        <?php echo htmlspecialchars($plan); ?>
                    </span>
                    <span class="ns-pred-tag" style="background:<?php echo $bc; ?>;">
                        <?php echo htmlspecialchars($beh); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Python output (collapsible) -->
<div class="ns-section-header mb-3">
    <span class="ns-section-title"><i class="bi bi-terminal text-danger me-2"></i>Python Script Output</span>
    <button class="btn btn-sm btn-outline-secondary" onclick="toggleConsole()">
        <i class="bi bi-eye me-1"></i>Toggle
    </button>
</div>
<div id="consoleOutput" class="ns-console mb-5 d-none">
    <?php echo htmlspecialchars($output ?? 'No output captured.'); ?>
</div>

<!-- Future ML Suggestions -->
<div class="ns-section-header mb-3">
    <span class="ns-section-title"><i class="bi bi-lightbulb text-danger me-2"></i>Future ML Enhancements</span>
    <span class="ns-section-pill">ROADMAP</span>
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
    foreach ($suggestions as [$icon,$color,$title,$desc]): ?>
    <div class="col-md-4">
        <div class="ns-chart-card h-100 p-3">
            <div class="d-flex align-items-start gap-3">
                <div class="ns-suggestion-icon" style="background:<?php echo $color; ?>22;">
                    <i class="bi <?php echo $icon; ?>" style="color:<?php echo $color; ?>;"></i>
                </div>
                <div>
                    <div class="fw-bold mb-1" style="font-size:.9rem;"><?php echo $title; ?></div>
                    <div class="text-muted" style="font-size:.82rem;"><?php echo $desc; ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Analytics-specific styles ──────────────────────────────── -->
<style>
.ns-analytics-hero {
    background: #fff;
    border-radius: 16px;
    padding: 22px 28px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
}
.ns-top-plan-card {
    background: linear-gradient(135deg, #e60000, #8b0000);
    border-radius: 16px;
    padding: 24px 28px;
    box-shadow: 0 6px 20px rgba(230,0,0,.25);
}
.ns-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border-radius: 12px;
    padding: 14px 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,.04);
}
.ns-section-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a1a;
    border-left: 3px solid #e60000;
    padding-left: 10px;
}
.ns-section-pill {
    background: #fff3f3;
    color: #e60000;
    border-radius: 20px;
    padding: 3px 12px;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .5px;
}
.ns-chart-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    overflow: hidden;
    transition: transform .25s, box-shadow .25s;
    border: none;
}
.ns-chart-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.1);
}
.ns-chart-header {
    background: #fff;
    border-bottom: 1px solid #f0f0f0;
    padding: 14px 20px;
    font-weight: 600;
    font-size: .9rem;
    color: #1a1a1a;
}
.ns-chart-img {
    width: 100%;
    height: auto;
    display: block;
    padding: 8px;
}
.ns-chart-placeholder {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    margin: 12px;
    padding: 40px;
    text-align: center;
    color: #bbb;
}
.ns-chart-placeholder i { font-size: 2.5rem; display: block; margin-bottom: 10px; }
.ns-quick-stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px 22px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    height: 100%;
}
.ns-qs-label { font-size: .8rem; color: #888; font-weight: 600; margin-bottom: 8px; }
.ns-qs-value { font-size: 1.8rem; font-weight: 700; color: #e60000; }
.ns-qs-sub   { font-size: .75rem; color: #bbb; margin-top: 4px; }
.ns-acc-badge {
    background: #e8f5e9;
    color: #2e7d32;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: .8rem;
    font-weight: 600;
}
.ns-pred-box {
    background: #fff8f8;
    border: 1px solid #fde0e0;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 8px;
}
.ns-pred-day { font-weight: 600; color: #e60000; font-size: .88rem; }
.ns-pred-tag {
    display: inline-block;
    background: #e60000;
    color: white;
    font-size: .72rem;
    border-radius: 20px;
    padding: 2px 10px;
    font-weight: 500;
}
.ns-console {
    background: #1e1e2e;
    color: #a6e3a1;
    font-family: 'Courier New', monospace;
    font-size: .8rem;
    border-radius: 12px;
    padding: 16px;
    max-height: 200px;
    overflow-y: auto;
    white-space: pre-wrap;
    border: 1px solid #313244;
}
.ns-suggestion-icon {
    width: 42px; height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.1rem;
}
</style>

<script>
function toggleConsole() {
    document.getElementById('consoleOutput').classList.toggle('d-none');
}
</script>

<?php include 'includes/sidebar_end.php'; ?>
