<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . app_url('login.php')); exit; }
$error = '';
if (isset($_GET['delete'])) {
    $id=intval($_GET['delete']);
    $deleteResponse = app_api_request('DELETE', 'admin/users/' . $id);
    if ($deleteResponse['ok']) {
        header('Location: ' . app_url('admin/manage_users.php'));
        exit;
    }
    $error = app_api_error($deleteResponse);
}
$usersResponse = app_api_request('GET', 'admin/users');
$users = $usersResponse['data'] ?? [];
?>
<?php include __DIR__ . '/../header.php'; ?>
<div class="container py-5"><?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?><?php if (!$usersResponse['ok']): ?><div class="alert alert-danger"><?= htmlspecialchars(app_api_error($usersResponse)) ?></div><?php endif; ?><h3 class="fw-bold mb-4">Manage Users</h3><div class="card"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead><tbody>
<?php foreach($users as $user): ?>
<tr><td><?= htmlspecialchars($user['name']) ?></td><td><?= htmlspecialchars($user['email']) ?></td><td><?= htmlspecialchars($user['role']) ?></td><td><?php if($user['id'] != $_SESSION['user_id']): ?><a class="btn btn-sm btn-outline-danger" href="<?= app_url('admin/manage_users.php') ?>?delete=<?= $user['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a><?php endif; ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div></div>
<?php include __DIR__ . '/../footer.php'; ?>
