<?php

$version = "2025.10.5";

// Set admin session timeout to 30 minutes (1800 seconds)
ini_set('session.gc_maxlifetime', 1800);
session_set_cookie_params(1800);

session_start();

// Admin credentials
$admin_users = [
    'mariajoseph' => 'maria83231',
    'yesudas' => 'yesu32425'
];

// Handle login
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (isset($admin_users[$username]) && $admin_users[$username] === $password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: a.php');
        exit;
    } else {
        $login_error = 'Invalid credentials';
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: a.php');
    exit;
}

// Check if logged in
$is_logged_in = $_SESSION['admin_logged_in'] ?? false;
$admin_username = $_SESSION['admin_username'] ?? '';

// Helper functions
function generateUniqueId() {
    return date('YmdHis') . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
}

function updateAllMeditationsFile() {
    $files = glob('meditations/*.json');
    $all_meditations = [];
    
    foreach ($files as $file) {
        $filename = basename($file);
        if ($filename === 'all-meditations.json') continue; // Skip the all-meditations.json file
        
        $data = json_decode(file_get_contents($file), true);
        
        if ($data) {
            $all_meditations[] = [
                'uniqueid' => $data['uniqueid'] ?? $filename, // Use uniqueid if exists, fallback to filename
                'filename' => $filename,
                'title' => $data['title'],
                'date' => $data['date']
            ];
        }
    }
    
    // Sort by date (latest first), then by uniqueid
    usort($all_meditations, function($a, $b) {
        $dateCompare = strcmp($b['date'], $a['date']);
        if ($dateCompare !== 0) return $dateCompare;
        return strcmp($b['uniqueid'], $a['uniqueid']);
    });
    
    file_put_contents('meditations/all-meditations.json', json_encode($all_meditations, JSON_PRETTY_PRINT));
}

function getNextFilename() {
    $files = glob('meditations/*.json');
    $max = 0;
    foreach ($files as $file) {
        $num = (int)basename($file, '.json');
        if ($num > $max) $max = $num;
    }
    return ($max + 1) . '.json';
}

