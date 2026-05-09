<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include 'db.php';

$pageTitle  = 'Manage Plans';
$activePage = 'plans';

// ── Fetch plans with optional filter/search ───────────────────
$filterCat = trim($_GET['category'] ?? '');
$search    = trim($_GET['search']   ?? '');

$where = [];
$params = [];
$types  = '';

if ($filterCat && in_array($filterCat, ['Broadband','Mobile','DTH'])) {
    $where[]  = 'category = ?';
    $params[] = $filterCat;
    $types   .= 's';
}
if ($search) {
    $like     = '%' . $search . '%';
    $where[]  = 'plan_name LIKE ?';
    $params[] = $like;
    $types   .= 's';
}

$sql = 'SELECT * FROM plans';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY category, created_at DESC';

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $plans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $plans = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

// Counts for stats cards
$total    = $conn->query("SELECT COUNT(*) c FROM plans")->fetch_assoc()['c'];
$active   = $conn->query("SELECT COUNT(*) c FROM plans WHERE status='Active'")->fetch_assoc()['c'];
$inactive = $conn->query("SELECT COUNT(*) c FROM plans WHERE status='Inactive'")->fetch_assoc()['c'];
$bb       = $conn->query("SELECT COUNT(*) c FROM plans WHERE category='Broadband'")->fetch_assoc()['c'];
$mob      = $conn->query("SELECT COUNT(*) c FROM plans WHERE category='Mobile'")->fetch_assoc()['c'];
$dth      = $conn->query("SELECT COUNT(*) c FROM plans WHERE category='DTH'")->fetch_assoc()['c'];

include 'includes/sidebar.php';
?>

<!-- Page Content -->
<div style="margin-bottom:28px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <h4 style="margin:0;font-weight:700;color:#1a1a1a;">Manage Plans</h4>
        <p style="margin:4px 0 0;color:#888;font-size:.9rem;">Add, edit, and control plans that appear on the website</p>
    </div>
    <button class="btn btn-danger" id="showAddForm" onclick="document.getElementById('addFormCard').style.display='block';this.style.display='none';window.scrollTo({top:0,behavior:'smooth'})">
        <i class="bi bi-plus-circle me-1"></i> Add New Plan
    </button>
</div>

<!-- ── Stats ── -->
<div class="row g-3 mb-4">
    <?php
    $stats = [
        ['label'=>'Total Plans',   'val'=>$total,    'cls'=>'sc-blue',   'icon'=>'bi-grid-3x3-gap-fill'],
        ['label'=>'Active',        'val'=>$active,   'cls'=>'sc-green',  'icon'=>'bi-check-circle-fill'],
        ['label'=>'Inactive',      'val'=>$inactive, 'cls'=>'sc-red',    'icon'=>'bi-x-circle-fill'],
        ['label'=>'Broadband',     'val'=>$bb,       'cls'=>'sc-purple', 'icon'=>'bi-wifi'],
        ['label'=>'Mobile',        'val'=>$mob,      'cls'=>'sc-orange', 'icon'=>'bi-phone-fill'],
        ['label'=>'DTH',           'val'=>$dth,      'cls'=>'sc-teal',   'icon'=>'bi-tv-fill'],
    ];
    foreach ($stats as $s): ?>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="ns-stat-card <?= $s['cls'] ?>">
            <div class="ns-stat-label"><?= $s['label'] ?></div>
            <div class="ns-stat-value"><?= $s['val'] ?></div>
            <i class="bi <?= $s['icon'] ?> ns-stat-icon"></i>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Add Plan Form (hidden by default) ── -->
