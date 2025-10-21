# Bible Verse Links

This folder contains JSON files that map Bible verses to meditation articles across different brands and languages.

## File Naming Convention

Each file is named after a Bible verse reference in the format: `[book_no]_[chapter_no]:[starting_verse_no]-[ending_verse_no].json`

Examples:
- `47_3:5.json` - 2 Corinthians 3:5
- `12_1:2-3.json` - 2 Kings 1:2-3
- `1_1:1.json` - Genesis 1:1

## File Structure

Each JSON file contains an array of meditation links for that specific verse. Each link object has the following structure:

```json
[
  {
    "brand": "3-minute-meditation",
    "title": "Walking by Faith",
    "language": "English",
    "file": "/3-minute-meditation/meditations/English/1.json"
  }
]
```

## Fields

- **brand**: The meditation brand/series name
  - `3-minute-meditation` - English brand
  - (More brands can be added in the future)

- **title**: The title of the meditation article

- **language**: The language of the content
  - `English`
  - `தமிழ்`
  - (More languages can be added)

- **file**: The relative path to the meditation JSON file from the root

## Purpose

This structure allows:
1. Cross-referencing meditations by Bible verse
2. Finding all meditations across brands/languages for a specific verse
3. Building a Bible verse index
4. Creating a verse-based navigation system
5. Linking related content across different languages and brands

## Auto-Generation

These files are automatically generated and updated when meditations are added or edited through the Admin Panel (a.php).