// Handle CRUD operations
if ($is_logged_in) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    // Add meditation
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $uniqueid = generateUniqueId();
        $filename = getNextFilename();
        
        $meditation = [
            'uniqueid' => $uniqueid,
            'date' => $_POST['date'],
            'title' => $_POST['title'],
            'key_verse' => $_POST['key_verse'],
            'memory_verse' => [
                'label' => $_POST['memory_verse_label'] ?: 'Memory Verse',
                'text' => $_POST['memory_verse_text']
            ],
            'devotion' => [
                'label' => $_POST['devotion_label'] ?: 'Insight / Reflection',
                'text' => $_POST['devotion_text']
            ],
            'quote' => [
                'label' => $_POST['quote_label'] ?: "Today's Quote",
                'text' => $_POST['quote_text']
            ],
            'prayer' => [
                'label' => $_POST['prayer_label'] ?: 'Prayer',
                'text' => $_POST['prayer_text']
            ],
            'conclusion' => [
                'label' => $_POST['conclusion_label'] ?: 'A Word to You',
                'text' => array_filter(explode("\n", $_POST['conclusion_text']))
            ],
            'author' => [
                'label' => 'Author',
                'author' => $_POST['author_name'],
                'mobile' => $_POST['author_mobile'],
                'whatsapp' => $_POST['author_whatsapp'],
                'email' => $_POST['author_email']
            ]
        ];
        
        // Add recommended_book section if provided
        if (!empty($_POST['book_title']) || !empty($_POST['book_author'])) {
            $meditation['recommended_book'] = [
                'title' => $_POST['book_title'] ?: '',
                'author' => $_POST['book_author'] ?: '',
                'page' => !empty($_POST['book_page']) ? (int)$_POST['book_page'] : 0,
                'quote' => $_POST['book_quote'] ?: '',
                'link' => $_POST['book_link'] ?: ''
            ];
        }
        
        // Add song section if provided
        if (!empty($_POST['song_text'])) {
            $meditation['song'] = [
                'label' => $_POST['song_label'] ?: 'Song',
                'text' => $_POST['song_text']
            ];
        }
        
        file_put_contents("meditations/{$filename}", json_encode($meditation, JSON_PRETTY_PRINT));
        updateAllMeditationsFile();
        $success_message = "Meditation added successfully!";
    }
    
    // Edit meditation
    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $filename = $_POST['filename'];
        
        // Load existing meditation to preserve uniqueid
        $existingData = json_decode(file_get_contents("meditations/{$filename}"), true);
        $uniqueid = $existingData['uniqueid'] ?? generateUniqueId();
        
        $meditation = [
            'uniqueid' => $uniqueid,
            'date' => $_POST['date'],
            'title' => $_POST['title'],
            'key_verse' => $_POST['key_verse'],
            'memory_verse' => [
                'label' => $_POST['memory_verse_label'] ?: 'Memory Verse',
                'text' => $_POST['memory_verse_text']
            ],
            'devotion' => [
                'label' => $_POST['devotion_label'] ?: 'Insight / Reflection',
                'text' => $_POST['devotion_text']
            ],
            'quote' => [
                'label' => $_POST['quote_label'] ?: "Today's Quote",
                'text' => $_POST['quote_text']
            ],
            'prayer' => [
                'label' => $_POST['prayer_label'] ?: 'Prayer',
                'text' => $_POST['prayer_text']
            ],
            'conclusion' => [
                'label' => $_POST['conclusion_label'] ?: 'A Word to You',
                'text' => array_filter(explode("\n", $_POST['conclusion_text']))
            ],
            'author' => [
                'label' => 'Author',
                'author' => $_POST['author_name'],
                'mobile' => $_POST['author_mobile'],
                'whatsapp' => $_POST['author_whatsapp'],
                'email' => $_POST['author_email']
            ]
        ];
        
        // Add recommended_book section if provided
        if (!empty($_POST['book_title']) || !empty($_POST['book_author'])) {
            $meditation['recommended_book'] = [
                'title' => $_POST['book_title'] ?: '',
                'author' => $_POST['book_author'] ?: '',
                'page' => !empty($_POST['book_page']) ? (int)$_POST['book_page'] : 0,
                'quote' => $_POST['book_quote'] ?: '',
                'link' => $_POST['book_link'] ?: ''
            ];
        }
        
        // Add song section if provided
        if (!empty($_POST['song_text'])) {
            $meditation['song'] = [
                'label' => $_POST['song_label'] ?: 'Song',
                'text' => $_POST['song_text']
            ];
        }
        
        file_put_contents("meditations/{$filename}", json_encode($meditation, JSON_PRETTY_PRINT));
        updateAllMeditationsFile();
        $success_message = "Meditation updated successfully!";
    }
    
    // Delete meditation
    if ($action === 'delete') {
        $filename = $_GET['filename'];
        if (file_exists("meditations/{$filename}")) {
            unlink("meditations/{$filename}");
            updateAllMeditationsFile();
            $success_message = "Meditation deleted successfully!";
        }
    }
    
    // Get meditation for editing
    $edit_meditation = null;
    if ($action === 'edit_form') {
        $filename = $_GET['filename'];
        $file = "meditations/{$filename}";
        if (file_exists($file)) {
            $edit_meditation = json_decode(file_get_contents($file), true);
            $edit_meditation['filename'] = $filename;
        }
    }
    
    // Load all meditations for display
    $all_meditations_file = 'meditations/all-meditations.json';
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
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Custom CSS -->
    <link href="../css/style.css?v=<?php echo $version; ?>" rel="stylesheet">