<div class="ns-filter-card" id="addFormCard" style="display:none;margin-bottom:24px;">
    <div class="ns-filter-title"><i class="bi bi-plus-circle-fill text-danger me-2"></i>Add New Plan</div>
    <form id="addPlanForm" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="action" value="add">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-600 small">Plan Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="plan_name" id="add_plan_name" placeholder="e.g. Gold Plan">
                <div class="invalid-feedback" id="err_plan_name"></div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-600 small">Category <span class="text-danger">*</span></label>
                <select class="form-select" name="category" id="add_category">
                    <option value="">-- Select --</option>
                    <option value="Broadband">Broadband</option>
                    <option value="Mobile">Mobile</option>
                    <option value="DTH">DTH</option>
                </select>
                <div class="invalid-feedback" id="err_category"></div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-600 small">Price (₹) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="price" id="add_price" placeholder="e.g. 499">
                <div class="invalid-feedback" id="err_price"></div>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-600 small">Validity</label>
                <input type="text" class="form-control" name="validity" placeholder="e.g. 28 Days">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-600 small">Status</label>
                <select class="form-select" name="status">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-600 small">Description</label>
                <input type="text" class="form-control" name="description" placeholder="Brief plan description (shown on website)">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600 small">Plan Image <small class="text-muted">(optional, max 2MB)</small></label>
                <input type="file" class="form-control" name="image" accept="image/*">
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-danger px-4" id="addSubmitBtn">
                <i class="bi bi-plus-circle me-1"></i> Add Plan
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('addFormCard').style.display='none';document.getElementById('showAddForm').style.display='';document.getElementById('addPlanForm').reset();clearAddErrors();">
                Cancel
            </button>
        </div>
    </form>
</div>

<!-- ── Filter / Search ── -->
<div class="ns-filter-card">
    <div class="ns-filter-title"><i class="bi bi-funnel-fill me-2"></i>Filter & Search</div>
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-600 small">Search by Plan Name</label>
            <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search plans...">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-600 small">Category</label>
            <select class="form-select" name="category">
                <option value="">All Categories</option>
                <?php foreach (['Broadband','Mobile','DTH'] as $cat): ?>
                <option value="<?= $cat ?>" <?= $filterCat === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-danger w-100"><i class="bi bi-search me-1"></i>Filter</button>
        </div>
        <div class="col-md-2">
            <a href="manage-plans.php" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle me-1"></i>Clear</a>
        </div>
    </form>
</div>

