# Nursing Skills - Parse/Back4App Schema Documentation

## New Parse Class Required: `nursingCustomSkills`

This class stores user-specific custom nursing skills that are not in the default skills list.

### Class Name
```
nursingCustomSkills
```

### Fields/Columns

| Field Name | Type | Description | Required | Indexed |
|------------|------|-------------|----------|---------|
| `userEmail` | String | User's email address (identifies the owner) | Yes | Yes |
| `customSkills` | Array | Array of custom skill names (strings) | Yes | No |

### Example Record

```json
{
  "objectId": "abc123xyz",
  "userEmail": "nurse@hospital.com",
  "customSkills": [
    "Pediatric IV therapy",
    "Wound vac management",
    "Custom procedure X"
  ],
  "createdAt": "2026-01-12T12:00:00.000Z",
  "updatedAt": "2026-01-12T12:30:00.000Z"
}
```

### Class Level Permissions (CLP)

Recommended settings for security:

- **Public Read**: `false`
- **Public Write**: `false`
- **Public Find**: `false`
- **Authenticated Read**: `true`
- **Authenticated Write**: `true`
- **Authenticated Find**: `true`

### Indexes

Create an index on `userEmail` for faster queries:
```
userEmail: ascending
```

## How It Works

### 1. **User-Specific Custom Skills**
- Each user can add their own custom skills
- Custom skills are stored in an array
- One record per user (identified by `userEmail`)
- Skills are private to the user who created them

### 2. **Skills Display**
- Default skills (109 predefined skills) are always shown
- User's custom skills are fetched from Parse and merged with defaults
- All skills are sorted alphabetically
- Custom skills are marked with a "Custom" label in the UI

### 3. **Adding Custom Skills**
- User taps "Add Custom Skill" button
- Enters skill name
- Skill is added to their `customSkills` array in Parse
- If first custom skill, a new record is created
- If user already has custom skills, the array is updated

### 4. **Removing Custom Skills**
- User can remove any of their custom skills
- Skill is removed from their `customSkills` array
- Does not affect other users

## API Operations

### Fetch Custom Skills
```dart
final customSkills = await ParseNursingSkillsService().fetchCustomSkills(userEmail);
// Returns: ['Custom Skill 1', 'Custom Skill 2', ...]
```

### Add Custom Skill
```dart
final success = await ParseNursingSkillsService().addCustomSkill(userEmail, 'New Skill Name');
// Returns: true if successful, false if skill already exists or error
```

### Remove Custom Skill
```dart
final success = await ParseNursingSkillsService().removeCustomSkill(userEmail, 'Skill To Remove');
// Returns: true if successful, false if skill not found or error
```

### Get All Skills (Default + Custom)
```dart
final allSkills = await ParseNursingSkillsService().getAllSkillsForUser(
  userEmail,
  NursingSkills.defaultSkills,
);
// Returns alphabetically sorted list of all skills
```

## Default Skills List

The app includes 109 predefined nursing skills:

- ABG collection (arterial line sample)
- ABG interpretation (RN role)
- Admission assessment
- Airway suctioning
- Ambulation assistance
- ... (complete list in `lib/core/data/nursing_skills_data.dart`)
- Wound assessment
- Wound care

## UI Features

### Skills Selection Screen

#### Collection View (Grid Layout)
- 2 columns
- Searchable
- Checkboxes for multi-select
- Orange highlight for selected skills
- "Custom" label for user-added skills

#### Search Functionality
- Real-time filtering
- Case-insensitive
- Searches within skill names

#### Selected Skills Display
- Shows at bottom of screen
- Chip-style display with remove buttons
- Shows count of selected skills
- "Clear All" button to deselect all

#### Add Custom Skill
- Dialog with text input
- Validates for duplicates
- Auto-selects newly added skill
- Saved to Parse immediately

## Integration Points

### Patient Creation Flow
1. User selects patient age category
2. Patient Creation Screen shows "Select Nursing Skills" button
3. Tapping opens Skills Selection Screen
4. User selects multiple skills (default + custom)
5. Confirms selection with checkmark
6. Selected skills shown on Patient Creation Screen
7. Skills will be saved with patient record (when feature is complete)

## Back4App Setup Steps

1. Log into Back4App dashboard
2. Select your app: "JerichoCaseLogs"
3. Go to "Browser" → "Create a class"
4. Class name: `nursingCustomSkills`
5. Add columns:
   - `userEmail` (String)
   - `customSkills` (Array)
6. Set Class Level Permissions (recommended above)
7. Create index on `userEmail`
8. Save

## Files Created

1. **Data Layer**
   - `lib/core/data/nursing_skills_data.dart` - Default skills list and utility functions
   - `lib/data/datasources/remote/parse_nursing_skills_service.dart` - Parse API service

2. **UI Layer**
   - `lib/presentation/screens/nurse/nursing_skills_selection_screen.dart` - Skills selection screen
   - `lib/presentation/screens/nurse/patient_creation_screen.dart` - Updated to integrate skills selection

## Future Enhancements

- Bulk import of custom skills
- Export custom skills
- Share custom skills between team members
- Analytics on most-used skills
- Skill categories/grouping
- Skill proficiency levels
