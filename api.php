<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getDevotions':
        getDevotions();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

/**
 * GET api.php?action=getDevotions&lang=<language>&book=<book number>&chapter=<chapter number>&verse=<verse number>
 *
 * Example: api.php?action=getDevotions&lang=தமிழ்&book=19&chapter=134&verse=1
 * Returns the raw contents of the matching index/{lang}/verses/{book}_{chapter}-{verse}.json
 * or index/{lang}/chapters/{book}_{chapter}.json file, e.g.:
 *   [{"brand": "3-minute-meditation", "title": "...", "filename": "44.json"}]
 * or [] if nothing is found anywhere.
 *
 * Lookup order:
 * 1. index/{lang}/verses/{book}_{chapter}-{verse}.json
 * 2. index/{lang}/chapters/{book}_{chapter}.json
 * 3. If $lang isn't English, repeat steps 1-2 against index/English instead.
 */
function getDevotions() {
    $baseDir = __DIR__;
    $lang = trim($_GET['lang'] ?? '');
    $book = trim($_GET['book'] ?? '');
    $chapter = trim($_GET['chapter'] ?? '');
    $verse = trim($_GET['verse'] ?? '');

    if ($lang === '' || $book === '' || $chapter === '') {
        echo json_encode(['success' => false, 'error' => 'lang, book and chapter parameters required']);
        return;
    }

    $devotions = lookupDevotions($baseDir, $lang, $book, $chapter, $verse);

    if (empty($devotions) && $lang !== 'English') {
        $devotions = lookupDevotions($baseDir, 'English', $book, $chapter, $verse);
    }

    echo json_encode($devotions, JSON_UNESCAPED_UNICODE);
}

/**
 * Looks up devotions for a given language, first at the verse level (if a
 * verse was given), then falling back to the chapter level. Returns the
 * decoded JSON contents of whichever file matches first, or [] if neither
 * exists.
 */
function lookupDevotions($baseDir, $lang, $book, $chapter, $verse) {
    $langDir = $baseDir . '/index/' . basename($lang);

    if ($verse !== '') {
        $verseFile = $langDir . '/verses/' . basename($book . '_' . $chapter . '-' . $verse) . '.json';
        if (file_exists($verseFile)) {
            return json_decode(file_get_contents($verseFile), true) ?? [];
        }
    }

    $chapterFile = $langDir . '/chapters/' . basename($book . '_' . $chapter) . '.json';
    if (file_exists($chapterFile)) {
        return json_decode(file_get_contents($chapterFile), true) ?? [];
    }

    return [];
}