<!-- ── Plans Table ── -->
<div class="ns-table-card">
    <div class="ns-table-header">
        <h6 class="ns-table-title"><i class="bi bi-grid-3x3-gap-fill text-danger me-2"></i>
            <?= count($plans) ?> Plan<?= count($plans) !== 1 ? 's' : '' ?> Found
        </h6>
    </div>
    <div class="ns-table" style="overflow-x:auto;">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Plan Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Validity</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($plans)): ?>
                <tr>
                    <td colspan="10">
                        <div class="ns-empty">
                            <i class="bi bi-inbox"></i>
                            <h5>No Plans Found</h5>
                            <p>Add your first plan using the "Add New Plan" button above.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($plans as $i => $p): ?>
                <tr id="row-<?= $p['id'] ?>">
                    <td><?= $i + 1 ?></td>
                    <td>
                        <?php if ($p['image'] && file_exists(__DIR__ . '/' . $p['image'])): ?>
                            <img src="<?= htmlspecialchars($p['image']) ?>" alt="plan" style="width:46px;height:46px;object-fit:cover;border-radius:8px;">
                        <?php else: ?>
                            <div style="width:46px;height:46px;background:#f0f2f5;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                <?php
                                $catIcons = ['Broadband'=>'bi-wifi','Mobile'=>'bi-phone-fill','DTH'=>'bi-tv-fill'];
                                $icon = $catIcons[$p['category']] ?? 'bi-grid';
                                ?>
                                <i class="bi <?= $icon ?>" style="color:#aaa;font-size:1.2rem;"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($p['plan_name']) ?></strong></td>
                    <td>
                        <?php
                        $catColors = ['Broadband'=>'nb-plan','Mobile'=>'nb-pending','DTH'=>'nb-paylater'];
                        $cc = $catColors[$p['category']] ?? 'nb-plan';
                        ?>
                        <span class="ns-badge <?= $cc ?>"><?= htmlspecialchars($p['category']) ?></span>
                    </td>
                    <td><strong style="color:#e60000;">₹<?= htmlspecialchars($p['price']) ?></strong></td>
                    <td><?= htmlspecialchars($p['validity'] ?: '—') ?></td>
                    <td style="max-width:220px;font-size:.82rem;color:#666;"><?= htmlspecialchars($p['description'] ?: '—') ?></td>
                    <td>
                        <span class="ns-badge <?= $p['status'] === 'Active' ? 'nb-approved' : 'nb-rejected' ?>" id="badge-<?= $p['id'] ?>">
                            <?= $p['status'] ?>
                        </span>
                    </td>
                    <td style="font-size:.82rem;color:#888;"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <!-- Toggle -->
                            <button class="ns-action-btn <?= $p['status']==='Active' ? 'nab-reject' : 'nab-approve' ?>"
                                    id="toggleBtn-<?= $p['id'] ?>"
                                    title="<?= $p['status']==='Active' ? 'Deactivate' : 'Activate' ?>"
                                    onclick="togglePlan(<?= $p['id'] ?>)">
                                <i class="bi <?= $p['status']==='Active' ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
                            </button>
                            <!-- Edit -->
                            <button class="ns-action-btn nab-pay" title="Edit"
                                    onclick="openEditModal(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <!-- Delete -->
                            <button class="ns-action-btn nab-delete" title="Delete"
                                    onclick="deletePlan(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['plan_name'])) ?>')">
                                <i class="bi bi-trash3-fill"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Edit Modal ── -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="editModalLabel"><i class="bi bi-pencil-fill text-danger me-2"></i>Edit Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPlanForm" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id"     id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Plan Name *</label>
                            <input type="text" class="form-control" name="plan_name" id="edit_plan_name">
                            <div class="invalid-feedback" id="edit_err_name"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Category *</label>
                            <select class="form-select" name="category" id="edit_category">
                                <option value="Broadband">Broadband</option>
                                <option value="Mobile">Mobile</option>
                                <option value="DTH">DTH</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">Price (₹) *</label>
                            <input type="text" class="form-control" name="price" id="edit_price">
                            <div class="invalid-feedback" id="edit_err_price"></div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">Validity</label>
                            <input type="text" class="form-control" name="validity" id="edit_validity" placeholder="e.g. 1 Month">
                        </div>
                        <div class="col-md-10">
                            <label class="form-label fw-semibold small">Description</label>
                            <input type="text" class="form-control" name="description" id="edit_description">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Replace Image <small class="text-muted">(optional, max 2MB)</small></label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <div id="currentImagePreview" class="mt-2"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="editSaveBtn" onclick="submitEdit()">
                    <i class="bi bi-floppy-fill me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/sidebar_end.php'; ?>

<script>
// ── Add Plan ─────────────────────────────────────────────────
document.getElementById('addPlanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!validateAddForm()) return;

    var btn = document.getElementById('addSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Adding...';

    var fd = new FormData(this);
    fetch('plan-action.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Add Plan';
            if (data.success) {
                Swal.fire({ icon:'success', title:'Plan Added!', text: data.message, timer:1800, showConfirmButton:false })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon:'error', title:'Error', text: data.message });
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Add Plan';
            Swal.fire({ icon:'error', title:'Request Failed', text:'Could not connect to server.' });
        });
});

function validateAddForm() {
    clearAddErrors();
    var ok = true;
    var name  = document.getElementById('add_plan_name').value.trim();
    var cat   = document.getElementById('add_category').value;
    var price = document.getElementById('add_price').value.trim();

    if (!name) { showAddErr('add_plan_name','err_plan_name','Plan name is required.'); ok=false; }
    if (!cat)  { showAddErr('add_category', 'err_category', 'Please select a category.'); ok=false; }
    if (!price) { showAddErr('add_price','err_price','Price is required.'); ok=false; }
    else if (isNaN(price) || parseFloat(price) <= 0) { showAddErr('add_price','err_price','Price must be a positive number.'); ok=false; }
    return ok;
}
function showAddErr(fieldId, errId, msg) {
    document.getElementById(fieldId).classList.add('is-invalid');
    var e = document.getElementById(errId);
    e.textContent = msg;
    e.style.display = 'block';
}
function clearAddErrors() {
    ['add_plan_name','add_category','add_price'].forEach(function(id) {
        document.getElementById(id).classList.remove('is-invalid');
    });
    ['err_plan_name','err_category','err_price'].forEach(function(id) {
        var e = document.getElementById(id);
        if (e) { e.textContent = ''; e.style.display = 'none'; }
    });
}

