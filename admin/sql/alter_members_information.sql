-- SQL to alter members_information table for IO/LO approval workflow

-- Add columns for Insurance Officer approval
ALTER TABLE `members_information` 
ADD COLUMN `io_approved` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending' AFTER `status`,
ADD COLUMN `io_name` VARCHAR(255) DEFAULT NULL AFTER `io_approved`,
ADD COLUMN `io_signature` VARCHAR(255) DEFAULT NULL AFTER `io_name`,
ADD COLUMN `io_approval_date` TIMESTAMP NULL DEFAULT NULL AFTER `io_signature`,
ADD COLUMN `io_notes` TEXT DEFAULT NULL AFTER `io_approval_date`;

-- Add columns for Loan Officer approval
ALTER TABLE `members_information` 
ADD COLUMN `lo_approved` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending' AFTER `io_notes`,
ADD COLUMN `lo_name` VARCHAR(255) DEFAULT NULL AFTER `lo_approved`,
ADD COLUMN `lo_signature` VARCHAR(255) DEFAULT NULL AFTER `lo_name`,
ADD COLUMN `lo_approval_date` TIMESTAMP NULL DEFAULT NULL AFTER `lo_signature`,
ADD COLUMN `lo_notes` TEXT DEFAULT NULL AFTER `lo_approval_date`;

-- Add a trigger to update the status column when both IO and LO approve
DELIMITER //
CREATE TRIGGER update_status_on_approval
AFTER UPDATE ON `members_information`
FOR EACH ROW
BEGIN
    IF (NEW.io_approved = 'approved' AND NEW.lo_approved = 'approved' AND NEW.status = 'pending') THEN
        UPDATE `members_information` SET `status` = 'approved' WHERE `id` = NEW.id;
    END IF;
END //
DELIMITER ; 