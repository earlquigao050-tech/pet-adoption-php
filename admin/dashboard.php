<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . app_url('login.php'));
    exit;
}
$statsResponse = app_api_request('GET', 'admin/dashboard');
$stats = $statsResponse['data'] ?? ['pets' => 0, 'available_pets' => 0, 'pending_requests' => 0, 'users' => 0];
include __DIR__ . '/../header.php';
?>
<div class="container py-5">
  <?php if (!$statsResponse['ok']): ?><div class="alert alert-danger"><?= htmlspecialchars(app_api_error($statsResponse)) ?></div><?php endif; ?>
  <h3 class="fw-bold mb-4"><i class="fas fa-cog me-2" style="color:var(--primary)"></i>Admin Dashboard</h3>
  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="stats-card"><div class="stats-icon">🐾</div><h3><?= (int)$stats['pets'] ?></h3><p class="text-muted mb-0">Total Pets</p></div></div>
    <div class="col-md-3"><div class="stats-card"><div class="stats-icon">✅</div><h3><?= (int)$stats['available_pets'] ?></h3><p class="text-muted mb-0">Available</p></div></div>
    <div class="col-md-3"><div class="stats-card"><div class="stats-icon">⏳</div><h3><?= (int)$stats['pending_requests'] ?></h3><p class="text-muted mb-0">Pending Requests</p></div></div>
    <div class="col-md-3"><div class="stats-card"><div class="stats-icon">👥</div><h3><?= (int)$stats['users'] ?></h3><p class="text-muted mb-0">Users</p></div></div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-primary" href="<?= app_url('admin/add_pet.php') ?>"><i class="fas fa-plus me-2"></i>Add Pet</a>
    <a class="btn btn-outline-primary" href="<?= app_url('admin/manage_pets.php') ?>"><i class="fas fa-list me-2"></i>Manage Pets</a>
    <a class="btn btn-outline-primary" href="<?= app_url('admin/manage_requests.php') ?>"><i class="fas fa-clipboard-list me-2"></i>Requests</a>
    <a class="btn btn-outline-primary" href="<?= app_url('admin/manage_users.php') ?>"><i class="fas fa-users me-2"></i>Users</a>
  </div>
</div>
<?php include __DIR__ . '/../footer.php'; ?>
