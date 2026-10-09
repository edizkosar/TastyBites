<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth_helpers.php';

requireAdmin();

$action = $_GET['action'] ?? 'list';

/** Returns id => name map of categories. */
function fetchCategories(mysqli $db): array
{
    $res = $db->query("SELECT id, name FROM menu_categories ORDER BY display_order ASC, name ASC");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

/** Returns a single menu item or null. */
function fetchMenuItem(mysqli $db, int $id): ?array
{
    $stmt = $db->prepare(
        "SELECT id, category_id, name, description, price, image_url, is_available
         FROM menu_items WHERE id = ? LIMIT 1"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

// ============ SAVE ============
if ($action === 'save' && currentMethod() === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $price       = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imageUrl    = trim($_POST['image_url'] ?? '');
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;

    $errors = [];
    if ($name === '' || mb_strlen($name) < 2 || mb_strlen($name) > 120) {
        $errors[] = 'Name is required (2–120 chars).';
    }
    if (!is_numeric($price) || (float)$price < 0 || (float)$price > 9999.99) {
        $errors[] = 'Price must be a non-negative number up to 9999.99.';
    }
    if (mb_strlen($description) > 1000) { $errors[] = 'Description too long (max 1000).'; }
    if (mb_strlen($imageUrl) > 255)     { $errors[] = 'Image URL too long (max 255).'; }

    // Category exists
    $cats   = fetchCategories($db);
    $catIds = array_column($cats, 'id');
    if (!in_array($categoryId, array_map('intval', $catIds), true)) {
        $errors[] = 'Invalid category.';
    }

    if (empty($errors)) {
        $priceFloat  = (float)$price;
        $descToStore = $description !== '' ? $description : null;
        $imgToStore  = $imageUrl !== '' ? $imageUrl : null;

        if ($id > 0) {
            $stmt = $db->prepare(
                "UPDATE menu_items
                 SET category_id = ?, name = ?, description = ?, price = ?, image_url = ?, is_available = ?
                 WHERE id = ?"
            );
            $stmt->bind_param('issdsii',
                $categoryId, $name, $descToStore, $priceFloat, $imgToStore, $isAvailable, $id);
            $stmt->execute();
            $stmt->close();
            setFlash('Menu item updated.', 'success');
        } else {
            $stmt = $db->prepare(
                "INSERT INTO menu_items
                    (category_id, name, description, price, image_url, is_available)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('issdsi',
                $categoryId, $name, $descToStore, $priceFloat, $imgToStore, $isAvailable);
            $stmt->execute();
            $stmt->close();
            setFlash('Menu item created.', 'success');
        }
        redirect('/admin/menu_manage.php');
    }

    // Fall through with errors → re-render form
    $item = [
        'id'           => $id,
        'category_id'  => $categoryId,
        'name'         => $name,
        'description'  => $description,
        'price'        => $price,
        'image_url'    => $imageUrl,
        'is_available' => $isAvailable,
    ];
    $action = $id > 0 ? 'edit' : 'new';
}

// ============ DELETE ============
if ($action === 'delete' && currentMethod() === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $db->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        setFlash('Menu item deleted.', 'info');
    } else {
        setFlash('Invalid item reference.', 'danger');
    }
    redirect('/admin/menu_manage.php');
}

// ============ EDIT (load) ============
if ($action === 'edit' && !isset($item)) {
    $id   = (int)($_GET['id'] ?? 0);
    $item = $id > 0 ? fetchMenuItem($db, $id) : null;
    if (!$item) {
        setFlash('Menu item not found.', 'danger');
        redirect('/admin/menu_manage.php');
    }
}

// ============ NEW (empty form) ============
if ($action === 'new' && !isset($item)) {
    $item = [
        'id' => 0, 'category_id' => 0, 'name' => '',
        'description' => '', 'price' => '', 'image_url' => '', 'is_available' => 1,
    ];
}

$pageTitle = 'Manage Menu';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($action === 'new' || $action === 'edit'): ?>
    <h1 class="mb-4"><?= $item['id'] > 0 ? 'Edit Menu Item' : 'New Menu Item' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/admin/menu_manage.php?action=save" novalidate>
        <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">

        <div class="mb-3">
            <label class="form-label" for="name">Name</label>
            <input type="text" id="name" name="name" class="form-control"
                   value="<?= e($item['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="category_id">Category</label>
            <select id="category_id" name="category_id" class="form-select" required>
                <option value="">-- Select category --</option>
                <?php foreach (fetchCategories($db) as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"
                        <?= (int)$item['category_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label" for="price">Price (USD)</label>
            <input type="number" step="0.01" min="0" id="price" name="price"
                   class="form-control" value="<?= e($item['price']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="form-control" maxlength="1000"><?= e($item['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label" for="image_url">Image Path or URL</label>
            <input type="text" id="image_url" name="image_url"
                   class="form-control" maxlength="255"
                   value="<?= e($item['image_url']) ?>">
            <small class="text-muted">Optional. Example: assets/images/menu/pizza.jpg</small>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" id="is_available" name="is_available"
                   class="form-check-input" <?= !empty($item['is_available']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_available">Available</label>
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="<?= BASE_URL ?>/admin/menu_manage.php" class="btn btn-link">Cancel</a>
    </form>

<?php else: /* list */ ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Manage Menu</h1>
        <a href="<?= BASE_URL ?>/admin/menu_manage.php?action=new" class="btn btn-primary">
            + New Item
        </a>
    </div>

    <?php
    $res = $db->query(
        "SELECT mi.id, mi.name, mi.price, mi.image_url, mi.is_available,
                mc.name AS category_name, mc.display_order
         FROM menu_items mi
         JOIN menu_categories mc ON mc.id = mi.category_id
         ORDER BY mc.display_order ASC, mc.name ASC, mi.name ASC"
    );
    $items = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    ?>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Available</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="7" class="text-center text-muted">No menu items yet.</td></tr>
            <?php else: foreach ($items as $r): ?>
                <tr>
                    <td>#<?= (int)$r['id'] ?></td>
                    <td>
                        <?php if (!empty($r['image_url'])): ?>
                            <img src="<?= BASE_URL ?>/<?= e($r['image_url']) ?>"
                                 alt="" style="height: 40px;"
                                 onerror="this.style.display='none'">
                        <?php endif; ?>
                    </td>
                    <td><?= e($r['name']) ?></td>
                    <td><?= e($r['category_name']) ?></td>
                    <td><?= formatPrice($r['price']) ?></td>
                    <td>
                        <?php if (!empty($r['is_available'])): ?>
                            <span class="badge bg-success">Yes</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">No</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>/admin/menu_manage.php?action=edit&id=<?= (int)$r['id'] ?>"
                           class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="post"
                              action="<?= BASE_URL ?>/admin/menu_manage.php?action=delete"
                              class="d-inline"
                              onsubmit="return confirm('Delete this menu item?');">
                            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
