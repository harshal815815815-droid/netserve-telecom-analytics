<?php
// Session started for compatibility only (no login required for demo)
if (session_status() == PHP_SESSION_NONE) session_start();

require_once 'config.php';
include 'db.php';

// ── Page variables (for sidebar) ───────────────────────────────
$activePage = 'dashboard';
$pageTitle  = 'Bookings Dashboard';

// ── Pagination ─────────────────────────────────────────────────
$rowsPerPage = ROWS_PER_PAGE; // 10 (from config.php)
$page        = max(1, intval($_GET['page'] ?? 1));
$offset      = ($page - 1) * $rowsPerPage;

// ── Filter values from GET ─────────────────────────────────────
$search        = trim($_GET['search']         ?? '');
$filterPayment = trim($_GET['payment_status'] ?? '');
$filterStatus  = trim($_GET['status']         ?? '');
$filterPlan    = trim($_GET['plan']           ?? '');
$dateFrom      = trim($_GET['date_from']      ?? '');
$dateTo        = trim($_GET['date_to']        ?? '');
$sortOrder     = in_array($_GET['sort'] ?? '', ['ASC','DESC']) ? $_GET['sort'] : 'DESC';

// ── Build dynamic WHERE clause ─────────────────────────────────
$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[]  = '(name LIKE ? OR mobile LIKE ? OR email LIKE ?)';
    $s = "%$search%";
    $params[] = $s; $params[] = $s; $params[] = $s;
    $types   .= 'sss';
}
if ($filterPayment !== '') {
    $where[]  = 'payment_status = ?';
    $params[] = $filterPayment;
    $types   .= 's';
}
if ($filterStatus !== '') {
    $where[]  = 'status = ?';
    $params[] = $filterStatus;
    $types   .= 's';
}
if ($filterPlan !== '') {
    $where[]  = 'plan LIKE ?';
    $params[] = "%$filterPlan%";
    $types   .= 's';
}
if ($dateFrom !== '') {
    $where[]  = 'DATE(created_at) >= ?';
    $params[] = $dateFrom;
    $types   .= 's';
}
if ($dateTo !== '') {
    $where[]  = 'DATE(created_at) <= ?';
    $params[] = $dateTo;
    $types   .= 's';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ── Count total matching rows (for pagination) ─────────────────
$countSQL = "SELECT COUNT(*) as total FROM bookings $whereSQL";
if ($types) {
    $cStmt = $conn->prepare($countSQL);
    $cStmt->bind_param($types, ...$params);
    $cStmt->execute();
    $totalRows = $cStmt->get_result()->fetch_assoc()['total'];
} else {
    $totalRows = $conn->query($countSQL)->fetch_assoc()['total'];
}
$totalPages = max(1, ceil($totalRows / $rowsPerPage));
$page       = min($page, $totalPages); // clamp page
$offset     = ($page - 1) * $rowsPerPage;

// ── Fetch paginated data ───────────────────────────────────────
$sql = "SELECT * FROM bookings $whereSQL ORDER BY id $sortOrder LIMIT $rowsPerPage OFFSET $offset";
if ($types) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// ── Distinct plans for filter dropdown ─────────────────────────
$plansResult = $conn->query("SELECT DISTINCT plan FROM bookings ORDER BY plan ASC");

// ── Summary stats ──────────────────────────────────────────────
$totalBookings  = $conn->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c'];
$paid           = $conn->query("SELECT COUNT(*) c FROM bookings WHERE payment_status='Paid'")->fetch_assoc()['c'];
$pending        = $conn->query("SELECT COUNT(*) c FROM bookings WHERE payment_status='Pending'")->fetch_assoc()['c'];
$payLater       = $conn->query("SELECT COUNT(*) c FROM bookings WHERE payment_status='Pay Later'")->fetch_assoc()['c'];
$approved       = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='Approved'")->fetch_assoc()['c'];
$rejected       = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='Rejected'")->fetch_assoc()['c'];
$pendingStatus  = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='Pending'")->fetch_assoc()['c'];

// Helper: build current filter query string (for pagination links)
function buildQuery(array $overrides = []): string {
    $base = [
        'search'         => trim($_GET['search']         ?? ''),
        'payment_status' => trim($_GET['payment_status'] ?? ''),
        'status'         => trim($_GET['status']         ?? ''),
        'plan'           => trim($_GET['plan']           ?? ''),
        'date_from'      => trim($_GET['date_from']      ?? ''),
        'date_to'        => trim($_GET['date_to']        ?? ''),
        'sort'           => $_GET['sort']                ?? 'DESC',
    ];
    return http_build_query(array_merge($base, $overrides));
}
?>
<?php
// ── Include sidebar (also outputs <head>, <body>, topbar) ──────
include 'includes/sidebar.php';
?>

<!-- ── Stat Cards ──────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['sc-blue',   'bi-journal-text',       'Total Bookings',   $totalBookings],
        ['sc-green',  'bi-check-circle-fill',  'Paid',             $paid],
        ['sc-orange', 'bi-clock-history',      'Pending Payment',  $pending],
        ['sc-amber',  'bi-wallet2',            'Pay Later',        $payLater],
        ['sc-teal',   'bi-patch-check-fill',   'Approved',         $approved],
        ['sc-red',    'bi-x-circle-fill',      'Rejected',         $rejected],
        ['sc-purple', 'bi-hourglass-split',    'Pending Approval', $pendingStatus],
    ];
    foreach ($cards as [$cls, $icon, $label, $val]): ?>
    <div class="col-xl-3 col-md-4 col-sm-6">
        <div class="ns-stat-card <?php echo $cls; ?>">
            <div class="ns-stat-label"><?php echo $label; ?></div>
            <div class="ns-stat-value"><?php echo number_format($val); ?></div>
            <i class="bi <?php echo $icon; ?> ns-stat-icon"></i>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Filter Panel ───────────────────────────────────────────── -->
