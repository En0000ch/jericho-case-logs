# Surgery Loading Feature - Quick Reference

## Overview
User-specific saved surgeries are automatically loaded from Parse and merged with default surgery lists.

## Parse Database

**Class:** `savedSurgeries`

**Fields:**
- `userEmail`: String (user's email)
- `surgeryClass`: String (specialty name)
- `subSurgery`: String (surgery name)

## Key Files

### New Files Created
1. `/lib/data/datasources/remote/parse_surgery_service.dart` - Surgery service
2. `/lib/presentation/providers/surgery_provider.dart` - Surgery state management
3. `/SURGERY_LOADING_FEATURE.md` - Full documentation

### Modified Files
1. `/lib/core/constants/api_constants.dart` - Added `savedSurgeriesClass`
2. `/lib/data/datasources/remote/parse_case_service.dart` - Added `getSavedSurgeries()`
3. `/lib/presentation/screens/surgeries/surgeries_selection_screen.dart` - Uses provider
4. `/lib/main.dart` - Initializes surgery provider

## How It Works

### Data Flow
```
1. User logs in
2. AuthRouter initializes surgeryProvider (main.dart:135-142)
3. SurgeryNotifier.loadSurgeries() fetches data
4. For each specialty:
   - Fetches default surgeries from SurgeryData
   - Queries Parse for user's saved surgeries
   - Merges and deduplicates
   - Sorts alphabetically
5. UI displays merged surgery lists
```

### User Privacy
- All queries filter by `userEmail`
- Each user sees only their own saved surgeries
- No cross-user data leakage

## Usage in Code

### Access Surgery Data
```dart
// In a ConsumerWidget
final surgeryState = ref.watch(surgeryProvider);
final cardiovascularSurgeries = surgeryState.getSurgeriesForSpecialty('Cardiovascular');
```

### Save a Surgery
```dart
final success = await ref.read(surgeryProvider.notifier).saveSurgery(
  surgeryClass: 'General',
  subSurgery: 'Custom Procedure',
);
```

### Reload Specialty
```dart
await ref.read(surgeryProvider.notifier).reloadSpecialty('Orthopedic');
```

## Testing

### 1. Add Test Data in Parse
```
Open Parse Dashboard → savedSurgeries class
Add records:
- userEmail: your_test_email@example.com
- surgeryClass: "Cardiovascular"
- subSurgery: "Test Custom Surgery"
```

### 2. Verify in App
1. Login with test email
2. Navigate to Surgery Selection
3. Select "Cardiovascular"
4. Verify "Test Custom Surgery" appears in list
5. Verify list is alphabetically sorted

### 3. Check Error Handling
- Disable network → Should show default surgeries only
- Empty saved surgeries → Should show defaults only

## Error Handling
- Network failure → Falls back to default surgeries
- Parse error → Logs error, uses defaults
- Null/empty values → Filtered out automatically

## Performance
- Surgeries loaded once at login
- Cached in memory (Riverpod state)
- Specialty reload available if needed
- Query optimized with filters

## Future Enhancements
- Add UI to create custom surgeries
- Add UI to delete custom surgeries
- Surgery usage analytics
- Export/import surgery lists
- Team surgery list sharing
