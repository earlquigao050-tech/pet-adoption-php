<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: ' . app_url('login.php')); exit; }
$id = intval($_GET['id'] ?? 0);
$petResponse = app_api_request('GET', 'pets/' . $id);
$pet = $petResponse['data'];
if (!$pet) { header('Location: ' . app_url('admin/manage_pets.php')); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $petUpdate = app_api_request('PATCH', 'admin/pets/' . $id, [
        'name' => trim($_POST['name'] ?? ''),
        'breed' => trim($_POST['breed'] ?? ''),
        'species' => $_POST['species'] ?? '',
        'age' => intval($_POST['age'] ?? 0),
        'gender' => $_POST['gender'] ?? '',
        'size' => $_POST['size'] ?? '',
        'color' => trim($_POST['color'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'status' => $_POST['status'] ?? ''
    ]);
    if ($petUpdate['ok']) { header('Location: ' . app_url('admin/manage_pets.php')); exit; }
    $error = app_api_error($petUpdate);
}
?>
<?php include __DIR__ . '/../header.php'; ?>
<div class="container py-5"><h3 class="fw-bold mb-4">Edit Pet</h3>
<?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="card p-4"><form method="POST" class="row g-3">
<div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" value="<?= htmlspecialchars($pet['name']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Breed</label><input class="form-control" name="breed" value="<?= htmlspecialchars($pet['breed']) ?>"></div>
<div class="col-md-3"><label class="form-label">Species</label><select class="form-select" name="species"><?php foreach(['Dog','Cat','Bird','Rabbit','Other'] as $v): ?><option <?= $pet['species']===$v?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Age</label><input class="form-control" type="number" name="age" min="0" value="<?= intval($pet['age']) ?>"></div>
<div class="col-md-2"><label class="form-label">Gender</label><select class="form-select" name="gender"><option <?= $pet['gender']==='Male'?'selected':'' ?>>Male</option><option <?= $pet['gender']==='Female'?'selected':'' ?>>Female</option></select></div>
<div class="col-md-2"><label class="form-label">Size</label><select class="form-select" name="size"><?php foreach(['Small','Medium','Large'] as $v): ?><option <?= $pet['size']===$v?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><?php foreach(['Available','Pending','Adopted'] as $v): ?><option <?= $pet['status']===$v?'selected':'' ?>><?= $v ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Color</label><input class="form-control" name="color" value="<?= htmlspecialchars($pet['color']) ?>"></div>
<div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($pet['description']) ?></textarea></div>
<div class="col-12"><button class="btn btn-primary">Save Changes</button></div>
</form></div></div>
<?php include __DIR__ . '/../footer.php'; ?>
