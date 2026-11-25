<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . app_url('login.php')); exit; }
include __DIR__ . '/../header.php';
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $petResponse = app_api_request('POST', 'admin/pets', [
        'name' => trim($_POST['name'] ?? ''),
        'breed' => trim($_POST['breed'] ?? ''),
        'species' => $_POST['species'] ?? 'Other',
        'age' => intval($_POST['age'] ?? 0),
        'gender' => $_POST['gender'] ?? 'Male',
        'size' => $_POST['size'] ?? 'Medium',
        'color' => trim($_POST['color'] ?? ''),
        'description' => trim($_POST['description'] ?? '')
    ], $_FILES['image'] ?? null);
    if ($petResponse['ok']) $success = 'Pet added successfully.';
    else $error = app_api_error($petResponse);
}
?>
<div class="container py-5">
  <h3 class="fw-bold mb-4">Add Pet</h3>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
  <div class="card p-4">
    <form method="POST" enctype="multipart/form-data" class="row g-3">
      <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
      <div class="col-md-6"><label class="form-label">Breed</label><input class="form-control" name="breed"></div>
      <div class="col-md-3"><label class="form-label">Species</label><select class="form-select" name="species"><?php foreach(['Dog','Cat','Bird','Rabbit','Other'] as $v): ?><option><?= $v ?></option><?php endforeach; ?></select></div>
      <div class="col-md-3"><label class="form-label">Age (years)</label><input class="form-control" name="age" type="number" min="0"></div>
      <div class="col-md-3"><label class="form-label">Gender</label><select class="form-select" name="gender"><option>Male</option><option>Female</option></select></div>
      <div class="col-md-3"><label class="form-label">Size</label><select class="form-select" name="size"><option>Small</option><option selected>Medium</option><option>Large</option></select></div>
      <div class="col-md-6"><label class="form-label">Color</label><input class="form-control" name="color"></div>
      <div class="col-md-6"><label class="form-label">Image</label><input class="form-control" name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp"></div>
      <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="4"></textarea></div>
      <div class="col-12"><button class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Pet</button></div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../footer.php'; ?>
