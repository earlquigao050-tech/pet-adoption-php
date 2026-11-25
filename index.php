<?php include 'header.php'; ?>
<?php
$statsResponse = app_api_request('GET', 'stats');
$stats = $statsResponse['data'] ?? ['available_pets' => 0, 'adopted_pets' => 0, 'users' => 0];
$featuredResponse = app_api_request('GET', 'pets?limit=6');
$featured = $featuredResponse['data'] ?? [];
$backendError = !$statsResponse['ok'] ? app_api_error($statsResponse) : (!$featuredResponse['ok'] ? app_api_error($featuredResponse) : '');
?>

<?php if ($backendError): ?><div class="container mt-3"><div class="alert alert-danger"><?= htmlspecialchars($backendError) ?></div></div><?php endif; ?>
<section class="hero-section text-center">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h1 class="display-4 fw-bold mb-3">🐾 Find Your Perfect Pet</h1>
        <p class="lead mb-4 text-white-50">Thousands of adorable pets are waiting for you. Give one a loving home and change their life.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <a href="<?= app_url('pets.php') ?>" class="btn btn-primary btn-lg px-5"><i class="fas fa-search me-2"></i>Browse Pets</a>
          <a href="<?= app_url('register.php') ?>" class="btn btn-outline-light btn-lg px-5"><i class="fas fa-heart me-2"></i>Join Us</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-white">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="stats-card">
          <div class="stats-icon">🐕</div>
          <h2 class="fw-bold" style="color:var(--primary)"><?= (int)$stats['available_pets'] ?>+</h2>
          <p class="text-muted mb-0">Pets Available</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card">
          <div class="stats-icon">🏠</div>
          <h2 class="fw-bold" style="color:var(--accent)"><?= (int)$stats['adopted_pets'] ?>+</h2>
          <p class="text-muted mb-0">Successfully Adopted</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card">
          <div class="stats-icon">👥</div>
          <h2 class="fw-bold" style="color:var(--secondary)"><?= (int)$stats['users'] ?>+</h2>
          <p class="text-muted mb-0">Happy Adopters</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <h2 class="section-title text-center mb-5">Featured Pets 🐾</h2>
    <div class="row g-4">
      <?php foreach($featured as $pet): ?>
      <div class="col-lg-4 col-md-6">
        <div class="card pet-card h-100">
          <div class="position-relative">
            <?php
          $img_src = $pet['image_url'] ?: 'https://placedog.net/400/300?id='.$pet['id'];
          if($pet['species']=='Cat' && empty($pet['image_url'])) $img_src = 'https://cataas.com/cat?width=400&height=300';
          if($pet['species']=='Bird' && empty($pet['image_url'])) $img_src = 'https://picsum.photos/seed/bird'.$pet['id'].'/400/300';
          if($pet['species']=='Rabbit' && empty($pet['image_url'])) $img_src = 'https://picsum.photos/seed/rabbit'.$pet['id'].'/400/300';
          if($pet['species']=='Other' && empty($pet['image_url'])) $img_src = 'https://picsum.photos/seed/pet'.$pet['id'].'/400/300';
            ?>
            <img src="<?= $img_src ?>" class="card-img-top" alt="<?= $pet['name'] ?>" style="height:220px;object-fit:cover">
            <span class="position-absolute top-0 end-0 m-2 badge-species"><?= $pet['species'] ?></span>
            <span class="position-absolute top-0 start-0 m-2 badge-available"><?= $pet['status'] ?></span>
          </div>
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h5 class="card-title fw-bold mb-0"><?= htmlspecialchars($pet['name']) ?></h5>
              <small class="text-muted"><i class="fas fa-birthday-cake me-1"></i><?= $pet['age'] ?> yr</small>
            </div>
            <p class="text-muted small mb-1"><i class="fas fa-dog me-1" style="color:var(--primary)"></i><?= htmlspecialchars($pet['breed']) ?></p>
            <p class="text-muted small mb-2"><i class="fas fa-venus-mars me-1" style="color:var(--primary)"></i><?= $pet['gender'] ?> &bull; <?= $pet['size'] ?></p>
            <p class="card-text small text-muted"><?= substr(htmlspecialchars($pet['description']),0,80) ?>...</p>
          </div>
          <div class="card-footer bg-white border-0 pb-3">
            <a href="<?= app_url('pet_detail.php') ?>?id=<?= $pet['id'] ?>" class="btn btn-primary w-100">
              <i class="fas fa-heart me-2"></i>Adopt <?= htmlspecialchars($pet['name']) ?>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="<?= app_url('pets.php') ?>" class="btn btn-outline-primary btn-lg px-5">
        <i class="fas fa-paw me-2"></i>See All Pets
      </a>
    </div>
  </div>
</section>

<section class="py-5 bg-white">
  <div class="container">
    <h2 class="section-title text-center mb-5">Adoption Process 📋</h2>
    <div class="row g-4 text-center">
      <div class="col-md-3">
        <div class="p-4">
          <div style="width:70px;height:70px;background:linear-gradient(135deg,var(--primary),#ff8c5a);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.8rem;color:white;font-weight:bold;">1</div>
          <h5 class="fw-bold">Create an Account</h5>
          <p class="text-muted small">Start by creating an account and filling in your details.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4">
          <div style="width:70px;height:70px;background:linear-gradient(135deg,#3498DB,#5DADE2);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.8rem;color:white;font-weight:bold;">2</div>
          <h5 class="fw-bold">Find a Pet</h5>
          <p class="text-muted small">Browse and choose the pet that is right for you.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4">
          <div style="width:70px;height:70px;background:linear-gradient(135deg,#27AE60,#2ECC71);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.8rem;color:white;font-weight:bold;">3</div>
          <h5 class="fw-bold">Send a Request</h5>
          <p class="text-muted small">Send an adoption request and include a message.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4">
          <div style="width:70px;height:70px;background:linear-gradient(135deg,#9B59B6,#BB8FCE);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.8rem;color:white;font-weight:bold;">4</div>
          <h5 class="fw-bold">Bring Them Home! 🏠</h5>
          <p class="text-muted small">Once approved, bring your new family member home.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>
