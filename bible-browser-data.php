<?php
/**
 * bible-browser-data.php
 *
 * JSON data endpoint backing bible-browser.php's cascading Book/Chapter
 * dropdowns on the root landing page. Called via fetch() so the filter
 * never triggers a page reload (and therefore never resets scroll
 * position). Reuses the same cross-reference indexes and lookup function
 * as related-devotions.php.
 *
 * GET params:
 *   lang    - required, e.g. 'English', 'தமிழ்'
 *   book    - optional book number
 *   chapter - optional chapter number (only used if book is also given)
 *
 * Response:
 *   {book set, no chapter}    -> {"chapters": [1,2,3,...]}
 *   {book and chapter set}    -> {"verses": [1,2,...], "results": [...]}
 *   (results shape matches getRelatedDevotions()'s return value)
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/bible-reference-linker.php';
require_once __DIR__ . '/related-devotions.php';

$lang = $_GET['lang'] ?? 'English';
$book = (isset($_GET['book']) && $_GET['book'] !== '') ? (int) $_GET['book'] : null;
$chapter = (isset($_GET['chapter']) && $_GET['chapter'] !== '') ? (int) $_GET['chapter'] : null;

$chaptersDir = __DIR__ . '/index/' . $lang . '/chapters';
$response = [];

if ($book !== null && $chapter === null) {
    $chapters = [];
    if (is_dir($chaptersDir)) {
        foreach (scandir($chaptersDir) as $file) {
            if (preg_match('/^(\d+)_(\d+)\.json$/', $file, $m) && (int) $m[1] === $book) {
                $chapters[] = (int) $m[2];
            }
        }
        sort($chapters);
    }
    $response = ['chapters' => $chapters];
} elseif ($book !== null && $chapter !== null) {
    $verses = [];
    $chapterFile = $chaptersDir . '/' . $book . '_' . $chapter . '.json';
    if (file_exists($chapterFile)) {
        $chapterData = json_decode(file_get_contents($chapterFile), true);
        if (is_array($chapterData)) {
            foreach ($chapterData as $entry) {
                if (preg_match('/:(\d+)/', $entry['verse'] ?? '', $vm)) {
                    $verses[] = (int) $vm[1];
                }
            }
            sort($verses);
        }
    }

    $response = [
        'verses' => $verses,
        'results' => getRelatedDevotions($book . '_' . $chapter, $lang, '', '', ''),
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
