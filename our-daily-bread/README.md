# Our Daily Bread (Daily Manna)

A Tamil-focused daily Christian meditation and devotion web application designed for spiritual growth and reflection. Built with PHP and Bootstrap 5, featuring a modern, mobile-responsive interface with Progressive Web App (PWA) capabilities.

## 🌟 Features

### User Features

#### 📖 Meditation Content
- **Daily Devotions**: Structured meditation content with focused sections:
  - Memory Verse (Scripture reference)
  - Devotion (Main devotional content)
  - Song (Optional worship song lyrics)
  - Prayer (Prayer text)
  - Author Information (Contact details)

#### 🌐 Multi-Language Support
- **Multiple Languages**: Supports Tamil (தமிழ்) and English
- **Language Selector**: Easy switching between languages with dropdown
- **Session Persistence**: Selected language is remembered across sessions
- **Unicode Support**: Full UTF-8 support for Tamil and other Unicode content
- **Tamil-First**: Optimized for Tamil language devotional content

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
- **Add Meditations**: Create new meditation entries with streamlined content
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

#### 📝 Streamlined Content Sections
- **Memory Verse**: Customizable label and text
- **Devotion**: Multi-line reflection text
- **Song**: Optional worship song lyrics
- **Prayer**: Prayer text
- **Author Details**:
  - Name
  - Mobile number
  - WhatsApp number (with clickable link)
  - Email address (with mailto link)

> **Note**: Unlike the 3-minute-meditation brand, Our-Daily-Bread focuses on core devotional content and does not include Quote, Recommended Book, or Conclusion sections for a more streamlined experience.

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
Our-Daily-Bread/
├── a.php                      # Admin panel
├── index.php                  # Main user interface
├── counter.php                # View counter functionality
├── counter.txt                # View count storage
├── manifest.json              # PWA manifest (user)
├── manifest-a.json            # PWA manifest (admin)
├── sw.js                      # Service worker
├── README.md                  # This file
├── js/
│   └── translations.js        # Tamil/English label translations
└── meditations/
    ├── English/
    │   └── all-meditations.json
    └── தமிழ்/
        ├── 1.json
        ├── 2.json
        ├── 3.json
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
  - Bootstrap 5.3.0
  - Bootstrap Icons 1.10.0
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
  "title": "புயலில் அமைதி காண்பது",
  "key_verse": "43_14:27",
  "scheduled": false,
  "memory_verse": {
    "label": "மனப்பாட வசனம்",
    "text": "என்னுடைய சமாதானத்தை உங்களுக்குக் கொடுக்கிறேன்..."
  },
  "devotion": {
    "label": "தியானம்",
    "text": "கர்த்தர் நமக்கு அமைதியை அளிக்கிறார்..."
  },
  "song": {
    "label": "பாடல்",
    "text": "என் ஆத்துமா கர்த்தரை காத்திருக்கும்..."
  },
  "prayer": {
    "label": "ஜெபம்",
    "text": "பரலோக பிதாவே, எங்களுக்கு அமைதியை தாரும்..."
  },
  "author": {
    "label": "ஆசிரியர்",
    "author": "Gladys Sugandhi Hazlitt",
    "mobile": "",
    "whatsapp": "919243183231",
    "email": ""
  }
}
```

### All Meditations Index Format

```json
[
  {
    "uniqueid": "20241021143022_a1b2c3d4",
    "filename": "1.json",
    "title": "புயலில் அமைதி காண்பது",
    "date": "2024-10-21",
    "scheduled": false
  },
  {
    "uniqueid": "20241020120000_b2c3d4e5",
    "filename": "2.json",
    "title": "கர்த்தரின் வழிநடத்துதல்",
    "date": "2024-10-22",
    "scheduled": true
  }
]
```

### Cross-Reference Link Format

Located in `../links/[verse_reference].json`:

```json
[
  {
    "brand": "Our-Daily-Bread",
    "title": "புயலில் அமைதி காண்பது",
    "language": "தமிழ்",
    "file": "/Our-Daily-Bread/meditations/தமிழ்/1.json"
  },
  {
    "brand": "3-minute-meditation",
    "title": "Finding Peace",
    "language": "English",
    "file": "/3-minute-meditation/meditations/English/5.json"
  }
]
```

## 🚀 Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- Write permissions for meditation and counter directories

### Quick Start

1. **Clone or download the repository**

2. **Set up directory permissions**
   ```bash
   chmod 755 meditations/
   chmod 755 meditations/தமிழ்/
   chmod 755 meditations/English/
   chmod 644 counter.txt
   ```

3. **Start the development server**
   ```bash
   # From the project root
   php -S localhost:8000
   ```

4. **Access the application**
   - User Interface: `http://localhost:8000/Our-Daily-Bread/`
   - Admin Panel: `http://localhost:8000/Our-Daily-Bread/a.php`

### Admin Credentials

Default admin accounts (configured in `a.php`):
```php
$admin_users = [
    'mariajoseph' => 'maria83231',
    'yesudas' => 'yesu32425'
];
```

> **Security Note**: Change these credentials before deploying to production!

## 📱 PWA Installation

### On Mobile Devices

1. Open the application in your mobile browser
2. Look for the "Install" or "Add to Home Screen" prompt
3. Follow the browser-specific installation steps
4. The app icon will appear on your home screen

### On Desktop

1. Open the application in Chrome, Edge, or other PWA-compatible browser
2. Look for the install icon in the address bar
3. Click "Install" when prompted
4. The app will open in a standalone window

## 🎯 Usage Guide

### For Readers

#### Browsing Meditations

