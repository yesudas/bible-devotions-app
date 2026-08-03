<?php
/**
 * bible-browser.php
 *
 * Cross-brand "browse by Bible reference" filter for the root landing page.
 * Lets a visitor pick Language -> Book -> Chapter -> Verse and see every
 * devotion, across all brands, that matches. Entirely client-side after the
 * initial page load (Chapter/Verse options and results are fetched from
 * bible-browser-data.php via fetch()) - there is no page reload, so there is
 * nothing to reset scroll position.
 *
 * Expects in scope:
 *   $devotionsData - decoded data/devotions.json (for the language list)
 * Requires bible-reference-linker.php (book names) to be included earlier.
 *
 * Usage:
 *   <?php include 'bible-browser.php'; ?>
 */

$browserLanguages = array_keys($devotionsData['devotions'] ?? []);

// Book list per language, embedded upfront (cheap - filename scans only,
// no file contents read). Chapters/verses/results are fetched on demand.
$browserBooksByLanguage = [];
foreach ($browserLanguages as $lang) {
    $chaptersDir = __DIR__ . '/index/' . $lang . '/chapters';
    $bookNumbers = [];
    if (is_dir($chaptersDir)) {
        foreach (scandir($chaptersDir) as $file) {
            if (preg_match('/^(\d+)_(\d+)\.json$/', $file, $m)) {
                $bookNumbers[(int) $m[1]] = true;
            }
        }
    }
    ksort($bookNumbers);

    $books = [];
    foreach (array_keys($bookNumbers) as $bookNo) {
        $books[$bookNo] = $GLOBALS['bibleBookNames'][$lang][$bookNo][0]
            ?? $GLOBALS['bibleBookNames']['English'][$bookNo][0]
            ?? ('Book ' . $bookNo);
    }
    $browserBooksByLanguage[$lang] = $books;
}

$browserDefaultLang = $browserLanguages[0] ?? 'English';
?>
<div class="devotion-filter-bar" id="bibleBrowserBar">
    <div class="devotion-filter-field">
        <label for="browseLang">Language</label>
        <select id="browseLang">
            <?php foreach ($browserLanguages as $lang): ?>
                <option value="<?php echo htmlspecialchars($lang); ?>"><?php echo htmlspecialchars($lang); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="devotion-filter-field">
        <label for="browseBook">Book</label>
        <select id="browseBook">
            <option value="">All Books</option>
        </select>
    </div>
    <div class="devotion-filter-field">
        <label for="browseChapter">Chapter</label>
        <select id="browseChapter" disabled>
            <option value="">All Chapters</option>
        </select>
    </div>
    <div class="devotion-filter-field">
        <label for="browseVerse">Verse</label>
        <select id="browseVerse" disabled>
            <option value="">All Verses</option>
        </select>
    </div>
    <button type="button" id="browseClear" class="devotion-filter-clear">
        <i class="bi bi-x-lg"></i> Clear
    </button>
