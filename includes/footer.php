</div><!-- /.app-shell -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?php if (!empty($pageScripts)): foreach ((array)$pageScripts as $src): ?>
<script src="<?= htmlspecialchars($src) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
