<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . app_url('login.php')); exit; }
$error = '';
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $deleteResponse = app_api_request('DELETE', 'admin/pets/' . $id);
    if ($deleteResponse['ok']) {
        header('Location: ' . app_url('admin/manage_pets.php'));
        exit;
    }
    $error = app_api_error($deleteResponse);
}
$petsResponse = app_api_request('GET', 'admin/pets');
$pets = $petsResponse['data'] ?? [];
?>
<?php include __DIR__ . '/../header.php'; ?>
<div class="container py-5">
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if (!$petsResponse['ok']): ?><div class="alert alert-danger"><?= htmlspecialchars(app_api_error($petsResponse)) ?></div><?php endif; ?>
  <div class="d-flex justify-content-between align-items-center mb-4"><h3 class="fw-bold mb-0">Manage Pets</h3><a class="btn btn-primary" href="<?= app_url('admin/add_pet.php') ?>">Add Pet</a></div>
  <div class="card"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Name</th><th>Species</th><th>Status</th><th>Actions</th></tr></thead><tbody>
  <?php foreach($pets as $pet): ?>
  <tr><td><strong><?= htmlspecialchars($pet['name']) ?></strong></td><td><?= htmlspecialchars($pet['species']) ?></td><td><?= htmlspecialchars($pet['status']) ?></td><td><a class="btn btn-sm btn-outline-primary" href="<?= app_url('admin/edit_pet.php') ?>?id=<?= $pet['id'] ?>">Edit</a> <a class="btn btn-sm btn-outline-danger" href="<?= app_url('admin/manage_pets.php') ?>?delete=<?= $pet['id'] ?>" onclick="return confirm('Delete this pet?')">Delete</a></td></tr>
  <?php endforeach; ?>
  </tbody></table></div></div>
</div>
<?php include __DIR__ . '/../footer.php'; ?>