// ── Edit Modal ───────────────────────────────────────────────
var editModal;
document.addEventListener('DOMContentLoaded', function() {
    editModal = new bootstrap.Modal(document.getElementById('editModal'));
});

function openEditModal(plan) {
    document.getElementById('edit_id').value          = plan.id;
    document.getElementById('edit_plan_name').value   = plan.plan_name;
    document.getElementById('edit_category').value    = plan.category;
    document.getElementById('edit_price').value       = plan.price;
    document.getElementById('edit_validity').value    = plan.validity  || '';
    document.getElementById('edit_description').value = plan.description || '';
    document.getElementById('edit_status').value      = plan.status;

    var preview = document.getElementById('currentImagePreview');
    if (plan.image) {
        preview.innerHTML = '<small class="text-muted">Current image:</small><br><img src="'+plan.image+'" style="height:48px;border-radius:6px;margin-top:4px;">';
    } else {
        preview.innerHTML = '';
    }
    editModal.show();
}

function submitEdit() {
    var name  = document.getElementById('edit_plan_name').value.trim();
    var price = document.getElementById('edit_price').value.trim();

    ['edit_plan_name','edit_price'].forEach(function(id) { document.getElementById(id).classList.remove('is-invalid'); });
    var ok = true;
    if (!name)  { document.getElementById('edit_plan_name').classList.add('is-invalid'); document.getElementById('edit_err_name').textContent='Required.'; ok=false; }
    if (!price || isNaN(price) || parseFloat(price)<=0) { document.getElementById('edit_price').classList.add('is-invalid'); document.getElementById('edit_err_price').textContent='Valid price required.'; ok=false; }
    if (!ok) return;

    var btn = document.getElementById('editSaveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

    var fd = new FormData(document.getElementById('editPlanForm'));
    fetch('plan-action.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-floppy-fill me-1"></i> Save Changes';
            if (data.success) {
                editModal.hide();
                Swal.fire({ icon:'success', title:'Updated!', text:data.message, timer:1600, showConfirmButton:false })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon:'error', title:'Error', text:data.message });
            }
        });
}

// ── Toggle Status ────────────────────────────────────────────
function togglePlan(id) {
    var fd = new FormData();
    fd.append('action','toggle');
    fd.append('id', id);
    fetch('plan-action.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                var badge = document.getElementById('badge-'+id);
                var btn   = document.getElementById('toggleBtn-'+id);
                if (data.newStatus === 'Active') {
                    badge.className = 'ns-badge nb-approved';
                    badge.textContent = 'Active';
                    btn.className = 'ns-action-btn nab-reject';
                    btn.title = 'Deactivate';
                    btn.querySelector('i').className = 'bi bi-toggle-on';
                } else {
                    badge.className = 'ns-badge nb-rejected';
                    badge.textContent = 'Inactive';
                    btn.className = 'ns-action-btn nab-approve';
                    btn.title = 'Activate';
                    btn.querySelector('i').className = 'bi bi-toggle-off';
                }
            } else {
                Swal.fire({ icon:'error', title:'Error', text:data.message });
            }
        });
}

// ── Delete ───────────────────────────────────────────────────
function deletePlan(id, name) {
    Swal.fire({
        title: 'Delete Plan?',
        html: 'Are you sure you want to delete <strong>' + name + '</strong>?<br><small class="text-muted">This action cannot be undone.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e60000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;
        var fd = new FormData();
        fd.append('action','delete');
        fd.append('id', id);
        fetch('plan-action.php', { method:'POST', body:fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('row-'+id).remove();
                    Swal.fire({ icon:'success', title:'Deleted!', timer:1400, showConfirmButton:false });
                } else {
                    Swal.fire({ icon:'error', title:'Error', text:data.message });
                }
            });
    });
}
</script>
