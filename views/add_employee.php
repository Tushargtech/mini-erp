<?php
require_once '../includes/auth.php';
requireAdmin();

require_once '../config/db.php';

$errorMessage = '';
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleId = (int)($_POST['role_id'] ?? 2);
    
    if (empty($username) || empty($email) || empty($password)) {
        $errorMessage = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Invalid email format';
    } elseif (strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters';
    } else {
        try {
            $pdo->beginTransaction();
            
            $checkStatement = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
            $checkStatement->execute([$username, $email]);
            if ($checkStatement->fetch()) {
                $errorMessage = 'Username or email already exists';
                $pdo->rollBack();
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $insertStatement = $pdo->prepare('INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)');
                $insertStatement->execute([$username, $email, $hashedPassword, $roleId]);
                
                $newUserId = $pdo->lastInsertId();
                
                $auditStatement = $pdo->prepare('INSERT INTO audit_logs (user_id, action, table_name) VALUES (?, ?, ?)');
                $auditStatement->execute([$_SESSION['user_id'], "Created user: $username", 'users']);
                
                $pdo->commit();
                
                header('Location: employees.php?success=1');
                exit();
            }
        } catch (PDOException $exception) {
            $pdo->rollBack();
            $errorMessage = 'Error creating employee. Please try again.';
        }
    }
}

$pageTitle = 'Add Employee - Mini ERP';
require_once '../includes/header.php';

$rolesStatement = $pdo->query('SELECT id, role_name FROM roles ORDER BY id');
$availableRoles = $rolesStatement->fetchAll();
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Add New Employee</h4>
            </div>
            <div class="card-body">
                <?php if ($errorMessage): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               minlength="6" required>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role *</label>
                        <select class="form-select" id="role_id" name="role_id" required>
                            <?php foreach($availableRoles as $role): ?>
                                <option value="<?php echo $role['id']; ?>" 
                                    <?php echo (isset($roleId) && $roleId == $role['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Employee
                        </button>
                        <a href="employees.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
