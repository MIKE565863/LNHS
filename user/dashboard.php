<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a student or alumni
if (!isLoggedIn() || !isUser()) {
    header('Location: ../index.php');
    exit();
}

$user = getCurrentUser();
$recentRequests = getUserRequests($user['id'], 5);
$unreadNotifications = getUnreadNotificationCount($user['id']);
$notifications = getUserNotifications($user['id'], 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LNHS Documents Request Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>LNHS Portal
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
                        <a class="nav-link" href="request-document.php">
                            <i class="fas fa-file-alt me-1"></i>Request Document
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my-requests.php">
                            <i class="fas fa-list me-1"></i>My Requests
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell me-1"></i>
                            <?php if ($unreadNotifications > 0): ?>
                                <span class="badge bg-danger"><?php echo $unreadNotifications; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                            <?php if (empty($notifications)): ?>
                                <li><span class="dropdown-item-text text-muted">No notifications</span></li>
                            <?php else: ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <li>
                                        <a class="dropdown-item <?php echo $notification['is_read'] ? '' : 'fw-bold'; ?>" href="#" onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                <small><?php echo formatDateTime($notification['sent_at']); ?></small>
                                            </div>
                                            <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="notifications.php">View all notifications</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
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
                                <h4 class="card-title mb-2">Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h4>
                                <p class="card-text mb-0">
                                    You can request documents online without visiting the school. 
                                    Track your requests and get notified when they're ready.
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="request-document.php" class="btn btn-light">
                                    <i class="fas fa-plus me-2"></i>New Request
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
                        <h3 class="text-primary"><?php echo count(getUserRequests($user['id'])); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <h5 class="card-title">Pending</h5>
                        <h3 class="text-warning">
                            <?php 
                            $pendingRequests = array_filter(getUserRequests($user['id']), function($req) {
                                return $req['status'] == 'pending';
                            });
                            echo count($pendingRequests);
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h5 class="card-title">Completed</h5>
                        <h3 class="text-success">
                            <?php 
                            $completedRequests = array_filter(getUserRequests($user['id']), function($req) {
                                return $req['status'] == 'completed';
                            });
                            echo count($completedRequests);
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-bell fa-2x text-info mb-2"></i>
                        <h5 class="card-title">Notifications</h5>
                        <h3 class="text-info"><?php echo $unreadNotifications; ?></h3>
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
                                <p class="text-muted">Start by requesting your first document</p>
                                <a href="request-document.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Request Document
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Document</th>
                                            <th>Status</th>
                                            <th>Request Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentRequests as $request): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($request['document_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['purpose']); ?></small>
                                                </td>
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
                                <a href="my-requests.php" class="btn btn-outline-primary">
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
                            <a href="request-document.php" class="btn btn-primary">
                                <i class="fas fa-file-alt me-2"></i>Request New Document
                            </a>
                            <a href="my-requests.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>View My Requests
                            </a>
                            <a href="profile.php" class="btn btn-outline-secondary">
                                <i class="fas fa-user-edit me-2"></i>Update Profile
                            </a>
                            <a href="notifications.php" class="btn btn-outline-info">
                                <i class="fas fa-bell me-2"></i>View Notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Available Documents -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file me-2"></i>Available Documents
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        $documentTypes = getAllDocumentTypes();
                        foreach ($documentTypes as $docType): 
                        ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($docType['name']); ?></strong>
                                    <br>
                                    <small class="text-muted">₱<?php echo number_format($docType['fee'], 2); ?></small>
                                </div>
                                <span class="badge bg-success"><?php echo $docType['processing_days']; ?> days</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAsRead(notificationId) {
            fetch('../ajax/mark-notification-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    </script>
</body>
</html>