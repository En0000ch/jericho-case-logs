# jclJobs - Expanded Parse Server Schema

## Purpose
Comprehensive job posting schema for travel healthcare professionals (CRNAs, RNs, Allied Health, Locum physicians)

## Schema Version
- **Current Version:** 1.0 (basic)
- **Proposed Version:** 2.0 (professional job board)
- **Date:** 2025-12-04

---

## PHASE 1: Core Fields (Launch Ready)

### 1. Employer / Facility Information

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `facilityName` | String | ✅ Yes | Facility/company name | "Mayo Clinic" |
| `facilityDepartment` | String | No | Department or unit | "ICU", "OR", "ER" |
| `facilityType` | String | ✅ Yes | Type of facility | "Hospital", "LTAC", "SNF", "Outpatient Clinic" |
| `facilityWebsite` | String | No | Company website | "https://mayoclinic.org" |
| `facilityLogoUrl` | String | No | Company logo URL | "https://..." |
| `facilityEIN` | String | No | Tax ID for credibility | "12-3456789" |

### 2. Location

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `jobCity` | String | ✅ Yes | Primary job location city | "Rochester" |
| `jobState` | String | ✅ Yes | State abbreviation | "MN" |
| `zipCode` | String | ✅ Yes | ZIP code | "55901" |
| `multipleLocations` | Boolean | No | Multiple sites? | true/false |
| `housingProvided` | Boolean | No | Housing provided? | true/false |
| `housingStipend` | Number | No | Weekly housing stipend | 1500 |

### 3. Contact Information

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `facContactName` | String | ✅ Yes | Recruiter/hiring manager | "Jane Smith" |
| `facContactEmail` | String | ✅ Yes | Contact email | "jane.smith@facility.com" |
| `facContactPhone` | String | ✅ Yes | Contact phone | "555-123-4567" |
| `preferredContactMethod` | String | No | Email, Phone, Platform | "Email" |
| `contactVisibility` | String | No | Public, Hidden, Forward | "Public" |

### 4. Job Basics

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `jobTitle` | String | ✅ Yes | Job title | "Travel ICU RN" |
| `jclProf` | String | ✅ Yes | Primary profession | "CRNA", "RN", "RT", "NP" |
| `specialty` | String | ✅ Yes | Specialty/discipline | "ICU", "ER", "OR", "L&D" |
| `discipline` | String | No | Allied discipline | "RT", "PT", "OT", "Rad Tech" |
| `credentialsRequired` | Array | ✅ Yes | Required credentials | ["RN License", "BLS", "ACLS"] |
| `credentialsPreferred` | Array | No | Preferred credentials | ["CCRN", "TNCC"] |
| `shiftType` | String | ✅ Yes | Shift | "Days", "Nights", "Evenings", "Rotating" |
| `shiftLength` | String | ✅ Yes | Shift length | "8hr", "10hr", "12hr" |
| `weekendsRequired` | Boolean | No | Weekends required? | true/false |
| `onCallRequired` | Boolean | No | On-call required? | true/false |
| `contractType` | String | ✅ Yes | Contract type | "Travel", "Locum", "Per Diem", "Seasonal" |
| `jTypeString` | String | ✅ Yes | Job type | "Locum", "Permanent", "Both" |
| `numberOfOpenings` | Number | No | Number of openings | 3 |

### 5. Assignment Details

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `startDate` | Date | ✅ Yes | Start date | DateTime |
| `endDate` | Date | No | End date | DateTime |
| `contractWeeks` | Number | ✅ Yes | Contract length in weeks | 13 |
| `assignmntDates` | String | No | Human-readable dates | "Jan 15 - Apr 15" |
| `orientationLength` | String | No | Orientation duration | "1 week" |
| `weeklyHours` | Number | ✅ Yes | Expected weekly hours | 36, 40, 48 |

### 6. Pay Details (Critical for Travel Healthcare)

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `totalWeeklyPay` | Number | ✅ Yes | Total weekly compensation | 3000 |
| `hourlyTaxableRate` | Number | ✅ Yes | Hourly taxable rate | 35 |
| `weeklyHousingStipend` | Number | No | Weekly housing stipend | 1500 |
| `weeklyMealsStipend` | Number | No | Weekly meals stipend | 400 |
| `overtimeRate` | Number | No | Overtime hourly rate | 52.50 |
| `callPay` | Number | No | Call pay rate | 5 |
| `chargePay` | Number | No | Charge nurse differential | 3 |
| `completionBonus` | Number | No | Contract completion bonus | 2000 |
| `travelReimbursement` | Number | No | Travel reimbursement amount | 500 |
| `guaranteedHours` | Boolean | No | Hours guaranteed? | true/false |

