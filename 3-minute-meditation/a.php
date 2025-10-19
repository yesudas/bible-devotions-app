<?php
session_start();

// Admin credentials
$admin_users = [
    'mariajoseph' => 'maria83231',
    'yesudas' => 'yesu32425'
];

// Handle login
if ($_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (isset($admin_users[$username]) && $admin_users[$username] === $password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin.php');
        exit;
    } else {
        $login_error = 'Invalid credentials';
    }
}

// Handle logout
if ($_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Check if logged in
$is_logged_in = $_SESSION['admin_logged_in'] ?? false;
$admin_username = $_SESSION['admin_username'] ?? '';

// Helper functions
function updateAllMeditationsFile() {
    $files = glob('meditations/*.json');
    $all_meditations = [];
    
    foreach ($files as $file) {
        $sequence = (int)basename($file, '.json');
        $data = json_decode(file_get_contents($file), true);
        
        if ($data) {
            $all_meditations[] = [
                'sequence' => $sequence,
                'title' => $data['title'],
                'date' => $data['date'],
                'username' => $data['uploaded_by'] ?? 'Unknown'
            ];
        }
    }
    
    // Sort by sequence number (latest first)
    usort($all_meditations, function($a, $b) {
        return $b['sequence'] - $a['sequence'];
    });
    
    file_put_contents('all-meditations.json', json_encode($all_meditations, JSON_PRETTY_PRINT));
}

function getNextSequenceNumber() {
    $files = glob('meditations/*.json');
    $max = 0;
    foreach ($files as $file) {
        $num = (int)basename($file, '.json');
        if ($num > $max) $max = $num;
    }
    return $max + 1;
}

// Handle CRUD operations
if ($is_logged_in) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    // Add meditation
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $sequence = getNextSequenceNumber();
        $meditation = [
            'date' => $_POST['date'],
            'title' => $_POST['title'],
            'memory_verse' => [
                'text' => $_POST['memory_verse_text'],
                'reference' => $_POST['memory_verse_reference']
            ],
            'insight_reflection' => $_POST['insight_reflection'],
            'todays_quote' => $_POST['todays_quote'],
            'recommended_book' => [
                'title' => $_POST['book_title'],
                'author' => $_POST['book_author'],
                'page' => (int)$_POST['book_page'],
                'quote' => $_POST['book_quote'],
                'link' => $_POST['book_link']
            ],
            'prayer' => $_POST['prayer'],
            'a_word_to_you' => array_filter(explode("\n", $_POST['a_word_to_you'])),
            'uploaded_by' => $admin_username
        ];
        
        file_put_contents("meditations/{$sequence}.json", json_encode($meditation));
        updateAllMeditationsFile();
        $success_message = "Meditation added successfully!";
    }
    
    // Edit meditation
    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $sequence = (int)$_POST['sequence'];
        $meditation = [
            'date' => $_POST['date'],
            'title' => $_POST['title'],
            'memory_verse' => [
                'text' => $_POST['memory_verse_text'],
                'reference' => $_POST['memory_verse_reference']
            ],
            'insight_reflection' => $_POST['insight_reflection'],
            'todays_quote' => $_POST['todays_quote'],
            'recommended_book' => [
                'title' => $_POST['book_title'],
                'author' => $_POST['book_author'],
                'page' => (int)$_POST['book_page'],
                'quote' => $_POST['book_quote'],
                'link' => $_POST['book_link']
            ],
            'prayer' => $_POST['prayer'],
            'a_word_to_you' => array_filter(explode("\n", $_POST['a_word_to_you'])),
            'uploaded_by' => $_POST['uploaded_by'] // Keep original uploader
        ];
        
        file_put_contents("meditations/{$sequence}.json", json_encode($meditation));
        updateAllMeditationsFile();
        $success_message = "Meditation updated successfully!";
    }
    
    // Delete meditation
    if ($action === 'delete') {
        $sequence = (int)$_GET['id'];
        if (file_exists("meditations/{$sequence}.json")) {
            unlink("meditations/{$sequence}.json");
            updateAllMeditationsFile();
            $success_message = "Meditation deleted successfully!";
        }
    }
    
    // Get meditation for editing
    $edit_meditation = null;
    if ($action === 'edit_form') {
        $sequence = (int)$_GET['id'];
        $file = "meditations/{$sequence}.json";
        if (file_exists($file)) {
            $edit_meditation = json_decode(file_get_contents($file), true);
            $edit_meditation['sequence'] = $sequence;
        }
    }
    
    // Load all meditations for display
    $all_meditations_file = 'all-meditations.json';
    $all_meditations = file_exists($all_meditations_file) ? 
        json_decode(file_get_contents($all_meditations_file), true) : [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - 3-Minute Meditation</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-shield-lock me-2"></i>Admin Panel - 3-Minute Meditation
            </a>
            
            <?php if ($is_logged_in): ?>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle me-1"></i>Welcome, <?php echo htmlspecialchars($admin_username); ?>
                </span>
                <a href="admin.php?action=logout" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container my-4">
        <?php if (!$is_logged_in): ?>
            <!-- Login Form -->
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white text-center">
                            <h4><i class="bi bi-lock me-2"></i>Admin Login</h4>
                        </div>
                        <div class="card-body">
                            <?php if (isset($login_error)): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $login_error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <input type="hidden" name="action" value="login">
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Admin Dashboard -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="bi bi-gear me-2"></i>Meditation Management</h2>
                        <div class="btn-group">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-1"></i>Back to Site
                            </a>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-plus-circle me-1"></i>Add New
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Meditations List -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-list me-2"></i>All Meditations</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($all_meditations)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-inbox display-4"></i>
                                    <p class="mt-2">No meditations found.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th>Date</th>
                                                <th>Uploaded By</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($all_meditations as $meditation): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo $meditation['sequence']; ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($meditation['title']); ?></td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar me-1"></i><?php echo $meditation['date']; ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($meditation['username']); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="index.php?id=<?php echo $meditation['sequence']; ?>" 
                                                               class="btn btn-outline-primary" target="_blank">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <a href="admin.php?action=edit_form&id=<?php echo $meditation['sequence']; ?>" 
                                                               class="btn btn-outline-warning">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a href="admin.php?action=delete&id=<?php echo $meditation['sequence']; ?>" 
                                                               class="btn btn-outline-danger"
                                                               onclick="return confirm('Are you sure you want to delete this meditation?')">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
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
            
            <!-- Add/Edit Modal -->
            <div class="modal fade" id="addModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-plus-circle me-2"></i>
                                <?php echo $edit_meditation ? 'Edit Meditation' : 'Add New Meditation'; ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" id="meditationForm">
                            <input type="hidden" name="action" value="<?php echo $edit_meditation ? 'edit' : 'add'; ?>">
                            <?php if ($edit_meditation): ?>
                                <input type="hidden" name="sequence" value="<?php echo $edit_meditation['sequence']; ?>">
                                <input type="hidden" name="uploaded_by" value="<?php echo htmlspecialchars($edit_meditation['uploaded_by']); ?>">
                            <?php endif; ?>
                            
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="date" class="form-label">Date *</label>
                                            <input type="text" class="form-control" id="date" name="date" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['date']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Title *</label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['title']) : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="memory_verse_text" class="form-label">Memory Verse Text *</label>
                                            <input type="text" class="form-control" id="memory_verse_text" name="memory_verse_text" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['memory_verse']['text']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="memory_verse_reference" class="form-label">Reference *</label>
                                            <input type="text" class="form-control" id="memory_verse_reference" name="memory_verse_reference" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['memory_verse']['reference']) : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="insight_reflection" class="form-label">Insight & Reflection *</label>
                                    <textarea class="form-control" id="insight_reflection" name="insight_reflection" rows="6" required><?php echo $edit_meditation ? htmlspecialchars($edit_meditation['insight_reflection']) : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="todays_quote" class="form-label">Today's Quote *</label>
                                    <input type="text" class="form-control" id="todays_quote" name="todays_quote" 
                                           value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['todays_quote']) : ''; ?>" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="book_title" class="form-label">Book Title *</label>
                                            <input type="text" class="form-control" id="book_title" name="book_title" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['recommended_book']['title']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="book_author" class="form-label">Author *</label>
                                            <input type="text" class="form-control" id="book_author" name="book_author" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['recommended_book']['author']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label for="book_page" class="form-label">Page *</label>
                                            <input type="number" class="form-control" id="book_page" name="book_page" 
                                                   value="<?php echo $edit_meditation ? $edit_meditation['recommended_book']['page'] : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="book_quote" class="form-label">Book Quote *</label>
                                    <input type="text" class="form-control" id="book_quote" name="book_quote" 
                                           value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['recommended_book']['quote']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="book_link" class="form-label">Book Link</label>
                                    <input type="url" class="form-control" id="book_link" name="book_link" 
                                           value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['recommended_book']['link']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="prayer" class="form-label">Prayer *</label>
                                    <textarea class="form-control" id="prayer" name="prayer" rows="3" required><?php echo $edit_meditation ? htmlspecialchars($edit_meditation['prayer']) : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="a_word_to_you" class="form-label">A Word to You (one per line) *</label>
                                    <textarea class="form-control" id="a_word_to_you" name="a_word_to_you" rows="3" required><?php echo $edit_meditation ? implode("\n", $edit_meditation['a_word_to_you']) : ''; ?></textarea>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <?php echo $edit_meditation ? 'Update' : 'Save'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php if ($edit_meditation): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var addModal = new bootstrap.Modal(document.getElementById('addModal'));
                    addModal.show();
                });
            </script>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>