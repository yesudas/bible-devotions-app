<?php

// Set admin session timeout to 30 minutes (1800 seconds)
ini_set('session.gc_maxlifetime', 1800);
session_set_cookie_params(1800);

session_start();

$version = "2025.10.7";


$languages = ["தமிழ்"];

// Language selection for admin (defaults to first language)
$selectedLanguage = $_GET['lang'] ?? $_SESSION['admin_selected_language'] ?? $languages[0];

// Validate selected language
if (!in_array($selectedLanguage, $languages)) {
    $selectedLanguage = $languages[0];
}

// Store in session for persistence
$_SESSION['admin_selected_language'] = $selectedLanguage;


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
//$is_logged_in = $_SESSION['admin_logged_in'] ?? false;
$is_logged_in = true;
//$admin_username = $_SESSION['admin_username'] ?? '';
$admin_username = 'mariajoseph';

// Helper functions
function generateUniqueId() {
    return date('YmdHis') . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
}

function updateAllMeditationsFile($language) {
    $files = glob("meditations/{$language}/*.json");
    $all_meditations = [];
    
    foreach ($files as $file) {
        $filename = basename($file);
        if ($filename === 'all-meditations.json') continue; // Skip the all-meditations.json file
        
        $data = json_decode(file_get_contents($file), true);
        
        if ($data) {
            $entry = [
                'uniqueid' => $data['uniqueid'] ?? $filename, // Use uniqueid if exists, fallback to filename
                'filename' => $filename,
                'title' => $data['title'],
                'date' => $data['date']
            ];
            
            // Add scheduled attribute if present in the meditation file
            if (isset($data['scheduled']) && $data['scheduled'] === true) {
                $entry['scheduled'] = true;
            }
            
            $all_meditations[] = $entry;
        }
    }
    
    // Sort by date (latest first), then by uniqueid
    usort($all_meditations, function($a, $b) {
        $dateCompare = strcmp($b['date'], $a['date']);
        if ($dateCompare !== 0) return $dateCompare;
        return strcmp($b['uniqueid'], $a['uniqueid']);
    });
    
    file_put_contents("meditations/{$language}/all-meditations.json", json_encode($all_meditations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getNextFilename($language) {
    $files = glob("meditations/{$language}/*.json");
    $max = 0;
    foreach ($files as $file) {
        $num = (int)basename($file, '.json');
        if ($num > $max) $max = $num;
    }
    return ($max + 1) . '.json';
}

// Save link information to links folder
function saveLinkInfo($key_verse, $title, $language, $filename, $brand = '3-minute-meditation') {
    if (empty($key_verse)) {
        return false;
    }
    
    $links_dir = __DIR__ . '/../links';
    
    // Create links directory if it doesn't exist
    if (!file_exists($links_dir)) {
        mkdir($links_dir, 0755, true);
    }
    
    $link_file = $links_dir . '/' . $key_verse . '.json';
    
    // Load existing links or create new array
    $links = [];
    if (file_exists($link_file)) {
        $existing = json_decode(file_get_contents($link_file), true);
        if (is_array($existing)) {
            $links = $existing;
        }
    }
    
    // Create new link entry
    $new_link = [
        'brand' => $brand,
        'title' => $title,
        'language' => $language,
        'file' => '/3-minute-meditation/meditations/' . $language . '/' . $filename
    ];
    
    // Check if this file already exists (check by file path only)
    $link_exists = false;
    foreach ($links as $index => $link) {
        if ($link['file'] === $new_link['file']) {
            // Update existing link (title and language may have changed)
            $links[$index] = $new_link;
            $link_exists = true;
            break;
        }
    }
    
    // Add new link if it doesn't exist
    if (!$link_exists) {
        $links[] = $new_link;
    }
    
    // Save to file
    file_put_contents($link_file, json_encode($links, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return true;
}

// Handle CRUD operations
if ($is_logged_in || true) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    // Add meditation
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $language = $_POST['language'];
        $uniqueid = generateUniqueId();
        $filename = getNextFilename($language);
        
        // Check if date is in the future
        $meditation_date = $_POST['date'];
        $today = date('Y-m-d');
        $is_future = $meditation_date > $today;
        
        $meditation = [
            'uniqueid' => $uniqueid,
            'date' => $meditation_date,
            'title' => $_POST['title'],
            'key_verse' => $_POST['key_verse'],
            'memory_verse' => [
                'label' => $_POST['memory_verse_label'] ?: ($language === 'தமிழ்' ? 'மனப்பாட வசனம்' : 'Memory Verse'),
                'text' => $_POST['memory_verse_text']
            ],
            'devotion' => [
                'label' => $_POST['devotion_label'] ?: ($language === 'தமிழ்' ? 'தியானம்' : 'Insight / Reflection'),
                'text' => $_POST['devotion_text']
            ],
            'prayer' => [
                'label' => $_POST['prayer_label'] ?: ($language === 'தமிழ்' ? 'ஜெபம்' : 'Prayer'),
                'text' => $_POST['prayer_text']
            ],
            'author' => [
                'label' => $_POST['author_label'] ?: ($language === 'தமிழ்' ? 'ஆசிரியர்' : 'Author'),
                'author' => $_POST['author_name'],
                'mobile' => $_POST['author_mobile'],
                'whatsapp' => $_POST['author_whatsapp'],
                'email' => $_POST['author_email']
            ]
        ];
        
        // Add scheduled attribute if date is in the future
        if ($is_future) {
            $meditation['scheduled'] = true;
        }
        
        // Add song section if provided
        if (!empty($_POST['song_text'])) {
            $meditation['song'] = [
                'label' => $_POST['song_label'] ?: ($language === 'தமிழ்' ? 'பாடல்' : 'Song'),
                'text' => $_POST['song_text']
            ];
        }
        
        file_put_contents("meditations/{$language}/{$filename}", json_encode($meditation, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        updateAllMeditationsFile($language);
        
        // Save link information - brand is always '3-minute-meditation' for this admin panel
        saveLinkInfo($_POST['key_verse'], $_POST['title'], $language, $filename, '3-minute-meditation');
        
        $success_message = "Meditation added successfully!";
    }
    
    // Edit meditation
    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $filename = $_POST['filename'];
        $language = $_POST['language'];
        
        // Load existing meditation to preserve uniqueid
        $existingData = json_decode(file_get_contents("meditations/{$language}/{$filename}"), true);
        $uniqueid = $existingData['uniqueid'] ?? generateUniqueId();
        
        // Check if date is in the future
        $meditation_date = $_POST['date'];
        $today = date('Y-m-d');
        $is_future = $meditation_date > $today;
        
        $meditation = [
            'uniqueid' => $uniqueid,
            'date' => $meditation_date,
            'title' => $_POST['title'],
            'key_verse' => $_POST['key_verse'],
            'memory_verse' => [
                'label' => $_POST['memory_verse_label'] ?: ($language === 'தமிழ்' ? 'மனப்பாட வசனம்' : 'Memory Verse'),
                'text' => $_POST['memory_verse_text']
            ],
            'devotion' => [
                'label' => $_POST['devotion_label'] ?: ($language === 'தமிழ்' ? 'தியானம்' : 'Insight / Reflection'),
                'text' => $_POST['devotion_text']
            ],
            'prayer' => [
                'label' => $_POST['prayer_label'] ?: ($language === 'தமிழ்' ? 'ஜெபம்' : 'Prayer'),
                'text' => $_POST['prayer_text']
            ],
            'author' => [
                'label' => $_POST['author_label'] ?: ($language === 'தமிழ்' ? 'ஆசிரியர்' : 'Author'),
                'author' => $_POST['author_name'],
                'mobile' => $_POST['author_mobile'],
                'whatsapp' => $_POST['author_whatsapp'],
                'email' => $_POST['author_email']
            ]
        ];
        
        // Add scheduled attribute if date is in the future
        if ($is_future) {
            $meditation['scheduled'] = true;
        }
        
        // Add song section if provided
        if (!empty($_POST['song_text'])) {
            $meditation['song'] = [
                'label' => $_POST['song_label'] ?: ($language === 'தமிழ்' ? 'பாடல்' : 'Song'),
                'text' => $_POST['song_text']
            ];
        }
        
        file_put_contents("meditations/{$language}/{$filename}", json_encode($meditation, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        updateAllMeditationsFile($language);
        
        // Save link information - brand is always '3-minute-meditation' for this admin panel
        saveLinkInfo($_POST['key_verse'], $_POST['title'], $language, $filename, '3-minute-meditation');
        
        $success_message = "Meditation updated successfully!";
    }
    
    // Delete meditation
    if ($action === 'delete') {
        $filename = $_GET['filename'];
        $language = $_GET['lang'] ?? $selectedLanguage;
        if (file_exists("meditations/{$language}/{$filename}")) {
            unlink("meditations/{$language}/{$filename}");
            updateAllMeditationsFile($language);
            $success_message = "Meditation deleted successfully!";
        }
    }
    
    // Get meditation for editing
    $edit_meditation = null;
    if ($action === 'edit_form') {
        $filename = $_GET['filename'];
        $language = $_GET['lang'] ?? $selectedLanguage;
        $file = "meditations/{$language}/{$filename}";
        if (file_exists($file)) {
            $edit_meditation = json_decode(file_get_contents($file), true);
            $edit_meditation['filename'] = $filename;
            $edit_meditation['language'] = $language;
        }
    }
    
    // Load all meditations for display
    $all_meditations_file = "meditations/{$selectedLanguage}/all-meditations.json";
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

    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest-a.json?v=<?php echo $version; ?>">
    <meta name="theme-color" content="#9657deff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Admin - 3-Minute Meditation">

    <!-- Google Analytics -->
    <?php include '../google-analytics.php'; ?>

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
                            <a href="index.php" class="btn btn-outline-secondary admin-header-btn">
                                <i class="bi bi-arrow-left me-1"></i><span>Back to Site</span>
                            </a>
                            <a href="?lang=<?php echo urlencode($selectedLanguage); ?>" class="btn btn-outline-primary admin-header-btn">
                                <i class="bi bi-arrow-clockwise me-1"></i><span>Refresh</span>
                            </a>
                            <button id="installAppBtn" class="btn btn-outline-primary admin-header-btn">
                                <i class="bi bi-save"></i> Install as App
                            </button>
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
                    
                    <!-- Filters Section -->
                    <div class="admin-filters p-3 border-bottom">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">
                                    <i class="bi bi-translate me-1"></i>Language
                                </label>
                                <select id="languageFilter" class="form-select form-select-sm" onchange="window.location.href='?lang=' + this.value">
                                    <?php foreach ($languages as $lang): ?>
                                        <option value="<?php echo htmlspecialchars($lang); ?>" <?php echo $selectedLanguage === $lang ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($lang); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">
                                    <i class="bi bi-funnel me-1"></i>Status
                                </label>
                                <select id="statusFilter" class="form-select form-select-sm">
                                    <option value="all">All Meditations</option>
                                    <option value="scheduled">Scheduled Only</option>
                                    <option value="published">Published Only</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">
                                    <i class="bi bi-calendar me-1"></i>Filter by Date
                                </label>
                                <input type="date" id="dateFilter" class="form-control form-control-sm" placeholder="Filter by date">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted mb-1">
                                    <i class="bi bi-search me-1"></i>Search Title
                                </label>
                                <input type="text" id="titleFilter" class="form-control form-control-sm" placeholder="Search by title...">
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFilters">
                                <i class="bi bi-x-circle me-1"></i>Clear Filters
                            </button>
                        </div>
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
                                <table class="admin-table mb-0" id="meditationsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>File</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_meditations as $index => $meditation): ?>
                                            <tr data-scheduled="<?php echo isset($meditation['scheduled']) && $meditation['scheduled'] ? 'true' : 'false'; ?>" 
                                                data-date="<?php echo $meditation['date']; ?>" 
                                                data-title="<?php echo htmlspecialchars($meditation['title']); ?>">
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
                                                    <?php if (isset($meditation['scheduled']) && $meditation['scheduled']): ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-clock-history me-1"></i>Scheduled
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>Published
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <i class="bi bi-file-earmark me-1"></i><?php echo htmlspecialchars($meditation['filename']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="?action=edit_form&filename=<?php echo urlencode($meditation['filename']); ?>&lang=<?php echo urlencode($selectedLanguage); ?>" 
                                                           class="btn admin-btn-outline-warning" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="?action=delete&filename=<?php echo urlencode($meditation['filename']); ?>&lang=<?php echo urlencode($selectedLanguage); ?>" 
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
                            <div class="d-md-none admin-mobile-list" id="mobileList">
                                <?php foreach ($all_meditations as $index => $meditation): ?>
                                    <div class="admin-meditation-card" 
                                         data-scheduled="<?php echo isset($meditation['scheduled']) && $meditation['scheduled'] ? 'true' : 'false'; ?>" 
                                         data-date="<?php echo $meditation['date']; ?>" 
                                         data-title="<?php echo htmlspecialchars($meditation['title']); ?>">
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
                                                    <?php if (isset($meditation['scheduled']) && $meditation['scheduled']): ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-clock-history me-1"></i>Scheduled
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i>Published
                                                        </span>
                                                    <?php endif; ?>
                                                </span>
                                                <span class="admin-meta-item">
                                                    <i class="bi bi-file-earmark me-1"></i><?php echo htmlspecialchars($meditation['filename']); ?>
                                                </span>
                                            </div>
                                            <div class="admin-meditation-actions">
                                                <a href="?action=edit_form&filename=<?php echo urlencode($meditation['filename']); ?>&lang=<?php echo urlencode($selectedLanguage); ?>" 
                                                   class="btn btn-sm admin-btn-outline-warning">
                                                    <i class="bi bi-pencil me-1"></i>Edit
                                                </a>
                                                <a href="?action=delete&filename=<?php echo urlencode($meditation['filename']); ?>&lang=<?php echo urlencode($selectedLanguage); ?>" 
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
                                            <label for="language" class="admin-form-label">Language *</label>
                                            <select class="admin-form-control" id="language" name="language" required>
                                                <?php foreach ($languages as $lang): ?>
                                                    <option value="<?php echo htmlspecialchars($lang); ?>" 
                                                        <?php echo ($edit_meditation && isset($edit_meditation['language']) && $edit_meditation['language'] === $lang) || (!$edit_meditation && $lang === $languages[0]) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($lang); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="title" class="admin-form-label">Title *</label>
                                    <input type="text" class="admin-form-control" id="title" name="title" 
                                           value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['title']) : ''; ?>" required>
                                </div>
                                
                                <!-- Key Verse Reference Section -->
                                <div class="admin-form-group">
                                    <label class="admin-form-label">Key Verse Reference *</label>
                                    <input type="hidden" id="key_verse" name="key_verse" 
                                           value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['key_verse']) : ''; ?>" required>
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <select class="admin-form-control" id="verse_book" required>
                                                <option value="">Select Book</option>
                                            </select>
                                            <small class="text-muted">Bible Book</small>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="admin-form-control" id="verse_chapter" required disabled>
                                                <option value="">Chapter</option>
                                            </select>
                                            <small class="text-muted">Chapter</small>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="admin-form-control" id="verse_start" required disabled>
                                                <option value="">Verse</option>
                                            </select>
                                            <small class="text-muted">Start Verse</small>
                                        </div>
                                        <div class="col-md-2">
                                            <select class="admin-form-control" id="verse_end" disabled>
                                                <option value="">End</option>
                                            </select>
                                            <small class="text-muted">End Verse (Optional)</small>
                                        </div>
                                        <div class="col-md-1 d-flex align-items-start">
                                            <button type="button" class="btn btn-sm btn-outline-secondary mt-0" id="clearVerse" title="Clear Selection">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            <span id="versePreview">No verse selected</span>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="admin-form-group">
                                            <label for="memory_verse_label" class="admin-form-label">Memory Verse Label</label>
                                            <input type="text" class="admin-form-control" id="memory_verse_label" name="memory_verse_label" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['memory_verse']['label']) : ''; ?>">
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
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['devotion']['label']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="admin-form-group">
                                            <label for="devotion_text" class="admin-form-label">Devotion Text *</label>
                                            <textarea class="admin-form-control" id="devotion_text" name="devotion_text" rows="6" required><?php echo $edit_meditation ? htmlspecialchars($edit_meditation['devotion']['text']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Quote Section - Hidden for அனுதின-மன்னா -->
                                <div style="display: none;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="admin-form-group">
                                                <label for="quote_label" class="admin-form-label">Quote Label</label>
                                                <input type="text" class="admin-form-control" id="quote_label" name="quote_label" 
                                                       value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['quote']['label'] ?? '') : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="admin-form-group">
                                                <label for="quote_text" class="admin-form-label">Quote Text</label>
                                                <input type="text" class="admin-form-control" id="quote_text" name="quote_text" 
                                                       value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['quote']['text'] ?? '') : ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Recommended Book Section - Hidden for அனுதின-மன்னா -->
                                <div style="display: none;">
                                    <h6 class="mt-4 mb-3 text-primary"><i class="bi bi-book me-2"></i>Recommended Book</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="admin-form-group">
                                            <label for="book_label" class="admin-form-label">Book Label</label>
                                            <input type="text" class="admin-form-control" id="book_label" name="book_label" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['recommended_book']['label'] ?? '') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
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
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="admin-form-group">
                                            <label for="book_page" class="admin-form-label">Page</label>
                                            <input type="text" class="admin-form-control" id="book_page" name="book_page" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['recommended_book']['page'] ?? '') : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="admin-form-group">
                                            <label for="book_quote" class="admin-form-label">Book Quote</label>
                                            <input type="text" class="admin-form-control" id="book_quote" name="book_quote" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['recommended_book']['quote'] ?? '') : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="admin-form-group">
                                    <label for="book_link" class="admin-form-label">Book Link</label>
                                    <input type="url" class="admin-form-control" id="book_link" name="book_link" 
                                           value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['recommended_book']['link'] ?? '') : ''; ?>">
                                </div>
                                </div><!-- End of Hidden Recommended Book Section -->
                                
                                <!-- Song Section (Optional) -->
                                <h6 class="mt-4 mb-3 text-primary"><i class="bi bi-music-note me-2"></i>Song (Optional)</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="admin-form-group">
                                            <label for="song_label" class="admin-form-label">Song Label</label>
                                            <input type="text" class="admin-form-control" id="song_label" name="song_label" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['song']['label'] ?? '') : ''; ?>">
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
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['prayer']['label']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="admin-form-group">
                                            <label for="prayer_text" class="admin-form-label">Prayer Text *</label>
                                            <textarea class="admin-form-control" id="prayer_text" name="prayer_text" rows="3" required><?php echo $edit_meditation ? htmlspecialchars($edit_meditation['prayer']['text']) : ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Conclusion Section - Hidden for அனுதின-மன்னா -->
                                <div style="display: none;">
                                    <h6 class="mt-4 mb-3 text-primary"><i class="bi bi-star me-2"></i>Conclusion</h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="admin-form-group">
                                                <label for="conclusion_label" class="admin-form-label">Conclusion Label</label>
                                                <input type="text" class="admin-form-control" id="conclusion_label" name="conclusion_label" 
                                                       value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['conclusion']['label'] ?? '') : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="admin-form-group">
                                                <label for="conclusion_text" class="admin-form-label">Conclusion Text (one per line)</label>
                                                <textarea class="admin-form-control" id="conclusion_text" name="conclusion_text" rows="3"><?php echo $edit_meditation ? implode("\n", $edit_meditation['conclusion']['text'] ?? []) : ''; ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- End of Hidden Conclusion Section -->
                                
                                <!-- Author Section -->
                                <h6 class="mt-4 mb-3 text-primary"><i class="bi bi-person me-2"></i>Author Information</h6>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="admin-form-group">
                                            <label for="author_label" class="admin-form-label">Author Label</label>
                                            <input type="text" class="admin-form-control" id="author_label" name="author_label" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['author']['label'] ?? '') : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="admin-form-group">
                                            <label for="author_name" class="admin-form-label">Author Name *</label>
                                            <input type="text" class="admin-form-control" id="author_name" name="author_name" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['author']['author']) : 'Gladys Sugandhi Hazlitt'; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="admin-form-group">
                                            <label for="author_whatsapp" class="admin-form-label">WhatsApp Number</label>
                                            <input type="text" class="admin-form-control" id="author_whatsapp" name="author_whatsapp" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['author']['whatsapp']) : '919243183231'; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="admin-form-group">
                                            <label for="author_mobile" class="admin-form-label">Mobile Number</label>
                                            <input type="text" class="admin-form-control" id="author_mobile" name="author_mobile" 
                                                   value="<?php echo $edit_meditation ? htmlspecialchars($edit_meditation['author']['mobile']) : ''; ?>">
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
            
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Bible Data JS -->
    <script src="../js/bible-data.js?v=<?php echo $version; ?>"></script>
    <!-- Translations JS -->
    <script src="js/translations.js?v=<?php echo $version; ?>"></script>
    <script src="../pwa/pwa.js?v=<?php echo $version; ?>" type="text/javascript"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Flatpickr
            flatpickr("#date", {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
            });

            // Bible Verse Selector Logic
            const verseBookSelect = document.getElementById('verse_book');
            const verseChapterSelect = document.getElementById('verse_chapter');
            const verseStartSelect = document.getElementById('verse_start');
            const verseEndSelect = document.getElementById('verse_end');
            const keyVerseInput = document.getElementById('key_verse');
            const versePreview = document.getElementById('versePreview');
            const clearVerseBtn = document.getElementById('clearVerse');

            // Populate books dropdown
            function populateBooks() {
                verseBookSelect.innerHTML = '<option value="">Select Book</option>';
                bibleData.books.forEach(book => {
                    const option = document.createElement('option');
                    option.value = book.no;
                    option.textContent = `${book.no}. ${book.name}`;
                    verseBookSelect.appendChild(option);
                });
            }

            // Populate chapters dropdown
            function populateChapters(bookNo) {
                verseChapterSelect.innerHTML = '<option value="">Select Chapter</option>';
                verseChapterSelect.disabled = true;
                verseStartSelect.innerHTML = '<option value="">Select Verse</option>';
                verseStartSelect.disabled = true;
                verseEndSelect.innerHTML = '<option value="">End Verse (Optional)</option>';
                verseEndSelect.disabled = true;

                if (!bookNo) return;

                const chapters = bibleData.getChapters(parseInt(bookNo));
                for (let i = 1; i <= chapters; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `Chapter ${i}`;
                    verseChapterSelect.appendChild(option);
                }
                verseChapterSelect.disabled = false;
            }

            // Populate verses dropdown
            function populateVerses(bookNo, chapterNo) {
                verseStartSelect.innerHTML = '<option value="">Select Verse</option>';
                verseStartSelect.disabled = true;
                verseEndSelect.innerHTML = '<option value="">End Verse (Optional)</option>';
                verseEndSelect.disabled = true;

                if (!bookNo || !chapterNo) return;

                const verses = bibleData.getVerses(parseInt(bookNo), parseInt(chapterNo));
                for (let i = 1; i <= verses; i++) {
                    const startOption = document.createElement('option');
                    startOption.value = i;
                    startOption.textContent = `Verse ${i}`;
                    verseStartSelect.appendChild(startOption);
                }
                verseStartSelect.disabled = false;
            }

            // Populate end verses dropdown
            function populateEndVerses(bookNo, chapterNo, startVerse) {
                verseEndSelect.innerHTML = '<option value="">End Verse (Optional)</option>';
                verseEndSelect.disabled = true;

                if (!bookNo || !chapterNo || !startVerse) return;

                const verses = bibleData.getVerses(parseInt(bookNo), parseInt(chapterNo));
                const start = parseInt(startVerse);
                
                for (let i = start + 1; i <= verses; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `Verse ${i}`;
                    verseEndSelect.appendChild(option);
                }
                verseEndSelect.disabled = false;
            }

            // Update hidden input and preview
            function updateKeyVerse() {
                const bookNo = verseBookSelect.value;
                const chapterNo = verseChapterSelect.value;
                const startVerse = verseStartSelect.value;
                const endVerse = verseEndSelect.value;

                if (!bookNo || !chapterNo || !startVerse) {
                    keyVerseInput.value = '';
                    versePreview.textContent = 'No verse selected';
                    return;
                }

                const reference = bibleData.formatReference(bookNo, chapterNo, startVerse, endVerse);
                keyVerseInput.value = reference;

                // Update preview with book name
                const book = bibleData.getBook(parseInt(bookNo));
                let previewText = `${book.name} ${chapterNo}:${startVerse}`;
                if (endVerse) {
                    previewText += `-${endVerse}`;
                }
                previewText += ` (${reference})`;
                versePreview.textContent = previewText;
            }

            // Clear verse selection
            function clearVerseSelection() {
                verseBookSelect.value = '';
                verseChapterSelect.innerHTML = '<option value="">Select Chapter</option>';
                verseChapterSelect.disabled = true;
                verseStartSelect.innerHTML = '<option value="">Select Verse</option>';
                verseStartSelect.disabled = true;
                verseEndSelect.innerHTML = '<option value="">End Verse (Optional)</option>';
                verseEndSelect.disabled = true;
                keyVerseInput.value = '';
                versePreview.textContent = 'No verse selected';
            }

            // Load existing verse reference (for edit mode)
            function loadExistingVerse() {
                const existingRef = keyVerseInput.value;
                if (!existingRef) return;

                const parsed = bibleData.parseReference(existingRef);
                if (!parsed) return;

                verseBookSelect.value = parsed.bookNo;
                populateChapters(parsed.bookNo);
                
                setTimeout(() => {
                    verseChapterSelect.value = parsed.chapterNo;
                    populateVerses(parsed.bookNo, parsed.chapterNo);
                    
                    setTimeout(() => {
                        verseStartSelect.value = parsed.startVerse;
                        populateEndVerses(parsed.bookNo, parsed.chapterNo, parsed.startVerse);
                        
                        if (parsed.endVerse) {
                            setTimeout(() => {
                                verseEndSelect.value = parsed.endVerse;
                                updateKeyVerse();
                            }, 50);
                        } else {
                            updateKeyVerse();
                        }
                    }, 50);
                }, 50);
            }

            // Event listeners
            verseBookSelect.addEventListener('change', function() {
                populateChapters(this.value);
                updateKeyVerse();
            });

            verseChapterSelect.addEventListener('change', function() {
                populateVerses(verseBookSelect.value, this.value);
                updateKeyVerse();
            });

            verseStartSelect.addEventListener('change', function() {
                populateEndVerses(verseBookSelect.value, verseChapterSelect.value, this.value);
                updateKeyVerse();
            });

            verseEndSelect.addEventListener('change', function() {
                updateKeyVerse();
            });

            clearVerseBtn.addEventListener('click', function() {
                clearVerseSelection();
            });

            // Initialize
            populateBooks();
            loadExistingVerse();

            // Language change handler
            const languageSelect = document.getElementById('language');
            if (languageSelect) {
                languageSelect.addEventListener('change', function() {
                    const selectedLanguage = this.value;
                    updateLabels(selectedLanguage);
                });

                // Set initial labels based on selected language when modal opens
                <?php if (!$edit_meditation): ?>
                // For new meditation, set labels based on default language
                const initialLanguage = languageSelect.value;
                updateLabels(initialLanguage);
                <?php endif; ?>
            }

            function updateLabels(language) {
                if (labelTranslations[language]) {
                    const labels = labelTranslations[language];
                    
                    // Only update if the field is empty or has the default value from another language
                    Object.keys(labels).forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            // Check if current value matches any default value from any language
                            const isDefaultValue = Object.values(labelTranslations).some(langLabels => 
                                langLabels[fieldId] === field.value
                            );
                            
                            // Update only if it's a default value or empty
                            if (isDefaultValue || field.value === '') {
                                field.value = labels[fieldId];
                            }
                        }
                    });
                }
            }

            // Handle modal cleanup for both add and edit cases
            var modalElement = document.getElementById('addModal');
            
            // When modal is hidden, clean up URL parameters if they exist
            modalElement.addEventListener('hidden.bs.modal', function () {
                var url = new URL(window.location);
                if (url.searchParams.has('action') || url.searchParams.has('filename')) {
                    url.searchParams.delete('action');
                    url.searchParams.delete('filename');
                    url.searchParams.delete('lang');
                    window.history.replaceState({}, '', url);
                }
            });

            <?php if ($edit_meditation): ?>
            // Open modal for editing
            var editModal = new bootstrap.Modal(modalElement);
            editModal.show();
            <?php endif; ?>
            
            // Filter functionality
            const statusFilter = document.getElementById('statusFilter');
            const dateFilter = document.getElementById('dateFilter');
            const titleFilter = document.getElementById('titleFilter');
            const clearFiltersBtn = document.getElementById('clearFilters');
            const tableRows = document.querySelectorAll('#meditationsTable tbody tr');
            const mobileCards = document.querySelectorAll('#mobileList .admin-meditation-card');
            
            function applyFilters() {
                const statusValue = statusFilter ? statusFilter.value : 'all';
                const dateValue = dateFilter ? dateFilter.value : '';
                const titleValue = titleFilter ? titleFilter.value.toLowerCase() : '';
                
                // Filter table rows (desktop)
                tableRows.forEach(row => {
                    const scheduled = row.getAttribute('data-scheduled');
                    const date = row.getAttribute('data-date');
                    const title = row.getAttribute('data-title').toLowerCase();
                    
                    let showRow = true;
                    
                    // Status filter
                    if (statusValue === 'scheduled' && scheduled !== 'true') {
                        showRow = false;
                    } else if (statusValue === 'published' && scheduled === 'true') {
                        showRow = false;
                    }
                    
                    // Date filter
                    if (dateValue && date !== dateValue) {
                        showRow = false;
                    }
                    
                    // Title filter
                    if (titleValue && !title.includes(titleValue)) {
                        showRow = false;
                    }
                    
                    row.style.display = showRow ? '' : 'none';
                });
                
                // Filter mobile cards
                mobileCards.forEach(card => {
                    const scheduled = card.getAttribute('data-scheduled');
                    const date = card.getAttribute('data-date');
                    const title = card.getAttribute('data-title').toLowerCase();
                    
                    let showCard = true;
                    
                    // Status filter
                    if (statusValue === 'scheduled' && scheduled !== 'true') {
                        showCard = false;
                    } else if (statusValue === 'published' && scheduled === 'true') {
                        showCard = false;
                    }
                    
                    // Date filter
                    if (dateValue && date !== dateValue) {
                        showCard = false;
                    }
                    
                    // Title filter
                    if (titleValue && !title.includes(titleValue)) {
                        showCard = false;
                    }
                    
                    card.style.display = showCard ? '' : 'none';
                });
            }
            
            // Attach event listeners
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);
            if (dateFilter) dateFilter.addEventListener('change', applyFilters);
            if (titleFilter) titleFilter.addEventListener('input', applyFilters);
            
            // Clear filters
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', function() {
                    if (statusFilter) statusFilter.value = 'all';
                    if (dateFilter) dateFilter.value = '';
                    if (titleFilter) titleFilter.value = '';
                    applyFilters();
                });
            }
        });
    </script>
</body>
</html>