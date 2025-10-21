# Bible Devotions App

> "Freely you have received; freely give." - Matthew 10:8

A comprehensive platform for daily Christian devotions, meditations, and spiritual content in multiple languages. Built with love by the Word of God Team at [WordOfGod.in](https://wordofgod.in).

[![License: Free](https://img.shields.io/badge/License-Free-brightgreen.svg)](LICENSE)
[![Platform: Web](https://img.shields.io/badge/Platform-Web-blue.svg)]()
[![PWA Ready](https://img.shields.io/badge/PWA-Ready-purple.svg)]()

## 🌟 Overview

Bible Devotions App is a free, mobile-friendly platform that brings together spiritual content from various authors in multiple languages. Our mission is to make daily Christian devotions accessible to everyone, anywhere, at no cost.

### ✨ Key Highlights

- **📱 Mobile-First Design**: Optimized for all devices (phone, tablet, desktop)
- **🌐 Multi-Language Support**: Tamil (தமிழ்) and English with more languages coming
- **💰 Completely Free**: No ads, no subscriptions, no hidden costs
- **🔌 Offline Capable**: Progressive Web App (PWA) for offline access
- **📖 Rich Content**: Daily devotions, Bible verses, prayers, and spiritual insights
- **🎨 Modern UX**: Beautiful, intuitive interface with accessibility features

## 📚 Sub-Modules

### 1. 3-Minute Meditation

**Status**: ✅ Live and Active

Quick daily spiritual meditations designed to fit into your busy schedule. Perfect for morning devotions or moments of reflection throughout the day.

**Key Features**:
- 📅 Daily devotional content with scheduling support
- 🔀 Latest and Random reading modes
- 🔎 Zoom controls (100% - 250%)
- 📖 Complete Bible verse selector (66 books)
- 🎯 Multi-section content structure:
  - Memory Verse
  - Insight & Reflection
  - Today's Quote
  - Recommended Book (with quotes)
  - Prayer
  - Song (optional)
  - Author Information
- 🔗 Cross-reference system by Bible verses
- 🗂️ Advanced filtering (status, date, title)
- 📱 PWA support with offline capability
- 🔐 Secure admin panel for content management

**Access**: [/3-minute-meditation/](3-minute-meditation/)  
**Documentation**: [3-Minute Meditation README](3-minute-meditation/README.md)

---

### 2. அனுதின மன்னா (Daily Manna)

**Status**: ✅ Live and Active

Traditional Tamil devotions providing daily spiritual nourishment. Streamlined scriptural insights and reflections for Tamil-speaking believers.

**Key Features**:
- 📅 Daily devotional content with scheduling support
- 🔀 Latest and Random reading modes
- 🔎 Zoom controls (100% - 250%)
- 📖 Complete Bible verse selector (66 books)
- 🎯 Streamlined content structure:
  - Memory Verse
  - Devotion
  - Song (optional)
  - Prayer
  - Author Information
- 🌐 Tamil-first with English support
- 🔗 Cross-reference system by Bible verses
- 🗂️ Advanced filtering (status, date, title)
- 📱 PWA support with offline capability
- 🔐 Secure admin panel for content management

**Access**: [/அனுதின-மன்னா/](அனுதின-மன்னா/)  
**Documentation**: [அனுதின மன்னா README](அனுதின-மன்னா/README.md)

---

## 🎯 Core Features

### For Users

#### 📖 Content Features
- **Multi-Language Support**: Switch between languages seamlessly
- **Reading Modes**: 
  - Latest Mode: Read chronologically from newest to oldest
  - Random Mode: Shuffle through meditations randomly
  - View All: Browse complete meditation list
- **Navigation**: Easy Previous/Next buttons with progress indicator
- **Rich Formatting**: Well-structured content with icons and sections

#### 🎨 User Experience
- **Zoom Controls**: Adjust text size from 100% to 250%
- **Responsive Design**: Works perfectly on any screen size
- **Touch-Friendly**: Large buttons and intuitive gestures
- **Keyboard Shortcuts**: Ctrl/Cmd + Plus/Minus for zoom
- **Session Persistence**: Settings remembered across visits

#### 📱 Progressive Web App (PWA)
- **Install as App**: Add to home screen on any device
- **Offline Access**: Read devotions without internet
- **App-Like Experience**: Full-screen mode, no browser UI
- **Custom Icons**: Beautiful app icons and splash screens
- **Fast Loading**: Service worker caching for instant load

#### ♿ Accessibility
- **Screen Reader Support**: Semantic HTML structure
- **High Contrast**: Clear, readable text
- **Keyboard Navigation**: Full keyboard accessibility
- **Adjustable Text Size**: Zoom up to 250%
- **WCAG Compliant**: Following web accessibility guidelines

### For Content Creators & Administrators

#### 🔐 Admin Panel
- **Secure Authentication**: Password-protected access
- **User Management**: Multiple admin accounts
- **Session Timeout**: Auto-logout after 30 minutes

#### ✏️ Content Management
- **Add/Edit/Delete**: Full CRUD operations for meditations
- **Rich Text Editor**: Multi-section content structure
- **Date Scheduling**: Schedule future publications
- **Unique IDs**: Auto-generated identifiers
- **Language Selection**: Choose content language

#### 📖 Bible Verse System
- **Complete Bible Structure**: All 66 books from Genesis to Revelation
- **Cascading Dropdowns**: Book → Chapter → Verse → End Verse
- **Verse Ranges**: Support for single verses or ranges (e.g., John 3:16-17)
- **Auto-Formatting**: Automatic reference formatting
- **Preview**: Real-time preview of selected verses
- **Cross-References**: Automatic linking system

#### 🔍 Advanced Features
- **Multi-Language Admin**: Manage content in multiple languages
- **Filtering System**:
  - Status: Published / Scheduled
  - Date: Filter by publication date
  - Language: Filter by content language
  - Title Search: Find meditations by title
- **Bulk Operations**: Efficient content management
- **Responsive Admin UI**: Works on all devices

#### 🔗 Cross-Reference System
- **Verse-Based Links**: Track all meditations by Bible verse
- **Multi-Brand Support**: Link across different devotion brands
- **Auto-Generation**: Links created automatically on save
- **Deduplication**: Smart duplicate prevention
- **JSON Storage**: Efficient file-based link storage

## 🛠️ Technology Stack

### Frontend
- **Framework**: Bootstrap 5.3.2
- **Icons**: 
  - Font Awesome 6.4.0
  - Bootstrap Icons 1.11.1
- **Date Picker**: Flatpickr
- **JavaScript**: Vanilla ES6+

### Backend
- **Language**: PHP 7.4+
- **Storage**: JSON file-based storage
- **Session Management**: PHP sessions with timeout

### PWA Technologies
- **Service Worker**: For offline functionality
- **Web App Manifest**: App installation metadata
- **Cache API**: Offline content caching

### Analytics
- **Google Analytics**: User behavior tracking
- **View Counter**: Meditation view tracking

## 📁 Project Structure

```
bible-devotions-app/
├── 3-minute-meditation/          # Main meditation app
│   ├── a.php                     # Admin panel
│   ├── index.php                 # User interface
│   ├── meditations/              # Content storage
│   │   ├── English/              # English meditations
│   │   └── தமிழ்/                # Tamil meditations
│   ├── manifest.json             # PWA manifest
│   ├── sw.js                     # Service worker
│   └── README.md                 # Module documentation
│
├── அனுதின-மன்னா/                # Tamil Daily Manna
│   ├── a.php                     # Admin panel
│   ├── index.php                 # User interface
│   ├── meditations/              # Content storage
│   │   ├── English/              # English meditations
│   │   └── தமிழ்/                # Tamil meditations
│   ├── js/
│   │   └── translations.js       # Tamil/English translations
│   ├── manifest.json             # PWA manifest
│   ├── sw.js                     # Service worker
│   └── README.md                 # Module documentation
│
├── css/                          # Global stylesheets
│   └── style.css                 # Main styles
│
├── js/                           # JavaScript modules
│   ├── zoom.js                   # Zoom functionality
│   ├── copy.js                   # Copy to clipboard
│   ├── bible-data.js             # Bible structure data
│   └── translations.js           # Label translations
│
├── links/                        # Bible verse cross-references
│   ├── README.md                 # Link system docs
│   └── [verse_ref].json          # Verse-based links
│
├── pwa/                          # PWA resources
│   └── pwa.js                    # PWA installation logic
│
├── index.php                     # Landing page
├── menu-links.php                # Navigation menu
├── footer-links.php              # Footer content
├── copyright.php                 # Copyright notice
├── detect-app.php                # App detection
├── google-analytics.php          # Analytics tracking
│
├── LICENSE                       # License file
├── README.md                     # This file
├── ZOOM_FEATURE.md               # Zoom feature docs
└── SCHEDULED_FEATURE.md          # Scheduling feature docs
```

## 🚀 Getting Started

### Prerequisites

- Web server (Apache/Nginx)
- PHP 7.4 or higher
- Modern web browser
- Write permissions for data directories

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/bible-devotions-app.git
   cd bible-devotions-app
   ```

2. **Set up permissions**
   ```bash
   chmod 755 3-minute-meditation/meditations
   chmod 755 அனுதின-மன்னா/meditations
   chmod 755 links
   chmod 644 3-minute-meditation/counter.txt
   chmod 644 அனுதின-மன்னா/counter.txt
   ```

3. **Configure admin users** (edit both `3-minute-meditation/a.php` and `அனுதின-மன்னா/a.php`)
   ```php
   $admin_users = [
       'username' => 'password'
   ];
   ```

4. **Start web server**
   ```bash
   # Using PHP built-in server
   php -S localhost:8000
   
   # Or configure Apache/Nginx virtual host
   ```

5. **Access the application**
   - Landing Page: `http://localhost:8000/`
   - 3-Minute Meditation: `http://localhost:8000/3-minute-meditation/`
   - 3-Minute Meditation Admin: `http://localhost:8000/3-minute-meditation/a.php`
   - அனுதின மன்னா: `http://localhost:8000/அனுதின-மன்னா/`
   - அனுதின மன்னா Admin: `http://localhost:8000/அனுதின-மன்னா/a.php`

### Initial Setup

1. **Create meditation directories**
   ```bash
   mkdir -p 3-minute-meditation/meditations/English
   mkdir -p 3-minute-meditation/meditations/தமிழ்
   mkdir -p அனுதின-மன்னா/meditations/English
   mkdir -p அனுதின-மன்னா/meditations/தமிழ்
   ```

2. **Set up Google Analytics** (optional)
   - Edit `google-analytics.php`
   - Add your tracking ID

3. **Customize branding** (optional)
   - Update `copyright.php`
   - Modify `menu-links.php` and `footer-links.php`

## 📖 Usage

### For End Users

1. **Access the App**: Visit the application URL
2. **Select Language**: Choose your preferred language from the dropdown
3. **Choose Reading Mode**:
   - 📅 Latest: Read newest meditations first
   - 🔀 Random: Shuffle through content
   - 📋 View All: Browse complete list
4. **Adjust Reading Experience**:
   - Use zoom controls for comfortable text size
   - Navigate with Previous/Next or keyboard arrows
5. **Install as App** (optional):
   - Click install button in header
   - Follow browser prompts to add to home screen

### For Content Creators

1. **Login**: Access `/3-minute-meditation/a.php` or `/அனுதின-மன்னா/a.php`
2. **Add Content**:
   - Click "Add New" button
   - Select language and date
   - Choose Bible verse using dropdowns
   - Fill in all content sections
   - Save meditation
3. **Schedule Publications**:
   - Set future date when adding meditation
   - Content automatically publishes on that date
4. **Manage Content**:
   - Use filters to find specific meditations
   - Edit or delete as needed
   - Track scheduled vs published content

## 🌐 Multi-Language Support

### Current Languages
- **English**: Full support
- **தமிழ் (Tamil)**: Full support

### Adding New Languages

1. **Update language arrays** in both files:
   ```php
   // a.php and index.php
   $languages = ["தமிழ்", "English", "New Language"];
   ```

2. **Create meditation folder**:
   ```bash
   mkdir 3-minute-meditation/meditations/"New Language"
   ```

3. **Add translations** in `js/translations.js`:
   ```javascript
   const labelTranslations = {
       "New Language": {
           memory_verse_label: "Translation",
           // ... other labels
       }
   };
   ```

4. **Update UI labels** in template files as needed

## 🔗 Bible Reference System

### Reference Format

Bible verses use the format: `[book_no]_[chapter]:[verse]-[end_verse]`

**Examples**:
- Single verse: `43_3:16` (John 3:16)
- Verse range: `43_3:16-17` (John 3:16-17)
- Old Testament: `19_23:1` (Psalm 23:1)

### Book Numbers

- Old Testament: 1-39 (Genesis to Malachi)
- New Testament: 40-66 (Matthew to Revelation)

**Common Books**:
- 1 = Genesis
- 19 = Psalms
- 20 = Proverbs
- 40 = Matthew
- 43 = John
- 45 = Romans
- 66 = Revelation

See `js/bible-data.js` for complete structure.

### Cross-Reference Links

The system automatically creates cross-reference files in `/links/` directory:

**File**: `43_3:16.json`
```json
[
  {
    "brand": "3-minute-meditation",
    "title": "For God So Loved the World",
    "language": "English",
    "file": "/3-minute-meditation/meditations/English/7.json"
  }
]
```

This enables finding all meditations that reference a specific Bible verse across all brands and languages.

## 🔐 Security

### Current Implementation
- Password authentication for admin panel
- Session management with timeout
- Input sanitization with `htmlspecialchars()`
- JSON data validation
- CSRF protection through session tokens

### Recommendations for Production
- Use password hashing (bcrypt/argon2)
- Implement HTTPS/SSL
- Add rate limiting for login attempts
- Regular security audits
- Database migration from JSON files
- Implement proper user roles and permissions

## 📱 Progressive Web App (PWA)

### Features
- **Offline Access**: Read meditations without internet
- **Install Prompt**: Add to home screen on any device
- **Fast Loading**: Service worker caching
- **App-Like Feel**: Full-screen standalone mode
- **Push Notifications**: (Coming soon)

### Installation
Users can install the app on:
- Android (Chrome, Samsung Internet)
- iOS (Safari - Add to Home Screen)
- Windows (Chrome, Edge)
- macOS (Chrome, Safari)
- Linux (Chrome, Firefox)

### Cache Strategy
- **Cache First**: Static assets (CSS, JS, images)
- **Network First**: Dynamic content (meditations)
- **Cache Fallback**: Offline page when network unavailable

## 📊 Analytics & Tracking

### Implemented
- Google Analytics integration
- Page view tracking
- Meditation view counter
- Language preference tracking

### Future Enhancements
- User engagement metrics
- Most read meditations
- Search analytics
- Share tracking

## ♿ Accessibility Features

### WCAG Compliance
- **Level AA** standards followed
- Semantic HTML structure
- ARIA labels where needed
- Keyboard navigation support
- Screen reader compatible

### Features
- ✅ Adjustable text size (100% - 250%)
- ✅ High contrast text and buttons
- ✅ Keyboard shortcuts
- ✅ Touch-friendly buttons (min 44x44px)
- ✅ Focus indicators
- ✅ Alt text for images
- ✅ Proper heading hierarchy

## 🤝 Contributing

We welcome contributions! Here's how you can help:

### Ways to Contribute
- 🐛 Report bugs and issues
- 💡 Suggest new features
- 📝 Improve documentation
- 🌐 Add translations
- ✏️ Submit meditation content
- 🎨 Enhance UI/UX
- 🔧 Fix bugs and add features

### Development Process
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Code Style
- PHP: PSR-12 coding standard
- JavaScript: ES6+ with modern practices
- CSS: BEM naming convention
- HTML: Semantic, accessible markup

## 📝 License

This project is provided free of cost based on Matthew 10:8 - "Freely you have received; freely give."

See [LICENSE](LICENSE) file for details.

## 👥 Authors & Contributors

### Word of God Team
- **Website**: [WordOfGod.in](https://wordofgod.in)
- **Email**: mjosephnj@gmail.com
- **WhatsApp**: +91 9243183231

### Content Authors
We're grateful to all authors who contribute meditations and devotional content to bless others.

## 🙏 Acknowledgments

- All meditation content authors
- Bootstrap framework
- Font Awesome icon library
- Bootstrap Icons
- Flatpickr date picker
- Google Analytics
- The global Christian community

## 📞 Support & Contact

### Get Help
- 📧 **Email**: mjosephnj@gmail.com
- 💬 **WhatsApp**: +91 9243183231
- 🌐 **Website**: [WordOfGod.in](https://wordofgod.in)

### Report Issues
- Use GitHub Issues for bug reports
- Include browser and device information
- Provide steps to reproduce

### Feature Requests
- Open a GitHub Issue with [Feature Request] tag
- Describe the feature and use case
- Explain expected behavior

## 🗺️ Roadmap

### Recently Completed ✅
- [x] 3-Minute Meditation module (Full-featured)
- [x] அனுதின மன்னா module (Streamlined Tamil devotions)
- [x] Multi-language support (Tamil & English)
- [x] PWA with offline capabilities
- [x] Bible verse cross-reference system
- [x] Scheduled publishing system

### Short Term (Q1 2025)
- [ ] Add audio devotions support
- [ ] Implement search functionality
- [ ] Add social sharing features
- [ ] Enhanced offline capabilities

### Medium Term (Q2-Q3 2025)
- [ ] Add more languages (Hindi, Malayalam, etc.)
- [ ] User accounts and bookmarks
- [ ] Personalized reading plans
- [ ] Push notifications for daily devotions
- [ ] Mobile apps (iOS/Android)

### Long Term (2026+)
- [ ] Community features (comments, discussions)
- [ ] Content translation system
- [ ] Video devotions
- [ ] Podcast integration
- [ ] Bible study tools integration

## 📈 Project Status

- **3-Minute Meditation**: ✅ Live and Active
- **அனுதின மன்னா**: ✅ Live and Active
- **Other Modules**: 📋 Planned

## 💝 Support the Project

This project is and will always be free. However, if you'd like to support:

- 🙏 Pray for the team and users
- ✍️ Contribute content or code
- 📢 Share with your community
- 🐛 Report bugs and suggest features
- 📖 Provide feedback on usability

## 📜 Verse of the Day

> "All Scripture is God-breathed and is useful for teaching, rebuking, correcting and training in righteousness, so that the servant of God may be thoroughly equipped for every good work."  
> — 2 Timothy 3:16-17

---

**Made with ❤️ for the Kingdom of God by Word of God Team**

*"For where two or three gather in my name, there am I with them." - Matthew 18:20*
