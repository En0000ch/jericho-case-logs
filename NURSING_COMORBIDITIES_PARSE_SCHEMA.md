# Nursing Comorbidities - Parse/Back4App Schema Documentation

## New Parse Class Required: `nursingCustomComorbidities`

This class stores user-specific custom nursing comorbidities that are not in the default comorbidities list.

### Class Name
```
nursingCustomComorbidities
```

### Fields/Columns

| Field Name | Type | Description | Required | Indexed |
|------------|------|-------------|----------|---------|
| `userEmail` | String | User's email address (identifies the owner) | Yes | Yes |
| `customComorbidities` | Array | Array of custom comorbidity names (strings) | Yes | No |

### Example Record

```json
{
  "objectId": "def456xyz",
  "userEmail": "nurse@hospital.com",
  "customComorbidities": [
    "Rare genetic disorder",
    "Custom condition X",
    "Unusual autoimmune disease"
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

### 1. **User-Specific Custom Comorbidities**
- Each user can add their own custom comorbidities
- Custom comorbidities are stored in an array
- One record per user (identified by `userEmail`)
- Comorbidities are private to the user who created them

### 2. **Comorbidities Display**
- Default comorbidities (68 predefined conditions) are always shown
- User's custom comorbidities are fetched from Parse and merged with defaults
- All comorbidities are sorted alphabetically
- Custom comorbidities are marked with a "Custom" label in the UI

### 3. **Adding Custom Comorbidities**
- User taps "Add Custom Comorbidity" button
- Enters comorbidity name
- Comorbidity is added to their `customComorbidities` array in Parse
- If first custom comorbidity, a new record is created
- If user already has custom comorbidities, the array is updated

### 4. **Removing Custom Comorbidities**
- User can remove any of their custom comorbidities
- Comorbidity is removed from their `customComorbidities` array
- Does not affect other users

## API Operations

### Fetch Custom Comorbidities
```dart
final customComorbidities = await ParseNursingComorbiditiesService().fetchCustomComorbidities(userEmail);
// Returns: ['Custom Comorbidity 1', 'Custom Comorbidity 2', ...]
```

### Add Custom Comorbidity
```dart
final success = await ParseNursingComorbiditiesService().addCustomComorbidity(userEmail, 'New Comorbidity Name');
// Returns: true if successful, false if comorbidity already exists or error
```

### Remove Custom Comorbidity
```dart
final success = await ParseNursingComorbiditiesService().removeCustomComorbidity(userEmail, 'Comorbidity To Remove');
// Returns: true if successful, false if comorbidity not found or error
```

### Get All Comorbidities (Default + Custom)
```dart
final allComorbidities = await ParseNursingComorbiditiesService().getAllComorbiditiesForUser(
  userEmail,
  NursingComorbidities.defaultComorbidities,
);
// Returns alphabetically sorted list of all comorbidities
```

## Default Comorbidities List

The app includes 68 predefined nursing comorbidities:

- Alcohol use disorder
- Alzheimer's disease / dementia
- Anemia
- Anxiety
- Arthritis
- Asthma
- Atrial fibrillation
- Bipolar disorder
- Bleeding disorder
- Blindness / visual impairment
- Cancer (active)
- Cancer (history of)
- Chronic kidney disease (CKD)
- Chronic obstructive pulmonary disease (COPD)
- Chronic pain
- Cirrhosis
- Congestive heart failure (CHF)
- Constipation
- Coronary artery disease (CAD)
- Depression
- Diabetes mellitus type 1
- Diabetes mellitus type 2
- Diabetic neuropathy
- Diabetic retinopathy
- Drug use disorder
- Eating disorder
- End-stage renal disease (ESRD)
- Epilepsy / seizure disorder
- Falls (history of)
- Fatty liver disease
- Gastroesophageal reflux disease (GERD)
- Glaucoma
- Hearing impairment
- Heart failure
- Hepatitis B
- Hepatitis C
- HIV
- Hyperlipidemia
- Hypertension
- Hyperthyroidism
- Hypothyroidism
- Incontinence (bowel)
- Incontinence (urinary)
- Intellectual disability
- Kidney disease
- Liver disease
- Malnutrition
- Mobility impairment
- Multiple sclerosis
- Neuropathy
- Obesity
- Obstructive sleep apnea (OSA)
- Osteoarthritis
- Osteoporosis
- Parkinson's disease
- Peripheral vascular disease (PVD)
- Pressure injuries (history of)
- Pulmonary disease (chronic)
- Rheumatoid arthritis
- Schizophrenia
- Sickle cell disease
- Smoking (current)
- Smoking (former)
- Stroke (history of)
- Thyroid disease
- Urinary retention
- Urinary tract infections (recurrent)
- Vision impairment

## UI Features

### Comorbidities Selection Screen

#### Collection View (Grid Layout)
- 2 columns
- Searchable
- Checkboxes for multi-select
- Orange highlight for selected comorbidities
- "Custom" label for user-added comorbidities

#### Search Functionality
- Real-time filtering
- Case-insensitive
- Searches within comorbidity names

#### Selected Comorbidities Display
- Shows at bottom of screen
- Chip-style display with remove buttons
- Shows count of selected comorbidities
- "Clear All" button to deselect all

#### Add Custom Comorbidity
- Dialog with text input
- Validates for duplicates
- Auto-selects newly added comorbidity
- Saved to Parse immediately

## Integration Points

### Patient Creation Flow
1. User selects patient age category
2. Patient Creation Screen shows "Select Nursing Skills" button
3. User selects skills if desired
4. Patient Creation Screen shows "Select Comorbidities" button
5. Tapping opens Comorbidities Selection Screen
6. User selects multiple comorbidities (default + custom)
7. Confirms selection with checkmark
8. Selected comorbidities shown on Patient Creation Screen
9. Comorbidities will be saved with patient record (when feature is complete)

## Back4App Setup Steps

1. Log into Back4App dashboard
2. Select your app: "JerichoCaseLogs"
3. Go to "Browser" → "Create a class"
4. Class name: `nursingCustomComorbidities`
5. Add columns:
   - `userEmail` (String)
   - `customComorbidities` (Array)
6. Set Class Level Permissions (recommended above)
7. Create index on `userEmail`
8. Save

## Files Created

1. **Data Layer**
   - `lib/core/data/nursing_comorbidities_data.dart` - Default comorbidities list and utility functions
   - `lib/data/datasources/remote/parse_nursing_comorbidities_service.dart` - Parse API service

2. **UI Layer**
   - `lib/presentation/screens/nurse/nursing_comorbidities_selection_screen.dart` - Comorbidities selection screen
   - `lib/presentation/screens/nurse/patient_creation_screen.dart` - Updated to integrate comorbidities selection

## Future Enhancements

- Bulk import of custom comorbidities
- Export custom comorbidities
- Share custom comorbidities between team members
- Analytics on most common comorbidities
- Comorbidity categories/grouping
- Severity levels for comorbidities
- Link comorbidities to specific medications or care plans
