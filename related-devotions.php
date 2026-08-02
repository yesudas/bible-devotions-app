<?php
/**
 * related-devotions.php
 *
 * Shared "Related Devotions" section. Include this directly in a brand's
 * index.php right after the navigation panel — it renders (or silently
 * renders nothing) using variables already in scope at that point:
 *
 *   $meditation      - the currently displayed meditation (needs 'key_verse')
 *   $selectedLanguage - current language, e.g. 'English', 'தமிழ்'
 *   $allMeditations, $currentIndex - used to exclude the current meditation
 *                      itself from its own related list
 *   createSlug()     - every brand's index.php already defines this; reused
 *                      here instead of duplicating it
 *
 * Data comes from the per-chapter cross-reference indexes built by l.php
 * (index/{language}/chapters/{book}_{chapter}.json), so it covers every
 * brand, not just the current one — a devotion on the same chapter/verse
 * from a different brand can appear here.
 *
 * Usage:
 *   <?php include_once '../related-devotions.php'; ?>
 */

if (!empty($meditation['key_verse'])) {
    $relatedDevotionsCurrentBrand = basename(dirname($_SERVER['SCRIPT_FILENAME']));
    $relatedDevotionsCurrentFilename = (isset($allMeditations, $currentIndex) && isset($allMeditations[$currentIndex]))
        ? $allMeditations[$currentIndex]['filename']
        : null;

    $relatedDevotions = getRelatedDevotions(
        $meditation['key_verse'],
        $selectedLanguage,
        $relatedDevotionsCurrentBrand,
        $relatedDevotionsCurrentFilename
    );

    if (!empty($relatedDevotions)) {
        $relatedDevotionsChapterLabel = getChapterLabel($meditation['key_verse'], $selectedLanguage);
        ?>
        <div class="section related-devotions-section">
            <h2><i class="fas fa-layer-group"></i> Other Devotions in This Chapter<?php echo $relatedDevotionsChapterLabel !== '' ? ' - ' . htmlspecialchars($relatedDevotionsChapterLabel) : ''; ?></h2>
            <div class="related-devotions-list">
                <?php foreach ($relatedDevotions as $item): ?>
                    <a class="related-devotion-item" href="<?php echo htmlspecialchars($item['url']); ?>">
                        <?php if ($item['verseLabel'] !== ''): ?>
                            <span class="related-devotion-verse"><?php echo htmlspecialchars($item['verseLabel']); ?></span>
                        <?php endif; ?>
                        <span class="related-devotion-title"><?php echo htmlspecialchars($item['title']); ?></span>
                        <span class="related-devotion-brand"><?php echo htmlspecialchars($item['brandLabel']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}

/**
 * Collects other meditations (any brand) that reference the same chapter(s)
 * as $keyVerse (e.g. "1_1:1" or "45_8:18-23;66_21:1-5" for multiple refs),
 * excluding the one currently being viewed. Returns a list of
 * ['url','title','brand','brandLabel','verseLabel','sortKey'], sorted by
 * verse number.
 */
function getRelatedDevotions($keyVerse, $language, $excludeBrand, $excludeFilename) {
    $rootDir = __DIR__;
    $results = [];
    $seen = [];

    foreach (explode(';', $keyVerse) as $segment) {
        $segment = trim($segment);
        if (!preg_match('/^(\d+)_(\d+)/', $segment, $m)) {
            continue;
        }
        $book = (int) $m[1];
        $chapter = (int) $m[2];

        $chapterFile = $rootDir . '/index/' . $language . '/chapters/' . $book . '_' . $chapter . '.json';
        if (!file_exists($chapterFile)) {
            continue;
        }

        $chapterData = json_decode(file_get_contents($chapterFile), true);
        if (!is_array($chapterData)) {
            continue;
        }

        foreach ($chapterData as $verseEntry) {
            $verseNo = null;
            if (preg_match('/:(\d+)/', $verseEntry['verse'] ?? '', $vm)) {
                $verseNo = (int) $vm[1];
            }

            foreach ($verseEntry['meditations'] ?? [] as $item) {
                $brand = $item['brand'] ?? '';
                $filename = $item['filename'] ?? '';
                if ($brand === '' || $filename === '') {
                    continue;
                }
                if ($brand === $excludeBrand && $filename === $excludeFilename) {
                    continue;
                }

                $dedupeKey = $brand . '|' . $filename;
                if (isset($seen[$dedupeKey])) {
                    continue;
                }
                $seen[$dedupeKey] = true;

                $medFile = $rootDir . '/' . $brand . '/meditations/' . $language . '/' . $filename;
                if (!file_exists($medFile)) {
                    // Index is stale relative to actual content; skip rather than link to nothing.
                    continue;
                }
                $medData = json_decode(file_get_contents($medFile), true);
                $uniqueId = $medData['uniqueid'] ?? null;
                $title = $item['title'] ?? ($medData['title'] ?? '');
                if ($uniqueId === null || $title === '') {
                    continue;
                }

                $slug = function_exists('createSlug') ? createSlug($title) : rawurlencode($title);
                $url = '../' . $brand . '/?mode=latest&id=' . urlencode($uniqueId)
                    . '&lang=' . urlencode($language) . '&title=' . urlencode($slug);

                $results[] = [
                    'url' => $url,
                    'title' => $title,
                    'brand' => $brand,
                    'brandLabel' => ucwords(str_replace(['-', '_'], ' ', $brand)),
                    'verseLabel' => $verseNo ? ('Verse ' . $verseNo) : '',
                    'sortKey' => $verseNo ?? 0,
                ];
            }
        }
    }

    usort($results, function ($a, $b) {
        return $a['sortKey'] <=> $b['sortKey'];
    });

    return $results;
}

/**
 * Builds a "Book Chapter" label (e.g. "Genesis 1", "ஆதியாகமம் 1") from the
 * first book_chapter found in a key_verse value, using the book-name table
 * from bible-reference-linker.php if it's loaded. Returns '' if it can't be
 * determined (book table not loaded, or key_verse unparseable).
 */
function getChapterLabel($keyVerse, $language) {
    if (empty($GLOBALS['bibleBookNames']) || !preg_match('/^(\d+)_(\d+)/', trim($keyVerse), $m)) {
        return '';
    }

    $bookNo = (int) $m[1];
    $chapter = (int) $m[2];

    $names = $GLOBALS['bibleBookNames'][$language] ?? $GLOBALS['bibleBookNames']['English'] ?? [];
    $bookName = $names[$bookNo][0] ?? $GLOBALS['bibleBookNames']['English'][$bookNo][0] ?? null;

    return $bookName !== null ? ($bookName . ' ' . $chapter) : '';
}