1. **View Latest**: Default view shows the most recent meditation
2. **Navigate**: Use Previous/Next buttons to move between meditations
3. **Change Language**: Select your preferred language from the dropdown
4. **Adjust Text Size**: Use zoom controls (+/-) for comfortable reading
5. **Random Mode**: Click "Random" to shuffle through meditations
6. **View All**: Click "All" to see the complete list

#### Contacting Authors

- Click on the WhatsApp number to open a chat
- Click on the email address to compose an email

### For Administrators

#### Adding a New Meditation

1. Log in to the admin panel
2. Click "Add New" button
3. Fill in the required fields:
   - Date (defaults to today)
   - Language (Tamil or English)
   - Title
   - Key Verse (use the Bible selector)
   - Memory Verse text
   - Devotion text
   - Prayer text
   - Author information
4. Optionally add:
   - Song lyrics
   - Custom labels for each section
5. Click "Save"

#### Editing a Meditation

1. Find the meditation in the list
2. Click the "Edit" button
3. Modify the fields as needed
4. Click "Update"

#### Scheduling Future Content

1. When adding or editing a meditation
2. Set the date to a future date
3. The meditation will automatically be marked as "Scheduled"
4. It will become visible on the scheduled date

#### Filtering and Search

- **By Status**: Filter scheduled or published meditations
- **By Date**: Filter meditations for a specific date
- **By Title**: Search for meditations by title
- **Clear All**: Reset all filters at once

## 🔗 Bible Reference System

### How It Works

1. When you save a meditation with a key verse (e.g., John 14:27)
2. The system creates a formatted reference: `43_14:27`
3. A link file is created/updated in `../links/43_14:27.json`
4. The file contains all meditations referencing that verse
5. This enables cross-brand verse lookups

### Verse Reference Format

- Book Number (1-66) + Chapter + Verse(s)
- Examples:
  - `1_1:1` = Genesis 1:1
  - `43_3:16` = John 3:16
  - `43_3:16-17` = John 3:16-17

## 🌍 Multi-Language Support

### Supported Languages

- **தமிழ்** (Tamil) - Primary language
- **English** - Secondary language

### Language-Specific Features

- Separate meditation folders per language
- Auto-populated default labels based on selected language
- Language filter in admin panel
- Session-based language persistence

### Default Labels by Language

**Tamil (தமிழ்)**:
- மனப்பாட வசனம் (Memory Verse)
- தியானம் (Devotion)
- பாடல் (Song)
- ஜெபம் (Prayer)
- ஆசிரியர் (Author)

**English**:
- Memory Verse
- Insight / Reflection
- Song
- Prayer
- Author

## 🔒 Security Features

- Password-protected admin panel
- Session timeout (30 minutes of inactivity)
- No SQL injection (uses file-based storage)
- Input sanitization with `htmlspecialchars()`
- File path validation

## 📊 Analytics & Tracking

### View Counter

- Tracks total meditation views
- Stored in `counter.txt`
- Incremented on each page load
- Displayed in the user interface

### Google Analytics

- Integrated Google Analytics tracking
- Tracks page views, user interactions
- Configure your tracking ID in `google-analytics.php`

## 🎨 Customization

### Changing Brands/Theme

The application uses CSS variables for easy theming. Edit `../css/style.css`:

```css
:root {
    --primary-color: #9657de;
    --secondary-color: #6c757d;
    /* Add more variables as needed */
}
```

### Adding New Languages

1. Add the language to the `$languages` array in `a.php`
2. Create a new folder in `meditations/[language_name]/`
3. Add translations in `js/translations.js`
4. Update default label logic in the PHP save functions

### Custom Labels

Each meditation section supports custom labels. When adding/editing:
- Leave label fields empty for default language labels
- Or enter custom labels for personalized section headers

## 🐛 Troubleshooting

### Common Issues

**Problem**: Admin panel not accessible
- **Solution**: Check that `$is_logged_in` is properly set (currently hardcoded to `true` for development)

**Problem**: Meditations not saving
- **Solution**: Check file permissions on `meditations/` directory

**Problem**: View counter not updating
- **Solution**: Ensure `counter.txt` has write permissions

**Problem**: PWA not installing
- **Solution**: Ensure you're using HTTPS (required for PWA) or localhost

**Problem**: Tamil text not displaying correctly
- **Solution**: Verify UTF-8 encoding in PHP files and database

## 🔄 Differences from 3-Minute Meditation

Our-Daily-Bread is a streamlined version focused on essential devotional content:

### Removed Sections
- ❌ Quote (Today's Quote)
- ❌ Recommended Book
- ❌ Conclusion (A Word to You)

### Retained Sections
- ✅ Memory Verse
- ✅ Devotion
- ✅ Song (Optional)
- ✅ Prayer
- ✅ Author Information

### Purpose
This simplified structure allows for:
- Faster content creation
- More focused devotional reading
- Better mobile experience
- Tamil-language optimization

## 📝 Version History

- **v2025.10.7** - Current version
  - Streamlined content structure (removed Quote, Book, Conclusion)
  - Enhanced modal handling for better UX
  - Fixed URL cleanup on modal close
  - Multi-language support with Tamil and English

## 👥 Authors & Contributors

- **Primary Author**: Pr. Maria Joseph
- **Developer**: Yesudas

## 📄 License

See LICENSE file in the project root.

## 🙏 Credits

- Bootstrap 5 for the UI framework
- Bootstrap Icons for icons
- Flatpickr for date selection
- Google Fonts for Tamil fonts
- The Christian community for inspiration and feedback

## 📞 Support

For questions, issues, or suggestions:
- WhatsApp: +91 92431 83231
- GitHub Issues: [Create an issue](https://github.com/yesudas/bible-devotions-app/issues)

---

**May this tool help spread God's Word and bring spiritual nourishment to many! 🙏**

---

*Last Updated: October 21, 2025*
