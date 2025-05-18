-- Migration: Add status column for application tracking
ALTER TABLE `members_information`
  ADD COLUMN `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending' AFTER `beneficiary_signature`; 