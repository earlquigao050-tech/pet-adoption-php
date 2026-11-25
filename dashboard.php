<?php
require_once __DIR__ . '/config.php';
if(!isset($_SESSION['user_id'])) { header('Location: ' . app_url('login.php')); exit; }
include __DIR__ . '/header.php';
$requestsResponse = app_api_request('GET', 'me/requests');
$requests = $requestsResponse['data'] ?? [];
$total_req = count($requests);
$approved = count(array_filter($requests, function ($request) { return $request['status'] === 'Approved'; }));
$pending = count(array_filter($requests, function ($request) { return $request['status'] === 'Pending'; }));
$rejected = count(array_filter($requests, function ($request) { return $request['status'] === 'Rejected'; }));
?>
<div class="container py-5">
  <?php if (!$requestsResponse['ok']): ?><div class="alert alert-danger"><?= htmlspecialchars(app_api_error($requestsResponse)) ?></div><?php endif; ?>
  <div class="row mb-4">
    <div class="col">
      <h3 class="fw-bold"><i class="fas fa-tachometer-alt me-2" style="color:var(--primary)"></i>My Dashboard</h3>
      <p class="text-muted">Welcome back, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong>! 👋</p>
    </div>
    <div class="col-auto">
      <a href="<?= app_url('pets.php') ?>" class="btn btn-primary"><i class="fas fa-search me-2"></i>Find a Pet</a>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stats-card"><div class="stats-icon">📋</div><h3 class="fw-bold" style="color:var(--primary)"><?= $total_req ?></h3><p class="text-muted mb-0">Total Requests</p></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stats-card"><div class="stats-icon">✅</div><h3 class="fw-bold" style="color:var(--accent)"><?= $approved ?></h3><p class="text-muted mb-0">Approved</p></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stats-card"><div class="stats-icon">⏳</div><h3 class="fw-bold text-warning"><?= $pending ?></h3><p class="text-muted mb-0">Pending</p></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stats-card"><div class="stats-icon">❌</div><h3 class="fw-bold text-danger"><?= $rejected ?></h3><p class="text-muted mb-0">Rejected</p></div>
    </div>
  </div>

  <div class="card">
    <div class="card-header bg-white py-3">
      <h5 class="mb-0 fw-bold"><i class="fas fa-clipboard-list me-2" style="color:var(--primary)"></i>My Adoption Requests</h5>
    </div>
    <div class="card-body p-0">
      <?php if($total_req == 0): ?>
      <div class="text-center py-5">
        <div style="font-size:4rem">🐾</div>
        <h5 class="text-muted">You don't have any adoption requests yet.</h5>
        <p class="text-muted">Find a pet and submit an adoption request to get started!</p>
        <a href="<?= app_url('pets.php') ?>" class="btn btn-primary mt-2"><i class="fas fa-search me-2"></i>Browse Pets</a>
      </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Pet</th>
              <th>Breed</th>
              <th>Species</th>
              <th>Request Date</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($requests as $req): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <?php
                $has = !empty($req['image_url']);
                if($has) $src = $req['image_url'];
                elseif($req['species']=='Cat') $src = 'https://cataas.com/cat?width=50&height=50';
                else $src = 'https://placedog.net/50/50?id='.$req['pet_id'];
                ?>
                <img src="<?= $src ?>" class="rounded-circle" width="45" height="45" style="object-fit:cover">
                <strong><?= htmlspecialchars($req['pet_name']) ?></strong>
              </div>
            </td>
            <td><?= htmlspecialchars($req['breed']) ?></td>
            <td><span class="badge-species"><?= $req['species'] ?></span></td>
            <td><?= date('d M Y', strtotime($req['created_at'])) ?></td>
            <td>
              <?php
              $badges = ['Pending'=>'warning','Approved'=>'success','Rejected'=>'danger'];
              $icons = ['Pending'=>'clock','Approved'=>'check-circle','Rejected'=>'times-circle'];
              $s = $req['status'];
              ?>
              <span class="badge bg-<?= $badges[$s] ?>"><i class="fas fa-<?= $icons[$s] ?> me-1"></i><?= $s ?></span>
            </td>
            <td>
              <a href="<?= app_url('pet_detail.php') ?>?id=<?= $req['pet_id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-eye me-1"></i>View
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
