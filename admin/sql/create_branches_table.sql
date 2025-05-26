-- SQL to create branches table

CREATE TABLE `branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(100) NOT NULL,
  `branch_code` varchar(50) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `center_no` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_branch_code` (`branch_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert some sample branches
INSERT INTO `branches` (`branch_name`, `branch_code`, `address`, `contact_number`, `center_no`) VALUES
('Main Branch', 'BR001', '123 Main Street, Metro Manila', '09123456789', '001'),
('North Branch', 'BR002', '456 North Avenue, Quezon City', '09234567890', '002'),
('South Branch', 'BR003', '789 South Road, Makati City', '09345678901', '003'),
('East Branch', 'BR004', '101 East Boulevard, Pasig City', '09456789012', '004'),
('West Branch', 'BR005', '202 West Highway, Mandaluyong City', '09567890123', '005'); 