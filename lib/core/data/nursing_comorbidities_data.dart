/// Comprehensive list of nursing clinical scenarios
/// These clinical scenarios are available for nurses to select during patient assessment
class NursingComorbidities {
  /// Default nursing clinical scenarios (None at top, rest alphabetically sorted)
  static const List<String> defaultComorbidities = [
    'None',
    'Alcohol use disorder',
    'Alzheimer\'s disease / dementia',
    'Anemia',
    'Anxiety',
    'Arthritis',
    'Asthma',
    'Atrial fibrillation',
    'Bipolar disorder',
    'Bleeding disorder',
    'Blindness / visual impairment',
    'Cancer (active)',
    'Cancer (history of)',
    'Chronic kidney disease (CKD)',
    'Chronic obstructive pulmonary disease (COPD)',
    'Chronic pain',
    'Cirrhosis',
    'Congestive heart failure (CHF)',
    'Constipation',
    'Coronary artery disease (CAD)',
    'Depression',
    'Diabetes mellitus type 1',
    'Diabetes mellitus type 2',
    'Diabetic neuropathy',
    'Diabetic retinopathy',
    'Drug use disorder',
    'Eating disorder',
    'End-stage renal disease (ESRD)',
    'Epilepsy / seizure disorder',
    'Falls (history of)',
    'Fatty liver disease',
    'Gastroesophageal reflux disease (GERD)',
    'Glaucoma',
    'Hearing impairment',
    'Heart failure',
    'Hepatitis B',
    'Hepatitis C',
    'HIV',
    'Hyperlipidemia',
    'Hypertension',
    'Hyperthyroidism',
    'Hypothyroidism',
    'Incontinence (bowel)',
    'Incontinence (urinary)',
    'Intellectual disability',
    'Kidney disease',
    'Liver disease',
    'Malnutrition',
    'Mobility impairment',
    'Multiple sclerosis',
    'Neuropathy',
    'Obesity',
    'Obstructive sleep apnea (OSA)',
    'Osteoarthritis',
    'Osteoporosis',
    'Parkinson\'s disease',
    'Peripheral vascular disease (PVD)',
    'Pressure injuries (history of)',
    'Pulmonary disease (chronic)',
    'Rheumatoid arthritis',
    'Schizophrenia',
    'Sickle cell disease',
    'Smoking (current)',
    'Smoking (former)',
    'Stroke (history of)',
    'Thyroid disease',
    'Urinary retention',
    'Urinary tract infections (recurrent)',
    'Vision impairment',
  ];

  /// Get all comorbidities including user's custom comorbidities (alphabetically sorted)
  static List<String> getAllComorbidities(List<String> customComorbidities) {
    final allComorbidities = [...defaultComorbidities, ...customComorbidities];
    allComorbidities.sort((a, b) => a.toLowerCase().compareTo(b.toLowerCase()));
    return allComorbidities;
  }

  /// Filter comorbidities by search query
  static List<String> filterComorbidities(List<String> comorbidities, String query) {
    if (query.isEmpty) return comorbidities;
    final lowerQuery = query.toLowerCase();
    return comorbidities
        .where((comorbidity) => comorbidity.toLowerCase().contains(lowerQuery))
        .toList();
  }
}
