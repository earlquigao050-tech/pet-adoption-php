<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . app_url('login.php')); exit; }
$error = '';
if (isset($_GET['action'], $_GET['id'])) {
    $requestId = intval($_GET['id']);
    $action = $_GET['action'];
    $status = $action === 'approve' ? 'Approved' : ($action === 'reject' ? 'Rejected' : '');
    if ($status !== '') {
        $decisionResponse = app_api_request('PATCH', 'admin/requests/' . $requestId, ['status' => $status]);
        if ($decisionResponse['ok']) {
            header('Location: ' . app_url('admin/manage_requests.php'));
            exit;
        }
        $error = app_api_error($decisionResponse);
    }
}
$requestsResponse = app_api_request('GET', 'admin/requests');
$requests = $requestsResponse['data'] ?? [];
?>
<?php include __DIR__ . '/../header.php'; ?>
<div class="container py-5"><?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?><?php if (!$requestsResponse['ok']): ?><div class="alert alert-danger"><?= htmlspecialchars(app_api_error($requestsResponse)) ?></div><?php endif; ?><h3 class="fw-bold mb-4">Adoption Requests</h3><div class="card"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>User</th><th>Pet</th><th>Message</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($requests as $request): ?>
<tr><td><strong><?= htmlspecialchars($request['user_name']) ?></strong><br><small><?= htmlspecialchars($request['email']) ?></small></td><td><?= htmlspecialchars($request['pet_name']) ?></td><td><?= htmlspecialchars($request['message']) ?></td><td><?= htmlspecialchars($request['status']) ?></td><td><?php if($request['status']==='Pending'): ?><a class="btn btn-sm btn-success" href="<?= app_url('admin/manage_requests.php') ?>?action=approve&id=<?= $request['id'] ?>">Approve</a> <a class="btn btn-sm btn-danger" href="<?= app_url('admin/manage_requests.php') ?>?action=reject&id=<?= $request['id'] ?>">Reject</a><?php else: ?><span class="text-muted">Complete</span><?php endif; ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div></div>
<?php include __DIR__ . '/../footer.php'; ?>
