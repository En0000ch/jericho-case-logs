/**
 * Parse Server Schema Migration Script
 * Adds Phase 1 fields to jclJobs class
 *
 * This script creates a sample job with all Phase 1 fields,
 * which will automatically create the columns in Parse Server
 */

const Parse = require('parse/node');

// Initialize Parse
Parse.initialize(
  '9Zso4z2xN8gTLfauAqShE7gMkYAaDav3HoTGFimF', // Application ID
  'fTLpvY4OQG1kj1Njq7rz6WqcFuN0HQT70mMI90SQ', // JavaScript Key
  'KJXMXgyqXKePXZeSp3jKFOBlUqX1UVjUYWia3AM9'  // Master Key
);
Parse.serverURL = 'https://parseapi.back4app.com';
Parse.masterKey = 'KJXMXgyqXKePXZeSp3jKFOBlUqX1UVjUYWia3AM9';

async function migrateSchema() {
  console.log('🚀 Starting Parse Server Schema Migration...\n');

  try {
    // Create a sample job with ALL Phase 1 fields
    const Job = Parse.Object.extend('jclJobs');
    const sampleJob = new Job();

    // EXISTING FIELDS (keep these)
    sampleJob.set('jclProf', 'CRNA');
    sampleJob.set('jobTitle', 'Travel CRNA - Cardiovascular OR');
    sampleJob.set('reqNum', 'SAMPLE-001');
    sampleJob.set('assignmntDates', 'March 1 - May 31, 2025');
    sampleJob.set('jobCity', 'Rochester');
    sampleJob.set('jobState', 'MN');
    sampleJob.set('facName', 'Mayo Clinic');
    sampleJob.set('facContactName', 'Jane Smith');
    sampleJob.set('facContactPhone', '507-284-2511');
    sampleJob.set('facContactEmail', 'recruitment@mayo.edu');
    sampleJob.set('jTypeString', 'Locum');
    sampleJob.set('jStatusString', 'Active');
    sampleJob.set('durationString', '13 weeks');
    sampleJob.set('jobDescText', 'We are seeking an experienced CRNA for our Cardiovascular OR. This is a fast-paced, high-acuity environment with complex cardiac cases including CABG, valve replacements, and thoracic procedures.');
    sampleJob.set('clientID', 'SAMPLE_CLIENT_ID');
    sampleJob.set('clientName', 'Mayo Clinic Recruitment');
    sampleJob.set('clientEmail', 'recruitment@mayo.edu');
    sampleJob.set('startDate', new Date('2025-03-01'));
    sampleJob.set('endDate', new Date('2025-05-31'));
    sampleJob.set('emBOOL', false);

    // NEW PHASE 1 FIELDS - Employer/Facility
    sampleJob.set('facilityDepartment', 'Cardiovascular Operating Room');
    sampleJob.set('facilityType', 'Hospital');
    sampleJob.set('facilityWebsite', 'https://www.mayoclinic.org');
    sampleJob.set('facilityLogoUrl', '');
    sampleJob.set('facilityEIN', '');

    // NEW - Location
    sampleJob.set('zipCode', '55901');
    sampleJob.set('multipleLocations', false);
    sampleJob.set('housingProvided', false);
    sampleJob.set('housingStipend', 1500);

    // NEW - Contact
    sampleJob.set('preferredContactMethod', 'Email');
    sampleJob.set('contactVisibility', 'Public');

    // NEW - Job Basics
    sampleJob.set('specialty', 'Cardiovascular OR');
    sampleJob.set('discipline', 'CRNA');
    sampleJob.set('credentialsRequired', ['CRNA License', 'BLS', 'ACLS', 'PALS']);
    sampleJob.set('credentialsPreferred', ['Cardiac certification']);
    sampleJob.set('shiftType', 'Days');
    sampleJob.set('shiftLength', '10hr');
    sampleJob.set('weekendsRequired', false);
    sampleJob.set('onCallRequired', true);
    sampleJob.set('contractType', 'Travel');
    sampleJob.set('numberOfOpenings', 2);

    // NEW - Assignment Details
    sampleJob.set('contractWeeks', 13);
    sampleJob.set('orientationLength', '1 week');
    sampleJob.set('weeklyHours', 40);

    // NEW - Pay Details (CRITICAL)
    sampleJob.set('totalWeeklyPay', 3500);
    sampleJob.set('hourlyTaxableRate', 45);
    sampleJob.set('weeklyHousingStipend', 1500);
    sampleJob.set('weeklyMealsStipend', 400);
    sampleJob.set('overtimeRate', 67.50);
    sampleJob.set('callPay', 5);
    sampleJob.set('chargePay', 0);
    sampleJob.set('completionBonus', 2500);
    sampleJob.set('travelReimbursement', 500);
    sampleJob.set('guaranteedHours', true);

    // NEW - Requirements
    sampleJob.set('licenseType', 'CRNA');
    sampleJob.set('statesAccepted', ['MN', 'WI', 'IA', 'ND', 'SD']);
    sampleJob.set('compactAccepted', false);
    sampleJob.set('minimumYearsExperience', 2);
    sampleJob.set('specificExperienceRequired', 'Cardiac OR experience required. Experience with CABG, valve procedures, and cardiac drips essential.');
    sampleJob.set('backgroundCheckRequired', true);
    sampleJob.set('drugScreenRequired', true);

    // NEW - Vaccine Requirements
    sampleJob.set('fluShotRequired', true);
    sampleJob.set('covidVaccineRequired', true);
    sampleJob.set('tbTestRequired', true);
    sampleJob.set('hepBRequired', false);
    sampleJob.set('tdapRequired', true);
    sampleJob.set('varicellaRequired', false);
    sampleJob.set('mmrRequired', false);

    // NEW - Job Description Details
    sampleJob.set('unitSize', '12 OR suites');
    sampleJob.set('patientRatio', '1:1 (CRNA to patient)');
    sampleJob.set('emrSystem', 'Epic');
    sampleJob.set('teamStructure', 'CRNAs work independently with MD oversight. Strong perfusion team support.');
    sampleJob.set('uniqueAspects', 'Level I Cardiac Care, Magnet Hospital, Teaching Environment');

    // NEW - Metadata
    sampleJob.set('postingDuration', 60);
    sampleJob.set('autoRenew', false);
    sampleJob.set('isFeatured', false);
    sampleJob.set('isVisible', true);
    sampleJob.set('viewCount', 0);
    sampleJob.set('applicationCount', 0);

    // NEW - Client Info
    sampleJob.set('clientSilo', 'jclJobs');

    // NEW - Compliance
    sampleJob.set('wageTransparencyCompliant', true);
    sampleJob.set('isEOE', true);
    sampleJob.set('licenseVerificationConsent', true);
    sampleJob.set('termsAccepted', true);
    sampleJob.set('termsAcceptedDate', new Date());

    // Save the sample job
    const result = await sampleJob.save(null, { useMasterKey: true });

    console.log('✅ Sample job created successfully!');
    console.log(`   ObjectId: ${result.id}`);
    console.log('\n📊 Schema Migration Complete!');
    console.log('   All Phase 1 fields have been added to jclJobs class\n');

    // List all the fields that were added
    console.log('📋 New Fields Added:');
    console.log('   Employer/Facility: facilityDepartment, facilityType, facilityWebsite, facilityLogoUrl, facilityEIN');
    console.log('   Location: zipCode, multipleLocations, housingProvided, housingStipend');
    console.log('   Contact: preferredContactMethod, contactVisibility');
    console.log('   Job Basics: specialty, discipline, credentialsRequired, credentialsPreferred, shiftType, shiftLength, weekendsRequired, onCallRequired, contractType, numberOfOpenings');
    console.log('   Assignment: contractWeeks, orientationLength, weeklyHours');
    console.log('   Pay: totalWeeklyPay, hourlyTaxableRate, weeklyHousingStipend, weeklyMealsStipend, overtimeRate, callPay, chargePay, completionBonus, travelReimbursement, guaranteedHours');
    console.log('   Requirements: licenseType, statesAccepted, compactAccepted, minimumYearsExperience, specificExperienceRequired, backgroundCheckRequired, drugScreenRequired');
    console.log('   Vaccines: fluShotRequired, covidVaccineRequired, tbTestRequired, hepBRequired, tdapRequired, varicellaRequired, mmrRequired');
    console.log('   Details: unitSize, patientRatio, emrSystem, teamStructure, uniqueAspects');
    console.log('   Metadata: postingDuration, autoRenew, isFeatured, isVisible, viewCount, applicationCount, clientSilo');
    console.log('   Compliance: wageTransparencyCompliant, isEOE, licenseVerificationConsent, termsAccepted, termsAcceptedDate');
    console.log('\n🎉 Your Parse Server is now ready for the professional job board!');

    // Now let's verify the schema was created
    const schema = await new Parse.Schema('jclJobs').get({ useMasterKey: true });
    console.log('\n✅ Verified: jclJobs schema now has', Object.keys(schema.fields).length, 'fields');

  } catch (error) {
    console.error('❌ Error during migration:', error);
    throw error;
  }
}

// Run the migration
migrateSchema()
  .then(() => {
    console.log('\n✅ Migration completed successfully!');
    process.exit(0);
  })
  .catch((error) => {
    console.error('\n❌ Migration failed:', error);
    process.exit(1);
  });
