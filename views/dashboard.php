<?php
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'Dashboard - Mini ERP';
require_once '../includes/header.php';
require_once '../config/db.php';

$stmt = $pdo->prepare('SELECT COUNT(*) as total FROM projects WHERE status = ?');
$stmt->execute(['active']);
$activeProjects = $stmt->fetch()['total'];

$stmt = $pdo->prepare('SELECT COUNT(*) as total FROM tasks WHERE status = ?');
$stmt->execute(['pending']);
$pendingTasks = $stmt->fetch()['total'];

$stmt = $pdo->prepare('SELECT COUNT(*) as total FROM tasks WHERE status = ?');
$stmt->execute(['in_progress']);
$inProgressTasks = $stmt->fetch()['total'];

$stmt = $pdo->prepare('SELECT COUNT(*) as total FROM users');
$stmt->execute();
$totalUsers = $stmt->fetch()['total'];

$isAdmin = $_SESSION['role_id'] == 1;
?>

<div class="row">
    <div class="col-12">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p class="text-muted">Role: <?php echo $isAdmin ? 'Administrator' : 'Employee'; ?></p>
        <hr>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Active Projects</h5>
                <h2 class="mb-0"><?php echo $activeProjects; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Pending Tasks</h5>
                <h2 class="mb-0"><?php echo $pendingTasks; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">In Progress</h5>
                <h2 class="mb-0"><?php echo $inProgressTasks; ?></h2>
            </div>
        </div>
    </div>
    <?php if($isAdmin): ?>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <h2 class="mb-0"><?php echo $totalUsers; ?></h2>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Tasks</h5>
            </div>
            <div class="card-body">
                <?php
                if ($isAdmin) {
                    $stmt = $pdo->prepare('SELECT t.*, p.project_name, u.username FROM tasks t 
                                          LEFT JOIN projects p ON t.project_id = p.id 
                                          LEFT JOIN users u ON t.assigned_to = u.id 
                                          ORDER BY t.created_at DESC LIMIT 5');
                } else {
                    $stmt = $pdo->prepare('SELECT t.*, p.project_name, u.username FROM tasks t 
                                          LEFT JOIN projects p ON t.project_id = p.id 
                                          LEFT JOIN users u ON t.assigned_to = u.id 
                                          WHERE t.assigned_to = ? 
                                          ORDER BY t.created_at DESC LIMIT 5');
                    $stmt->execute([$_SESSION['user_id']]);
                }
                
                if (!$isAdmin) {
                    $tasks = $stmt->fetchAll();
                } else {
                    $stmt->execute();
                    $tasks = $stmt->fetchAll();
                }
                
                if (count($tasks) > 0):
                ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Project</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($tasks as $task): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                <td><?php echo htmlspecialchars($task['project_name']); ?></td>
                                <td><?php echo htmlspecialchars($task['username']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $task['status'] == 'completed' ? 'success' : 
                                            ($task['status'] == 'in_progress' ? 'info' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($task['due_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-muted">No tasks found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if($isAdmin): ?>
                    <a href="/mini-erp/views/projects.php" class="btn btn-outline-primary">
                        <i class="bi bi-folder-plus"></i> New Project
                    </a>
                    <a href="/mini-erp/views/users.php" class="btn btn-outline-success">
                        <i class="bi bi-person-plus"></i> Add User
                    </a>
                    <?php endif; ?>
                    <a href="/mini-erp/views/tasks.php" class="btn btn-outline-info">
                        <i class="bi bi-plus-circle"></i> Create Task
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
