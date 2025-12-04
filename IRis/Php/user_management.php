<?php
session_start();
require_once 'config.php';

// Check admin session
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=rocelenrj_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Handle Delete
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$deleteId]);
    header("Location: user_management.php");
    exit();
}

// Handle Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = (int)$_POST['id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $position = trim($_POST['position']);
    $username = trim($_POST['username']);
    $employee_id = trim($_POST['employee_id']);
    $password = $_POST['password'];

    if ($full_name && $email && $position && $username && $employee_id) {
        if (!empty($password)) {
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, position=?, username=?, employee_id=?, password=? WHERE id=?");
            $stmt->execute([$full_name, $email, $position, $username, $employee_id, $password_hashed, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, email=?, position=?, username=?, employee_id=? WHERE id=?");
            $stmt->execute([$full_name, $email, $position, $username, $employee_id, $id]);
        }
        header("Location: user_management.php");
        exit();
    }
}

// Fetch users
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user for editing if edit_id is set
$editUser = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$editId]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>User Management - Rocelyn RJ</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f9fdf9;
        margin: 0;
    }
    header {
        background: #0a8f3c;
        color: white;
        padding: 18px 36px;
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .logo img {
        width: 48px;
        height: 48px;
        object-fit: contain;
        border-radius: 10px;
    }
    h2 {
        font-weight: bold;
        margin-bottom: 18px;
    }
    .btn-secondary {
        background: #0a8f3c;
        color: #fff;
        border: none;
        font-weight: bold;
        font-size: 16px;
        border-radius: 6px;
        padding: 10px 24px;
    }
    .btn-secondary:hover {
        background: #197143;
        color: #fff;
    }
    .btn-warning {
        background: #1a9f3f;
        color: #fff;
        font-weight: bold;
        border-radius: 7px;
        border: none;
        font-size: 15px;
        padding: 7px 18px;
    }
    .btn-warning:hover {
        background: #148a34;
        color: #fff;
    }
    .btn-danger {
        background: #d32f2f;
        color: #fff;
        font-weight: bold;
        border-radius: 7px;
        border: none;
        font-size: 15px;
        padding: 7px 18px;
    }
    .btn-danger:hover {
        background: #ab2222;
        color: #fff;
    }
    .table th {
        background: #eaf9f1;
        color: #181818;
        font-size: 17px;
    }
    .table td {
        font-size: 16px;
    }
    .table-striped > tbody > tr:nth-of-type(even) {
        background-color: #f5fcf6;
    }
    .container {
        max-width: 1200px;
    }
</style>
</head>
<body>
<header>
    <div class="logo"><img src="../Capstone pics/LOGO.jpg" alt="Company Logo"></div>
    <h1 style="margin: 0;font-size:26px;font-weight:bold;">User Management - Rocelyn RJ Building Trades Inc</h1>
</header>
<div class="container my-4">
    <div class="mb-3">
        <a href="admin.php" class="btn btn-secondary">&larr; Back to Admin Dashboard</a>
    </div>
 


    

<h2>User Management</h2>

<?php if ($editUser): ?>
<h4>Edit User: <?= htmlspecialchars($editUser['full_name']) ?></h4>
<form method="POST" class="mb-4">
<input type="hidden" name="id" value="<?= $editUser['id'] ?>">
<div class="mb-3">
<label>Full Name</label>
<input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($editUser['full_name']) ?>" required>
</div>
<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editUser['email']) ?>" required>
</div>
<div class="mb-3">
<label>Position</label>
<input type="text" name="position" class="form-control" value="<?= htmlspecialchars($editUser['position']) ?>" required>
</div>
<div class="mb-3">
<label>Username</label>
<input type="text" name="username" class="form-control" value="<?= htmlspecialchars($editUser['username']) ?>" required>
</div>
<div class="mb-3">
<label>Employee ID</label>
<input type="text" name="employee_id" class="form-control" value="<?= htmlspecialchars($editUser['employee_id']) ?>" required>
</div>
<div class="mb-3">
<label>New Password (leave blank to keep current)</label>
<input type="password" name="password" class="form-control" placeholder="Enter new password">
</div>
<button type="submit" name="edit_user" class="btn btn-primary">Update User</button>
<a href="user_management.php" class="btn btn-secondary">Cancel</a>
</form>
<?php endif; ?>

<table class="table table-bordered table-striped">
<thead class="table-light">
<tr>
<th>ID</th>
<th>Full Name</th>
<th>Email</th>
<th>Position</th>
<th>Username</th>
<th>Employee ID</th>
<th class="text-center">Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($users as $user): ?>
<tr>
<td><?= $user['id'] ?></td>
<td><?= htmlspecialchars($user['full_name']) ?></td>
<td><?= htmlspecialchars($user['email']) ?></td>
<td><?= htmlspecialchars($user['position']) ?></td>
<td><?= htmlspecialchars($user['username']) ?></td>
<td><?= htmlspecialchars($user['employee_id']) ?></td>
<td class="text-center">
<a href="?edit_id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
<a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?');">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</body>
</html>
