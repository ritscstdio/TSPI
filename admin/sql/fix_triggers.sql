-- First, drop the existing trigger that causes recursive update errors
DROP TRIGGER IF EXISTS `update_status_on_approval`;

-- Create a new trigger that sets the status directly in the same operation
-- This uses BEFORE UPDATE to modify the NEW values before they're actually saved
DELIMITER $$
CREATE TRIGGER `before_member_update` BEFORE UPDATE ON `members_information` 
FOR EACH ROW 
BEGIN
    -- If both officers approved, automatically set status to approved
    IF (NEW.io_approved = 'approved' AND NEW.lo_approved = 'approved' AND NEW.secretary_approved = 'approved' AND OLD.status = 'pending') THEN
        SET NEW.status = 'approved';
    END IF;
END$$
DELIMITER ;

-- Show confirmation message
SELECT 'Triggers updated successfully' AS message; 