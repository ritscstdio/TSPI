-- Fix LO approval status where LO name and signature are present
-- but lo_approved is not set to 'approved'

UPDATE members_information
SET lo_approved = 'approved'
WHERE lo_name IS NOT NULL 
  AND lo_signature IS NOT NULL 
  AND (lo_approved = '' OR lo_approved IS NULL);

-- Display results
SELECT id, first_name, last_name, 
       io_name, io_approved, 
       lo_name, lo_approved, 
       secretary_name, secretary_approved
FROM members_information
WHERE lo_name IS NOT NULL AND lo_signature IS NOT NULL; 