# User-Specific Saved Surgeries Feature

## Overview
This feature loads user-specific saved surgeries from the Parse database and merges them with the default surgery lists provided by the app. Each user sees their own custom surgeries in addition to the pre-defined surgery options.

## Implementation Details

### 1. Data Flow

```
App Startup (main.dart)
    ↓
AuthRouter initializes surgeryProvider when user is authenticated
    ↓
SurgeryNotifier.loadSurgeries() is called
    ↓
ParseSurgeryService.getAllSurgeries(userEmail) fetches data
    ↓
For each specialty:
  - Gets default surgeries from SurgeryData
  - Queries Parse 'savedSurgeries' class for user's custom surgeries
  - Merges both lists (avoiding duplicates)
  - Sorts alphabetically (case-insensitive)
    ↓
SurgeryState is updated with merged surgery data
    ↓
UI screens (SurgeriesSelectionScreen, SurgeriesListScreen) display merged data
```

### 2. Parse Database Schema

**Class Name:** `savedSurgeries`

**Fields:**
- `userEmail` (String): The email of the user who saved the surgery
- `surgeryClass` (String): The specialty/category (e.g., "Cardiovascular", "General")
- `subSurgery` (String): The name of the surgery (e.g., "Custom Appendectomy")

**Indexes Recommended:**
- Compound index on `userEmail` + `surgeryClass` for optimal query performance

### 3. Key Components

#### A. ParseSurgeryService
**File:** `/lib/data/datasources/remote/parse_surgery_service.dart`

**Purpose:** Handles all Parse database operations for surgeries

**Key Methods:**
- `getSurgeriesForSpecialty(specialty, userEmail)`: Fetches and merges surgeries for one specialty
- `getAllSurgeries(userEmail)`: Fetches and merges surgeries for all specialties
- `saveSurgery(userEmail, surgeryClass, subSurgery)`: Saves a new custom surgery
- `deleteSavedSurgery(objectId)`: Removes a custom surgery
- `isSurgerySaved(userEmail, surgeryClass, subSurgery)`: Checks if a surgery exists

#### B. SurgeryProvider
**File:** `/lib/presentation/providers/surgery_provider.dart`

**Purpose:** Manages surgery state using Riverpod

**State Structure:**
```dart
class SurgeryState {
  Map<String, List<String>> surgeriesBySpecialty;
  bool isLoading;
  String? error;
}
```

**Key Methods:**
- `loadSurgeries()`: Loads all surgeries for the current user
- `reloadSpecialty(specialty)`: Reloads a specific specialty's surgeries
- `saveSurgery(surgeryClass, subSurgery)`: Saves a new surgery and updates state

#### C. Updated UI Components

**SurgeriesSelectionScreen:**
- Now uses `ConsumerStatefulWidget` to access Riverpod
- Loads surgeries on initialization
- Shows loading indicator while fetching data
- Displays merged surgery lists

**SurgeriesListScreen:**
- Receives `SurgerySpecialty` with populated surgeries
- No changes needed (already displays specialty.surgeries list)

### 4. User Privacy & Data Isolation

The implementation ensures strict user data isolation:
- All queries filter by `userEmail` field
- Users only see their own saved surgeries
- No cross-user data leakage possible

### 5. Duplicate Prevention

The system prevents duplicate surgeries through:
1. Using a `Set<String>` during merging to automatically deduplicate
2. `isSurgerySaved()` method checks before saving new surgeries
3. Case-sensitive matching (exact string comparison)

### 6. Alphabetical Sorting

All surgery lists are sorted alphabetically:
- Case-insensitive sorting using `toLowerCase().compareTo()`
- Applied after merging default and saved surgeries
- Consistent across all specialties

### 7. Error Handling

The system gracefully handles errors:
- If Parse query fails, falls back to default surgeries only
- Null/empty values are filtered out
- Loading state indicates when data is being fetched

## Usage Examples

### For Users
1. Navigate to Surgery Selection screen
2. Select a specialty (e.g., "Cardiovascular")
3. See both default surgeries and your custom saved surgeries
4. All surgeries are sorted alphabetically

### For Developers

**Accessing surgery data in a widget:**
```dart
class MyWidget extends ConsumerWidget {
  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final surgeryState = ref.watch(surgeryProvider);
    final cardiovascularSurgeries = surgeryState.getSurgeriesForSpecialty('Cardiovascular');

    // Use the surgeries...
  }
}
```

**Saving a new surgery:**
```dart
final success = await ref.read(surgeryProvider.notifier).saveSurgery(
  surgeryClass: 'General',
  subSurgery: 'Custom Procedure',
);
```

**Reloading a specialty:**
```dart
await ref.read(surgeryProvider.notifier).reloadSpecialty('Orthopedic');
```

## Testing

To test the feature:

1. **Create test data in Parse:**
   - Open Parse Dashboard
   - Navigate to 'savedSurgeries' class
   - Add test records with your user email

2. **Verify loading:**
   - Login to the app
   - Navigate to Surgery Selection
   - Select a specialty with saved surgeries
   - Verify custom surgeries appear in the list

3. **Check sorting:**
   - Verify all surgeries are alphabetically sorted
   - Check that duplicates don't appear

4. **Test error scenarios:**
   - Disable network and verify fallback to defaults
   - Test with empty saved surgeries list

## Files Modified

1. `/lib/core/constants/api_constants.dart`
   - Added `savedSurgeriesClass` constant

2. `/lib/data/datasources/remote/parse_case_service.dart`
   - Added `getSavedSurgeries()` method

3. `/lib/data/datasources/remote/parse_surgery_service.dart` (NEW)
   - Created new service for surgery-specific operations

4. `/lib/presentation/providers/surgery_provider.dart` (NEW)
   - Created Riverpod provider for surgery state management

5. `/lib/presentation/screens/surgeries/surgeries_selection_screen.dart`
   - Updated to use surgery provider
   - Added loading state handling

6. `/lib/main.dart`
   - Added surgery provider initialization in AuthRouter

## Future Enhancements

Possible improvements:
1. Add UI for users to add custom surgeries directly in the app
2. Add UI to delete saved surgeries
3. Implement surgery search/filtering
4. Add surgery usage statistics
5. Sync surgeries across devices
6. Export/import custom surgery lists
7. Share surgery lists between team members
