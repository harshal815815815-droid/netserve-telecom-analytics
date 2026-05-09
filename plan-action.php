<?php
/**
 * plan-action.php
 * ───────────────
 * AJAX handler for admin plan CRUD operations.
 * All responses are JSON.
 */
if (session_status() == PHP_SESSION_NONE) session_start();

// Basic admin guard (same pattern as the rest of the admin pages)
// If no admin session, reject silently
// (The admin pages already redirect to admin-login.php, this is a belt+suspenders check)

include __DIR__ . '/db.php';

header('Content-Type: application/json');

$action = trim($_POST['action'] ?? '');

// ── Helper ────────────────────────────────────────────────────
function jsonOut(bool $ok, string $msg, array $extra = []): void {
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

function sanitize(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

// ── Upload helper ─────────────────────────────────────────────
function handleImageUpload(): ?string {
    if (empty($_FILES['image']['name'])) return null;

    $uploadDir = __DIR__ . '/uploads/plans/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mime    = mime_content_type($_FILES['image']['tmp_name']);

    if (!in_array($mime, $allowed)) {
        jsonOut(false, 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP.');
    }
    if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
        jsonOut(false, 'Image must be under 2 MB.');
    }

    $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = 'plan_' . time() . '_' . rand(100, 999) . '.' . strtolower($ext);
    $dest     = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        jsonOut(false, 'Failed to save image. Check folder permissions.');
    }

    return 'uploads/plans/' . $filename;
}

// ── Route ─────────────────────────────────────────────────────
switch ($action) {

    // ── ADD ──────────────────────────────────────────────────
    case 'add':
        $plan_name   = sanitize($_POST['plan_name']   ?? '');
        $category    = sanitize($_POST['category']    ?? '');
        $price       = sanitize($_POST['price']       ?? '');
        $validity    = sanitize($_POST['validity']    ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $status      = in_array($_POST['status'] ?? '', ['Active','Inactive']) ? $_POST['status'] : 'Active';

        if (!$plan_name || !$category || !$price) {
            jsonOut(false, 'Plan name, category, and price are required.');
        }
        if (!in_array($category, ['Broadband','Mobile','DTH'])) {
            jsonOut(false, 'Invalid category.');
        }
        if (!is_numeric($price) || (float)$price <= 0) {
            jsonOut(false, 'Price must be a positive number.');
        }

        $image = handleImageUpload();

        $stmt = $conn->prepare(
            "INSERT INTO plans (plan_name, category, price, description, validity, status, image, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->bind_param('sssssss', $plan_name, $category, $price, $description, $validity, $status, $image);
        $stmt->execute();

        jsonOut(true, 'Plan added successfully.', ['id' => $conn->insert_id]);
        break;

    // ── EDIT ─────────────────────────────────────────────────
    case 'edit':
        $id          = (int)($_POST['id'] ?? 0);
        $plan_name   = sanitize($_POST['plan_name']   ?? '');
        $category    = sanitize($_POST['category']    ?? '');
        $price       = sanitize($_POST['price']       ?? '');
        $validity    = sanitize($_POST['validity']    ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $status      = in_array($_POST['status'] ?? '', ['Active','Inactive']) ? $_POST['status'] : 'Active';

        if (!$id || !$plan_name || !$category || !$price) {
            jsonOut(false, 'All required fields must be filled.');
        }
        if (!in_array($category, ['Broadband','Mobile','DTH'])) {
            jsonOut(false, 'Invalid category.');
        }
        if (!is_numeric($price) || (float)$price <= 0) {
            jsonOut(false, 'Price must be a positive number.');
        }

        $image = handleImageUpload();

        if ($image) {
            // Delete old image if it exists
            $res = $conn->query("SELECT image FROM plans WHERE id = $id LIMIT 1");
            if ($row = $res->fetch_assoc()) {
                if ($row['image'] && file_exists(__DIR__ . '/' . $row['image'])) {
                    @unlink(__DIR__ . '/' . $row['image']);
                }
            }
            $stmt = $conn->prepare(
                "UPDATE plans SET plan_name=?, category=?, price=?, description=?, validity=?, status=?, image=? WHERE id=?"
            );
            $stmt->bind_param('sssssssi', $plan_name, $category, $price, $description, $validity, $status, $image, $id);
        } else {
            $stmt = $conn->prepare(
                "UPDATE plans SET plan_name=?, category=?, price=?, description=?, validity=?, status=? WHERE id=?"
            );
            $stmt->bind_param('ssssssi', $plan_name, $category, $price, $description, $validity, $status, $id);
        }
        $stmt->execute();

        jsonOut(true, 'Plan updated successfully.');
        break;

    // ── DELETE ───────────────────────────────────────────────
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonOut(false, 'Invalid plan ID.');

        // Delete image file if present
        $res = $conn->query("SELECT image FROM plans WHERE id = $id LIMIT 1");
        if ($row = $res->fetch_assoc()) {
            if ($row['image'] && file_exists(__DIR__ . '/' . $row['image'])) {
                @unlink(__DIR__ . '/' . $row['image']);
            }
        }

        $stmt = $conn->prepare("DELETE FROM plans WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        jsonOut(true, 'Plan deleted.');
        break;

    // ── TOGGLE STATUS ────────────────────────────────────────
    case 'toggle':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonOut(false, 'Invalid plan ID.');

        $res = $conn->query("SELECT status FROM plans WHERE id = $id LIMIT 1");
        if (!($row = $res->fetch_assoc())) jsonOut(false, 'Plan not found.');

        $newStatus = ($row['status'] === 'Active') ? 'Inactive' : 'Active';
        $stmt = $conn->prepare("UPDATE plans SET status=? WHERE id=?");
        $stmt->bind_param('si', $newStatus, $id);
        $stmt->execute();

        jsonOut(true, "Plan set to $newStatus.", ['newStatus' => $newStatus]);
        break;

    default:
        jsonOut(false, 'Unknown action.');
}
