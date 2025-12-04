<?php
// user management.php
session_start();
require_once __DIR__ . "/config.php"; // defines $pdo (PDO)

// Access control: only logged-in admins
if (!isset($_SESSION['user_id'])) {
    header("Location: user management.php");
    exit();
}

// Optional: further restrict to Admin department/role
// if (empty($_SESSION['department']) || $_SESSION['department'] !== 'Admin') { http_response_code(403); exit('Forbidden'); }

// Fetch summary counts and user list
try {
    // Total users
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = (int)$totalStmt->fetchColumn();

    // Count by department/role (adjust columns if different)
    $adminStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE department = ?");
    $adminStmt->execute(['Administration']);
    $adminUsers = (int)$adminStmt->fetchColumn();

    $hrStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE department = ?");
    $hrStmt->execute(['Human Resources']);
    $hrUsers = (int)$hrStmt->fetchColumn();

    $finStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE department = ?");
    $finStmt->execute(['Finance']);
    $finUsers = (int)$finStmt->fetchColumn();

    // Fetch user rows (adjust column names to match your schema)
    $usersStmt = $pdo->query("
        SELECT 
            id,
            employee_id,
            full_name,
            email,
            phone,
            position,
            department,
            username,
            created_at
        FROM users
        ORDER BY created_at ASC, id ASC
    ");
    $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    http_response_code(500);
    exit("Database error.");
}

// Helper for safe output
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Simple badge color map
function roleBadge($position) {
    $p = strtolower(trim((string)$position));
    if (str_contains($p, 'admin')) return ['Administrator', '#6f42c1'];
    if (str_contains($p, 'hr')) return ['HR Manager', '#0d6efd'];
    if (str_contains($p, 'finance') || str_contains($p, 'account')) return ['Finance', '#198754'];
    return [$position ?: 'User', '#6c757d'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User Management - Rocelyn RJ Building Trades Inc</title>
  <style>
    :root{
      --green:#0a8f3c;
      --green-2:#1a9f3f;
      --bg:#f3fff6;
      --card:#ffffff;
      --muted:#5b6b62;
      --border:#e6f3ea;
      --shadow:0 6px 16px rgba(0,0,0,0.06);
    }
    *{box-sizing:border-box}
    body{
      margin:0; font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
      background:linear-gradient(180deg,#ebfff2 0%, #f9fffb 100%);
      color:#222;
    }
    .topbar{
      background:var(--green);
      color:#fff;
      padding:14px 16px;
      display:flex; align-items:center; justify-content:space-between;
    }
    .top-left{display:flex; align-items:center; gap:12px}
    .logo{
      width:36px; height:36px; background:#fff; border-radius:10px;
      display:grid; place-items:center; color:var(--green); font-weight:700;
      box-shadow:var(--shadow);
    }
    .title{
      font-weight:700; font-size:18px;
      display:flex; flex-direction:column; line-height:1.1;
    }
    .subtitle{font-weight:500; font-size:12px; opacity:.9}
    .actions{display:flex; gap:10px}
    .btn{
      padding:8px 14px; border-radius:8px; border:1px solid transparent;
      background:#fff; color:var(--green); font-weight:700; cursor:pointer;
    }
    .btn.secondary{background:transparent; color:#fff; border-color:#ffffff80}
    .container{max-width:1100px; margin:22px auto; padding:0 16px}
    .kpis{display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px}
    .kpi{
      background:var(--card); border:1px solid var(--border); border-radius:16px;
      padding:16px; box-shadow:var(--shadow); display:flex; justify-content:space-between; align-items:center;
    }
    .kpi h4{margin:0; font-size:14px; color:#2c4636}
    .kpi .val{font-size:28px; font-weight:800}
    .pill{
      width:42px; height:42px; border-radius:12px; display:grid; place-items:center; font-size:18px;
      color:#fff;
    }
    .pill.purple{background:#6f42c1}
    .pill.blue{background:#0d6efd}
    .pill.teal{background:#20c997}
    .section{
      background:var(--card); border:1px solid var(--border); border-radius:16px;
      margin-top:18px; box-shadow:var(--shadow);
    }
    .section-header{
      padding:16px; border-bottom:1px solid var(--border);
      display:flex; align-items:center; justify-content:space-between;
    }
    .section h3{margin:0; font-size:18px}
    .section p{margin:6px 0 0; color:var(--muted); font-size:13px}
    .btn-green{
      background:var(--green-2); color:#fff; border:none; padding:10px 14px; border-radius:10px; font-weight:700; cursor:pointer;
    }
    .table-wrap{overflow:auto}
    table{width:100%; border-collapse:separate; border-spacing:0}
    thead th{
      text-align:left; font-size:12px; color:#3d5a49; background:#f6fff9;
      padding:12px 14px; border-bottom:1px solid var(--border); position:sticky; top:0; z-index:1;
    }
    tbody td{
      padding:12px 14px; border-bottom:1px solid var(--border); font-size:14px; color:#2b3a32;
    }
    .badge{
      display:inline-block; padding:6px 10px; border-radius:999px; color:#fff; font-size:12px; font-weight:700;
    }
    .chip{
      display:inline-block; padding:4px 8px; border-radius:999px; background:#eef7f1; color:#274437; font-size:12px; font-weight:700; border:1px solid var(--border);
    }
    .actions-col{display:flex; gap:8px}
    .icon-btn{
      width:34px; height:34px; border-radius:8px; border:1px solid var(--border);
      background:#fff; display:grid; place-items:center; cursor:pointer; color:#2d4738;
    }
    .icon-btn:hover{background:#f3fbf6}
    .muted{color:#667a70}
    @media (max-width:900px){ .kpis{grid-template-columns:repeat(2,1fr)} }
    @media (max-width:520px){ .kpis{grid-template-columns:1fr} .actions{gap:6px} .btn{padding:7px 10px} }
  </style>
</head>
<body>

<header class="topbar">
  <div class="top-left">
    <div class="logo">UM</div>
    <div class="title">
      <span>User Management</span>
      <span class="subtitle">Create and manage user accounts</span>
    </div>
  </div>
  <div class="actions">
    <a href="admin.php"><button class="btn secondary">Back to Dashboard</button></a>
    <form action="logout.php" method="post" style="margin:0">
      <button class="btn">Logout</button>
    </form>
  </div>
</header>

<main class="container">
  <section class="kpis">
    <div class="kpi">
      <div>
        <h4>Total Users</h4>
        <div class="val"><?= h($totalUsers) ?></div>
      </div>
      <div class="pill teal">👥</div>
    </div>
    <div class="kpi">
      <div>
        <h4>Administrators</h4>
        <div class="val"><?= h($adminUsers) ?></div>
      </div>
      <div class="pill purple">🛡️</div>
    </div>
    <div class="kpi">
      <div>
        <h4>HR Users</h4>
        <div class="val"><?= h($hrUsers) ?></div>
      </div>
      <div class="pill blue">📋</div>
    </div>
    <div class="kpi">
      <div>
        <h4>Finance Users</h4>
        <div class="val"><?= h($finUsers) ?></div>
      </div>
      <div class="pill teal">💲</div>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <div>
        <h3>User Accounts</h3>
        <p>Manage all system user accounts and their permissions</p>
      </div>
      <a href="user_create.php"><button class="btn-green">➕ Create New User</button></a>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th style="min-width:90px">Employee ID</th>
            <th style="min-width:160px">Full Name</th>
            <th style="min-width:200px">Email</th>
            <th style="min-width:140px">Phone</th>
            <th style="min-width:140px">Position</th>
            <th style="min-width:160px">Department</th>
            <th style="min-width:120px">Username</th>
            <th style="min-width:140px">Date Created</th>
            <th style="min-width:120px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$users): ?>
            <tr><td colspan="9" class="muted">No users found.</td></tr>
          <?php else: ?>
            <?php foreach ($users as $u): 
              [$badgeText,$badgeColor] = roleBadge($u['position']);
            ?>
              <tr>
                <td><?= h($u['employee_id'] ?? ('EMP'.str_pad((string)$u['id'],3,'0',STR_PAD_LEFT))) ?></td>
                <td><?= h($u['full_name'] ?? '') ?></td>
                <td><?= h($u['email'] ?? '') ?></td>
                <td><?= h($u['phone'] ?? '') ?></td>
                <td>
                  <span class="badge" style="background: <?= h($badgeColor) ?>;"><?= h($badgeText) ?></span>
                </td>
                <td><span class="chip"><?= h($u['department'] ?? '') ?></span></td>
                <td><span class="chip"><?= h($u['username'] ?? '') ?></span></td>
                <td><?= h(isset($u['created_at']) ? date('Y-m-d', strtotime($u['created_at'])) : '') ?></td>
                <td class="actions-col">
                  <a class="icon-btn" title="Edit" href="user_edit.php?id=<?= urlencode((string)$u['id']) ?>">✏️</a>
                  <form action="user_delete.php" method="post" onsubmit="return confirm('Delete this user?');" style="margin:0">
                    <input type="hidden" name="id" value="<?= h($u['id']) ?>">
                    <button class="icon-btn" title="Delete" type="submit">🗑️</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

</body>
</html>