### 7. Clinician Requirements

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `licenseType` | String | ✅ Yes | License type | "RN", "LPN", "CRNA", "RT" |
| `statesAccepted` | Array | ✅ Yes | States accepted | ["MN", "WI", "IA"] |
| `compactAccepted` | Boolean | No | Compact license OK? | true/false |
| `minimumYearsExperience` | Number | ✅ Yes | Min years experience | 2 |
| `specificExperienceRequired` | String | No | Specific requirements | "Level I Trauma experience required" |
| `backgroundCheckRequired` | Boolean | No | Background check? | true/false |
| `drugScreenRequired` | Boolean | No | Drug screen? | true/false |

### 8. Vaccine Requirements

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `fluShotRequired` | Boolean | No | Flu shot required? | true/false |
| `covidVaccineRequired` | Boolean | No | COVID vaccine required? | true/false |
| `tbTestRequired` | Boolean | No | TB/PPD required? | true/false |
| `hepBRequired` | Boolean | No | Hep B required? | true/false |
| `tdapRequired` | Boolean | No | Tdap required? | true/false |
| `varicellaRequired` | Boolean | No | Varicella required? | true/false |
| `mmrRequired` | Boolean | No | MMR required? | true/false |

### 9. Job Description (Long Form)

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `jobDescText` | String | ✅ Yes | Full job description | "We are seeking an experienced ICU RN..." |
| `unitSize` | String | No | Unit size | "24-bed ICU" |
| `patientRatio` | String | No | Nurse:patient ratio | "1:2" |
| `emrSystem` | String | No | EMR system used | "Epic", "Cerner", "Meditech" |
| `teamStructure` | String | No | Team description | "RT always available, charge supports nurses" |
| `uniqueAspects` | String | No | Special features | "Level I Trauma Center, Magnet Hospital" |

### 10. Metadata & Platform Management

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `reqNum` | String | No | Requisition number | "REQ-2025-001" |
| `jStatusString` | String | ✅ Yes | Job status | "Active", "Filled", "Expired", "Draft" |
| `durationString` | String | No | Human-readable duration | "13 weeks" |
| `postingDuration` | Number | No | Auto-expire in days | 30, 60, 90 |
| `autoRenew` | Boolean | No | Auto-renew listing? | true/false |
| `isFeatured` | Boolean | No | Featured job? | true/false |
| `isVisible` | Boolean | No | Publicly visible? | true/false |
| `emBOOL` | Boolean | No | Emergent/urgent? | true/false |
| `viewCount` | Number | No | Number of views | 245 |
| `applicationCount` | Number | No | Applications received | 12 |

### 11. Client/Poster Information

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `clientID` | String | ✅ Yes | Posting user's ID | Pointer to _User |
| `clientName` | String | No | Poster's name | "John Doe" |
| `clientEmail` | String | No | Poster's email | "john@facility.com" |
| `clientSilo` | String | ✅ Yes | User silo type | "jclJobs" |

### 12. Compliance & Legal

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `wageTransparencyCompliant` | Boolean | No | Pay transparency met? | true/false |
| `isEOE` | Boolean | No | Equal opportunity employer? | true/false |
| `licenseVerificationConsent` | Boolean | No | License verification OK? | true/false |
| `termsAccepted` | Boolean | ✅ Yes | Terms of posting accepted? | true/false |
| `termsAcceptedDate` | Date | No | When terms accepted | DateTime |

---

## PHASE 2: Advanced Features (Future Enhancement)

### 13. Media & Rich Content

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `videoIntroUrl` | String | No | Video introduction URL | "https://..." |
| `benefitsPdfUrl` | String | No | Benefits PDF | "https://..." |
| `compliancePacketUrl` | String | No | Compliance packet | "https://..." |
| `facilityPhotos` | Array | No | Facility images | ["url1", "url2"] |

### 14. Reviews & Ratings

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `employerRating` | Number | No | Average rating (1-5) | 4.5 |
| `reviewCount` | Number | No | Number of reviews | 23 |
| `reviewsArray` | Array | No | Array of review objects | [...] |

### 15. ATS Integration

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `atsJobId` | String | No | External ATS job ID | "ATS-12345" |
| `atsSystem` | String | No | ATS system name | "Bullhorn", "JobDiva" |
| `zapierWebhook` | String | No | Zapier webhook URL | "https://..." |

