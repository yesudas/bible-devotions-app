<?php
/**
 * devotion-filter.php
 *
 * Shared filter bar: Book / Chapter / Verse, cascading and populated only
 * with values this brand's own content actually has. Two usage modes,
 * controlled by $devotionFilterMode before including this file:
 *
 * - 'list' (default): for the "All Meditations" list. Book/Chapter/Verse
 *   filter the already-rendered `.meditation-item` elements client-side
 *   (show/hide, no page reload). Each `.meditation-item` must carry
 *   data-book/data-chapter/data-verse attributes.
 *
 * - 'navigate': for the single-devotion reading page, where there's no list
 *   to filter. As Book/Chapter/Verse are picked, a live results list of
 *   matching devotions (sorted by chapter then verse) appears below the
 *   filter bar, each linking straight to that devotion.
 *
 * Expects in scope:
 *   $allMeditations   - array of meditation summaries for the current
 *                       language, each with 'uniqueid', 'title', and an
 *                       optional 'key_verse'
 *   $selectedLanguage - current language, e.g. 'English', 'தமிழ்'
 *   $GLOBALS['bibleBookNames'] - from bible-reference-linker.php (must be
 *                       included earlier in the page) for book display
 *                       names; falls back to "Book {n}" if not loaded
 *   createSlug()      - every brand's index.php already defines this; reused
 *                       here for building target URLs
 *
 * Usage (list mode, on the All Meditations page):
 *   <?php include_once '../devotion-filter.php'; ?>
 *   <?php foreach ($allMeditations as $idx => $med): ?>
 *       <div class="meditation-item" data-book="..." data-chapter="..." data-verse="...">
 *       ...
 *
 * Usage (navigate mode, on the reading page):
 *   <?php $devotionFilterMode = 'navigate'; include_once '../devotion-filter.php'; ?>
 */

$devotionFilterMode = $devotionFilterMode ?? 'list';

$devotionFilterBooks = [];   // bookNo => ['name' => string, 'chapters' => [chapterNo => [verseNo, ...]]]
$devotionFilterEntries = []; // flat list: [id, title, slug, book, chapter, verse]

foreach ($allMeditations as $med) {
    if (empty($med['key_verse'])) {
        continue;
    }
    $firstParsed = null;

    foreach (explode(';', $med['key_verse']) as $segment) {
        $segment = trim($segment);
        if (!preg_match('/^(\d+)_(\d+)(?::(\d+))?/', $segment, $m)) {
            continue;
        }
        $bookNo = (int) $m[1];
        $chapter = (int) $m[2];
        $verse = isset($m[3]) && $m[3] !== '' ? (int) $m[3] : null;

        if ($firstParsed === null) {
            $firstParsed = ['book' => $bookNo, 'chapter' => $chapter, 'verse' => $verse];
        }

        if (!isset($devotionFilterBooks[$bookNo])) {
            $bookName = $GLOBALS['bibleBookNames'][$selectedLanguage][$bookNo][0]
                ?? $GLOBALS['bibleBookNames']['English'][$bookNo][0]
                ?? ('Book ' . $bookNo);
            $devotionFilterBooks[$bookNo] = ['name' => $bookName, 'chapters' => []];
        }
        if (!isset($devotionFilterBooks[$bookNo]['chapters'][$chapter])) {
            $devotionFilterBooks[$bookNo]['chapters'][$chapter] = [];
        }
        if ($verse !== null && !in_array($verse, $devotionFilterBooks[$bookNo]['chapters'][$chapter], true)) {
            $devotionFilterBooks[$bookNo]['chapters'][$chapter][] = $verse;
        }
    }

    if ($firstParsed !== null && !empty($med['uniqueid']) && !empty($med['title'])) {
        $devotionFilterEntries[] = [
            'id' => $med['uniqueid'],
            'title' => $med['title'],
            'slug' => function_exists('createSlug') ? createSlug($med['title']) : rawurlencode($med['title']),
            'book' => $firstParsed['book'],
            'chapter' => $firstParsed['chapter'],
            'verse' => $firstParsed['verse'],
        ];
    }
}

