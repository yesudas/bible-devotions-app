# Bible Verse Links

This folder contains JSON files that map Bible verses to meditation articles across different brands and languages. The system is organized by language for better scalability and performance.

## Directory Structure

```
links/
├── migrate-links.php          # Migration script (run once)
├── README.md                  # This file
├── English/
│   ├── verses/                # Verse-level links
│   │   ├── 1_1:1.json        # Genesis 1:1
│   │   ├── 43_3:16.json      # John 3:16
│   │   └── ...
│   └── chapters/              # Chapter-level index
│       ├── 1_1.json          # All verses in Genesis 1
│       ├── 43_3.json         # All verses in John 3
│       └── ...
└── தமிழ்/
    ├── verses/                # Tamil verse-level links
    │   └── ...
    └── chapters/              # Tamil chapter-level index
        └── ...
```

## File Naming Convention

### Verse Files
Located in `[language]/verses/`

Format: `[book_no]_[chapter_no]:[starting_verse_no]-[ending_verse_no].json`

Examples:
- `1_1:1.json` - Genesis 1:1
- `43_3:16.json` - John 3:16
- `43_3:16-17.json` - John 3:16-17
- `19_23:1.json` - Psalm 23:1

### Chapter Files
Located in `[language]/chapters/`

Format: `[book_no]_[chapter_no].json`

Examples:
- `1_1.json` - All verses in Genesis chapter 1
- `43_3.json` - All verses in John chapter 3
- `19_23.json` - All verses in Psalm 23

## File Structure

### Verse-Level Files (`verses/`)

Each verse file contains an array of meditations that reference that specific verse:

```json
[
  {
    "brand": "3-minute-meditation",
    "title": "Walking by Faith",
    "file": "/3-minute-meditation/meditations/English/1.json"
  },
  {
    "brand": "அனுதின-மன்னா",
    "title": "Finding Peace",
    "file": "/அனுதின-மன்னா/meditations/English/5.json"
  }
]
```

**Note**: The `language` attribute is **not stored** in verse files since it's implied by the folder structure, saving space and improving efficiency.

### Chapter-Level Files (`chapters/`)

Each chapter file contains an index of all verses referenced in that chapter, sorted by verse number:

```json
[
  {
    "brand": "3-minute-meditation",
    "title": "Walking by Faith",
    "verse": "43_3:16",
    "file": "/3-minute-meditation/meditations/English/1.json"
  },
  {
    "brand": "அனுதின-மன்னா",
    "title": "God's Love",
    "verse": "43_3:16-17",
    "file": "/அனுதின-மன்னா/meditations/தமிழ்/2.json"
  },
  {
    "brand": "3-minute-meditation",
    "title": "Eternal Life",
    "verse": "43_3:36",
    "file": "/3-minute-meditation/meditations/English/3.json"
  }
]
```

## Fields

### Verse-Level Links
- **brand**: The meditation brand/series name
  - `3-minute-meditation` - Quick daily meditations
  - `அனுதின-மன்னா` - Tamil daily devotions
  - (More brands can be added)

- **title**: The title of the meditation article

- **file**: The relative path to the meditation JSON file from the root

### Chapter-Level Links
All fields from verse-level, plus:
- **verse**: The specific verse reference (e.g., `43_3:16`)

## Purpose

This two-tier structure enables:

### Verse-Level Benefits
1. ✅ Quick lookup of all meditations for a specific verse
2. ✅ Cross-brand verse references
3. ✅ Language-separated for better organization
4. ✅ Smaller file sizes (no duplicate language info)
5. ✅ Easy to find related content

### Chapter-Level Benefits
1. ✅ Browse all meditations in a chapter at once
2. ✅ Sorted by verse order for sequential reading
3. ✅ Discover related verses in context
4. ✅ Chapter-based navigation and exploration
5. ✅ Better for building chapter summaries

### Overall Benefits
- 📦 Reduced file sizes (removed redundant language attribute)
- 🚀 Faster lookups (language-specific folders)
- 📊 Better scalability for multiple languages
- 🔍 Two levels of discovery (verse and chapter)
- 🌐 Language-isolated for parallel processing

## Auto-Generation

These files are **automatically generated and updated** when meditations are added or edited through the Admin Panel (`a.php`).

### What Happens on Save
1. ✅ Creates verse-level link in `[language]/verses/[verse].json`
2. ✅ Updates chapter-level index in `[language]/chapters/[chapter].json`
3. ✅ Removes duplicates (by file path)
4. ✅ Sorts chapter links by verse reference
5. ✅ Preserves existing links from other files

## Migration

If you have old links in the root `/links/` directory, run the migration script:

```bash
cd links
php migrate-links.php
```

This will:
- ✅ Backup old files to `backup_[timestamp]/`
- ✅ Create new language-specific folders
- ✅ Generate verse-level files (without language attribute)
- ✅ Generate chapter-level index files
- ✅ Delete old files after successful migration
- ✅ Provide detailed migration report

## Bible Book Numbers

For reference, the book numbering system:

### Old Testament (1-39)
- 1 = Genesis
- 19 = Psalms
- 20 = Proverbs
- 23 = Isaiah

### New Testament (40-66)
- 40 = Matthew
- 43 = John
- 45 = Romans
- 66 = Revelation

See `../js/bible-data.js` for the complete list.

## Usage Examples

### Find all meditations for John 3:16
```php
$verse = '43_3:16';
$language = 'English';
$file = "links/{$language}/verses/{$verse}.json";
$links = json_decode(file_get_contents($file), true);
```

### Browse all meditations in John chapter 3
```php
$chapter = '43_3';
$language = 'தமிழ்';
$file = "links/{$language}/chapters/{$chapter}.json";
$chapter_links = json_decode(file_get_contents($file), true);
// Returns array sorted by verse number
```

### Get specific verse from chapter index
```php
$chapter_links = json_decode(file_get_contents($file), true);
$verse_links = array_filter($chapter_links, function($link) {
    return $link['verse'] === '43_3:16';
});
```

## Performance Notes

- Language-specific folders enable parallel processing
- Smaller JSON files load faster
- Chapter files enable efficient range queries
- File-based storage scales well for thousands of verses

---

**Last Updated**: October 22, 2025  
**Version**: 2.0 (Language-separated with chapter index)