### 16. Application Management

| Field Name | Type | Required | Description | Example |
|------------|------|----------|-------------|---------|
| `quickApplyEnabled` | Boolean | No | Quick apply available? | true/false |
| `applicationUrl` | String | No | External application URL | "https://..." |
| `screeningQuestions` | Array | No | Custom screening questions | [...] |
| `autoRejectCriteria` | Object | No | Auto-rejection rules | {...} |

---

## Parse Server Configuration

### Class-Level Permissions (CLP)
```
{
  "get": { "*": true },              // Public can read
  "find": { "*": true },             // Public can search
  "create": { "requiresAuthentication": true },  // Must be logged in
  "update": { "requiresAuthentication": true },
  "delete": { "requiresAuthentication": true },
  "addField": { "requiresAuthentication": false }
}
```

### Row-Level Security (ACL)
Each job should have ACL:
- Read: Public (everyone can view)
- Write: Only the user who created it (clientID)

### Indexes (for performance)
Create indexes on these frequently queried fields:
- `jobState` (search by location)
- `jclProf` (search by profession)
- `specialty` (search by specialty)
- `contractType` (filter by type)
- `startDate` (sort by date)
- `jStatusString` (filter active jobs)
- `isFeatured` (show featured first)
- `clientID` (employer dashboard queries)

---

## Migration Strategy

### Step 1: Add New Fields to Parse Server
1. Log into Back4App dashboard
2. Navigate to `jclJobs` class
3. Add new columns (fields will auto-create on first use, or manually add via dashboard)

### Step 2: Update Flutter App (Optional)
The Flutter app can continue working with existing fields. New fields are optional enhancements.

### Step 3: Build Website Forms
WordPress job posting forms will include all Phase 1 fields.

### Step 4: Data Validation
Implement server-side validation (Cloud Code) to ensure:
- Required fields are present
- Email formats are valid
- Phone numbers are formatted correctly
- Dates are logical (startDate < endDate)
- Pay rates are positive numbers

---

## Sample Job Object (JSON)

```json
{
  "objectId": "abc123",
  "facilityName": "Mayo Clinic",
  "facilityDepartment": "Cardiovascular ICU",
  "facilityType": "Hospital",
  "jobCity": "Rochester",
  "jobState": "MN",
  "zipCode": "55901",
  "facContactName": "Jane Smith",
  "facContactEmail": "jane.smith@mayo.edu",
  "facContactPhone": "507-284-2511",
  "jobTitle": "Travel ICU RN - Cardiovascular",
  "jclProf": "RN",
  "specialty": "ICU",
  "credentialsRequired": ["RN License", "BLS", "ACLS"],
  "shiftType": "Nights",
  "shiftLength": "12hr",
  "contractType": "Travel",
  "jTypeString": "Locum",
  "numberOfOpenings": 2,
  "startDate": {"__type": "Date", "iso": "2025-03-01T00:00:00.000Z"},
  "contractWeeks": 13,
  "weeklyHours": 36,
  "totalWeeklyPay": 3200,
  "hourlyTaxableRate": 38,
  "weeklyHousingStipend": 1500,
  "weeklyMealsStipend": 400,
  "guaranteedHours": true,
  "licenseType": "RN",
  "statesAccepted": ["MN", "WI", "IA", "ND", "SD"],
  "compactAccepted": true,
  "minimumYearsExperience": 2,
  "specificExperienceRequired": "CVICU experience required. Will consider general ICU with cardiac drips.",
  "jobDescText": "We are seeking an experienced ICU RN for our 24-bed Cardiovascular ICU...",
  "unitSize": "24-bed CVICU",
  "patientRatio": "1:2",
  "emrSystem": "Epic",
  "jStatusString": "Active",
  "isFeatured": false,
  "isVisible": true,
  "emBOOL": false,
  "clientID": "xyz789",
  "clientSilo": "jclJobs",
  "termsAccepted": true,
  "isEOE": true,
  "createdAt": {"__type": "Date", "iso": "2025-01-15T10:30:00.000Z"},
  "updatedAt": {"__type": "Date", "iso": "2025-01-15T10:30:00.000Z"}
}
```

---

## Notes
- All existing jobs will continue to work with current fields
- New fields are **additive** (backward compatible)
- Phase 1 fields provide a professional, competitive job board
- Phase 2 fields can be added incrementally based on user feedback