<div class="ns-filter-card mb-4">
    <div class="ns-filter-title">
        <i class="bi bi-funnel-fill text-danger"></i> Filters & Search
    </div>
    <form method="GET" id="filterForm">
        <div class="row g-3">

            <!-- Search -->
            <div class="col-md-4">
                <label class="form-label small text-muted fw-semibold mb-1">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                           placeholder="Name, mobile or email..."
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>

            <!-- Payment Status -->
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Payment</label>
                <select name="payment_status" class="form-select">
                    <option value="">All</option>
                    <option value="Paid"      <?php echo $filterPayment=='Paid'      ?'selected':''; ?>>✅ Paid</option>
                    <option value="Pending"   <?php echo $filterPayment=='Pending'   ?'selected':''; ?>>🕐 Pending</option>
                    <option value="Pay Later" <?php echo $filterPayment=='Pay Later' ?'selected':''; ?>>👛 Pay Later</option>
                </select>
            </div>

            <!-- Approval Status -->
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="Pending"  <?php echo $filterStatus=='Pending'  ?'selected':''; ?>>⏳ Pending</option>
                    <option value="Approved" <?php echo $filterStatus=='Approved' ?'selected':''; ?>>✔️ Approved</option>
                    <option value="Rejected" <?php echo $filterStatus=='Rejected' ?'selected':''; ?>>❌ Rejected</option>
                </select>
            </div>

            <!-- Plan -->
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Plan</label>
                <select name="plan" class="form-select">
                    <option value="">All Plans</option>
                    <?php while($p = $plansResult->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($p['plan']); ?>"
                        <?php echo ($filterPlan && strpos($p['plan'], $filterPlan) !== false) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['plan']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Sort -->
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">Sort</label>
                <select name="sort" class="form-select">
                    <option value="DESC" <?php echo $sortOrder=='DESC'?'selected':''; ?>>Newest First</option>
                    <option value="ASC"  <?php echo $sortOrder=='ASC' ?'selected':''; ?>>Oldest First</option>
                </select>
            </div>

            <!-- Date From -->
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">From Date</label>
                <input type="date" name="date_from" class="form-control"
                       value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>

            <!-- Date To -->
            <div class="col-md-2">
                <label class="form-label small text-muted fw-semibold mb-1">To Date</label>
                <input type="date" name="date_to" class="form-control"
                       value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>

            <!-- Buttons -->
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-danger px-4">
                    <i class="bi bi-funnel-fill me-1"></i>Apply
                </button>
                <a href="view-booking.php" class="btn btn-outline-secondary px-3">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
            </div>

        </div>
    </form>
</div>

<!-- ── Booking Table Card ──────────────────────────────────────── -->
<div class="ns-table-card ns-table mb-4">

    <!-- Table Header -->
    <div class="ns-table-header">
        <h5 class="ns-table-title">
            <i class="bi bi-list-ul text-danger"></i>
            All Service Bookings
            <span class="badge ms-2" style="background:#f5f5f5;color:#666;font-size:0.78rem;padding:5px 12px;border-radius:20px;">
                <?php echo $totalRows; ?> record(s)
                <?php if (!empty(array_filter([$search,$filterPayment,$filterStatus,$filterPlan,$dateFrom,$dateTo]))): ?>
                    <span class="text-warning fw-bold">· filtered</span>
                <?php endif; ?>
            </span>
        </h5>
        <a href="export-csv.php" class="btn btn-sm btn-success d-flex align-items-center gap-1"
           title="Download all bookings as CSV">
            <i class="bi bi-file-earmark-arrow-down"></i>
            Export CSV
        </a>
    </div>

    <!-- Table -->
    <?php if ($result->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Customer</th>
                    <th>Plan</th>
                    <th>Price</th>
                    <th>Date</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td class="text-muted fw-bold" style="font-size:0.82rem;">#<?php echo $row['id']; ?></td>

                <td>
                    <div class="fw-semibold text-dark mb-1"><?php echo htmlspecialchars($row['name']); ?></div>
                    <div class="text-muted" style="font-size:0.8rem;">
                        <i class="bi bi-telephone-fill me-1"></i><?php echo htmlspecialchars($row['mobile']); ?>
                    </div>
                    <div class="text-muted" style="font-size:0.78rem;">
                        <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($row['email']); ?>
                    </div>
                </td>

                <td>
                    <span class="ns-badge nb-plan"><?php echo htmlspecialchars($row['plan']); ?></span>
                </td>

                <td class="fw-semibold text-dark">
                    ₹<?php echo htmlspecialchars($row['price'] ?? '—'); ?>
                </td>

                <td class="text-muted" style="font-size:0.82rem;">
                    <i class="bi bi-calendar3 me-1"></i>
                    <?php echo date("d M Y", strtotime($row['created_at'])); ?><br>
                    <span style="font-size:0.75rem;"><?php echo date("h:i A", strtotime($row['created_at'])); ?></span>
                </td>

                <td>
                <?php
                $pay = $row['payment_status'] ?? 'Pending';
                if     ($pay == 'Paid')       echo '<span class="ns-badge nb-paid"><i class="bi bi-check-circle-fill"></i>Paid</span>';
                elseif ($pay == 'Pay Later')  echo '<span class="ns-badge nb-paylater"><i class="bi bi-wallet2"></i>Pay Later</span>';
                else                          echo '<span class="ns-badge nb-pending"><i class="bi bi-clock"></i>Pending</span>';
                ?>
                </td>

                <td>
                <?php
                $status = $row['status'] ?? 'Pending';
                if     ($status == 'Approved') echo '<span class="ns-badge nb-approved"><i class="bi bi-patch-check-fill"></i>Approved</span>';
                elseif ($status == 'Rejected') echo '<span class="ns-badge nb-rejected"><i class="bi bi-x-circle-fill"></i>Rejected</span>';
                else                           echo '<span class="ns-badge nb-pending"><i class="bi bi-hourglass-split"></i>Pending</span>';
                ?>
                </td>

                <td class="text-end pe-3">
                    <div class="d-flex justify-content-end gap-1">
                        <?php if ($status == 'Pending'): ?>
                        <a href="update-status.php?id=<?php echo $row['id']; ?>&status=Approved"
                           class="ns-action-btn nab-approve" title="Approve">
                            <i class="bi bi-check-lg"></i>
                        </a>
                        <a href="update-status.php?id=<?php echo $row['id']; ?>&status=Rejected"
                           class="ns-action-btn nab-reject" title="Reject">
                            <i class="bi bi-x-lg"></i>
                        </a>
                        <?php endif; ?>

                        <?php if ($pay != 'Paid'): ?>
                        <a href="update-payment.php?id=<?php echo $row['id']; ?>"
                           class="ns-action-btn nab-pay" title="Mark as Paid">
                            <i class="bi bi-cash-coin"></i>
                        </a>
                        <?php endif; ?>

                        <button class="ns-action-btn nab-delete"
                                onclick="confirmDelete(<?php echo $row['id']; ?>)" title="Delete">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- ── Pagination ──────────────────────────────────────────── -->
    <?php if ($totalPages > 1): ?>
    <div class="ns-pagination">
        <div class="page-info">
            Showing rows <strong><?php echo $offset + 1; ?>–<?php echo min($offset + $rowsPerPage, $totalRows); ?></strong>
            of <strong><?php echo $totalRows; ?></strong>
        </div>
        <div class="page-buttons">
            <!-- Previous -->
            <a href="?<?php echo buildQuery(['page' => $page - 1]); ?>"
               class="ns-page-btn <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <i class="bi bi-chevron-left me-1"></i>Prev
            </a>

            <!-- Page numbers (show up to 5 around current) -->
            <?php
            $start = max(1, $page - 2);
            $end   = min($totalPages, $page + 2);
            if ($start > 1): ?>
                <a href="?<?php echo buildQuery(['page' => 1]); ?>" class="ns-page-btn">1</a>
                <?php if ($start > 2): ?><span class="ns-page-btn disabled">…</span><?php endif; ?>
            <?php endif;
            for ($p = $start; $p <= $end; $p++): ?>
                <a href="?<?php echo buildQuery(['page' => $p]); ?>"
                   class="ns-page-btn <?php echo $p == $page ? 'active' : ''; ?>">
                    <?php echo $p; ?>
                </a>
            <?php endfor;
            if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><span class="ns-page-btn disabled">…</span><?php endif; ?>
                <a href="?<?php echo buildQuery(['page' => $totalPages]); ?>" class="ns-page-btn"><?php echo $totalPages; ?></a>
            <?php endif; ?>

            <!-- Next -->
            <a href="?<?php echo buildQuery(['page' => $page + 1]); ?>"
               class="ns-page-btn <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                Next<i class="bi bi-chevron-right ms-1"></i>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="ns-empty">
        <i class="bi bi-inbox"></i>
        <h5>No bookings found</h5>
        <p>Try clearing your search filters to see all records.</p>
        <?php if ($search || $filterPayment || $filterStatus || $filterPlan || $dateFrom || $dateTo): ?>
            <a href="view-booking.php" class="btn btn-outline-danger mt-2">
                <i class="bi bi-x-circle me-1"></i>Clear All Filters
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div><!-- /.ns-table-card -->

<!-- ── Delete Confirmation (SweetAlert2) ─────────────────────── -->
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Delete Booking?',
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e60000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        borderRadius: '12px'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = "delete-booking.php?id=" + id;
        }
    });
}
</script>

<?php include 'includes/sidebar_end.php'; ?>
