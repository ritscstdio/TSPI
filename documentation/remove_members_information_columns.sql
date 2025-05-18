-- Migration Rollback: Remove other income and old beneficiary columns
ALTER TABLE `members_information`
  DROP COLUMN `other_income_source_1`,
  DROP COLUMN `other_income_source_2`,
  DROP COLUMN `other_income_source_3`,
  DROP COLUMN `other_income_source_4`,
  DROP COLUMN `beneficiary_1_firstname`,
  DROP COLUMN `beneficiary_1_lastname`,
  DROP COLUMN `beneficiary_1_dependent`,
  DROP COLUMN `beneficiary_2_firstname`,
  DROP COLUMN `beneficiary_2_lastname`,
  DROP COLUMN `beneficiary_2_dependent`,
  DROP COLUMN `beneficiary_3_firstname`,
  DROP COLUMN `beneficiary_3_lastname`,
  DROP COLUMN `beneficiary_3_dependent`,
  DROP COLUMN `beneficiary_4_firstname`,
  DROP COLUMN `beneficiary_4_lastname`,
  DROP COLUMN `beneficiary_4_dependent`; 