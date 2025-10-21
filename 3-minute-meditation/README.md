# 3-Minute Meditation

A daily Christian meditation and devotion web application designed for spiritual growth and reflection. Built with PHP and Bootstrap 5, featuring a modern, mobile-responsive interface with Progressive Web App (PWA) capabilities.

## 🌟 Features

### User Features

#### 📖 Meditation Content
- **Daily Devotions**: Structured meditation content with multiple sections:
  - Memory Verse (Scripture reference)
  - Insight & Reflection (Main devotional content)
  - Today's Quote (Inspirational quote)
  - Recommended Book (Optional book recommendation with quotes)
  - Song (Optional worship song lyrics)
  - Prayer (Prayer text)
  - A Word to You (Concluding thoughts)
  - Author Information (Contact details)

#### 🌐 Multi-Language Support
- **Multiple Languages**: Supports Tamil (தமிழ்) and English
- **Language Selector**: Easy switching between languages with dropdown
- **Session Persistence**: Selected language is remembered across sessions
- **Unicode Support**: Full UTF-8 support for Tamil and other Unicode content

#### 🎯 Reading Modes
- **Latest Mode**: Browse meditations chronologically from newest to oldest
- **Random Mode**: Shuffle through meditations in random order
- **View All**: See a complete list of all available meditations
- **Navigation**: Previous/Next buttons with counter showing current position

#### 📅 Scheduled Publishing
- **Future Scheduling**: Schedule meditations to be published on specific dates
- **Automatic Activation**: Scheduled content automatically becomes visible on the scheduled date
- **Status Indicators**: Visual badges show scheduled vs published status

#### 🔎 Zoom & Accessibility
- **Zoom Controls**: Increase or decrease text size for better readability
- **Reset Zoom**: Quick reset to default text size
- **Responsive Design**: Optimized for mobile, tablet, and desktop devices
- **Mobile-First**: Touch-friendly interface with large tap targets

#### 📱 Progressive Web App (PWA)
- **Install as App**: Install on any device like a native app
- **Offline Support**: Service worker for offline functionality
- **App Manifest**: Custom icons, splash screens, and app metadata
- **Standalone Mode**: Runs in full-screen mode without browser UI

#### 📊 Analytics
- **Google Analytics**: Built-in tracking for user insights
- **View Counter**: Track meditation views

#### 🔗 Cross-Reference System
- **Bible Verse Links**: All meditations are indexed by their key Bible verse
- **Multi-Brand Support**: Link files track meditations across different brands
- **Easy Discovery**: Find all meditations referencing a specific Bible verse

### Admin Features

#### 🔐 Secure Authentication
- **Login System**: Password-protected admin panel
- **Session Management**: 30-minute admin session timeout
- **Multiple Users**: Support for multiple admin accounts

#### ✏️ Content Management
- **Add Meditations**: Create new meditation entries with rich content
- **Edit Meditations**: Modify existing meditations
- **Delete Meditations**: Remove meditations (with confirmation)
- **Unique IDs**: Auto-generated unique identifiers for each meditation
- **Date Assignment**: Set publication dates for each meditation

#### 📖 Bible Verse Selector
- **Cascading Dropdowns**: User-friendly Bible verse selection
- **66 Books**: Complete Bible structure (Genesis to Revelation)
- **Chapter Selection**: Dynamic chapter dropdown based on selected book
- **Verse Range**: Select single verse or verse range (e.g., John 3:16 or John 3:16-17)
- **Reference Preview**: Real-time preview of selected verse reference
- **Auto-Formatting**: Automatic verse reference formatting (e.g., `43_3:16-17`)

#### 🗂️ Multi-Language Content
- **Language Selector**: Choose language when adding/editing meditations
- **Language Filters**: Filter meditations by language in admin view
- **Separate Folders**: Content organized in language-specific folders
- **Auto-Labeling**: Default labels in selected language

#### 🔍 Advanced Filtering
- **Status Filter**: Filter by Published/Scheduled status
- **Date Filter**: Filter by specific publication date
- **Title Search**: Search meditations by title
- **Clear Filters**: Quick reset of all filters

#### 📊 Content Overview
- **List View**: Table view (desktop) and card view (mobile)
- **Meditation Counter**: Display total count of meditations
- **Status Badges**: Visual indicators for scheduled content
- **Quick Actions**: Edit/Delete buttons for each meditation

