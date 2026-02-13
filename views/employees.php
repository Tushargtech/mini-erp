<?php
require_once '../includes/auth.php';
requireAdmin();

$pageTitle = 'Employees - Mini ERP';
require_once '../includes/header.php';
require_once '../config/db.php';

$statement = $pdo->query('SELECT u.id, u.username, u.email, u.created_at, r.role_name 
                     FROM users u 
                     LEFT JOIN roles r ON u.role_id = r.id 
                     ORDER BY u.created_at DESC');
$employees = $statement->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Employee Management</h2>
    <a href="add_employee.php" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Add Employee
    </a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        Employee added successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (count($employees) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($employees as $employee): ?>
                    <tr>
                        <td><?php echo $employee['id']; ?></td>
                        <td><?php echo htmlspecialchars($employee['username']); ?></td>
                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $employee['role_name'] == 'Admin' ? 'danger' : 'info'; ?>">
                                <?php echo htmlspecialchars($employee['role_name']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($employee['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p class="text-muted text-center py-4">No employees found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
