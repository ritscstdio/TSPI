-- Add secretary approval fields to members_information table
-- This migration adds columns needed for secretary final approval process

ALTER TABLE members_information
ADD COLUMN secretary_approved VARCHAR(20) DEFAULT 'pending' AFTER lo_approval_date,
ADD COLUMN secretary_name VARCHAR(100) NULL AFTER secretary_approved,
ADD COLUMN secretary_signature VARCHAR(255) NULL AFTER secretary_name,
ADD COLUMN secretary_comments TEXT NULL AFTER secretary_signature,
ADD COLUMN secretary_approval_date TIMESTAMP NULL AFTER secretary_comments;

-- Add index to improve query performance
ALTER TABLE members_information
ADD INDEX idx_secretary_approved (secretary_approved);

-- Add comment to table for documentation
ALTER TABLE members_information
COMMENT = 'Stores member application data with approvals from IO, LO, and secretary'; 