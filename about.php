<?php
$pageTitle = 'About Us';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="mb-4">About TastyBites</h1>

<div class="row align-items-center mb-5">
    <div class="col-md-7">
        <p>TastyBites started in 2018 as a small family kitchen with one promise:
           food made from fresh, honest ingredients, served without pretense.</p>
        <p>Over the years we've grown into a neighborhood favorite. Our menu is short
           by design, our suppliers are local, and our welcome is always warm.</p>
        <p>Whether you're stopping by for an espresso or booking a private dinner,
           we're glad to have you.</p>
    </div>
    <div class="col-md-5">
        <div class="bg-secondary rounded" style="height: 280px;"
             aria-label="Restaurant photo placeholder"></div>
    </div>
</div>

<div class="card mb-5">
    <div class="card-body">
        <h4 class="card-title mb-3">Visit Us</h4>
        <div class="row">
            <div class="col-md-4">
                <strong>Address</strong><br>
                123 Bistro Lane<br>
                Foodtown, FT 12345
            </div>
            <div class="col-md-4">
                <strong>Phone</strong><br>
                +1 (555) 123-4567<br>
                <strong>Email</strong><br>
                hello@tastybites.local
            </div>
            <div class="col-md-4">
                <strong>Hours</strong><br>
                Mon–Sun · 11:00 – 22:00<br>
                Last reservation: 21:30
            </div>
        </div>
    </div>
</div>

<h2 class="mb-3">Meet the Team</h2>
<div class="row g-4">
    <?php
    $team = [
        ['name' => 'Marco Bianchi',  'role' => 'Head Chef'],
        ['name' => 'Lena Park',      'role' => 'Sous Chef'],
        ['name' => 'Sam O\'Connor',  'role' => 'Restaurant Manager'],
    ];
    foreach ($team as $member):
    ?>
        <div class="col-md-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="bg-secondary rounded-circle mx-auto mb-3"
                         style="width: 90px; height: 90px;"
                         aria-label="Team member photo placeholder"></div>
                    <h5 class="card-title mb-1"><?= e($member['name']) ?></h5>
                    <p class="text-muted mb-0"><?= e($member['role']) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