ksort($devotionFilterBooks);
foreach ($devotionFilterBooks as &$devotionFilterBook) {
    ksort($devotionFilterBook['chapters']);
    foreach ($devotionFilterBook['chapters'] as &$devotionFilterVerses) {
        sort($devotionFilterVerses);
    }
    unset($devotionFilterVerses);
}
unset($devotionFilterBook);
?>
<?php if (!empty($devotionFilterBooks)): ?>
<?php if ($devotionFilterMode === 'navigate'): ?>
<button type="button" id="devotionFilterToggle" class="devotion-filter-toggle" aria-expanded="false">
    <i class="fas fa-filter"></i> Filter by Book / Chapter / Verse
</button>
<?php endif; ?>
<div id="devotionFilterPanel" class="devotion-filter-panel<?php echo $devotionFilterMode === 'navigate' ? ' devotion-filter-panel-collapsed' : ''; ?>">
    <div class="devotion-filter-bar">
        <div class="devotion-filter-field">
            <label for="devotionFilterBook">Book</label>
            <select id="devotionFilterBook">
                <option value="">All Books</option>
                <?php foreach ($devotionFilterBooks as $bookNo => $book): ?>
                    <option value="<?php echo (int) $bookNo; ?>"><?php echo htmlspecialchars($book['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="devotion-filter-field">
            <label for="devotionFilterChapter">Chapter</label>
            <select id="devotionFilterChapter" disabled>
                <option value="">All Chapters</option>
            </select>
        </div>
        <div class="devotion-filter-field">
            <label for="devotionFilterVerse">Verse</label>
            <select id="devotionFilterVerse" disabled>
                <option value="">All Verses</option>
            </select>
        </div>
        <button type="button" id="devotionFilterClear" class="devotion-filter-clear">
            <i class="fas fa-times"></i> Clear
        </button>
    </div>
    <p class="devotion-filter-status" id="devotionFilterStatus"></p>
    <?php if ($devotionFilterMode === 'navigate'): ?>
    <div class="related-devotions-list" id="devotionFilterResults"></div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var mode = <?php echo json_encode($devotionFilterMode); ?>;
    var filterData = <?php echo json_encode($devotionFilterBooks, JSON_UNESCAPED_UNICODE); ?>;
    var filterEntries = <?php echo json_encode($devotionFilterEntries, JSON_UNESCAPED_UNICODE); ?>;
    var currentLanguage = <?php echo json_encode($selectedLanguage, JSON_UNESCAPED_UNICODE); ?>;

    var bookSelect = document.getElementById('devotionFilterBook');
    var chapterSelect = document.getElementById('devotionFilterChapter');
    var verseSelect = document.getElementById('devotionFilterVerse');
    var clearBtn = document.getElementById('devotionFilterClear');
    var statusEl = document.getElementById('devotionFilterStatus');
    var resultsEl = document.getElementById('devotionFilterResults');
    var toggleBtn = document.getElementById('devotionFilterToggle');
    var panelEl = document.getElementById('devotionFilterPanel');
    var items = mode === 'list' ? document.querySelectorAll('.meditation-item') : [];

    if (toggleBtn && panelEl) {
        toggleBtn.addEventListener('click', function() {
            var collapsed = panelEl.classList.toggle('devotion-filter-panel-collapsed');
            toggleBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        });
    }

    function matchingEntries() {
        var book = bookSelect.value;
        var chapter = chapterSelect.value;
        var verse = verseSelect.value;

        return filterEntries.filter(function(entry) {
            if (book && String(entry.book) !== book) return false;
            if (chapter && String(entry.chapter) !== chapter) return false;
            if (verse && String(entry.verse) !== verse) return false;
            return true;
        });
    }

    function entryUrl(entry) {
        return '?mode=latest&id=' + encodeURIComponent(entry.id) +
            '&lang=' + encodeURIComponent(currentLanguage) +
            '&title=' + encodeURIComponent(entry.slug);
    }

    function referenceLabel(entry) {
        var bookName = filterData[entry.book] ? filterData[entry.book].name : ('Book ' + entry.book);
        return bookName + ' ' + entry.chapter + (entry.verse ? ':' + entry.verse : '');
    }

    function populateChapters(bookNo) {
        chapterSelect.innerHTML = '<option value="">All Chapters</option>';
        if (!bookNo || !filterData[bookNo]) {
            chapterSelect.disabled = true;
            return;
        }
        chapterSelect.disabled = false;
        Object.keys(filterData[bookNo].chapters).forEach(function(ch) {
            var opt = document.createElement('option');
            opt.value = ch;
            opt.textContent = 'Chapter ' + ch;
            chapterSelect.appendChild(opt);
        });
    }

    function populateVerses(bookNo, chapter) {
        verseSelect.innerHTML = '<option value="">All Verses</option>';
        var verses = bookNo && chapter && filterData[bookNo] ? filterData[bookNo].chapters[chapter] : null;
        if (!verses || !verses.length) {
            verseSelect.disabled = true;
            return;
        }
        verseSelect.disabled = false;
        verses.forEach(function(v) {
            var opt = document.createElement('option');
            opt.value = v;
            opt.textContent = 'Verse ' + v;
            verseSelect.appendChild(opt);
        });
    }

    function renderResults() {
        var matches = matchingEntries().slice().sort(function(a, b) {
            if (a.chapter !== b.chapter) return a.chapter - b.chapter;
            return (a.verse || 0) - (b.verse || 0);
        });
        var book = bookSelect.value, chapter = chapterSelect.value, verse = verseSelect.value;
        var hasSelection = book || chapter || verse;

        if (!hasSelection) {
            resultsEl.innerHTML = '';
            statusEl.textContent = 'Choose a book (and optionally chapter/verse) to see devotions.';
            return;
        }

        statusEl.textContent = matches.length + ' devotion' + (matches.length === 1 ? '' : 's') + ' found';

        resultsEl.innerHTML = '';
        matches.forEach(function(entry) {
            var a = document.createElement('a');
            a.className = 'related-devotion-item';
            a.href = entryUrl(entry);

            var verseSpan = document.createElement('span');
            verseSpan.className = 'related-devotion-verse';
            verseSpan.textContent = referenceLabel(entry);
            a.appendChild(verseSpan);

            var titleSpan = document.createElement('span');
            titleSpan.className = 'related-devotion-title';
            titleSpan.textContent = entry.title;
            a.appendChild(titleSpan);

            resultsEl.appendChild(a);
        });
    }

    function applyListFilter() {
        var book = bookSelect.value;
        var chapter = chapterSelect.value;
        var verse = verseSelect.value;
        var isFiltered = book || chapter || verse;
        var matched = [];

        items.forEach(function(item) {
            var match = true;
            if (book && item.dataset.book !== book) match = false;
            if (match && chapter && item.dataset.chapter !== chapter) match = false;
            if (match && verse && item.dataset.verse !== verse) match = false;
            item.style.display = match ? '' : 'none';
            if (match) matched.push(item);
        });

        if (isFiltered && book) {
            matched.sort(function(a, b) {
                var chA = parseInt(a.dataset.chapter, 10) || 0;
                var chB = parseInt(b.dataset.chapter, 10) || 0;
                if (chA !== chB) return chA - chB;
                var vA = parseInt(a.dataset.verse, 10) || 0;
                var vB = parseInt(b.dataset.verse, 10) || 0;
                return vA - vB;
            });
            matched.forEach(function(item) {
                item.parentNode.appendChild(item);
            });
        }

        statusEl.textContent = isFiltered ? ('Showing ' + matched.length + ' of ' + items.length + ' meditations') : '';
    }

    function refresh() {
        if (mode === 'list') {
            applyListFilter();
        } else {
            renderResults();
        }
    }

    bookSelect.addEventListener('change', function() {
        populateChapters(this.value);
        populateVerses('', '');
        refresh();
    });
    chapterSelect.addEventListener('change', function() {
        populateVerses(bookSelect.value, this.value);
        refresh();
    });
    verseSelect.addEventListener('change', refresh);

    clearBtn.addEventListener('click', function() {
        bookSelect.value = '';
        chapterSelect.innerHTML = '<option value="">All Chapters</option>';
        chapterSelect.disabled = true;
        verseSelect.innerHTML = '<option value="">All Verses</option>';
        verseSelect.disabled = true;
        statusEl.textContent = '';
        refresh();
    });

    if (mode === 'list') applyListFilter();
});
</script>
<?php endif; ?>
