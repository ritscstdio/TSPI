-- Database structure updates for TSPI membership system

-- 1. Drop the problematic recursive trigger
DROP TRIGGER IF EXISTS `update_status_on_approval`;

-- 2. Make sure secretary_approved is an ENUM like the other approval fields
ALTER TABLE `members_information` 
MODIFY COLUMN `secretary_approved` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending';

-- 3. Create a proper BEFORE UPDATE trigger that sets status directly
DELIMITER $$
CREATE TRIGGER `before_member_approval` BEFORE UPDATE ON `members_information` 
FOR EACH ROW 
BEGIN
    -- If Secretary has approved and both officers approved, set status to approved
    IF (NEW.io_approved = 'approved' AND NEW.lo_approved = 'approved' AND 
        NEW.secretary_approved = 'approved' AND NEW.status = 'pending') THEN
        SET NEW.status = 'approved';
    END IF;
    
    -- If any approver has rejected, set status to rejected
    IF (NEW.io_approved = 'rejected' OR NEW.lo_approved = 'rejected' OR 
        NEW.secretary_approved = 'rejected') AND NEW.status = 'pending' THEN
        SET NEW.status = 'rejected';
    END IF;
END$$
DELIMITER ;

-- 4. Show success message
SELECT 'Database structures updated successfully' as message; 