#### 📝 Rich Content Sections
- **Memory Verse**: Customizable label and text
- **Devotion**: Multi-line reflection text
- **Quote**: Inspirational quote
- **Recommended Book**: 
  - Book title and author
  - Page reference (text field for ranges like "45-50")
  - Book quote excerpt
  - External link to book
- **Song**: Optional worship song lyrics
- **Prayer**: Prayer text
- **Conclusion**: Multi-line concluding thoughts
- **Author Details**:
  - Name
  - Mobile number
  - WhatsApp number (with clickable link)
  - Email address (with mailto link)

#### 🎨 Modern Admin UI
- **Bootstrap 5**: Modern, responsive admin interface
- **Modal Forms**: Clean, focused editing experience
- **Form Validation**: Required field validation
- **Date Picker**: Flatpickr for easy date selection
- **Success Messages**: Visual feedback for all actions
- **Responsive Tables**: Mobile-friendly data display

#### 🔗 Link Management
- **Auto-Generated Links**: Automatically creates verse-based cross-references
- **Deduplication**: Prevents duplicate entries in link files
- **Update on Edit**: Updates link information when meditation is edited
- **Brand Tracking**: Tracks which brand created each meditation

## 📁 Project Structure

```
3-minute-meditation/
├── a.php                      # Admin panel
├── index.php                  # Main user interface
├── counter.php                # View counter functionality
├── counter.txt                # View count storage
├── manifest.json              # PWA manifest (user)
├── manifest-a.json            # PWA manifest (admin)
├── sw.js                      # Service worker
├── README.md                  # This file
└── meditations/
    ├── English/
    │   ├── 2.json
    │   ├── 6.json
    │   ├── 7.json
    │   └── all-meditations.json
    └── தமிழ்/
        ├── 1.json
        └── all-meditations.json
```

### Shared Resources (Parent Directory)

```
../
├── css/
│   └── style.css              # Global styles
├── js/
│   ├── zoom.js                # Zoom functionality
│   ├── copy.js                # Copy to clipboard
│   ├── bible-data.js          # Bible structure data
│   └── translations.js        # Label translations
├── links/
│   ├── README.md              # Link system documentation
│   └── [verse_reference].json # Verse-based cross-references
├── pwa/
│   └── pwa.js                 # PWA installation logic
├── menu-links.php             # Navigation menu
├── footer-links.php           # Footer links
├── copyright.php              # Copyright notice
├── detect-app.php             # App detection logic
└── google-analytics.php       # Analytics tracking
```

## 🔧 Technology Stack

- **Backend**: PHP 7.4+
- **Frontend**: 
  - Bootstrap 5.3.2
  - Font Awesome 6.4.0
  - Bootstrap Icons 1.11.1
- **Date Picker**: Flatpickr
- **PWA**: Service Worker, Web App Manifest
- **Storage**: JSON files (file-based storage)
- **Analytics**: Google Analytics

## 📋 Data Structure

### Meditation JSON Format

```json
{
  "uniqueid": "20241021143022_a1b2c3d4",
  "date": "2024-10-21",
  "title": "Finding Peace in the Storm",
  "key_verse": "43_14:27",
  "scheduled": false,
  "memory_verse": {
    "label": "Memory Verse",
    "text": "Peace I leave with you; my peace I give you. - John 14:27"
  },
  "devotion": {
    "label": "Insight / Reflection",
    "text": "In times of trouble..."
  },
  "quote": {
    "label": "Today's Quote",
    "text": "Peace is not the absence of trouble..."
  },
  "recommended_book": {
    "label": "Recommended Book",
    "title": "The Power of Prayer",
    "author": "John Smith",
    "page": "45-50",
    "quote": "Prayer is the key to peace...",
    "link": "https://example.com/book"
  },
  "song": {
    "label": "Song",
    "text": "Amazing grace, how sweet the sound..."
  },
  "prayer": {
    "label": "Prayer",
    "text": "Dear Lord, grant us your peace..."
  },
  "conclusion": {
    "label": "A Word to You",
    "text": [
      "Remember, God's peace is available to you.",
      "Trust in Him today."
    ]
  },
  "author": {
    "label": "Author",
    "author": "Maria Joseph",
    "mobile": "+91 9243183231",
    "whatsapp": "+91 9243183231",
    "email": "mjosephnj@gmail.com"
  }
}
```

