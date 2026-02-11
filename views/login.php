<?php
session_start();
require_once '../config/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$debug = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $debug .= "Form submitted. Username: " . htmlspecialchars($username) . "<br>";
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password, role_id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        $debug .= "User found: " . ($user ? 'Yes' : 'No') . "<br>";
        
        if ($user) {
            $debug .= "Password verify: " . (password_verify($password, $user['password']) ? 'Success' : 'Failed') . "<br>";
        }
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_id'] = $user['role_id'];
            
            $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id, action, table_name) VALUES (?, ?, ?)');
            $stmt->execute([$user['id'], 'User logged in', 'users']);
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    }
}

$pageTitle = 'Login - Mini ERP';
require_once '../includes/header.php';
?>

<?php if ($debug): ?>
<div class="alert alert-info">
    <strong>Debug Info:</strong><br>
    <?php echo $debug; ?>
</div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-5">
                <h2 class="text-center mb-4">Mini ERP System</h2>
                <h5 class="text-center text-muted mb-4">Login to your account</h5>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus value="<?php echo htmlspecialchars($username ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <small class="text-muted">
                        Demo: admin / admin123
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
