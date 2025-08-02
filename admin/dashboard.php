<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../index.php');
    exit();
}

$user = getCurrentUser();
$pendingRequests = getAllRequests('pending');
$processingRequests = getAllRequests('processing');
$recentRequests = getAllRequests(null, 10);
$totalRequests = count(getAllRequests());
$totalUsers = $pdo->query("SELECT COUNT(*) as count FROM users WHERE user_type IN ('student', 'alumni')")->fetch()['count'];
$totalDocuments = $pdo->query("SELECT COUNT(*) as count FROM document_types WHERE is_active = 1")->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LNHS Documents Request Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>LNHS Admin Portal
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="requests.php">
                            <i class="fas fa-list me-1"></i>All Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-1"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="documents.php">
                            <i class="fas fa-file me-1"></i>Document Types
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-1"></i>Reports
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['first_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="change-password.php"><i class="fas fa-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="card-title mb-2">Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h4>
                                <p class="card-text mb-0">
                                    Manage document requests, users, and system settings from this admin dashboard.
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="requests.php" class="btn btn-light">
                                    <i class="fas fa-list me-2"></i>View All Requests
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                        <h5 class="card-title">Total Requests</h5>
                        <h3 class="text-primary"><?php echo $totalRequests; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <h5 class="card-title">Pending</h5>
                        <h3 class="text-warning"><?php echo count($pendingRequests); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-success mb-2"></i>
                        <h5 class="card-title">Total Users</h5>
                        <h3 class="text-success"><?php echo $totalUsers; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-file fa-2x text-info mb-2"></i>
                        <h5 class="card-title">Document Types</h5>
                        <h3 class="text-info"><?php echo $totalDocuments; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>Pending Requests
                        </h5>
                        <a href="requests.php?status=pending" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pendingRequests)): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <p class="text-muted mb-0">No pending requests</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Document</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($pendingRequests, 0, 5) as $request): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['email']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($request['document_name']); ?></td>
                                                <td><?php echo formatDate($request['request_date']); ?></td>
                                                <td>
                                                    <a href="view-request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Processing Requests -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cogs me-2"></i>Processing Requests
                        </h5>
                        <a href="requests.php?status=processing" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($processingRequests)): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-info-circle fa-2x text-info mb-2"></i>
                                <p class="text-muted mb-0">No processing requests</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Document</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($processingRequests, 0, 5) as $request): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['email']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($request['document_name']); ?></td>
                                                <td><?php echo formatDate($request['request_date']); ?></td>
                                                <td>
                                                    <a href="view-request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Requests -->
        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Recent Requests
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentRequests)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No requests yet</h6>
                                <p class="text-muted">Requests will appear here when users submit them</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Document</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentRequests as $request): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['email']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($request['document_name']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo getStatusBadgeClass($request['status']); ?>">
                                                        <?php echo getStatusText($request['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($request['request_date']); ?></td>
                                                <td>
                                                    <a href="view-request.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="requests.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-2"></i>View All Requests
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="requests.php" class="btn btn-primary">
                                <i class="fas fa-list me-2"></i>Manage Requests
                            </a>
                            <a href="users.php" class="btn btn-outline-primary">
                                <i class="fas fa-users me-2"></i>Manage Users
                            </a>
                            <a href="documents.php" class="btn btn-outline-secondary">
                                <i class="fas fa-file me-2"></i>Document Types
                            </a>
                            <a href="reports.php" class="btn btn-outline-info">
                                <i class="fas fa-chart-bar me-2"></i>Generate Reports
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Info -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>System Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
                        </div>
                        <div class="mb-2">
                            <strong>Database:</strong> MySQL
                        </div>
                        <div class="mb-2">
                            <strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                        </div>
                        <div class="mb-0">
                            <strong>Upload Limit:</strong> <?php echo ini_get('upload_max_filesize'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>