### Link File Format (`/links/[verse_reference].json`)

```json
[
  {
    "brand": "3-minute-meditation",
    "title": "Finding Peace in the Storm",
    "language": "English",
    "file": "/3-minute-meditation/meditations/English/7.json"
  },
  {
    "brand": "3-minute-meditation",
    "title": "புயலில் அமைதியைக் கண்டுபிடித்தல்",
    "language": "தமிழ்",
    "file": "/3-minute-meditation/meditations/தமிழ்/1.json"
  }
]
```

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone [repository-url]
   cd bible-devotions-app/3-minute-meditation
   ```

2. **Set up web server**
   - Apache/Nginx with PHP 7.4+ support
   - Ensure write permissions for `counter.txt` and `meditations/` directories

3. **Configure admin users** (in `a.php`)
   ```php
   $admin_users = [
       'username' => 'password'
   ];
   ```

4. **Create meditation folders**
   ```bash
   mkdir -p meditations/English meditations/தமிழ்
   chmod 755 meditations meditations/*
   ```

5. **Access the application**
   - User interface: `http://yoursite.com/3-minute-meditation/`
   - Admin panel: `http://yoursite.com/3-minute-meditation/a.php`

## 📱 Usage

### For Users

1. **Access the App**: Navigate to the application URL
2. **Select Language**: Click the language selector to choose your preferred language
3. **Choose Reading Mode**:
   - Click clock icon for Latest mode
   - Click shuffle icon for Random mode
   - Click list icon to View All
4. **Navigate**: Use Previous/Next buttons or select from View All list
5. **Install as App**: Click the install button to add to your home screen
6. **Adjust Zoom**: Use zoom controls for comfortable reading

### For Administrators

1. **Login**: Access `a.php` and enter credentials
2. **Add Meditation**:
   - Click "Add New" button
   - Fill in all required fields
   - Select language and date
   - Choose Bible verse using dropdowns
   - Save meditation
3. **Edit Meditation**:
   - Click edit icon next to meditation
   - Modify fields as needed
   - Save changes
4. **Filter Content**:
   - Use language filter to switch between languages
   - Use status filter for scheduled/published content
   - Use date filter or search by title
5. **Schedule Future Content**:
   - Set date in the future when adding meditation
   - Meditation will automatically appear on that date

## 🔐 Security Considerations

- Admin credentials are stored in plain text (consider using password hashing)
- Session timeout set to 30 minutes for admin
- Input sanitization with `htmlspecialchars()`
- JSON data validation before processing

## 🌍 Internationalization

The application supports multiple languages:
- Tamil (தமிழ்)
- English

To add a new language:
1. Add language to `$languages` array in `a.php` and `index.php`
2. Create new folder in `meditations/[language]/`
3. Add translations to `../js/translations.js`
4. Add language option to dropdowns

## 📊 Bible Reference Format

Bible verses are stored in the format: `[book_no]_[chapter]:[verse]-[end_verse]`

Examples:
- Single verse: `43_3:16` (John 3:16)
- Verse range: `43_3:16-17` (John 3:16-17)
- Single verse in chapter 1: `19_119:105` (Psalm 119:105)

Book numbers:
- 1 = Genesis
- 43 = John
- 66 = Revelation

See `/js/bible-data.js` for complete book structure.

## 🤝 Contributing

To contribute:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📝 Version History

- **v2025.10.6** (Current)
  - Multi-language support
  - Bible verse selector with cascading dropdowns
  - Link generation system
  - Scheduled meditation publishing
  - PWA support
  - Zoom controls
  - Admin filtering

## 👥 Authors

- **Word of God Team** - WordOfGod.in
- **Contact**: mjosephnj@gmail.com
- **Phone**: +91 9243183231

## 📜 License

Based on Matthew 10:8 - "Freely you have received; freely give."

This project is made available free of cost for spiritual edification.

## 🙏 Acknowledgments

- Bible verse data structure
- Bootstrap framework
- Font Awesome icons
- All contributors and content authors

## 📞 Support

For questions or support:
- Email: mjosephnj@gmail.com
- WhatsApp: +91 9243183231
- Website: WordOfGod.in

---

**Made with ❤️ by Word of God Team**
