# Scheduled Meditations Feature

## Overview
This feature allows admins to schedule meditations for future dates. Scheduled meditations are automatically activated when their date arrives.

## Admin Panel (a.php) Enhancements

### 1. Automatic Scheduling
- When adding or editing a meditation with a **future date**, the system automatically adds `"scheduled": true` attribute
- Meditations with **past or current dates** do not get the scheduled attribute
- The scheduled flag is stored in both:
  - Individual meditation JSON files (`meditations/X.json`)
  - Master list (`meditations/all-meditations.json`)

### 2. Status Column
A new "Status" column displays:
- **Scheduled** badge (yellow/warning) for future meditations with clock icon
- **Published** badge (green/success) for current/past meditations with check icon

### 3. Filter Controls
Three powerful filters above the meditation list:

#### a) Status Filter
- **All Meditations**: Shows everything
- **Scheduled Only**: Shows only future meditations
- **Published Only**: Shows only active/past meditations

#### b) Date Filter
- Filter meditations by specific date
- Uses native date picker

#### c) Title Search
- Real-time search as you type
- Case-insensitive search through titles

#### Clear Filters Button
- Resets all filters to default state

### 4. Mobile Responsive
- Filters work on both desktop table view and mobile card view
- Status badges shown in mobile cards as well

## User Screen (index.php) Enhancements

### Auto-Activation Logic
Every time a user visits `index.php`, the system:

1. **Checks all scheduled meditations** in `all-meditations.json`
2. **Finds matches** where:
   - `scheduled === true`
   - `date === today's date`
3. **Activates matches** by:
   - Removing `scheduled` attribute from `all-meditations.json`
   - Removing `scheduled` attribute from individual meditation files
4. **Makes them visible** to users immediately

### Benefits
- No manual intervention needed
- Automatic publishing on scheduled date
- Seamless user experience
- Meditations appear exactly when intended

## Technical Implementation

### File Structure Changes

#### Individual Meditation Files (e.g., `6.json`)
```json
{
  "uniqueid": "20251019132033_f3fa0492",
  "scheduled": true,  // Only present if future date
  "date": "2025-10-25",
  "title": "Future Meditation",
  ...
}
```

#### all-meditations.json
```json
[
  {
    "uniqueid": "20251019132033_f3fa0492",
    "filename": "6.json",
    "title": "Future Meditation",
    "date": "2025-10-25",
    "scheduled": true  // Only present if future date
  },
  {
    "uniqueid": "20251019132023_0230450d",
    "filename": "2.json",
    "title": "Current Meditation",
    "date": "2025-10-19"
    // No scheduled attribute for past/current dates
  }
]
```

### PHP Functions

#### a.php Functions
- `updateAllMeditationsFile()`: Enhanced to preserve scheduled attribute
- Add action: Checks date and adds scheduled flag if future
- Edit action: Re-evaluates date and updates scheduled flag

#### index.php Functions
- `checkAndActivateScheduledMeditations()`: New function that:
  - Scans all meditations
  - Finds scheduled items for today
  - Removes scheduled flag
  - Updates both all-meditations.json and individual files

### JavaScript Filter Logic
- Filters work on data attributes: `data-scheduled`, `data-date`, `data-title`
- Real-time filtering without page reload
- Works on both desktop and mobile views

## Usage Examples

### Example 1: Schedule Future Meditation
1. Admin logs into a.php
2. Clicks "Add New"
3. Sets date to December 25, 2025
4. Fills in content and saves
5. **Result**: Meditation saved with `"scheduled": true`
6. Status column shows yellow "Scheduled" badge

### Example 2: Filter Scheduled Meditations
1. Admin opens a.php
2. Selects "Scheduled Only" from Status filter
3. **Result**: Only shows meditations with future dates

### Example 3: Auto-Activation
1. Scheduled meditation date is 2025-10-20
2. User visits index.php on 2025-10-20
3. **Result**: 
   - Scheduled flag removed automatically
   - Meditation becomes visible
   - Shows as "Published" in admin panel

### Example 4: Search by Title
1. Admin has 50+ meditations
2. Types "prayer" in Title Search
3. **Result**: Instantly shows only meditations with "prayer" in title

## Testing Checklist

- [ ] Add meditation with future date → Verify scheduled badge appears
- [ ] Add meditation with current date → Verify published badge appears
- [ ] Edit meditation, change date to future → Verify becomes scheduled
- [ ] Edit meditation, change date to past → Verify becomes published
- [ ] Filter by "Scheduled Only" → Verify only future meditations shown
- [ ] Filter by "Published Only" → Verify only current/past shown
- [ ] Filter by specific date → Verify only that date shown
- [ ] Search by title → Verify real-time filtering works
- [ ] Clear filters → Verify all meditations shown again
- [ ] Mobile view → Verify filters work on card view
- [ ] User visits with scheduled meditation for today → Verify auto-activation

## Benefits

1. **Content Planning**: Schedule meditations weeks or months in advance
2. **Time Zones**: Meditations activate based on server date
3. **Automation**: No manual publishing needed
4. **Organization**: Easy to see what's scheduled vs published
5. **Flexibility**: Edit scheduled content before it goes live
6. **User Experience**: Seamless delivery of daily content

## Future Enhancements (Optional)

- Time-based scheduling (not just date)
- Bulk scheduling
- Draft status (separate from scheduled)
- Email notifications when scheduled content activates
- Calendar view of scheduled meditations
- Timezone selection for scheduling