</head>
<body class="admin-body">
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">
                <i class="bi bi-shield-lock me-2"></i>Admin Panel - 3-Minute Meditation
            </a>
            
            <?php if ($is_logged_in): ?>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="bi bi-person-circle me-1"></i>Welcome, <?php echo htmlspecialchars($admin_username); ?>
                </span>
                <a href="a.php?action=logout" class="btn btn-outline-light btn-sm">
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
                    <div class="admin-login-card">
                        <div class="admin-login-header">
                            <h4><i class="bi bi-lock me-2"></i>Admin Login</h4>
                        </div>
                        <div class="card-body p-4">
                            <?php if (isset($login_error)): ?>
                                <div class="admin-alert admin-alert-danger">
                                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $login_error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <input type="hidden" name="action" value="login">
                                
                                <div class="admin-form-group">
                                    <label for="username" class="form-label fw-semibold">Username</label>
                                    <input type="text" class="form-control admin-form-control" id="username" name="username" required>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password" class="form-label fw-semibold">Password</label>
                                    <input type="password" class="form-control admin-form-control" id="password" name="password" required>
                                </div>
                                
                                <button type="submit" class="btn admin-btn-primary w-100">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Admin Dashboard -->
            <div class="admin-container">
                <?php if (isset($success_message)): ?>
                    <div class="admin-alert admin-alert-success alert-dismissible fade show mx-4 mt-4">
                        <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div class="p-4 pb-0">
                    <div class="admin-header-section mb-4">
                        <h2 class="admin-section-title"><i class="bi bi-gear me-2"></i>Meditation Management</h2>
                        <div class="admin-action-buttons">
                            <a href="../index.php" class="btn btn-outline-secondary admin-header-btn">
                                <i class="bi bi-arrow-left me-1"></i><span>Back to Site</span>
                            </a>
                            <button type="button" class="btn admin-btn-success admin-header-btn" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-plus-circle me-1"></i><span>Add New</span>
                            </button>
                        </div>
                    </div>
                </div>
            
            <!-- Meditations List -->
            <div class="p-4 pt-0">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h5><i class="bi bi-list me-2"></i>All Meditations</h5>
                    </div>
                    <div class="admin-card-body p-0">
                        <?php if (empty($all_meditations)): ?>
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-inbox display-4"></i>
                                <p class="mt-2">No meditations found.</p>
                            </div>
                        <?php else: ?>
                            <!-- Desktop Table View -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="admin-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Date</th>
                                            <th>File</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_meditations as $index => $meditation): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $index + 1; ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($meditation['title']); ?></td>
                                                <td>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar me-1"></i><?php echo $meditation['date']; ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <i class="bi bi-file-earmark me-1"></i><?php echo htmlspecialchars($meditation['filename']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?action=edit_form&filename=<?php echo urlencode($meditation['filename']); ?>" 
                                                           class="btn admin-btn-outline-warning" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="?action=delete&filename=<?php echo urlencode($meditation['filename']); ?>" 
                                                           class="btn admin-btn-outline-danger" title="Delete"
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
                            
                            <!-- Mobile Card View -->
                            <div class="d-md-none admin-mobile-list">
                                <?php foreach ($all_meditations as $index => $meditation): ?>
                                    <div class="admin-meditation-card">
                                        <div class="admin-meditation-card-header">
                                            <span class="admin-meditation-badge">#<?php echo $index + 1; ?></span>
                                            <h6 class="admin-meditation-title"><?php echo htmlspecialchars($meditation['title']); ?></h6>
                                        </div>
                                        <div class="admin-meditation-card-body">
                                            <div class="admin-meditation-meta">
                                                <span class="admin-meta-item">
                                                    <i class="bi bi-calendar me-1"></i><?php echo $meditation['date']; ?>
                                                </span>
                                                <span class="admin-meta-item">
                                                    <i class="bi bi-file-earmark me-1"></i><?php echo htmlspecialchars($meditation['filename']); ?>
                                                </span>
                                            </div>
                                            <div class="admin-meditation-actions">
                                                <a href="?action=edit_form&filename=<?php echo urlencode($meditation['filename']); ?>" 
                                                   class="btn btn-sm admin-btn-outline-warning">
                                                    <i class="bi bi-pencil me-1"></i>Edit
                                                </a>
                                                <a href="?action=delete&filename=<?php echo urlencode($meditation['filename']); ?>" 
                                                   class="btn btn-sm admin-btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this meditation?')">
                                                    <i class="bi bi-trash me-1"></i>Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Add/Edit Modal -->
            <div class="modal fade admin-modal" id="addModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-plus-circle me-2"></i>
                                <?php echo $edit_meditation ? 'Edit Meditation' : 'Add New Meditation'; ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" id="meditationForm" class="admin-form">
                            <input type="hidden" name="action" value="<?php echo $edit_meditation ? 'edit' : 'add'; ?>">
                            <?php if ($edit_meditation): ?>
                                <input type="hidden" name="filename" value="<?php echo htmlspecialchars($edit_meditation['filename']); ?>">
                            <?php endif; ?>
                            
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="admin-form-group">
                                            <label for="date" class="admin-form-label">Date *</label>
                                            <input type="date" class="admin-form-control" id="date" name="date" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['date']) : date('Y-m-d'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="admin-form-group">
                                            <label for="title" class="admin-form-label">Title *</label>
                                            <input type="text" class="admin-form-control" id="title" name="title" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['title']) : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="key_verse" class="admin-form-label">Key Verse Reference</label>
                                    <input type="text" class="admin-form-control" id="key_verse" name="key_verse" 
                                           value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['key_verse']) : ''; ?>" 
                                           placeholder="e.g., 47_3:5">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="admin-form-group">
                                            <label for="memory_verse_label" class="admin-form-label">Memory Verse Label</label>
                                            <input type="text" class="admin-form-control" id="memory_verse_label" name="memory_verse_label" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['memory_verse']['label']) : 'Memory Verse'; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="admin-form-group">
                                            <label for="memory_verse_text" class="admin-form-label">Memory Verse Text *</label>
                                            <input type="text" class="admin-form-control" id="memory_verse_text" name="memory_verse_text" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['memory_verse']['text']) : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="admin-form-group">
                                            <label for="devotion_label" class="admin-form-label">Devotion Label</label>
                                            <input type="text" class="admin-form-control" id="devotion_label" name="devotion_label" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['devotion']['label']) : 'Insight / Reflection'; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="admin-form-group">
                                            <label for="devotion_text" class="admin-form-label">Devotion Text *</label>
                                            <textarea class="admin-form-control" id="devotion_text" name="devotion_text" rows="6" required><?php echo $edit_meditation ? htmlspecialchars($edit_meditation['devotion']['text']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="admin-form-group">
                                            <label for="quote_label" class="admin-form-label">Quote Label</label>
                                            <input type="text" class="admin-form-control" id="quote_label" name="quote_label" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['quote']['label']) : "Today's Quote"; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="admin-form-group">
                                            <label for="quote_text" class="admin-form-label">Quote Text *</label>
                                            <input type="text" class="admin-form-control" id="quote_text" name="quote_text" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['quote']['text']) : ''; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Recommended Book Section -->
                                <h6 class="mt-4 mb-3 text-primary"><i class="bi bi-book me-2"></i>Recommended Book</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="admin-form-group">
                                            <label for="book_title" class="admin-form-label">Book Title</label>
                                            <input type="text" class="admin-form-control" id="book_title" name="book_title" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['recommended_book']['title'] ?? '') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="admin-form-group">
                                            <label for="book_author" class="admin-form-label">Author</label>
                                            <input type="text" class="admin-form-control" id="book_author" name="book_author" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['recommended_book']['author'] ?? '') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="admin-form-group">
                                            <label for="book_page" class="admin-form-label">Page</label>
                                            <input type="number" class="admin-form-control" id="book_page" name="book_page" 
                                                   value="<?php echo $edit_meditation ? ($edit_meditation['recommended_book']['page'] ?? '') : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="book_quote" class="admin-form-label">Book Quote</label>
                                    <input type="text" class="admin-form-control" id="book_quote" name="book_quote" 
                                           value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['recommended_book']['quote'] ?? '') : ''; ?>">
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="book_link" class="admin-form-label">Book Link</label>
                                    <input type="url" class="admin-form-control" id="book_link" name="book_link" 
                                           value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['recommended_book']['link'] ?? '') : ''; ?>">
                                </div>
                                
                                <!-- Song Section (Optional) -->
                                <h6 class="mt-4 mb-3 text-primary"><i class="bi bi-music-note me-2"></i>Song (Optional)</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="admin-form-group">
                                            <label for="song_label" class="admin-form-label">Song Label</label>
                                            <input type="text" class="admin-form-control" id="song_label" name="song_label" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['song']['label'] ?? 'Song') : 'Song'; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="admin-form-group">
                                            <label for="song_text" class="admin-form-label">Song Text</label>
                                            <textarea class="admin-form-control" id="song_text" name="song_text" rows="3"><?php echo $edit_meditation ? htmlspecialchars($edit_meditation['song']['text'] ?? '') : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Prayer Section -->
                                <h6 class="mt-4 mb-3 text-primary"><i class="bi bi-heart me-2"></i>Prayer</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="admin-form-group">
                                            <label for="prayer_label" class="admin-form-label">Prayer Label</label>
                                            <input type="text" class="admin-form-control" id="prayer_label" name="prayer_label" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['prayer']['label']) : 'Prayer'; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="admin-form-group">
                                            <label for="prayer_text" class="admin-form-label">Prayer Text *</label>
                                            <textarea class="admin-form-control" id="prayer_text" name="prayer_text" rows="3" required><?php echo $edit_meditation ? htmlspecialchars($edit_meditation['prayer']['text']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Conclusion Section -->
                                <h6 class="mt-4 mb-3 text-primary"><i class="bi bi-star me-2"></i>Conclusion</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="admin-form-group">
                                            <label for="conclusion_label" class="admin-form-label">Conclusion Label</label>
                                            <input type="text" class="admin-form-control" id="conclusion_label" name="conclusion_label" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['conclusion']['label']) : 'A Word to You'; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="admin-form-group">
                                            <label for="conclusion_text" class="admin-form-label">Conclusion Text (one per line) *</label>
                                            <textarea class="admin-form-control" id="conclusion_text" name="conclusion_text" rows="3" required><?php echo $edit_meditation ? implode("\n", $edit_meditation['conclusion']['text']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Author Section -->
                                <h6 class="mt-4 mb-3 text-primary"><i class="bi bi-person me-2"></i>Author Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="admin-form-group">
                                            <label for="author_name" class="admin-form-label">Author Name *</label>
                                            <input type="text" class="admin-form-control" id="author_name" name="author_name" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['author']['author']) : 'Pr. Maria Joseph'; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="admin-form-group">
                                            <label for="author_mobile" class="admin-form-label">Mobile Number</label>
                                            <input type="text" class="admin-form-control" id="author_mobile" name="author_mobile" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['author']['mobile']) : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="admin-form-group">
                                            <label for="author_whatsapp" class="admin-form-label">WhatsApp Number</label>
                                            <input type="text" class="admin-form-control" id="author_whatsapp" name="author_whatsapp" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['author']['whatsapp']) : '919243183231'; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="admin-form-group">
                                            <label for="author_email" class="admin-form-label">Email Address</label>
                                            <input type="email" class="admin-form-control" id="author_email" name="author_email" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['author']['email']) : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn admin-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn admin-btn-primary">
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
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Flatpickr
            flatpickr("#date", {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
            });

            <?php if ($edit_meditation): ?>
            var addModal = new bootstrap.Modal(document.getElementById('addModal'));
            addModal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>