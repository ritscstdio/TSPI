-- Migration: Add other income sources and separate beneficiary columns

ALTER TABLE `members_information`
  ADD COLUMN `other_income_source_1` VARCHAR(255) DEFAULT NULL AFTER `business_address`,
  ADD COLUMN `other_income_source_2` VARCHAR(255) DEFAULT NULL AFTER `other_income_source_1`,
  ADD COLUMN `other_income_source_3` VARCHAR(255) DEFAULT NULL AFTER `other_income_source_2`,
  ADD COLUMN `other_income_source_4` VARCHAR(255) DEFAULT NULL AFTER `other_income_source_3`,
  ADD COLUMN `beneficiary_1_firstname` VARCHAR(100) DEFAULT NULL AFTER `spouse_birthdate`,
  ADD COLUMN `beneficiary_1_lastname` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_1_firstname`,
  ADD COLUMN `beneficiary_1_dependent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `beneficiary_1_lastname`,
  ADD COLUMN `beneficiary_2_firstname` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_1_dependent`,
  ADD COLUMN `beneficiary_2_lastname` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_2_firstname`,
  ADD COLUMN `beneficiary_2_dependent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `beneficiary_2_lastname`,
  ADD COLUMN `beneficiary_3_firstname` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_2_dependent`,
  ADD COLUMN `beneficiary_3_lastname` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_3_firstname`,
  ADD COLUMN `beneficiary_3_dependent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `beneficiary_3_lastname`,
  ADD COLUMN `beneficiary_4_firstname` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_3_dependent`,
  ADD COLUMN `beneficiary_4_lastname` VARCHAR(100) DEFAULT NULL AFTER `beneficiary_4_firstname`,
  ADD COLUMN `beneficiary_4_dependent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `beneficiary_4_lastname`; 