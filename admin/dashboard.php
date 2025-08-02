<?php
require_once '../includes/auth.php';
$auth->requireAdmin();

$admin = $auth->getCurrentAdmin();
$conn = getDB();

// Get overall statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_requests,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_requests,
        SUM(CASE WHEN status = 'denied' THEN 1 ELSE 0 END) as denied_requests,
        SUM(CASE WHEN status = 'ready_for_pickup' THEN 1 ELSE 0 END) as ready_requests,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_requests
    FROM document_requests
");
$stmt->execute();
$stats = $stmt->fetch();

// Get total users
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$user_stats = $stmt->fetch();

// Get recent requests
$stmt = $conn->prepare("
    SELECT dr.*, 
           CONCAT(u.first_name, ' ', u.last_name) as user_name,
           u.student_id,
           COUNT(du.id) as file_count
    FROM document_requests dr
    JOIN users u ON dr.user_id = u.id
    LEFT JOIN document_uploads du ON dr.id = du.request_id
    GROUP BY dr.id
    ORDER BY dr.created_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_requests = $stmt->fetchAll();

function getStatusBadge($status) {
    $badges = [
        'pending' => 'warning',
        'processing' => 'info',
        'approved' => 'success',
        'denied' => 'danger',
        'ready_for_pickup' => 'primary',
        'completed' => 'success'
    ];
    return $badges[$status] ?? 'secondary';
}

function formatStatus($status) {
    return ucwords(str_replace('_', ' ', $status));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LNHS Documents Request Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-shield-alt me-2"></i>
                LNHS Admin Panel
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($admin['full_name']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i>View Portal</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="requests.php">
                                <i class="fas fa-file-alt"></i>
                                All Requests
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pending-requests.php">
                                <i class="fas fa-clock"></i>
                                Pending Requests
                                <?php if ($stats['pending_requests'] > 0): ?>
                                    <span class="badge bg-warning text-dark ms-2"><?php echo $stats['pending_requests']; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i>
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i>
                                Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog"></i>
                                Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Admin Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="reports.php" class="btn btn-outline-secondary">
                                <i class="fas fa-download me-2"></i>Export Reports
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Welcome Message -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Welcome, <strong><?php echo htmlspecialchars($admin['full_name']); ?></strong>! 
                    You have <?php echo $stats['pending_requests']; ?> pending requests that need attention.
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-primary text-white">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3 class="mb-1"><?php echo $stats['total_requests']; ?></h3>
                            <p class="text-muted mb-0">Total Requests</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-warning text-dark">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3 class="mb-1"><?php echo $stats['pending_requests']; ?></h3>
                            <p class="text-muted mb-0">Pending</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-info text-white">
                                <i class="fas fa-cog"></i>
                            </div>
                            <h3 class="mb-1"><?php echo $stats['processing_requests']; ?></h3>
                            <p class="text-muted mb-0">Processing</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card">
                            <div class="stat-icon bg-success text-white">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="mb-1"><?php echo $user_stats['total_users']; ?></h3>
                            <p class="text-muted mb-0">Total Users</p>
                        </div>
                    </div>
                </div>

                <!-- Status Breakdown -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Request Status Breakdown</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2 text-center">
                                        <div class="mb-2">
                                            <span class="badge bg-warning fs-6"><?php echo $stats['pending_requests']; ?></span>
                                        </div>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="mb-2">
                                            <span class="badge bg-info fs-6"><?php echo $stats['processing_requests']; ?></span>
                                        </div>
                                        <small class="text-muted">Processing</small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="mb-2">
                                            <span class="badge bg-success fs-6"><?php echo $stats['approved_requests']; ?></span>
                                        </div>
                                        <small class="text-muted">Approved</small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="mb-2">
                                            <span class="badge bg-danger fs-6"><?php echo $stats['denied_requests']; ?></span>
                                        </div>
                                        <small class="text-muted">Denied</small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="mb-2">
                                            <span class="badge bg-primary fs-6"><?php echo $stats['ready_requests']; ?></span>
                                        </div>
                                        <small class="text-muted">Ready</small>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="mb-2">
                                            <span class="badge bg-success fs-6"><?php echo $stats['completed_requests']; ?></span>
                                        </div>
                                        <small class="text-muted">Completed</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Requests -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Requests</h5>
                                <a href="requests.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_requests)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No requests yet</h5>
                                        <p class="text-muted">Requests will appear here when users start submitting them</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Request #</th>
                                                    <th>Student</th>
                                                    <th>Document Type</th>
                                                    <th>Status</th>
                                                    <th>Submitted</th>
                                                    <th>Files</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_requests as $request): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($request['request_number']); ?></strong></td>
                                                    <td>
                                                        <?php echo htmlspecialchars($request['user_name']); ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($request['student_id']); ?></small>
                                                    </td>
                                                    <td><?php echo ucwords(str_replace('_', ' ', $request['document_type'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo getStatusBadge($request['status']); ?>">
                                                            <?php echo formatStatus($request['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M j, Y', strtotime($request['created_at'])); ?></td>
                                                    <td>
                                                        <i class="fas fa-paperclip me-1"></i>
                                                        <?php echo $request['file_count']; ?> files
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="view-request.php?id=<?php echo $request['id']; ?>" 
                                                               class="btn btn-outline-primary" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if ($request['status'] === 'pending'): ?>
                                                            <a href="process-request.php?id=<?php echo $request['id']; ?>" 
                                                               class="btn btn-outline-success" title="Process">
                                                                <i class="fas fa-cog"></i>
                                                            </a>
                                                            <?php endif; ?>
                                                        </div>
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

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="pending-requests.php" class="btn btn-outline-warning btn-lg w-100">
                                            <i class="fas fa-clock fa-2x mb-2"></i>
                                            <br>Process Pending
                                            <?php if ($stats['pending_requests'] > 0): ?>
                                                <span class="badge bg-warning text-dark ms-2"><?php echo $stats['pending_requests']; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="requests.php" class="btn btn-outline-primary btn-lg w-100">
                                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                                            <br>All Requests
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="users.php" class="btn btn-outline-info btn-lg w-100">
                                            <i class="fas fa-users fa-2x mb-2"></i>
                                            <br>Manage Users
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="reports.php" class="btn btn-outline-success btn-lg w-100">
                                            <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                            <br>Generate Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>