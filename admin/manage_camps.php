<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';
require_once 'includes/receipt-generator.php';

// Check user permissions
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}

$pageTitle = 'कैंप प्रबंधन';
$db = getDbConnection();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

$message = '';
$error = '';

// Handle form submissions (Admin only for create/update/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                if (!isAdmin()) {
                    $error = 'आपको इस कार्य की अनुमति नहीं है।';
                    break;
                }
                
                try {
                    $program = sanitizeInput($_POST['program']);
                    $name = sanitizeInput($_POST['name']);
                    $father_name = sanitizeInput($_POST['father_name']);
                    $address = sanitizeInput($_POST['address']);
                    $class = sanitizeInput($_POST['class']);
                    $amount = floatval($_POST['amount']);
                    $payment_method = sanitizeInput($_POST['payment_method']);
                    $place = sanitizeInput($_POST['place']);
                    $email = !empty($_POST['email']) ? sanitizeInput($_POST['email']) : null;
                    $payment_id = !empty($_POST['payment_id']) ? sanitizeInput($_POST['payment_id']) : null;
                    $status = sanitizeInput($_POST['status']);
                    $camp_id = 'CMP' . date('YmdHis');

                    // Handle payment proof upload
                    $payment_proof = null;
                    if (!empty($_FILES['payment_proof']['name']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['payment_proof'], 'img/payments');
                        if ($uploadResult['success']) {
                            $payment_proof = $uploadResult['filename'];
                        } else {
                            throw new Exception('भुगतान प्रमाण अपलोड में त्रुटि: ' . $uploadResult['error']);
                        }
                    }

                    $stmt = $db->prepare("INSERT INTO camps (program, name, father_name, address, class, amount, payment_method, place, email, payment_id, payment_proof, status, camp_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$program, $name, $father_name, $address, $class, $amount, $payment_method, $place, $email, $payment_id, $payment_proof, $status, $camp_id]);
                    
                    $message = 'कैंप रिकॉर्ड सफलतापूर्वक जोड़ा गया!';
                    $action = 'list';
                } catch (Exception $e) {
                    $error = 'कैंप जोड़ने में त्रुटि: ' . $e->getMessage();
                }
                break;

            case 'update':
                if (!isAdmin()) {
                    $error = 'आपको इस कार्य की अनुमति नहीं है।';
                    break;
                }
                
                try {
                    $camp_id = intval($_POST['id']);
                    $program = sanitizeInput($_POST['program']);
                    $name = sanitizeInput($_POST['name']);
                    $father_name = sanitizeInput($_POST['father_name']);
                    $address = sanitizeInput($_POST['address']);
                    $class = sanitizeInput($_POST['class']);
                    $amount = floatval($_POST['amount']);
                    $payment_method = sanitizeInput($_POST['payment_method']);
                    $place = sanitizeInput($_POST['place']);
                    $email = !empty($_POST['email']) ? sanitizeInput($_POST['email']) : null;
                    $payment_id = !empty($_POST['payment_id']) ? sanitizeInput($_POST['payment_id']) : null;
                    $status = sanitizeInput($_POST['status']);

                    // Validate required fields
                    if (empty($program) || empty($name) || empty($father_name) || empty($address) || empty($class) || empty($place) || empty($payment_method) || empty($status)) {
                        throw new Exception('सभी आवश्यक फील्ड भरें।');
                    }

                    // Get current data
                    $stmt = $db->prepare("SELECT payment_proof FROM camps WHERE id = ?");
                    $stmt->execute([$camp_id]);
                    $current_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$current_data) {
                        throw new Exception('कैंप रिकॉर्ड नहीं मिला।');
                    }
                    $payment_proof = $current_data['payment_proof'];

                    // Handle payment proof upload
                    if (!empty($_FILES['payment_proof']['name']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['payment_proof'], 'img/payments');
                        if ($uploadResult['success']) {
                            // Delete old payment proof
                            if (!empty($payment_proof) && file_exists('../img/payments/' . $payment_proof)) {
                                unlink('../img/payments/' . $payment_proof);
                            }
                            $payment_proof = $uploadResult['filename'];
                        } else {
                            throw new Exception('भुगतान प्रमाण अपलोड में त्रुटि: ' . $uploadResult['error']);
                        }
                    }

                    $stmt = $db->prepare("UPDATE camps SET program = ?, name = ?, father_name = ?, address = ?, class = ?, amount = ?, payment_method = ?, place = ?, email = ?, payment_id = ?, payment_proof = ?, status = ? WHERE id = ?");
                    $stmt->execute([$program, $name, $father_name, $address, $class, $amount, $payment_method, $place, $email, $payment_id, $payment_proof, $status, $camp_id]);
                    
                    $message = 'कैंप रिकॉर्ड सफलतापूर्वक अपडेट किया गया!';
                    header("Location: " . SITE_URL . "/admin/manage_camps.php");
                    exit;
                } catch (Exception $e) {
                    $error = 'कैंप अपडेट करने में त्रुटि: ' . $e->getMessage();
                }
                break;

            case 'update_status':
                if (!isAdmin()) {
                    $error = 'आपको इस कार्य की अनुमति नहीं है।';
                    break;
                }
                
                try {
                    $camp_id = intval($_POST['id']);
                    $status = sanitizeInput($_POST['status']);
                    
                    $stmt = $db->prepare("UPDATE camps SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $camp_id]);
                    
                    $message = 'कैंप की स्थिति अपडेट की गई!';
                } catch (Exception $e) {
                    $error = 'स्थिति अपडेट करने में त्रुटि: ' . $e->getMessage();
                }
                break;

            case 'delete':
                if (!isAdmin()) {
                    $error = 'आपको इस कार्य की अनुमति नहीं है।';
                    break;
                }
                
                try {
                    $delete_id = intval($_POST['id']);
                    
                    // Get payment proof before deleting
                    $stmt = $db->prepare("SELECT payment_proof FROM camps WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $camp = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Delete camp
                    $stmt = $db->prepare("DELETE FROM camps WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    
                    // Delete payment proof if exists
                    if (!empty($camp['payment_proof']) && file_exists('../img/payments/' . $camp['payment_proof'])) {
                        unlink('../img/payments/' . $camp['payment_proof']);
                    }
                    
                    $message = 'कैंप रिकॉर्ड सफलतापूर्वक हटाया गया!';
                } catch (Exception $e) {
                    $error = 'हटाने में त्रुटि: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Get camp data for editing/viewing
$camp_data = null;
if (($action === 'edit' || $action === 'view') && $id > 0) {
    if ($action === 'edit' && !isAdmin()) {
        $error = 'आपको इस कार्य की अनुमति नहीं है।';
        $action = 'list';
    } else {
        $sql = "SELECT * FROM camps WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $camp_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$camp_data) {
            $error = 'कैंप रिकॉर्ड नहीं मिला!';
            $action = 'list';
        }
    }
}

// Get camps list
$camps_list = [];
$filter_status = $_GET['status'] ?? '';
if ($action === 'list') {
    $sql = "SELECT * FROM camps";
    $params = [];
    
    if (!empty($filter_status)) {
        $sql .= " WHERE status = ?";
        $params[] = $filter_status;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $camps_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get statistics
$stats = [];
try {
    $sql = "SELECT 
        COUNT(*) as total_camps,
        COALESCE(SUM(amount), 0) as total_amount,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_camps,
        COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0) as completed_amount,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_camps
        FROM camps";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $stats = ['total_camps' => 0, 'total_amount' => 0, 'completed_camps' => 0, 'completed_amount' => 0, 'pending_camps' => 0];
}

// Handle receipt generation
if ($action === 'receipt' && $id > 0) {
    $sql = "SELECT * FROM camps WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $receipt_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($receipt_data) {
        $options = [
            'type' => 'camp',
            'auto_print' => false,
            'show_buttons' => true,
            'download' => isset($_GET['download'])
        ];
        
        $receipt_html = generateUniversalReceipt($receipt_data, $options);
        echo $receipt_html;
        exit;
    } else {
        $error = 'रसीद जनरेट करने में त्रुटि: रिकॉर्ड नहीं मिला!';
        $action = 'list';
    }
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-campground me-3"></i>
                <?php 
                if (isAdmin()) {
                    echo "कैंप प्रबंधन";
                } elseif (isCoordinator()) {
                    echo "कैंप सूची (केवल देखने के लिए)";
                } else {
                    echo "मेरे कैंप";
                }
                ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <div class="btn-group me-3" role="group">
                    <a href="?" class="btn btn-outline-primary <?php echo empty($filter_status) ? 'active' : ''; ?>">सभी</a>
                    <a href="?status=pending" class="btn btn-outline-warning <?php echo $filter_status === 'pending' ? 'active' : ''; ?>">लंबित</a>
                    <a href="?status=completed" class="btn btn-outline-success <?php echo $filter_status === 'completed' ? 'active' : ''; ?>">पूर्ण</a>
                </div>
                <?php if (isAdmin()): ?>
                <a href="?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> नया कैंप जोड़ें
                </a>
                <?php endif; ?>
                <?php else: ?>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> वापस जाएं
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-campground"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_camps']); ?></h3>
                    <p>कुल कैंप</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>₹<?php echo number_format($stats['total_amount'] ?? 0, 2); ?></h3>
                    <p>कुल राशि</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['completed_camps']); ?></h3>
                    <p>पूर्ण कैंप</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['pending_camps']); ?></h3>
                    <p>लंबित कैंप</p>
                </div>
            </div>
        </div>

        <!-- Camps List -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> कैंप सूची</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>कैंप ID</th>
                                <th>नाम</th>
                                <th>राशि</th>
                                <th>भुगतान विधि</th>
                                <th>स्थिति</th>
                                <th>दिनांक</th>
                                <th>कार्य</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($camps_list)): ?>
                            <tr>
                                <td colspan="7" class="text-center">कोई कैंप रिकॉर्ड नहीं मिला।</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($camps_list as $camp): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($camp['camp_id']); ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($camp['name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($camp['place']); ?></small>
                                </td>
                                <td>
                                    <strong class="text-success">₹<?php echo number_format($camp['amount'], 2); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $camp['payment_method'] === 'online' ? 'primary' : 
                                            ($camp['payment_method'] === 'offline' ? 'secondary' : 'success'); ?>">
                                        <?php 
                                        echo $camp['payment_method'] === 'online' ? 'ऑनलाइन' : 
                                            ($camp['payment_method'] === 'offline' ? 'ऑफलाइन' : 'निःशुल्क'); 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $camp['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                        <?php echo $camp['status'] === 'completed' ? 'पूर्ण' : 'लंबित'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('d M Y', strtotime($camp['created_at'])); ?>
                                    <br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($camp['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=view&id=<?php echo $camp['id']; ?>" class="btn btn-sm btn-outline-info" title="देखें">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (isAdmin()): ?>
                                        <a href="?action=edit&id=<?php echo $camp['id']; ?>" class="btn btn-sm btn-outline-primary" title="संपादित करें">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="<?php echo SITE_URL; ?>/generate_receipt.php?camp_id=<?php echo $camp['id']; ?>" class="btn btn-sm btn-outline-success" title="रसीद" target="_blank">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <?php if (isAdmin() && $camp['status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="updateStatus(<?php echo $camp['id']; ?>, 'completed')" title="स्वीकार करें">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if (isAdmin()): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCamp(<?php echo $camp['id']; ?>)" title="हटाएं">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php elseif ($action === 'edit' && $camp_data && isAdmin()): ?>
        <!-- Edit Camp Form -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-edit"></i> कैंप संपादित करें</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($camp_data['id']); ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="program" class="form-label"><strong>कार्यक्रम:</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="program" name="program" value="<?php echo htmlspecialchars($camp_data['program']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label"><strong>नाम:</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($camp_data['name']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="father_name" class="form-label"><strong>पिता का नाम:</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="father_name" name="father_name" value="<?php echo htmlspecialchars($camp_data['father_name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="address" class="form-label"><strong>पता:</strong> <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="4" required><?php echo htmlspecialchars($camp_data['address']); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="class" class="form-label"><strong>कक्षा:</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="class" name="class" value="<?php echo htmlspecialchars($camp_data['class']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="place" class="form-label"><strong>स्थान:</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="place" name="place" value="<?php echo htmlspecialchars($camp_data['place']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label"><strong>राशि:</strong> <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="<?php echo htmlspecialchars($camp_data['amount']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label"><strong>भुगतान विधि:</strong> <span class="text-danger">*</span></label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="online" <?php echo $camp_data['payment_method'] === 'online' ? 'selected' : ''; ?>>ऑनलाइन</option>
                                    <option value="offline" <?php echo $camp_data['payment_method'] === 'offline' ? 'selected' : ''; ?>>ऑफलाइन</option>
                                    <option value="free" <?php echo $camp_data['payment_method'] === 'free' ? 'selected' : ''; ?>>निःशुल्क</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label"><strong>ईमेल:</strong></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($camp_data['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_id" class="form-label"><strong>भुगतान ID:</strong></label>
                                <input type="text" class="form-control" id="payment_id" name="payment_id" value="<?php echo htmlspecialchars($camp_data['payment_id'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label"><strong>स्थिति:</strong> <span class="text-danger">*</span></label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="pending" <?php echo $camp_data['status'] === 'pending' ? 'selected' : ''; ?>>लंबित</option>
                            <option value="completed" <?php echo $camp_data['status'] === 'completed' ? 'selected' : ''; ?>>पूर्ण</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payment_proof" class="form-label"><strong>भुगतान प्रमाण:</strong></label>
                        <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept="image/*,application/pdf">
                        <?php if (!empty($camp_data['payment_proof'])): ?>
                        <p class="mt-2">
                            <a href="<?php echo SITE_URL . '/img/payments/' . $camp_data['payment_proof']; ?>" target="_blank">वर्तमान भुगतान प्रमाण देखें</a>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary me-md-2"><i class="fas fa-save"></i> सहेजें</button>
                        <a href="?" class="btn btn-secondary"><i class="fas fa-times"></i> रद्द करें</a>
                    </div>
                </form>
            </div>
        </div>

        <?php elseif ($action === 'view' && $camp_data): ?>
        <!-- Camp Details -->
        <div class="row">
            <div class="col-lg-8">
                <div class="admin-card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle"></i> कैंप विवरण</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>कैंप ID:</strong></label>
                                    <p><?php echo htmlspecialchars($camp_data['camp_id']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>कार्यक्रम:</strong></label>
                                    <p><?php echo htmlspecialchars($camp_data['program']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>नाम:</strong></label>
                                    <p><?php echo htmlspecialchars($camp_data['name']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>पिता का नाम:</strong></label>
                                    <p><?php echo htmlspecialchars($camp_data['father_name']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>पता:</strong></label>
                            <p><?php echo htmlspecialchars($camp_data['address']); ?></p>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>कक्षा:</strong></label>
                                    <p><?php echo htmlspecialchars($camp_data['class']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>स्थान:</strong></label>
                                    <p><?php echo htmlspecialchars($camp_data['place']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>राशि:</strong></label>
                                    <p class="text-success"><strong>₹<?php echo number_format($camp_data['amount'], 2); ?></strong></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>भुगतान विधि:</strong></label>
                                    <p>
                                        <span class="badge bg-<?php 
                                            echo $camp_data['payment_method'] === 'online' ? 'primary' : 
                                                ($camp_data['payment_method'] === 'offline' ? 'secondary' : 'success'); ?>">
                                            <?php 
                                            echo $camp_data['payment_method'] === 'online' ? 'ऑनलाइन' : 
                                                ($camp_data['payment_method'] === 'offline' ? 'ऑफलाइन' : 'निःशुल्क'); 
                                            ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>ईमेल:</strong></label>
                                    <p><?php echo htmlspecialchars($camp_data['email'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>स्थिति:</strong></label>
                                    <p>
                                        <span class="badge bg-<?php 
                                            echo $camp_data['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                            <?php echo $camp_data['status'] === 'completed' ? 'पूर्ण' : 'लंबित'; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($camp_data['payment_id'])): ?>
                        <div class="mb-3">
                            <label class="form-label"><strong>भुगतान ID:</strong></label>
                            <p><code><?php echo htmlspecialchars($camp_data['payment_id']); ?></code></p>
                        </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label"><strong>दिनांक:</strong></label>
                            <p><?php echo date('d M Y, h:i A', strtotime($camp_data['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <?php if (!empty($camp_data['payment_proof'])): ?>
                <div class="admin-card">
                    <div class="card-header">
                        <h5><i class="fas fa-image"></i> भुगतान प्रमाण</h5>
                    </div>
                    <div class="card-body text-center">
                        <a href="<?php echo SITE_URL . '/img/payments/' . $camp_data['payment_proof']; ?>" target="_blank">
                            <img src="<?php echo SITE_URL . '/img/payments/' . $camp_data['payment_proof']; ?>" 
                                 alt="Payment Proof" class="img-fluid rounded" style="max-height: 200px;">
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="admin-card">
                    <div class="card-header">
                        <h5><i class="fas fa-cog"></i> कार्य</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if (isAdmin()): ?>
                            <a href="?action=edit&id=<?php echo $camp_data['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> संपादित करें
                            </a>
                            <?php endif; ?>
                            <a href="<?php echo SITE_URL; ?>/generate_receipt.php?camp_id=<?php echo $camp_data['id']; ?>" class="btn btn-success" target="_blank">
                                <i class="fas fa-receipt"></i> रसीद प्रिंट करें
                            </a>
                            <a href="<?php echo SITE_URL; ?>/generate_receipt.php?camp_id=<?php echo $camp_data['id']; ?>&download=1" class="btn btn-outline-success" target="_blank">
                                <i class="fas fa-download"></i> रसीद डाउनलोड करें
                            </a>
                            <?php if (isAdmin() && $camp_data['status'] === 'pending'): ?>
                            <button type="button" class="btn btn-success" onclick="updateStatus(<?php echo $camp_data['id']; ?>, 'completed')">
                                <i class="fas fa-check"></i> स्वीकार करें
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/sidebar.php'; ?>

<!-- Status Update Form (Hidden) -->
<form id="statusForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="update_status">
    <input type="hidden" name="id" id="statusId">
    <input type="hidden" name="status" id="statusValue">
</form>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
function updateStatus(id, status) {
    const statusText = status === 'completed' ? 'स्वीकार' : 'अस्वीकार';
    if (confirm(`क्या आप वाकई इस कैंप को ${statusText} करना चाहते हैं?`)) {
        document.getElementById('statusId').value = id;
        document.getElementById('statusValue').value = status;
        document.getElementById('statusForm').submit();
    }
}

function deleteCamp(id) {
    if (confirm('क्या आप वाकई इस कैंप रिकॉर्ड को हटाना चाहते हैं? यह क्रिया पूर्ववत नहीं की जा सकती है।')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('कृपया सभी आवश्यक फील्ड भरें।');
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>