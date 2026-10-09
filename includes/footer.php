<?php
/**
 * TastyBites — Layout Footer
 * Closes <main>, renders footer, includes Bootstrap JS + main.js.
 */
?>
    </main>

    <footer class="tb-footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>TastyBites</h5>
                    <p class="mb-0">Fresh ingredients. Honest cooking.</p>
                </div>
                <div class="col-md-4">
                    <h6>Visit Us</h6>
                    <p class="mb-0">123 Bistro Lane, Foodtown</p>
                    <p class="mb-0">+1 (555) 123-4567</p>
                </div>
                <div class="col-md-4">
                    <h6>Opening Hours</h6>
                    <p class="mb-0">Mon–Sun · 11:00 – 22:00</p>
                    <p class="mb-0">Last reservation slot: 21:30</p>
                </div>
            </div>
            <hr>
            <p class="text-center mb-0 small">© <?= date('Y') ?> TastyBites. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
