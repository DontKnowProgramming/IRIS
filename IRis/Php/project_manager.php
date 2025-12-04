<?php
// === Database connection settings ===
$host = 'localhost';
$db = 'rocelenrj_db';    // Your database name
$user = 'root';          // Default XAMPP username
$pass = '';              // Default XAMPP password (empty)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('Database connection failed: ' . $e->getMessage());
}

// === Remove project if requested ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_project']) && isset($_POST['project_id'])) {
    $id = (int)$_POST['project_id'];
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// === Add new project ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
    $imagePath = "";
    $maxFileSize = 5 * 1024 * 1024;
    if (
        isset($_FILES['project_img']) &&
        $_FILES['project_img']['error'] === UPLOAD_ERR_OK
    ) {
        $fileTmp = $_FILES['project_img']['tmp_name'];
        $fileName = $_FILES['project_img']['name'];
        $fileSize = $_FILES['project_img']['size'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
        $mimeType = mime_content_type($fileTmp);

        if (
            in_array($ext, $allowedExt) &&
            in_array($mimeType, $allowedMime) &&
            $fileSize <= $maxFileSize
        ) {
            $uploadDir = 'uploads';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $imagePath = $uploadDir . '/project_' . time() . '_' . rand(100,999) . '.' . $ext;
            move_uploaded_file($fileTmp, $imagePath);
        }
    }
    $stmt = $pdo->prepare(
        "INSERT INTO projects (title, description, status, image, category, duration, budget, completion, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
    );
    $stmt->execute([
        $_POST['project_title'],
        $_POST['description'] ?? '',
        $_POST['status'],
        $imagePath,
        $_POST['category'] ?? '',
        $_POST['duration'] ?? '',
        $_POST['budget'] ?? '',
        $_POST['completion'] ?? ''
    ]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// === Fetch all projects from DB ===
$stmt = $pdo->prepare("SELECT * FROM projects ORDER BY id DESC");
$stmt->execute();
$projects = $stmt->fetchAll();

// === Summary calculation ===
$summary = [
    "total" => count($projects),
    "completed" => count(array_filter($projects, fn($p)=>isset($p['status']) && $p['status']=='Completed')),
    "inprogress" => count(array_filter($projects, fn($p)=>isset($p['status']) && $p['status']=='In Progress')),
    "planning" => count(array_filter($projects, fn($p)=>isset($p['status']) && $p['status']=='Planning')),
];

// === AJAX handler for View Details modal ===
if (isset($_GET['show_project'])) {
    $id = (int)$_GET['show_project'];
    // Query the single project by id (not index)
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if ($p): ?>
        <div class="modal-header"><h5 class="modal-title"><?= htmlspecialchars($p['title']) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <img src="<?= htmlspecialchars($p['image']) ?>" alt="project" style="width:100%;border-radius:0.5em;margin-bottom:1em;">
            <div><b>Status:</b> <?= htmlspecialchars($p['status']) ?></div>
            <?php if (!empty($p['description'])): ?><div><b>Description:</b> <?= htmlspecialchars($p['description']) ?></div><?php endif; ?>
            <?php if (!empty($p['category'])): ?><div><b>Category:</b> <?= htmlspecialchars($p['category']) ?></div><?php endif; ?>
            <?php if (!empty($p['duration'])): ?><div><b>Duration:</b> <?= htmlspecialchars($p['duration']) ?></div><?php endif; ?>
            <?php if (!empty($p['budget'])): ?><div><b>Budget:</b> <?= htmlspecialchars($p['budget']) ?></div><?php endif; ?>
            <?php if (!empty($p['completion'])): ?><div><b>Completion Date:</b> <?= htmlspecialchars($p['completion']) ?></div><?php endif; ?>
        </div>
    <?php endif;
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Project Manager Dashboard</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
body { background: #f2f6fa; font-family: 'Segoe UI', Arial, sans-serif; }
.topbar {
    background: linear-gradient(90deg, #07a33c 60%, #19d979 100%);
    color: #fff;
    padding: 18px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}
.topbar .left { display: flex; align-items: center; gap: 16px; }
.topbar img { height: 50px; filter: drop-shadow(0 2px 6px rgba(10,133,40,0.13)); }
.topbar h2 { margin: 0; font-weight: 800; font-size: 2.05rem; letter-spacing: 1px;}
.topbar .btn {
    background: #fff;
    color: #07a33c;
    font-weight: 700;
    border-radius: 16px;
    padding: 8px 22px;
    box-shadow: 0 1px 9px rgba(10,133,40,0.051);
    border: 2px solid #19d97955;
    transition: background 0.16s, color 0.16s;
}
.topbar .btn:hover {
    background: #e4fff0;
    color: #068a2b;
}
.summary-cards {
    display: flex;
    gap: 28px;
    justify-content: center;
    margin: 38px 0 30px;
}
.summary-card {
    flex: 1;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 4px 20px rgba(10,133,40,0.08);
    padding: 32px 0 27px 0;
    text-align: center;
    min-width: 170px;
    transition: box-shadow 0.22s;
}
.summary-card:hover { box-shadow: 0 8px 28px rgba(10,133,40,0.13); }
.summary-card span {
    display: block;
    font-size: 1.07rem;
    color: #707070;
    font-weight: 500;
}
.summary-card h1 {
    font-size: 2.4rem;
    margin-bottom: 0;
    letter-spacing: 1.5px;
}
.summary-card-green { color: #19b34e; }
.summary-card-orange { color: #ff9100; }
.summary-card-blue { color: #347eff; }
.tabs {
    display: flex;
    align-items: center;
    background: #e8fff5;
    border-radius: 23px;
    margin-bottom: 18px;
    box-shadow: 0 1px 5px rgba(10,133,40,0.03);
    overflow: hidden;
}
.tab {
    flex: 1;
    padding: 13px 10px;
    text-align: center;
    border: none;
    background: none;
    font-size: 1.16rem;
    font-weight: 700;
    color: #11b155;
    border-radius: 23px;
    transition: .2s;
    letter-spacing: 0.5px;
    cursor: pointer;
}
.tab.active { background: #fff; color: #068a2b; box-shadow: 0 2px 8px #19d97915; }
.section-card {
    background: #fff;
    border-radius: 22px;
    padding: 28px;
    margin-top: 28px;
    box-shadow: 0 3px 14px rgba(7,163,60,0.053);
}
.btn-green {
    background: linear-gradient(90deg, #07a33c 80%, #19d979 100%);
    color: #fff;
    font-weight: 700;
    border-radius: 13px;
    border: none;
    padding: 9px 27px;
    box-shadow: 0 2px 12px rgba(10,133,40,0.09);
    font-size: 1rem;
    transition: background 0.15s;
}
.btn-green:hover {
    background: #068a2b;
    color: #eaffed;
}
.project-cards { display: flex; flex-wrap: wrap; gap: 30px; justify-content: flex-start;}
.project-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 16px rgba(16,180,74,0.11);
    max-width: 340px;
    width: 100%;
    transition: box-shadow 0.18s;
    border: 1px solid #e2ffe7;
}
.project-card:hover { box-shadow: 0 8px 30px rgba(16,180,74,0.17);}
.project-img {
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
    width: 100%;
    height: 180px;
    object-fit: cover;
    filter: brightness(0.93);
}
.project-title {
    font-size: 1.18rem;
    font-weight: 700;
    margin: 13px 0 7px 0;
    color: #099b3d;
    letter-spacing: 0.3px;
}
.badge-small {
    font-size: 1.01rem;
    padding: 7px 17px;
    border-radius: 13px;
    margin-bottom: 8px;
    font-weight: 700;
    letter-spacing: 0.4px;
}
.badge-Completed { background: #171426; color: #fff;}
.badge-InProgress { background: #eaffed; color: #15be44;}
.badge-Planning { background: #fdedda; color: #ff9100; }
.modal-content label {font-weight: 700;}
.modal-content input,
.modal-content textarea,
.modal-content select {margin-bottom: 12px;}
</style>
</head>
<body>
<div class="topbar mb-4">
    <div class="left">
       <a href="login.php" class="btn btn-success">&larr; Back to Dashboard</a>
        <img src="https://img.icons8.com/color/48/000000/city-buildings.png" />
        <div>
            <h2 class="mb-0">Project Manager Dashboard</h2>
            <span style="font-size:1.05rem;font-weight:400;">Portfolio & Project Management</span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-outline" style="font-size:.98rem;">Project Manager</button>
        <button class="btn btn-outline ms-2">Logout</button>
    </div>
</div>
<div class="container">
    <div class="summary-cards">
        <div class="summary-card"><h1 class="summary-card-green"><?= $summary["total"] ?></h1><span>Total Projects</span></div>
        <div class="summary-card"><h1 class="summary-card-green"><?= $summary["completed"] ?></h1><span>Completed</span></div>
        <div class="summary-card"><h1 class="summary-card-blue"><?= $summary["inprogress"] ?></h1><span>In Progress</span></div>
        <div class="summary-card"><h1 class="summary-card-orange"><?= $summary["planning"] ?></h1><span>Planning</span></div>
    </div>
    <div class="tabs">
        <button class="tab active" onclick="showTab('portfolio')">Portfolio Management</button>
        <button class="tab" onclick="showTab('analytics')">Project Analytics</button>
    </div>
    <div id="portfolio" class="section-card tab-content active">
        <div class="d-flex justify-content-between mb-3 align-items-center">
            <div>
                <h5 class="mb-0">Project Portfolio</h5>
                <div style="color:#888;">Manage and showcase your construction projects</div>
            </div>
            <div>
                <button class="btn btn-green me-2" data-bs-toggle="modal" data-bs-target="#addProjectModal">Add New Project</button>
                <button class="btn btn-outline">Export Portfolio</button>
            </div>
        </div>
        <div class="project-cards">
        <?php foreach($projects as $i => $p):
            $badge = ($p['status']=="Completed") ? "badge-Completed" : (($p['status']=="In Progress") ? "badge-InProgress":"badge-Planning");
        ?>
            <div class="project-card">
                <img class="project-img" src="<?= htmlspecialchars($p['image']) ?>" alt="project">
                <div class="px-3 py-1">
                    <div class="project-title"><?= htmlspecialchars($p['title']) ?></div>
                    <span class="badge badge-small <?= $badge ?>">
                        <?= ($p['status']=="In Progress") ? "In Progress" : $p['status'] ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center px-3 pb-3">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="project_id" value="<?= htmlspecialchars($p['id']) ?>">
                        <button type="submit" name="remove_project" class="btn btn-danger btn-sm">Remove</button>
                    </form>
                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="showDetailsModal(<?= $p['id'] ?>)">View Details</button>
                </div>
            </div>
        <?php endforeach ?>
        </div>
    </div>
    <div id="analytics" class="section-card tab-content" style="display:none;">
        <h5>Project Analytics</h5>
        <p>Coming Soon...</p>
    </div>
    <!-- Modal for Add Project -->
    <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
      <div class="modal-dialog">
      <form method="POST" enctype="multipart/form-data" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addProjectModalLabel">Add New Project</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-2" style="color:#555;">Add a new project to your portfolio showcase.</div>
            <label>Project Title</label>
            <input type="text" name="project_title" class="form-control" placeholder="Enter project title" required>
            <label>Description</label>
            <textarea name="description" class="form-control" placeholder="Enter project description"></textarea>
            <div class="row">
                <div class="col-6">
                    <label>Category</label>
                    <select name="category" class="form-select">
                        <option value="">Select category</option>
                        <option>Hospitality</option>
                        <option>Commercial</option>
                        <option>Residential</option>
                        <option>Industrial</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="col-6">
                    <label>Status</label>
                    <select name="status" class="form-select">
                        <option>Planning</option>
                        <option>In Progress</option>
                        <option>Completed</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <label>Duration</label>
                    <input type="text" name="duration" class="form-control" placeholder="e.g., 18 months">
                </div>
                <div class="col-6">
                    <label>Budget</label>
                    <input type="text" name="budget" class="form-control" placeholder="e.g., ₱85M">
                </div>
            </div>
            <label>Completion Date</label>
            <input type="date" name="completion" class="form-control">
            <label>Image (Optional)</label>
            <input type="file" name="project_img" accept="image/*" class="form-control">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add_project" class="btn btn-green">Add Project</button>
          </div>
        </form>
      </div>
    </div>
    <!-- Modal for Details -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content" id="detailsModalContent"></div>
      </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showTab(tab) {
    document.querySelectorAll('.tab').forEach(button=>button.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(div=>div.style.display='none');
    if(tab==='portfolio'){document.querySelectorAll('.tab')[0].classList.add('active');document.getElementById('portfolio').style.display='block';}
    if(tab==='analytics'){document.querySelectorAll('.tab')[1].classList.add('active');document.getElementById('analytics').style.display='block';}
}
function showDetailsModal(index) {
    fetch('<?= $_SERVER['PHP_SELF'] ?>?show_project=' + index)
      .then(res => res.text())
      .then(html => {
        document.getElementById('detailsModalContent').innerHTML = html;
        var modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        modal.show();
      });
}
</script>
</body>
</html>
