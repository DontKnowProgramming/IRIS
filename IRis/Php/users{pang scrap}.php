<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "config.php";

// ===== AJAX DELETE HANDLER - must be FIRST and top-level =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'] ?? 0;
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo "User successfully deleted.";
    } else {
        $err = $stmt->errorInfo();
        echo "Error deleting user: " . $err[2];
    }
    exit;
}

// ===== AJAX EDIT MODAL HANDLER =====
if (isset($_GET['edit_id'])) {
    $id = $_GET['edit_id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
        $employee_id = $_POST['employee_id'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $position = $_POST['position'];
        $password = $_POST['password'];

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET employee_id=?, full_name=?, email=?, phone=?, position=?, password=? WHERE id=?";
            $params = [$employee_id, $full_name, $email, $phone, $position, $hashed_password, $id];
        } else {
            $sql = "UPDATE users SET employee_id=?, full_name=?, email=?, phone=?, position=? WHERE id=?";
            $params = [$employee_id, $full_name, $email, $phone, $position, $id];
        }

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        echo json_encode([
            "success" => $result,
            "params" => $params,
            "rowCount" => $stmt->rowCount(),
            "error" => $stmt->errorInfo()
        ]);
        exit;
    }

    // GET: Edit id, show user form
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo "User not found.";
        exit;
    }
    ?>
    <form id="editUserForm" method="POST">
        <div class="modal-header">
            <h5 class="modal-title">Edit User Account</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row g-2">
            <input type="hidden" name="update_user" value="1" />
            <div class="col-6"><label>Employee ID*</label>
                <input class="form-control" name="employee_id" value="<?= htmlspecialchars($user['employee_id']) ?>" required>
            </div>
            <div class="col-6"><label>Full Name*</label>
                <input class="form-control" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>
            <div class="col-6"><label>Email*</label>
                <input class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="col-6"><label>Phone*</label>
                <input class="form-control" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
            </div>
            <div class="col-6"><label>Position*</label>
                <select class="form-control" name="position" required>
                    <option <?= $user['position'] == 'Administrator' ? 'selected' : '' ?>>Administrator</option>
                    <option <?= $user['position'] == 'HR Manager' ? 'selected' : '' ?>>HR Manager</option>
                    <option <?= $user['position'] == 'Finance' ? 'selected' : '' ?>>Finance</option>
                    <option <?= $user['position'] == 'Project Manager' ? 'selected' : '' ?>>Project Manager</option>
                    <option <?= $user['position'] == 'Timekeeper' ? 'selected' : '' ?>>Timekeeper</option> <!-- ✅ Added -->
                </select>
            </div>
            <div class="col-6"><label>Password (leave blank to keep)</label>
                <input type="password" class="form-control" name="password" autocomplete="new-password">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Update User</button>
        </div>
    </form>
    <script>
        document.getElementById('editUserForm').onsubmit = function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            fetch(window.location.pathname + '?edit_id=<?= $user['id'] ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.rowCount > 0) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                    modal.hide();
                    location.reload();
                } else {
                    alert("Update failed!\nRow count: "+data.rowCount+
                          "\nParams: "+JSON.stringify(data.params)+
                          "\nSQL Error: "+data.error[2]);
                }
            });
        };
    </script>
<?php
    exit;
}

// ===== ADD USER HANDLER =====
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $employee_id = $_POST['employee_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $created_at = date('Y-m-d H:i:s');
    // Check for duplicates
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $check->execute([$username, $email]);
    if ($check->fetchColumn() > 0) {
        $error = "Username or Email already exists!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (employee_id, full_name, email, phone, position, username, password, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if($stmt->execute([$employee_id, $full_name, $email, $phone, $position, $username, $password, $created_at])){
            $success = "User added successfully!";
        } else {
            $err = $stmt->errorInfo();
            $error = "Error adding user: " . $err[2];
        }
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f6fff9;
        }
        .btn-main {
            background: #119f44;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 13px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-main:hover {
            background: #0c7c32;
            color: #fff;
        }
        .button-row {
            display: flex;
            gap: 18px;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 style="font-size: 3rem; font-weight: 60;">User Management</h1>
        <div class="button-row">
            <a href="admin.php" class="btn btn-main">&larr; Back to Dashboard</a>
            <button class="btn btn-main"
                onclick="document.getElementById('addForm').style.display = (document.getElementById('addForm').style.display === 'block' ? 'none' : 'block')">
                + Create New User
            </button>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif ?>
        <div id="addForm" style="display:none" class="mb-4">
            <form method="POST" class="row g-2 align-items-center">
                <input type="hidden" name="add_user" value="1">
                <div class="col">
                    <input class="form-control" name="employee_id" placeholder="Employee ID" required>
                </div>
                <div class="col">
                    <input class="form-control" name="full_name" placeholder="Full Name" required>
                </div>
                <div class="col">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>
                <div class="col">
                    <input class="form-control" name="phone" placeholder="Phone" required>
                </div>
                <div class="col">
                    <select class="form-control" name="position" required>
                        <option value="">Select Position</option>
                        <option>Administrator</option>
                        <option>HR Manager</option>
                        <option>Finance</option>
                        <option>Project Manager</option>
                        <option>Timekeeper</option> <!-- ✅ Added -->
                    </select>
                </div>
                <div class="col"><input class="form-control" name="username" placeholder="Username" required></div>
                <div class="col"><input type="password" class="form-control" name="password" placeholder="Password" required></div>
                <div class="col"><button class="btn btn-main">Add User</button></div>
            </form>
        </div>
        <table class="table table-bordered bg-white">
            <thead>
                <tr>
                    <th>Employee ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Position</th>
                    <th>Username</th>
                    <th>Date Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['employee_id']) ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['position']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td>
                            <a href="#" class="btn btn-info btn-sm" onclick="openEditModal(<?= $row['id'] ?>)">Edit</a>
                            <a href="#" class="btn btn-danger btn-sm" onclick="deleteUser(<?= $row['id'] ?>)">Delete</a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <!-- Bootstrap Modal for Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" id="editModalContent">
                <!-- Modal content loaded via JS -->
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEditModal(userId) {
            fetch('users.php?edit_id=' + userId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('editModalContent').innerHTML = html;
                    var modal = new bootstrap.Modal(document.getElementById('editModal'));
                    modal.show();
                });
        }
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                fetch('users.php?delete_id=' + userId, { method: 'POST' })
                    .then(response => response.text())
                    .then(msg => {
                        alert(msg);
                        location.reload();
                    });
            }
        }
    </script>
</body>
</html>
