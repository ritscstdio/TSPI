-- Migration: Add other income sources and full beneficiary detail columns
ALTER TABLE `members_information`
  -- Other income sources
  ADD COLUMN `other_income_source_1` VARCHAR(255) DEFAULT NULL AFTER `business_address`,
  ADD COLUMN `other_income_source_2` VARCHAR(255) DEFAULT NULL AFTER `other_income_source_1`,
  ADD COLUMN `other_income_source_3` VARCHAR(255) DEFAULT NULL AFTER `other_income_source_2`,
  ADD COLUMN `other_income_source_4` VARCHAR(255) DEFAULT NULL AFTER `other_income_source_3`,
  -- Beneficiary 1
  ADD COLUMN `beneficiary_fn_1` VARCHAR(100) DEFAULT NULL AFTER `spouse_birthdate`,
  ADD COLUMN `beneficiary_ln_1` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_fn_1`,
  ADD COLUMN `beneficiary_mi_1` VARCHAR(10) DEFAULT NULL AFTER `beneficiary_ln_1`,
  ADD COLUMN `beneficiary_birthdate_1` DATE DEFAULT NULL AFTER `beneficiary_mi_1`,
  ADD COLUMN `beneficiary_gender_1` CHAR(1) DEFAULT NULL AFTER `beneficiary_birthdate_1`,
  ADD COLUMN `beneficiary_relationship_1` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_gender_1`,
  ADD COLUMN `beneficiary_dependent_1` TINYINT(1) NOT NULL DEFAULT 0 AFTER `beneficiary_relationship_1`,
  -- Beneficiary 2
  ADD COLUMN `beneficiary_fn_2` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_dependent_1`,
  ADD COLUMN `beneficiary_ln_2` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_fn_2`,
  ADD COLUMN `beneficiary_mi_2` VARCHAR(10) DEFAULT NULL AFTER `beneficiary_ln_2`,
  ADD COLUMN `beneficiary_birthdate_2` DATE DEFAULT NULL AFTER `beneficiary_mi_2`,
  ADD COLUMN `beneficiary_gender_2` CHAR(1) DEFAULT NULL AFTER `beneficiary_birthdate_2`,
  ADD COLUMN `beneficiary_relationship_2` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_gender_2`,
  ADD COLUMN `beneficiary_dependent_2` TINYINT(1) NOT NULL DEFAULT 0 AFTER `beneficiary_relationship_2`,
  -- Beneficiary 3
  ADD COLUMN `beneficiary_fn_3` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_dependent_2`,
  ADD COLUMN `beneficiary_ln_3` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_fn_3`,
  ADD COLUMN `beneficiary_mi_3` VARCHAR(10) DEFAULT NULL AFTER `beneficiary_ln_3`,
  ADD COLUMN `beneficiary_birthdate_3` DATE DEFAULT NULL AFTER `beneficiary_mi_3`,
  ADD COLUMN `beneficiary_gender_3` CHAR(1) DEFAULT NULL AFTER `beneficiary_birthdate_3`,
  ADD COLUMN `beneficiary_relationship_3` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_gender_3`,
  ADD COLUMN `beneficiary_dependent_3` TINYINT(1) NOT NULL DEFAULT 0 AFTER `beneficiary_relationship_3`,
  -- Beneficiary 4
  ADD COLUMN `beneficiary_fn_4` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_dependent_3`,
  ADD COLUMN `beneficiary_ln_4` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_fn_4`,
  ADD COLUMN `beneficiary_mi_4` VARCHAR(10) DEFAULT NULL AFTER `beneficiary_ln_4`,
  ADD COLUMN `beneficiary_birthdate_4` DATE DEFAULT NULL AFTER `beneficiary_mi_4`,
  ADD COLUMN `beneficiary_gender_4` CHAR(1) DEFAULT NULL AFTER `beneficiary_birthdate_4`,
  ADD COLUMN `beneficiary_relationship_4` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_gender_4`,
  ADD COLUMN `beneficiary_dependent_4` TINYINT(1) NOT NULL DEFAULT 0 AFTER `beneficiary_relationship_4`; 