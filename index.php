<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

// Fetch 3 random available items with category name.
$stmt = $db->prepare(
    "SELECT mi.id, mi.name, mi.description, mi.price, mi.image_url,
            mc.name AS category_name
     FROM menu_items mi
     JOIN menu_categories mc ON mc.id = mi.category_id
     WHERE mi.is_available = 1
     ORDER BY RAND()
     LIMIT 3"
);
$stmt->execute();
$res      = $stmt->get_result();
$featured = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<section class="tb-hero text-center text-white">
    <div class="container py-5">
        <h1 class="display-4 fw-bold mb-3">Welcome to <?= APP_NAME ?></h1>
        <p class="lead mb-4">Fresh ingredients. Honest cooking. Reserve your table today.</p>
        <a href="<?= BASE_URL ?>/menu.php" class="btn btn-light btn-lg me-2">View Menu</a>
        <a href="<?= BASE_URL ?>/reservation.php" class="btn btn-outline-light btn-lg">Book a Table</a>
    </div>
</section>

<section class="my-5">
    <h2 class="text-center mb-4">Today's Picks</h2>
    <div class="row g-4">
        <?php foreach ($featured as $item): ?>
            <div class="col-md-4">
                <div class="card menu-card h-100">
                    <?php if (!empty($item['image_url'])): ?>
                        <img src="<?= BASE_URL ?>/<?= e($item['image_url']) ?>"
                             alt="<?= e($item['name']) ?>" class="card-img-top"
                             onerror="this.style.display='none'">
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="badge bg-secondary mb-2"><?= e($item['category_name']) ?></span>
                        <h5 class="card-title"><?= e($item['name']) ?></h5>
                        <p class="card-text text-muted"><?= e($item['description']) ?></p>
                        <p class="price mb-0"><?= formatPrice($item['price']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="text-center py-4">
    <h3>Open Daily</h3>
    <p class="lead mb-1">11:00 – 22:00</p>
    <p class="text-muted">Last reservation slot: 21:30</p>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
