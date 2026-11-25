<?php include 'header.php'; ?>
<?php
$filters = [];
foreach (['species', 'size', 'gender', 'search'] as $filter) {
    if (!empty($_GET[$filter])) $filters[] = rawurlencode($filter) . '=' . rawurlencode($_GET[$filter]);
}
$petsResponse = app_api_request('GET', 'pets' . ($filters ? '?' . implode('&', $filters) : ''));
$pets = $petsResponse['data'] ?? [];
$total = count($pets);
?>

<div class="container py-5">
  <?php if (!$petsResponse['ok']): ?><div class="alert alert-danger"><?= htmlspecialchars(app_api_error($petsResponse)) ?></div><?php endif; ?>
  <h2 class="section-title text-center mb-5">🐾 Available Pets (<?= $total ?>)</h2>

  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label fw-semibold">🔍 Search</label>
          <input type="text" name="search" class="form-control" placeholder="Pet name ya breed..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Species</label>
          <select name="species" class="form-select">
            <option value="">All</option>
            <?php foreach(['Dog','Cat','Bird','Rabbit','Other'] as $s): ?>
            <option <?= ($_GET['species'] ?? '')==$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Size</label>
          <select name="size" class="form-select">
            <option value="">All</option>
            <?php foreach(['Small','Medium','Large'] as $s): ?>
            <option <?= ($_GET['size'] ?? '')==$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Gender</label>
          <select name="gender" class="form-select">
            <option value="">All</option>
            <option <?= ($_GET['gender'] ?? '')=='Male'?'selected':'' ?>>Male</option>
            <option <?= ($_GET['gender'] ?? '')=='Female'?'selected':'' ?>>Female</option>
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-filter me-1"></i>Filter</button>
          <a href="pets.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
        </div>
      </form>
    </div>
  </div>

  <div class="row g-4">
    <?php if($total == 0): ?>
    <div class="col-12 text-center py-5">
      <div style="font-size:5rem">🐾</div>
      <h4 class="text-muted">Koi pet nahi mila. Filter change karo.</h4>
      <a href="pets.php" class="btn btn-primary mt-3">Sab Pets Dekho</a>
    </div>
    <?php endif; ?>
    <?php foreach($pets as $pet): ?>
    <div class="col-lg-3 col-md-4 col-sm-6">
      <div class="card pet-card h-100">
        <div class="position-relative">
          <?php
          if(!empty($pet['image_url'])) {
            $img_src = $pet['image_url'];
          } elseif($pet['species']=='Cat') {
            $img_src = 'https://cataas.com/cat?width=300&height=220&tag='.$pet['id'];
          } elseif($pet['species']=='Bird') {
            $img_src = 'https://picsum.photos/seed/bird'.$pet['id'].'/300/220';
          } elseif($pet['species']=='Rabbit') {
            $img_src = 'https://picsum.photos/seed/rabbit'.$pet['id'].'/300/220';
          } elseif($pet['species']=='Other') {
            $img_src = 'https://picsum.photos/seed/other'.$pet['id'].'/300/220';
          } else {
            $img_src = 'https://placedog.net/300/220?id='.$pet['id'];
          }
          ?>
          <img src="<?= $img_src ?>" class="card-img-top" alt="<?= $pet['name'] ?>" style="height:200px;object-fit:cover">
          <span class="position-absolute top-0 end-0 m-2 badge-species"><?= $pet['species'] ?></span>
        </div>
        <div class="card-body">
          <h6 class="fw-bold mb-1"><?= htmlspecialchars($pet['name']) ?></h6>
          <small class="text-muted d-block"><?= htmlspecialchars($pet['breed']) ?></small>
          <div class="d-flex gap-2 mt-2 flex-wrap">
            <span class="badge bg-light text-dark"><i class="fas fa-birthday-cake"></i> <?= $pet['age'] ?>yr</span>
            <span class="badge bg-light text-dark"><?= $pet['gender'] ?></span>
            <span class="badge bg-light text-dark"><?= $pet['size'] ?></span>
          </div>
        </div>
        <div class="card-footer bg-white border-0 pb-3">
          <a href="<?= app_url('pet_detail.php') ?>?id=<?= $pet['id'] ?>" class="btn btn-primary w-100 btn-sm">
            <i class="fas fa-eye me-1"></i>Details & Adopt
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include 'footer.php'; ?>
