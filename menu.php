<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

// 1) Fetch categories (ordered).
$catStmt = $db->prepare(
    "SELECT id, name FROM menu_categories ORDER BY display_order ASC, name ASC"
);
$catStmt->execute();
$categories = $catStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$catStmt->close();

// 2) Build a map of categoryId => items[].
$itemStmt = $db->prepare(
    "SELECT id, name, description, price, image_url
     FROM menu_items
     WHERE category_id = ? AND is_available = 1
     ORDER BY name ASC"
);

$itemsByCategory = [];
foreach ($categories as $cat) {
    $itemStmt->bind_param('i', $cat['id']);
    $itemStmt->execute();
    $itemsByCategory[$cat['id']] = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
$itemStmt->close();

$pageTitle = 'Menu';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="mb-4">Our Menu</h1>

<?php foreach ($categories as $cat): ?>
    <section class="mb-5">
        <h2 class="menu-category-title"><?= e($cat['name']) ?></h2>

        <?php $items = $itemsByCategory[$cat['id']] ?? []; ?>
        <?php if (empty($items)): ?>
            <p class="text-muted">No items in this category yet.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($items as $item): ?>
                    <div class="col-md-4">
                        <div class="card menu-card h-100">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?= BASE_URL ?>/<?= e($item['image_url']) ?>"
                                     alt="<?= e($item['name']) ?>" class="card-img-top"
                                     onerror="this.style.display='none'">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= e($item['name']) ?></h5>
                                <p class="card-text text-muted flex-grow-1">
                                    <?= e($item['description']) ?>
                                </p>
                                <p class="price mb-0 mt-2"><?= formatPrice($item['price']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endforeach; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
