-- Add administrator approval fields to members_information table
-- This migration adds columns needed for administrator final approval process

ALTER TABLE members_information
ADD COLUMN admin_approved VARCHAR(20) DEFAULT 'pending' AFTER lo_approval_date,
ADD COLUMN admin_name VARCHAR(100) NULL AFTER admin_approved,
ADD COLUMN admin_signature VARCHAR(255) NULL AFTER admin_name,
ADD COLUMN admin_comments TEXT NULL AFTER admin_signature,
ADD COLUMN admin_approval_date TIMESTAMP NULL AFTER admin_comments;

-- Add index to improve query performance
ALTER TABLE members_information
ADD INDEX idx_admin_approved (admin_approved);

-- Add comment to table for documentation
ALTER TABLE members_information
COMMENT = 'Stores member application data with approvals from IO, LO, and administrator'; 