</div>
<p class="devotion-filter-status" id="browseStatus">Choose a book to see devotions.</p>
<div class="related-devotions-list mb-5" id="browseResults"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var booksByLanguage = <?php echo json_encode($browserBooksByLanguage, JSON_UNESCAPED_UNICODE); ?>;
    var defaultLang = <?php echo json_encode($browserDefaultLang, JSON_UNESCAPED_UNICODE); ?>;

    var langSelect = document.getElementById('browseLang');
    var bookSelect = document.getElementById('browseBook');
    var chapterSelect = document.getElementById('browseChapter');
    var verseSelect = document.getElementById('browseVerse');
    var clearBtn = document.getElementById('browseClear');
    var statusEl = document.getElementById('browseStatus');
    var resultsEl = document.getElementById('browseResults');

    var currentResults = [];

    langSelect.value = defaultLang;

    function populateBooks() {
        var books = booksByLanguage[langSelect.value] || {};
        bookSelect.innerHTML = '<option value="">All Books</option>';
        Object.keys(books).forEach(function(bookNo) {
            var opt = document.createElement('option');
            opt.value = bookNo;
            opt.textContent = books[bookNo];
            bookSelect.appendChild(opt);
        });
    }

    function resetChapters() {
        chapterSelect.innerHTML = '<option value="">All Chapters</option>';
        chapterSelect.disabled = true;
    }

    function resetVerses() {
        verseSelect.innerHTML = '<option value="">All Verses</option>';
        verseSelect.disabled = true;
    }

    function renderResults(results) {
        currentResults = results;
        resultsEl.innerHTML = '';
        results.forEach(function(item) {
            var a = document.createElement('a');
            a.className = 'related-devotion-item';
            a.href = item.url;

            if (item.verseLabel) {
                var verseSpan = document.createElement('span');
                verseSpan.className = 'related-devotion-verse';
                verseSpan.textContent = item.verseLabel;
                a.appendChild(verseSpan);
            }

            var titleSpan = document.createElement('span');
            titleSpan.className = 'related-devotion-title';
            titleSpan.textContent = item.title;
            a.appendChild(titleSpan);

            var brandSpan = document.createElement('span');
            brandSpan.className = 'related-devotion-brand';
            brandSpan.textContent = item.brandLabel;
            a.appendChild(brandSpan);

            resultsEl.appendChild(a);
        });
    }

    function applyVerseFilter() {
        var verse = verseSelect.value;
        var filtered = verse
            ? currentResults.filter(function(r) { return String(r.sortKey) === verse; })
            : currentResults;
        statusEl.textContent = filtered.length + ' devotion' + (filtered.length === 1 ? '' : 's') + ' found across all brands';
        renderResults(filtered);
    }

    function loadChapters() {
        resetChapters();
        resetVerses();
        resultsEl.innerHTML = '';
        if (!bookSelect.value) {
            statusEl.textContent = 'Choose a book to see devotions.';
            return;
        }
        statusEl.textContent = 'Loading chapters...';
        fetch('bible-browser-data.php?lang=' + encodeURIComponent(langSelect.value) + '&book=' + encodeURIComponent(bookSelect.value))
            .then(function(res) { return res.json(); })
            .then(function(data) {
                chapterSelect.disabled = false;
                (data.chapters || []).forEach(function(ch) {
                    var opt = document.createElement('option');
                    opt.value = ch;
                    opt.textContent = 'Chapter ' + ch;
                    chapterSelect.appendChild(opt);
                });
                statusEl.textContent = 'Choose a chapter to see devotions.';
            });
    }

    function loadResults() {
        resetVerses();
        resultsEl.innerHTML = '';
        if (!chapterSelect.value) {
            statusEl.textContent = 'Choose a chapter to see devotions.';
            return;
        }
        statusEl.textContent = 'Loading...';
        fetch('bible-browser-data.php?lang=' + encodeURIComponent(langSelect.value) + '&book=' + encodeURIComponent(bookSelect.value) + '&chapter=' + encodeURIComponent(chapterSelect.value))
            .then(function(res) { return res.json(); })
            .then(function(data) {
                verseSelect.disabled = false;
                (data.verses || []).forEach(function(v) {
                    var opt = document.createElement('option');
                    opt.value = v;
                    opt.textContent = 'Verse ' + v;
                    verseSelect.appendChild(opt);
                });
                currentResults = data.results || [];
                applyVerseFilter();
            });
    }

    langSelect.addEventListener('change', function() {
        populateBooks();
        resetChapters();
        resetVerses();
        resultsEl.innerHTML = '';
        statusEl.textContent = 'Choose a book to see devotions.';
    });

    bookSelect.addEventListener('change', loadChapters);
    chapterSelect.addEventListener('change', loadResults);
    verseSelect.addEventListener('change', applyVerseFilter);

    clearBtn.addEventListener('click', function() {
        bookSelect.value = '';
        resetChapters();
        resetVerses();
        resultsEl.innerHTML = '';
        statusEl.textContent = 'Choose a book to see devotions.';
    });

    populateBooks();
});
</script>
