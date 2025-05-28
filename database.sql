-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2025 at 03:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tspi_blog`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrators`
--

CREATE TABLE `administrators` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','moderator','insurance_officer','loan_officer','secretary') DEFAULT 'moderator',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` (`id`, `username`, `password`, `name`, `email`, `role`, `created_at`, `profile_picture`) VALUES
(1, 'admin', '$2y$10$e3B3u4yaMGw77ausbK6rYOnvVOO4AuhuCx8clnC9ZvQgSntOGCN6C', 'Administrator', 'admin@tspi.org', 'admin', '2025-05-08 14:26:40', 'user_1_1747300417.png'),
(2, 'mariaclara.r', '$2y$10$0dJv9S5plVN6HuHRUh2s6O6OEV3xnaUjuv72FZT4YxMZ2zk8JvfcS', 'Maria Clara Reyes', 'mariaclara.r@email.com', 'insurance_officer', '2025-05-23 23:10:06', NULL),
(3, 'joserizal.m', '$2y$10$yEV4SUrwkPeXVCqWsW0taeQcEAkm4JHdV/.PW3Jp2hQiobm.vTeR.', 'Jose Rizal Mercado', 'joserizal.m@email.com', 'loan_officer', '2025-05-23 23:10:27', NULL),
(4, 'MariaS_23', '$2y$10$7QzGVo1SCTbWCBX02BlGmundxl4cRyPVNM9i11VQ5MUL/ory7.Lqe', 'Maria Santos', 'mariasantos.ph@email.com', 'secretary', '2025-05-27 06:45:34', NULL),
(5, 'xtine_r', '$2y$10$/z.XrEKKNK0Fu.3/A0X.Puoe5wiEW3RXghUB.RZxWiBRJXftKbBTC', 'Christine Reyes', 'christine.reyes.phl@email.com', 'moderator', '2025-05-27 20:39:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `center_no` varchar(3) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `address_link` text NOT NULL,
  `contact_num` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `center_no`, `branch`, `region`, `address`, `address_link`, `contact_num`) VALUES
(1, '001', 'TSPI HEAD OFFICE', 'HEAD OFFICE', '2363 Antipolo St. Guadalupe Nuevo Makati City, Philippines', 'https://www.google.com/maps?q=2363+Antipolo+St.+Guadalupe+Nuevo+Makati+City,+Philippines', '(02) 8 403 8625 loc 255'),
(2, '002', 'BATAC', 'REGION I - ILOCOS NORTE', 'City Pearl Complex, National Hi-way #7 Caunayan, Batac City', 'https://www.google.com/maps?q=City+Pearl+Complex,+National+Hi-way+%237+Caunayan,+Batac+City', '(0915)123-3381'),
(3, '003', 'PINILI', 'REGION I - ILOCOS NORTE', 'National Highway, Brgy. Darat, Pinili, Ilocos Norte', 'https://www.google.com/maps?q=National+Highway,+Brgy.+Darat,+Pinili,+Ilocos+Norte', '09773086322'),
(4, '004', 'LAOAG', 'REGION I - ILOCOS NORTE', 'Jomel 2 Bldg., P. Gomez St., Brgy. 23, Laoag City', 'https://www.google.com/maps?q=Jomel+2+Bldg.,+P.+Gomez+St.,+Brgy.+23,+Laoag+City', '(0949)700-6154'),
(5, '005', 'DINGRAS', 'REGION I - ILOCOS NORTE', 'Castro St., Brgy. Albano, Dingras, Ilocos Norte', 'https://www.google.com/maps?q=Castro+St.,+Brgy.+Albano,+Dingras,+Ilocos+Norte', '(0948) 107-9574'),
(6, '006', 'CANDON', 'REGION I - ILOCOS SUR', 'Cassy and J Real State, National Highway, Tablac Candon City, Ilocos Sur', 'https://www.google.com/maps?q=Cassy+and+J+Real+State,+National+Highway,+Tablac+Candon+City,+Ilocos+Sur', '(0995)772-7107'),
(7, '007', 'MAGSINGAL', 'REGION I - ILOCOS SUR', '2nd Floor Retreta Bldg., Brgy.Vacunero, Sto.Domingo, I. Sur', 'https://www.google.com/maps?q=Retreta+Bldg.,+Brgy.Vacunero,+Sto.Domingo,+Ilocos+Sur', '(0915) 273-2796'),
(8, '008', 'NARVACAN', 'REGION I - ILOCOS SUR', '2nd Floor Soliven Building Brgy Sta.Lucia, Narvacan, Ilocos Sur', 'https://www.google.com/maps?q=Soliven+Building+Brgy+Sta.Lucia,+Narvacan,+Ilocos+Sur', '(0995) 454-0399'),
(9, '009', 'STA CRUZ ILOCOS', 'REGION I - ILOCOS SUR', 'MAJVV Building, Poblacion Este Sta Cruz, Ilocos Sur', 'https://www.google.com/maps?q=MAJVV+Building,+Poblacion+Este+Sta+Cruz,+Ilocos+Sur', '(0915)101-0837'),
(10, '010', 'VIGAN', 'REGION I - ILOCOS SUR', 'Galleria De Vigan Bldg., 3rd Floor Florentino St. corner Governor Reyes,Vigan City', 'https://www.google.com/maps?q=Galleria+De+Vigan+Bldg.,+Florentino+St.+corner+Governor+Reyes,+Vigan+City', '(0915) 101-0692'),
(11, '011', 'CABUGAO', 'REGION I - ILOCOS SUR', 'National Highway, Brgy. Bonifacio Cabugao, Rebibis Bldg. Ilocos Sur', 'https://www.google.com/maps?q=National+Highway,+Brgy.+Bonifacio+Cabugao,+Ilocos+Sur', '(0916) 1163901'),
(12, '012', 'AGOO', 'REGION I - LA UNION', '2nd Floor RMAE Bldg., San Jose Norte, Agoo, La Union', 'https://www.google.com/maps?q=RMAE+Bldg.,+San+Jose+Norte,+Agoo,+La+Union', '(0917) 862-1933'),
(13, '013', 'ROSARIO', 'REGION I - LA UNION', 'Ordońa St., Poblacion East, Rosario, La Union', 'https://www.google.com/maps?q=Ordońa+St.,+Poblacion+East,+Rosario,+La+Union', '(0915)084-3415'),
(14, '014', 'TUBAO', 'REGION I - LA UNION', '2nd Floor YRQ Building, Poblacion, Tubao, La Union', 'https://www.google.com/maps?q=YRQ+Building,+Poblacion,+Tubao,+La+Union', '(0915)101-0091'),
(15, '015', 'BACNOTAN', 'REGION I - LA UNION', '2nd Floor Yamaha Building, Poblacion, Bacnotan', 'https://www.google.com/maps?q=Yamaha+Building,+Poblacion,+Bacnotan,+La+Union', '(0915)102-5475'),
(16, '016', 'BALAOAN', 'REGION I - LA UNION', 'National Highway, Brgy. San Pablo, Balaoan, La Union', 'https://www.google.com/maps?q=National+Highway,+Brgy.+San+Pablo,+Balaoan,+La+Union', '(0915)101-0381'),
(17, '017', 'BANGAR', 'REGION I - LA UNION', '2nd Floor LFLP Bldg (LOLA FRIDA and LOLO PETER), San Pedro St., Central West, Bangar, La Union', 'https://www.google.com/maps?q=LFLP+Bldg,+San+Pedro+St.,+Central+West,+Bangar,+La+Union', '(0936)636-0356'),
(18, '018', 'BAUANG', 'REGION I - LA UNION', 'Corner Florendo St., Central East, Bauang, La Union', 'https://www.google.com/maps?q=Florendo+St.,+Central+East,+Bauang,+La+Union', '(0915)102-5003'),
(19, '019', 'NAGUILIAN', 'REGION I - LA UNION', 'Sobrepeña Bldg., Brgy. Ortiz, Naguilian, La Union', 'https://www.google.com/maps?q=Sobrepeña+Bldg.,+Brgy.+Ortiz,+Naguilian,+La+Union', '(0915)102-5004'),
(20, '020', 'SAN FERNANDO', 'REGION I - LA UNION', 'Purok 2 Pagdaroan, San Fernando City La Union', 'https://www.google.com/maps?q=Purok+2+Pagdaroan,+San+Fernando+City+La+Union', '(0936)908-6341'),
(21, '021', 'URDANETA', 'REGION I - PANGASINAN', 'Apartment 3, UDH Site, Dilan Paurido, Urdaneta City, Pangasinan', 'https://www.google.com/maps?q=UDH+Site,+Dilan+Paurido,+Urdaneta+City,+Pangasinan', '(0927)231-7576'),
(22, '022', 'UMINGAN', 'REGION I - PANGASINAN', '2nd fl Delos Santos Bldg., Casadores St., Pob East, Umingan, Pangasinan', 'https://www.google.com/maps?q=Delos+Santos+Bldg.,+Casadores+St.,+Poblacion+East,+Umingan,+Pangasinan', '(0917)704-1289'),
(23, '023', 'TAYUG', 'REGION I - PANGASINAN', '2nd Flr Magic 8 Bldg Rizal St. Brgy C, Tayug Pangasinan', 'https://www.google.com/maps?q=Magic+8+Bldg+Rizal+St.+Brgy+C,+Tayug+Pangasinan', '(0947) 871-5221'),
(24, '024', 'POZZORUBIO', 'REGION I - PANGASINAN', '2nd Floor Lamsen Bldg., Caballero St., Pozzorubio, Pangasinan', 'https://www.google.com/maps?q=Lamsen+Bldg.,+Caballero+St.,+Pozzorubio,+Pangasinan', '(0921) 865-4325'),
(25, '025', 'SAN FABIAN', 'REGION I - PANGASINAN', 'Mc Arthur Hi-way, Cayanga, San Fabian, Pangasinan', 'https://www.google.com/maps?q=Mc+Arthur+Hi-way,+Cayanga,+San+Fabian,+Pangasinan', '0963-994-6260'),
(26, '026', 'MANAOAG', 'REGION I - PANGASINAN', '1B Tower Tabayoyong St. Poblacion Manaoag Pangasinan', 'https://www.google.com/maps?q=Tower+Tabayoyong+St.+Poblacion+Manaoag+Pangasinan', '09672879757'),
(27, '027', 'MANGALDAN', 'REGION I - PANGASINAN', '2nd flr RBCP Bldg. Rizal St. Poblacion, Mangaldan Pangasinan', 'https://www.google.com/maps?q=RBCP+Bldg.+Rizal+St.+Poblacion,+Mangaldan+Pangasinan', '(0915)101-0510'),
(28, '028', 'DAGUPAN', 'REGION I - PANGASINAN', '2nd floor A & G Bldng. Caranglaan District Dagupan City', 'https://www.google.com/maps?q=A+%26+G+Bldng.+Caranglaan+District+Dagupan+City', '(0915) 101-0741'),
(29, '029', 'BUGALLON', 'REGION I - PANGASINAN', 'Samson Bldg., Romulo Hi-way, Poblacion, Bugallon, Pangasinan', 'https://www.google.com/maps?q=Samson+Bldg.,+Romulo+Hi-way,+Poblacion,+Bugallon,+Pangasinan', '(0915)101-2697'),
(30, '030', 'LINGAYEN', 'REGION I - PANGASINAN', '2nd Floor 52 William Gabriel Bldg. Avenida Rizal East, Poblacion Lingayen, Pangasinan', 'https://www.google.com/maps?q=William+Gabriel+Bldg.+Avenida+Rizal+East,+Poblacion+Lingayen,+Pangasinan', '09508826993'),
(31, '031', 'ALAMINOS', 'REGION I - PANGASINAN', '#34 De Guzman St. Brgy Palamis Alaminos City, Pangasinan', 'https://www.google.com/maps?q=34+De+Guzman+St.+Brgy+Palamis+Alaminos+City,+Pangasinan', '(0930) 244-4933'),
(32, '032', 'BOLINAO', 'REGION I - PANGASINAN', '2nd Floor Casas Blgd. P Deperio St., Germinal, Poblacion Bolinao, Pangasinan', 'https://www.google.com/maps?q=Casas+Blgd.+P+Deperio+St.,+Germinal,+Poblacion+Bolinao,+Pangasinan', '(0967) 429-0015'),
(33, '033', 'DASOL', 'REGION I - PANGASINAN', 'Casolming St. Poblacion Dasol, Pangasinan', 'https://www.google.com/maps?q=Casolming+St.+Poblacion+Dasol,+Pangasinan', '09475671687'),
(34, '034', 'MANGATAREM', 'REGION I - PANGASINAN', '2nd Floor Tagorda Bldg., Lone Palm Aqua Center, Plaza Rizal, Poblacion, Mangatarem, Pangasinan', 'https://www.google.com/maps?q=Tagorda+Bldg.,+Lone+Palm+Aqua+Center,+Plaza+Rizal,+Poblacion,+Mangatarem,+Pangasinan', '(0956)759-1339'),
(35, '035', 'CALASIAO', 'REGION I - PANGASINAN', 'S & R Bldg Nalsian, Calasiao, Pangasinan', 'https://www.google.com/maps?q=S+%26+R+Bldg+Nalsian,+Calasiao,+Pangasinan', '0995-663-0313'),
(36, '036', 'MALASIQUI', 'REGION I - PANGASINAN', '2/F JPAS Commercial Bldg., Magsaysay Street, Poblacion, Malasiqui, Pangasinan', 'https://www.google.com/maps?q=JPAS+Commercial+Bldg.,+Magsaysay+Street,+Poblacion,+Malasiqui,+Pangasinan', '0938-690-3436'),
(37, '037', 'SAN CARLOS', 'REGION I - PANGASINAN', 'Caranto Bldg., Burgos-Posadas Street, San Carlos, Pangasinan', 'https://www.google.com/maps?q=Caranto+Bldg.,+Burgos-Posadas+Street,+San+Carlos,+Pangasinan', '0915-1025-422'),
(38, '038', 'BAYAMBANG', 'REGION I - PANGASINAN', 'Mayo Bldg., Magsaysay St., Bayambang, Pangasinan', 'https://www.google.com/maps?q=Mayo+Bldg.,+Magsaysay+St.,+Bayambang,+Pangasinan', '0995-336-6257'),
(39, '039', 'SOLANO', 'REGION II - NUEVA VIZCAYA', 'Fugaban Bldg. Binacao Street, Brgy. Roxas Solano Nueva Vizcaya', 'https://www.google.com/maps?q=Fugaban+Bldg.+Binacao+Street,+Brgy.+Roxas+Solano+Nueva+Vizcaya', '(0926) 861-0473'),
(40, '040', 'SANTIAGO', 'REGION II - ISABELA', '3rd flr. Villarica Bldg. City Road Centro West, Santiago City', 'https://www.google.com/maps?q=Villarica+Bldg.+City+Road+Centro+West,+Santiago+City,+Isabela', '(0917) 702-6946'),
(41, '041', 'ALICIA', 'REGION II - ISABELA', '2nd floor Adenas Bldg., Maharlika Hi-way, Antonino, Alicia, Isabela', 'https://www.google.com/maps?q=Adenas+Bldg.,+Maharlika+Hi-way,+Antonino,+Alicia,+Isabela', '09066682791'),
(42, '042', 'CABATUAN', 'REGION II - ISABELA', '# 67 Zamora St. Purok 3 San Andres, Cabatuan Isabela', 'https://www.google.com/maps?q=67+Zamora+St.+Purok+3+San+Andres,+Cabatuan+Isabela', '(0975)445-7653'),
(43, '043', 'ILAGAN', 'REGION II - ISABELA', '2nd Floor JBR Bldg., Calamagui 1st, Ilagan, Isabela', 'https://www.google.com/maps?q=JBR+Bldg.,+Calamagui+1st,+Ilagan,+Isabela', '09174194094'),
(44, '044', 'CAUAYAN', 'REGION II - ISABELA', '1st floor OH. Bldg. Cabatuan Road San Fermin Cauayan City', 'https://www.google.com/maps?q=OH.+Bldg.+Cabatuan+Road+San+Fermin+Cauayan+City,+Isabela', '(0956) 126-1611'),
(45, '045', 'ROXAS (SATELITE OFFICE)', 'REGION II - ISABELA', 'Purok 5 Brgy Vira, Roxas Isabela', 'https://www.google.com/maps?q=Purok+5+Brgy+Vira,+Roxas+Isabela', '(0915)104-1990'),
(46, '046', 'TUGUEGARAO', 'REGION II - CAGAYAN', '3/F NP Baccay Bldg. 118 Balzain Road, Balzain West, Tuguegarao City', 'https://www.google.com/maps?q=NP+Baccay+Bldg.+118+Balzain+Road,+Balzain+West,+Tuguegarao+City', '(0975) 328-0565'),
(47, '047', 'CABARROGUIS', 'REGION II - QUIRINO', 'Brgy. Mangandinay, Cabarroguis, Quirino Province', 'https://www.google.com/maps?q=Brgy.+Mangandinay,+Cabarroguis,+Quirino+Province', '(0997) 652-3890'),
(48, '048', 'BAGUIO', 'CAR - BENGUET', '3rd Floor Luy Wing Building, Magsaysay Avenue, Baguio City', 'https://www.google.com/maps?q=Luy+Wing+Building,+Magsaysay+Avenue,+Baguio+City', '(0915) 101-2360'),
(49, '049', 'SAN JOSE DEL MONTE', 'REGION III - BULACAN', '#1262 Blk. 6 Lt. 41 Farmview Subd., Brgy. Tungkong Mangga City of San Jose Del Monte', 'https://www.google.com/maps?q=1262+Blk.+6+Lt.+41+Farmview+Subd.,+Brgy.+Tungkong+Mangga+City+of+San+Jose+Del+Monte,+Bulacan', '(0915) 1010297'),
(50, '050', 'STA MARIA', 'REGION III - BULACAN', '6035 ME DR. F. Santiago Laguerta Str. Poblacion Sta. Maria Bulacan', 'https://www.google.com/maps?q=6035+ME+DR.+F.+Santiago+Laguerta+Str.+Poblacion+Sta.+Maria+Bulacan', '(0915) 102 5766'),
(51, '051', 'BALAGTAS', 'REGION III - BULACAN', '2/F 3A\'s Bldg. 209 Borol 1st, Balagtas Bulacan', 'https://www.google.com/maps?q=3A\'s+Bldg.+209+Borol+1st,+Balagtas+Bulacan', '(0915)1012652'),
(52, '052', 'MALOLOS', 'REGION III - BULACAN', '2nd Floor, APB Bldg. #5630 Paseo del Congreso St., Liang, Malolos City Bulacan', 'https://www.google.com/maps?q=APB+Bldg.+5630+Paseo+del+Congreso+St.,+Liang,+Malolos+City+Bulacan', '(0915)1012648'),
(53, '053', 'BALIUAG', 'REGION III - BULACAN', '2nd Floor Alexandra Bldg., #790 Col. Tomacruz St., Poblacion, Baliuag, Bulacan', 'https://www.google.com/maps?q=Alexandra+Bldg.,+790+Col.+Tomacruz+St.,+Poblacion,+Baliuag,+Bulacan', '(0906)690-2754'),
(54, '054', 'CAMILING', 'REGION III - TARLAC', 'Bonifacio St. Poblacion H, Camiling, Tarlac', 'https://www.google.com/maps?q=Bonifacio+St.+Poblacion+H,+Camiling,+Tarlac', '(0915) 101-0879'),
(55, '055', 'GERONA', 'REGION III - TARLAC', 'NJ Bldg., Unit 1,2,3 Poblacion 3, Gerona, Tarlac', 'https://www.google.com/maps?q=NJ+Bldg.,+Poblacion+3,+Gerona,+Tarlac', '(0906) 397-5021'),
(56, '056', 'PANIQUI', 'REGION III - TARLAC', 'Unit 5 Jemare Plaza, Magallanes St. Poblacion Sur Paniqui Tarlac', 'https://www.google.com/maps?q=Jemare+Plaza,+Magallanes+St.+Poblacion+Sur+Paniqui+Tarlac', '09956213220'),
(57, '057', 'MONCADA', 'REGION III - TARLAC', '2nd Floor BDO Bldg., Mc Arthur Hi-way Poblacion 1, Moncada, Tarlac', 'https://www.google.com/maps?q=BDO+Bldg.,+Mc+Arthur+Hi-way+Poblacion+1,+Moncada,+Tarlac', '09167000446'),
(58, '058', 'TARLAC', 'REGION III - TARLAC', 'Clinica Pascual Bldg Zamora St. San Roque Tarlac City', 'https://www.google.com/maps?q=Clinica+Pascual+Bldg+Zamora+St.+San+Roque+Tarlac+City', '(0915) 101-2189'),
(59, '059', 'CAPAS', 'REGION III - TARLAC', '2nd Floor/Manny Lo Building, Mc Arthur Hi Way, Barangay Cut Cut 1st, Capas, Tarlac', 'https://www.google.com/maps?q=Manny+Lo+Building,+Mc+Arthur+Hi+Way,+Barangay+Cut+Cut+1st,+Capas,+Tarlac', '(0950) 811-6922'),
(60, '060', 'APALIT', 'REGION III - PAMPANGA', '3rd Floor St. Jude Bldg., San Vicente Apalit Pampanga', 'https://www.google.com/maps?q=St.+Jude+Bldg.,+San+Vicente+Apalit+Pampanga', '0915-881-3172'),
(61, '061', 'SAN FERNANDO', 'REGION III - PAMPANGA', 'Block 9, Lot 1, Dolores Homesite, Dolores CSFP', 'https://www.google.com/maps?q=Block+9,+Lot+1,+Dolores+Homesite,+Dolores,+San+Fernando,+Pampanga', '0915-101-1183'),
(62, '062', 'GAPAN', 'REGION III - NUEVA ECIJA', '2nd Flr. Magbitang Apartment, San Vicente Gapan City Nueva Ecija', 'https://www.google.com/maps?q=Magbitang+Apartment,+San+Vicente+Gapan+City+Nueva+Ecija', '09959048226'),
(63, '063', 'PALAYAN', 'REGION III - NUEVA ECIJA', 'Unit 2, Santos Building, Barangay Malate, Palayan City', 'https://www.google.com/maps?q=Santos+Building,+Barangay+Malate,+Palayan+City,+Nueva+Ecija', '0995-663-2805'),
(64, '064', 'CABANATUAN', 'REGION III - NUEVA ECIJA', 'Unit 1, BRR Building, H. Concepcion Cabanatuan City', 'https://www.google.com/maps?q=BRR+Building,+H.+Concepcion+Cabanatuan+City,+Nueva+Ecija', '0915-102-5089'),
(65, '065', 'SAN JOSE', 'REGION III - NUEVA ECIJA', 'Sanchez Building, San Roque St. Rafael Rueda, San Jose City, Nueva Ecija', 'https://www.google.com/maps?q=Sanchez+Building,+San+Roque+St.+Rafael+Rueda,+San+Jose+City,+Nueva+Ecija', '(0906) 654 8658'),
(66, '066', 'GUIMBA', 'REGION III - NUEVA ECIJA', '2nd Floor CCN Bldg., Ongiangco St. corner Sarmiento St. Guimba, Nueva Ecija', 'https://www.google.com/maps?q=CCN+Bldg.,+Ongiangco+St.+corner+Sarmiento+St.+Guimba,+Nueva+Ecija', '09956220832'),
(67, '067', 'TALAVERA/STO DOMINGO', 'REGION III - NUEVA ECIJA', 'Maharlika Highway, Calipahan, Talavera Nueva Ecija', 'https://www.google.com/maps?q=Maharlika+Highway,+Calipahan,+Talavera+Nueva+Ecija', '(0995) 622-0833'),
(68, '068', 'DBB', 'REGION IV-A - CAVITE', 'Stall # 23&24 Navjar Complex, Don P. Campos Avenue, Dasmariñas, Cavite', 'https://www.google.com/maps?q=Navjar+Complex,+Don+P.+Campos+Avenue,+Dasmariñas,+Cavite', '(0915) 1543625'),
(69, '069', 'INDANG', 'REGION IV-A - CAVITE', 'Miguel Tan, San Gregorio St., Poblacion 1, Indang, Cavite', 'https://www.google.com/maps?q=Miguel+Tan,+San+Gregorio+St.,+Poblacion+1,+Indang,+Cavite', '(0915) 1024868'),
(70, '070', 'GMA (Gen. Mariano Alvarez)', 'REGION IV-A - CAVITE', 'Blk 3 lot 35 Congressional road, Brgy San Gabriel GMA Cavite', 'https://www.google.com/maps?q=Blk+3+lot+35+Congressional+road,+Brgy+San+Gabriel+GMA+Cavite', '0915(1012200)'),
(71, '071', 'BIÑAN', 'REGION IV-A - LAGUNA', 'Simpeys Bldg. 3rd floor Brgy. San Antonio Binan Laguna', 'https://www.google.com/maps?q=Simpeys+Bldg.+Brgy.+San+Antonio+Binan+Laguna', '(0977) 1909343'),
(72, '072', 'BALAYAN', 'REGION IV-A - BATANGAS', 'Brgy. 7, Paz St. Balayan Batangas', 'https://www.google.com/maps?q=Brgy.+7,+Paz+St.+Balayan+Batangas', '(0915) 1010426'),
(73, '073', 'ANTIPOLO', 'REGION IV-A - RIZAL', '4th Flr. FBM Bldg. San Roque Antipolo City', 'https://www.google.com/maps?q=FBM+Bldg.+San+Roque+Antipolo+City', '0995 631 0032'),
(74, '074', 'CANDELARIA', 'REGION IV-A - QUEZON', '2nd Floor Tocy Bldg., Rizal Avenue corner Ona St. Candelaria, Quezon', 'https://www.google.com/maps?q=Tocy+Bldg.,+Rizal+Avenue+corner+Ona+St.+Candelaria,+Quezon', '(0975) 287-2536'),
(75, '075', 'POLANGUI', 'REGION V - ALBAY', 'Ground Floor Valentin Bldg., Basud, Polangui, Albay', 'https://www.google.com/maps?q=Valentin+Bldg.,+Basud,+Polangui,+Albay', '(0977) 396-6511 / (0951) 497-5785'),
(76, '076', 'LIGAO', 'REGION V - ALBAY', '2nd Floor Cate\'s Bldg., Mabini St., Bagumabayan, Ligao City, Albay', 'https://www.google.com/maps?q=Cate\'s+Bldg.,+Mabini+St.,+Bagumabayan,+Ligao+City,+Albay', '(0912) 454-6158 / (0997) 865-6908'),
(77, '077', 'TABACO', 'REGION V - ALBAY', 'Purok 7 Panal, Tabaco City', 'https://www.google.com/maps?q=Purok+7+Panal,+Tabaco+City', '(0948) 282-5050 / (0915) 101-0989'),
(78, '078', 'DARAGA', 'REGION V - ALBAY', '2nd Floor Clinica Bethany, Bañag, Daraga, Albay', 'https://www.google.com/maps?q=Clinica+Bethany,+Bañag,+Daraga,+Albay', '(0915) 102-5808'),
(79, '079', 'LEGAZPI', 'REGION V - ALBAY', '2/f Calderon Bldg. Washington Drive, Legazpi City', 'https://www.google.com/maps?q=Calderon+Bldg.+Washington+Drive,+Legazpi+City', '(0915) 101-1005 / (0951) 952-9220'),
(80, '080', 'STA. ELENA', 'REGION V - CAMARINES NORTE', 'Purok 12, Brgy. Poblacion, Sta. Elena, Camarines Norte', 'https://www.google.com/maps?q=Purok+12,+Brgy.+Poblacion,+Sta.+Elena,+Camarines+Norte', '(0967) 325-6864'),
(81, '081', 'LABO', 'REGION V - CAMARINES NORTE', '#125 Crossing Street P2 Barangay Anahaw, Labo, Camarines Norte, Philippines', 'https://www.google.com/maps?q=125+Crossing+Street+P2+Barangay+Anahaw,+Labo,+Camarines+Norte,+Philippines', '(0908) 818-9163 / (0915) 102-5497'),
(82, '082', 'SAN FERNANDO', 'REGION V - CAMARINES SUR', 'Zone 3 Bonifacio, San Fernando, Camarines Sur', 'https://www.google.com/maps?q=Zone+3+Bonifacio,+San+Fernando,+Camarines+Sur', '(0950) 918-8935 / (0915) 102-4967'),
(83, '083', 'NAGA', 'REGION V - CAMARINES SUR', '2nd Floor MCJ Building, San Sebastian St., Tinago, Naga City', 'https://www.google.com/maps?q=MCJ+Building,+San+Sebastian+St.,+Tinago,+Naga+City', '(0907) 142-7836 / (0916) 416-0298'),
(84, '084', 'CALABANGA', 'REGION V - CAMARINES SUR', '2nd Floor NJB Building Zone 3, Sta. Cruz Poblacion Calabanga Camarines Sur', 'https://www.google.com/maps?q=NJB+Building+Zone+3,+Sta.+Cruz+Poblacion+Calabanga+Camarines+Sur', '(0915) 1024-954 / (0910) 721-2973'),
(85, '085', 'GOA', 'REGION V - CAMARINES SUR', '2nd floor, RDP Building, Bagumbayan Pequeno, Goa, Cam. Sur', 'https://www.google.com/maps?q=RDP+Building,+Bagumbayan+Pequeno,+Goa,+Camarines+Sur', '(0915) 102-5239 / (0910) 884-2485'),
(86, '086', 'PILI', 'REGION V - CAMARINES SUR', '2nd Floor Abilay Bldg. Casuncad St. San Vicente Pili Camarines Sur', 'https://www.google.com/maps?q=Abilay+Bldg.+Casuncad+St.+San+Vicente+Pili+Camarines+Sur', '(0995) 540-8412 / (0910) 569-6324'),
(87, '087', 'NABUA', 'REGION V - CAMARINES SUR', 'Orias Bldg, San Isidro Poblacion, Nabua, Camarines Sur', 'https://www.google.com/maps?q=Orias+Bldg,+San+Isidro+Poblacion,+Nabua,+Camarines+Sur', '(0967) 325-6847 / (0907) 119-8208'),
(88, '088', 'IRIGA', 'REGION V - CAMARINES SUR', '2nd Floor LST Bldg., Brgy. San Francisco, Iriga City', 'https://www.google.com/maps?q=LST+Bldg.,+Brgy.+San+Francisco,+Iriga+City', '(0909) 508-3931 / (0956) 467-6611'),
(89, '089', 'BACACAY', 'REGION V - SORSOGON', 'Purok 8 Bonga Bacacay, Albay', 'https://www.google.com/maps?q=Purok+8+Bonga+Bacacay,+Albay', '(0910) 894-5172 / (0915) 102-5783'),
(90, '090', 'SORSOGON', 'REGION V - SORSOGON', '2nd Floor F.B. Fajardo Bldg., Piot, Sorsogon City', 'https://www.google.com/maps?q=F.B.+Fajardo+Bldg.,+Piot,+Sorsogon+City', '(0917) 622-0585 / (0915) 102-5680'),
(91, '091', 'IROSIN', 'REGION V - SORSOGON', '2nd floor Terrace Building, CM Recto Street, San Pedro Irosin Sorsogon', 'https://www.google.com/maps?q=Terrace+Building,+CM+Recto+Street,+San+Pedro+Irosin+Sorsogon', '(0917) 622-0585 / (0915) 102-5680'),
(92, '092', 'LAS PINAS', 'NCR - LAS PINAS', 'Rm. 8, 3rd floor Luis Bldg. 379 Real St. Talon1 Las pinas City', 'https://www.google.com/maps?q=Luis+Bldg.+379+Real+St.+Talon1+Las+pinas+City', '(0915) 1012397'),
(93, '093', 'PARAÑAQUE', 'NCR - PARAÑAQUE', 'Jackley Bldg Lot 2, Block 17, Press Drive Street Corner Dr. A Santos Avenue Fourth Estate Subdivision Paranaque City', 'https://www.google.com/maps?q=Jackley+Bldg+Lot+2,+Block+17,+Press+Drive+Street+Corner+Dr.+A+Santos+Avenue+Fourth+Estate+Subdivision+Paranaque+City', '(0915) 1025139 / (02) 8944149'),
(94, '094', 'TAGUIG', 'NCR - TAGUIG', '#302 Bravo Cor.Salazar St., Signal Village Taguig', 'https://www.google.com/maps?q=302+Bravo+Cor.Salazar+St.,+Signal+Village+Taguig', '(0945) 9848223'),
(95, '095', 'TSPI CORPORATE CENTER BRANCH', 'NCR - MAKATI', '2363 Antipolo street Guadalupe Nuevo Makati City', 'https://www.google.com/maps?q=2363+Antipolo+street+Guadalupe+Nuevo+Makati+City', '(0915) 1012673 / (02) 8 403 8625 loc 255'),
(96, '096', 'QUEZON CITY', 'NCR - QUEZON CITY', '3/F Room 311, F&L Center Bldg., 2211 Commonwealth Avenue, Brgy Holy Spirit QC', 'https://www.google.com/maps?q=F%26L+Center+Bldg.,+2211+Commonwealth+Avenue,+Brgy+Holy+Spirit+Quezon+City', '(0995) 1864438 / (02) 9614775'),
(97, '097', 'BAGONG SILANG', 'NCR - CALOOCAN CITY', 'Phase 4 Package 8-A, Block 66 Lot 25 and 27 Bagong Silang Caloocan City', 'https://www.google.com/maps?q=Phase+4+Package+8-A,+Block+66+Lot+25+and+27+Bagong+Silang+Caloocan+City', '(0956) 0732889'),
(98, '098', 'TONDO', 'NCR - MANILA', '2408 General Lucban Gagalangin Tondo Manila', 'https://www.google.com/maps?q=2408+General+Lucban+Gagalangin+Tondo+Manila', '(0977) 7908430'),
(99, '099', 'MALABON', 'NCR - MALABON', '#93 Bronze Street Lybaert Apartelle 2nd Flr Tugatog Malabon City', 'https://www.google.com/maps?q=93+Bronze+Street+Lybaert+Apartelle+Tugatog+Malabon+City', '(0945) 3100300 / (02) 8 9901485'),
(100, '100', 'VALENZUELA', 'NCR - VALENZUELA', '11 Gov. Santiago St., Malinta Valenzuela City', 'https://www.google.com/maps?q=11+Gov.+Santiago+St.,+Malinta+Valenzuela+City', '(0915) 823-8413 / (02) 8 2912575');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'News', 'tspi_news'),
(2, 'Client Stories', 'cli_stories'),
(3, 'Updates', 'updates'),
(6, 'Client\'s Awards', 'cli_awards'),
(7, 'SAMBAYANIHAN', 'sambayanihan'),
(8, 'Organization Awards', 'org_awards'),
(9, 'SAMBAYANIHAN With Clients', 'sambayanihan_client'),
(10, 'SAMBAYANIHAN With Employees', 'sambayanihan_employees'),
(11, 'Annual Reports', 'ann_reports'),
(12, 'Audited Financial Statements', 'aud_financial'),
(13, 'Newsletter', 'newsletter'),
(16, 'Regulatory Registrations', 'reg_registrations'),
(17, 'Foundation Legal Documents', 'leg_documents'),
(18, 'Governance Framework', 'gov_framework');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `author_name` varchar(100) NOT NULL,
  `author_email` varchar(100) NOT NULL,
  `author_website` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','spam') DEFAULT 'pending',
  `ip` varchar(45) DEFAULT NULL,
  `pinned` tinyint(1) NOT NULL DEFAULT 0,
  `vote_score` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `content_id`, `user_id`, `parent_id`, `author_name`, `author_email`, `author_website`, `content`, `posted_at`, `status`, `ip`, `pinned`, `vote_score`) VALUES
(13, 13, 1, NULL, 'Administrator', 'admin@tspi.org', NULL, 'That\'s amazing?', '2025-05-15 17:36:10', 'pending', '::1', 0, 0),
(14, 13, 1, NULL, 'Administrator', 'admin@tspi.org', NULL, 'Wowwie', '2025-05-15 17:36:21', 'pending', '::1', 0, 0),
(15, 13, 1, NULL, 'Administrator', 'admin@tspi.org', NULL, 'Woah that\'s actually pretty sick', '2025-05-15 17:43:34', 'spam', '::1', 0, 0),
(16, 13, 1, NULL, 'Administrator', 'admin@tspi.org', NULL, 'This is a wonderful account of TSPI\'s 40th anniversary celebration! It truly highlights the organization\'s deep-rooted faith and the significant impact it has had on the lives of its clients. The theme \"Sustained by God\'s faithfulness\" resonates throughout the various aspects of the celebration, from the trustees\' testimonies to the clients\' heartfelt stories of transformation.\r\n\r\nIt\'s particularly touching to read about Mrs. Belen Marciano, the wife of the first TSPI client, and how the organization\'s trust and care made a real difference in their lives. The recognition of loyal clients, TAAS agents, and microentrepreneurs through the Unlad Awards further emphasizes the tangible outcomes of TSPI\'s work.\r\n\r\nThe warm messages from partner organizations, especially from TSKI, ASKI, and RPMI, underscore TSPI\'s pivotal role as a \"mother organization\" in the microfinance sector. Their acknowledgment of TSPI\'s steadfast vision and mission speaks volumes about the organization\'s enduring commitment to serving the poor.\r\n\r\nThis anniversary celebration is clearly a testament to four decades of dedicated service, guided by faith and a genuine desire to empower communities. It\'s inspiring to see how TSPI has not only provided financial assistance but has also fostered dignity, self-confidence, and a strengthened faith among its clients. Congratulations to TSPI on reaching this significant milestone!', '2025-05-15 17:46:12', 'approved', '::1', 0, -1),
(17, 13, 1, 16, 'Administrator', 'admin@tspi.org', NULL, 'Ugh, this whole thing just screams self-congratulatory fluff. Forty years, huh? Big deal. Every organization that sticks around long enough will pat themselves on the back and talk about how great they are. \"Deep-rooted faith\"? Please. It\'s a convenient narrative to make them sound noble.\r\n\r\nAnd all those \"heartfelt stories of transformation\"? I\'d bet there are plenty of clients who didn\'t have such rosy experiences, but you won\'t see their stories highlighted, will you? It\'s all about painting this perfect picture.\r\n\r\nMrs. Belen Marciano\'s story? Sure, it sounds nice, but it\'s one anecdote from forty years. What about the interest rates? \"Napakababa\"? I\'d like to see the actual numbers. And \"character loan\"? Sounds risky for them, so there must have been some other angle.\r\n\r\nThe \"Unlad Awards\" and the praise from partner organizations? It\'s all a bit too convenient, isn\'t it? These other microfinance groups were \"established in partnership with TSPI,\" so of course they\'re going to say nice things. It\'s all interconnected.\r\n\r\n\"Empower communities,\" \"fostered dignity,\" \"strengthened faith\" – it\'s the same old jargon. Show me the hard data, the long-term impact assessments that go beyond feel-good stories. Forty years should have generated some serious, measurable results, not just sentimental anecdotes and mutual back-patting. This whole celebration just feels like a carefully orchestrated PR stunt to mask whatever the real complexities and challenges might be.', '2025-05-15 17:46:49', 'approved', '::1', 0, 0),
(18, 13, 1, 16, 'Administrator', 'admin@tspi.org', NULL, 'Lmao that\'s hilarious.', '2025-05-15 18:13:27', 'approved', '::1', 0, 0),
(19, 12, 1, NULL, 'Administrator', 'admin@tspi.org', NULL, 'That\'s actually pretty sick', '2025-05-15 18:54:10', 'approved', '::1', 0, 1),
(20, 12, 1, NULL, 'Administrator', 'admin@tspi.org', NULL, 'Wow that\'s amazing', '2025-05-16 17:33:20', 'approved', '::1', 0, 0),
(21, 12, 1, 19, 'Administrator', 'admin@tspi.org', NULL, 'lmao', '2025-05-16 17:33:39', 'approved', '::1', 0, 0),
(22, 14, 16, NULL, 'Rich Darien', 'ritscstdio@gmail.com', NULL, 'Wow', '2025-05-23 21:32:55', 'pending', '::1', 0, 0),
(23, 12, 16, NULL, 'Rich Darien', 'ritscstdio@gmail.com', NULL, 'Wow!! that\'s amazing!! :O', '2025-05-27 07:15:51', 'approved', '::1', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `comment_votes`
--

CREATE TABLE `comment_votes` (
  `id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote` tinyint(1) NOT NULL COMMENT '1 for upvote, -1 for downvote',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment_votes`
--

INSERT INTO `comment_votes` (`id`, `comment_id`, `user_id`, `vote`, `created_at`, `updated_at`) VALUES
(32, 16, 1, -1, '2025-05-16 02:12:09', '2025-05-16 02:12:09'),
(33, 19, 1, 1, '2025-05-17 01:33:32', '2025-05-17 01:33:32');

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `vote_score` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`id`, `title`, `slug`, `thumbnail`, `content`, `excerpt`, `author_id`, `published_at`, `status`, `vote_score`) VALUES
(8, 'Rare Bioluminescent Fungus Discovered in Makati Park Glows with Unexpected Intensity', 'rare-bioluminescent-fungus-discovered-in-makati-park-glows-with-unexpected-intensity', 'http://localhost/TSPI/uploads/media/681e42975ea5c_6.jpg', '<chat-window-content _ngcontent-ng-c562636367=\"\" _nghost-ng-c4118627617=\"\" class=\"ng-tns-c562636367-1 ui-improvements-phase-1 ng-star-inserted\"><div _ngcontent-ng-c4118627617=\"\" id=\"chat-history\" class=\"chat-history-scroll-container\"><infinite-scroller _ngcontent-ng-c4118627617=\"\" data-test-id=\"chat-history-container\" class=\"chat-history\" _nghost-ng-c2704829390=\"\"><div _ngcontent-ng-c4118627617=\"\" class=\"conversation-container message-actions-hover-boundary tts-removed ng-star-inserted\" id=\"51c1a67e30c04fbf\" style=\"min-height: 751px;\"><model-response _ngcontent-ng-c4118627617=\"\" _nghost-ng-c857235362=\"\" class=\"ng-star-inserted\"><div _ngcontent-ng-c857235362=\"\"><response-container _ngcontent-ng-c857235362=\"\" _nghost-ng-c2943259502=\"\" class=\"ng-tns-c2943259502-16 reduced-bottom-padding ng-star-inserted\" jslog=\"188576;track:impression;BardVeMetadataKey:[[&quot;r_51c1a67e30c04fbf&quot;,&quot;c_4b5d3204991cf7e4&quot;,null,null,null,null,null,null,1,null,null,null,0]];mutable:true\"><div _ngcontent-ng-c2943259502=\"\" class=\"response-container ng-tns-c2943259502-16 response-container-with-gpi tts-removed ng-star-inserted response-container-has-multiple-responses\" jslog=\"173900;track:impression\"><div _ngcontent-ng-c2943259502=\"\" class=\"presented-response-container ng-tns-c2943259502-16\"><div _ngcontent-ng-c2943259502=\"\" selection=\"\" class=\"response-container-content ng-tns-c2943259502-16\"><div _ngcontent-ng-c857235362=\"\" class=\"response-content ng-tns-c2943259502-16\"><message-content _ngcontent-ng-c857235362=\"\" class=\"model-response-text ng-star-inserted\" _nghost-ng-c3640214162=\"\" id=\"message-content-id-r_51c1a67e30c04fbf\" style=\"height: auto;\"><div _ngcontent-ng-c3640214162=\"\" class=\"markdown markdown-main-panel stronger enable-updated-hr-color\" id=\"model-response-message-contentr_51c1a67e30c04fbf\" dir=\"ltr\" style=\"--animation-duration: 400ms; --fade-animation-function: linear;\"><p style=\"text-align: left;\"><strong>Makati City, Philippines</strong> - Local botanists and astonished park-goers are buzzing after the unprecedented discovery of a new species of bioluminescent fungus in Legazpi Active Park. Unlike other known glowing fungi that emit a faint shimmer, this newly identified species, tentatively dubbed \"Makati Moonlight,\" radiates a surprisingly bright, almost ethereal light.</p><p><br></p><p><br></p><p style=\"text-align: left;\"><iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/bya4WnbkXCw\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"\"></iframe></p><p><br></p><p data-sourcepos=\"7:1-7:320\">\"I was walking my dog late last night when I saw it,\" recounts resident Althea Reyes. \"At first, I thought someone had left a string of fairy lights on the ground, but then I realized it was coming from these little mushrooms. They were glowing so brightly, it was almost like moonlight reflecting off the forest floor!\"</p><p data-sourcepos=\"7:1-7:320\"><br></p><p data-sourcepos=\"9:1-9:430\">Dr. Evelyn Cruz, a mycologist at the University of the Philippines Diliman, was among the first scientists to examine the find. \"This level of luminescence is truly remarkable,\" she stated in a press conference earlier today. \"We\'ve observed bioluminescent fungi before, but nothing quite like this. The intensity of the light emitted by the Makati Moonlight is several orders of magnitude greater than anything we\'ve documented.\"</p><p data-sourcepos=\"9:1-9:430\"><br></p><p data-sourcepos=\"11:1-11:260\">Researchers are currently working to understand the unique biochemical processes that allow this fungus to produce such intense light. Theories range from a novel enzyme complex to a symbiotic relationship with an undiscovered microorganism in the park\'s soil.</p><p data-sourcepos=\"11:1-11:260\"><br></p><p data-sourcepos=\"13:1-13:252\">The discovery has already sparked considerable excitement, with many locals eager to witness the glowing phenomenon firsthand. However, park officials are urging the public to observe the fungi from a distance to avoid disturbing their natural habitat.</p><p data-sourcepos=\"13:1-13:252\"><br></p><p data-sourcepos=\"15:1-15:302\">\"We understand the public\'s curiosity,\" said Parks and Recreation Superintendent, Mr. Benjo Lim. \"But it\'s crucial that we protect this unique and potentially fragile ecosystem. We will be setting up designated viewing areas to allow people to appreciate the Makati Moonlight without causing any harm.\"</p><p data-sourcepos=\"17:1-17:368\">Further research is planned, and scientists hope that studying this extraordinary fungus could yield valuable insights into bioluminescence and potentially lead to new applications in biotechnology and sustainable lighting. For now, the Makati Moonlight stands as a dazzling reminder of the hidden wonders that can still be found in the heart of a bustling metropolis.</p></div></message-content></div></div></div></div></response-container></div></model-response></div></infinite-scroller></div></chat-window-content><input-container _ngcontent-ng-c562636367=\"\" _nghost-ng-c1505779109=\"\" class=\"ng-tns-c562636367-1 ui-improvements-phase-1 ng-star-inserted input-gradient\"><contextual-actions _ngcontent-ng-c1505779109=\"\" _nghost-ng-c2357284113=\"\" class=\"ng-star-inserted\"><div _ngcontent-ng-c2357284113=\"\" class=\"container hidden ng-star-inserted\"><!----></div><!----></contextual-actions><!----><div _ngcontent-ng-c1505779109=\"\" class=\"input-area-container ng-star-inserted\"><file-drop-indicator _ngcontent-ng-c1505779109=\"\" _nghost-ng-c1111744535=\"\" class=\"ng-tns-c1111744535-4 ng-star-inserted\"><!----></file-drop-indicator><!----><input-area-v2 _ngcontent-ng-c1505779109=\"\" _nghost-ng-c2030685250=\"\" class=\"ng-tns-c2030685250-3 ui-improvements-phase-1 with-toolbox-drawer ng-star-inserted\"><div _ngcontent-ng-c2030685250=\"\" data-node-type=\"input-area\" class=\"input-area ng-tns-c2030685250-3 with-toolbox-drawer\"><!----><!----><!----><div _ngcontent-ng-c2030685250=\"\" xapfileselectordropzone=\"\" class=\"text-input-field ng-tns-c2030685250-3 with-toolbox-drawer height-expanded-past-single-line\"><!----><div _ngcontent-ng-c2030685250=\"\" class=\"text-input-field_textarea-wrapper ng-tns-c2030685250-3\" style=\"--chat-container-height: 785;\"><div _ngcontent-ng-c2030685250=\"\" class=\"text-input-field-main-area ng-tns-c2030685250-3\"><div _ngcontent-ng-c2030685250=\"\" class=\"text-input-field_textarea-inner ng-tns-c2030685250-3\" style=\"height: 24px;\"><!----><!----><rich-textarea _ngcontent-ng-c2030685250=\"\" atmentions=\"\" class=\"text-input-field_textarea ql-container ql-bubble ng-tns-c2030685250-3 ng-untouched ng-valid ng-star-inserted ng-pristine\" _nghost-ng-c8440421=\"\" enterkeyhint=\"send\" dir=\"ltr\" style=\"--textarea-max-rows: 7; --chat-container-height: 785px;\"><div class=\"ql-editor textarea new-input-ui ql-blank\" data-gramm=\"false\" role=\"textbox\" aria-multiline=\"true\" aria-label=\"Enter a prompt here\" data-placeholder=\"Ask Gemini\"></div></rich-textarea></div></div></div></div></div></input-area-v2></div></input-container>', '', 1, '2025-05-09 18:00:12', 'published', 0),
(10, '\"Sky-High Farms\" Revolutionize Taguig\'s Urban Landscape with Vertical Agriculture Breakthrough', 'sky-high-farms-revolutionize-taguig-s-urban-landscape-with-vertical-agriculture-breakthrough', 'http://localhost/TSPI/uploads/media/681e566c6941a_BGC-CREDITS-TO-JESON-CABILIC.jpg', '<chat-window-content _ngcontent-ng-c562636367=\"\" _nghost-ng-c4118627617=\"\" class=\"ng-tns-c562636367-1 ui-improvements-phase-1 ng-star-inserted\"><div _ngcontent-ng-c4118627617=\"\" id=\"chat-history\" class=\"chat-history-scroll-container\"><infinite-scroller _ngcontent-ng-c4118627617=\"\" data-test-id=\"chat-history-container\" class=\"chat-history\" _nghost-ng-c2704829390=\"\"><div _ngcontent-ng-c4118627617=\"\" class=\"conversation-container message-actions-hover-boundary tts-removed ng-star-inserted\" id=\"2611c1797dd47cb6\" style=\"min-height: 751px;\"><model-response _ngcontent-ng-c4118627617=\"\" _nghost-ng-c857235362=\"\" class=\"ng-star-inserted\"><div _ngcontent-ng-c857235362=\"\"><response-container _ngcontent-ng-c857235362=\"\" _nghost-ng-c2943259502=\"\" class=\"ng-tns-c2943259502-21 reduced-bottom-padding ng-star-inserted\" jslog=\"188576;track:impression;BardVeMetadataKey:[[&quot;r_2611c1797dd47cb6&quot;,&quot;c_4b5d3204991cf7e4&quot;,null,null,null,null,null,null,1,null,null,null,0]];mutable:true\"><div _ngcontent-ng-c2943259502=\"\" class=\"response-container ng-tns-c2943259502-21 response-container-with-gpi tts-removed ng-star-inserted response-container-has-multiple-responses\" jslog=\"173900;track:impression\"><div _ngcontent-ng-c2943259502=\"\" class=\"presented-response-container ng-tns-c2943259502-21\"><div _ngcontent-ng-c2943259502=\"\" selection=\"\" class=\"response-container-content ng-tns-c2943259502-21\"><div _ngcontent-ng-c857235362=\"\" class=\"response-content ng-tns-c2943259502-21\"><message-content _ngcontent-ng-c857235362=\"\" class=\"model-response-text ng-star-inserted\" _nghost-ng-c3640214162=\"\" id=\"message-content-id-r_2611c1797dd47cb6\" style=\"height: auto;\"><div _ngcontent-ng-c3640214162=\"\" class=\"markdown markdown-main-panel stronger enable-updated-hr-color\" id=\"model-response-message-contentr_2611c1797dd47cb6\" dir=\"ltr\" style=\"--animation-duration: 400ms; --fade-animation-function: linear;\"><p data-sourcepos=\"1:1-1:55\"><strong>Taguig City, Philippines</strong> - In a groundbreaking move that\'s turning heads and raising eyebrows across Metro Manila, Taguig City has unveiled its ambitious \"Sky-High Farms\" initiative. Utilizing cutting-edge vertical farming technology integrated directly into the facades of select high-rise buildings in Bonifacio Global City (BGC), the city aims to become a self-sufficient urban agricultural hub.</p><p data-sourcepos=\"1:1-1:55\"><br></p><p data-sourcepos=\"7:1-7:394\">Residents were astonished this week as specialized robotic systems began installing modular farming units onto the sides of several prominent skyscrapers. These units, resembling high-tech balconies, are equipped with automated irrigation, nutrient delivery, and climate control systems, allowing for the cultivation of a wide variety of fruits, vegetables, and even grains at dizzying heights.</p><p data-sourcepos=\"7:1-7:394\"><br></p><p data-sourcepos=\"9:1-9:227\">\"It\'s surreal to see lettuce and tomatoes growing thirty stories up,\" commented Sofia dela Cruz, a BGC resident. \"Just a few weeks ago, it was just a blank wall. Now, it\'s a vibrant, green, living facade. It\'s quite something.\"</p><p data-sourcepos=\"11:1-11:316\">The initiative is spearheaded by the Taguig City Agricultural Innovation Department (TCAID), which claims that the Sky-High Farms will significantly reduce the city\'s reliance on external food sources, lower carbon emissions associated with transportation, and provide fresh, locally grown produce for its residents.</p><p data-sourcepos=\"11:1-11:316\"><br></p><p data-sourcepos=\"13:1-13:369\">\"Our vision is to transform Taguig into a truly sustainable and resilient city,\" stated Engr. Ricardo Vargas, head of TCAID, during the project\'s official launch. \"By harnessing the vertical space available in our urban environment, we can create a localized food system that benefits everyone. Imagine picking your salad ingredients grown just a few floors above you!\"</p><p data-sourcepos=\"13:1-13:369\"><br></p><p data-sourcepos=\"15:1-15:371\">While the initial phase involves a pilot program on a limited number of buildings, the city has ambitious plans to expand the Sky-High Farms across more of its urban landscape. Challenges remain, including managing the aesthetics of the building facades, ensuring the structural integrity of the added units, and training personnel to manage the sophisticated technology.</p><p data-sourcepos=\"15:1-15:371\"><br></p><p data-sourcepos=\"17:1-17:368\">Nevertheless, the Sky-High Farms project has generated considerable buzz and is being lauded by urban planning experts as a bold and innovative approach to addressing food security in densely populated metropolitan areas. Taguig City may very well be setting a new precedent for how cities of the future can feed their growing populations, one sky-high farm at a time.</p><p data-sourcepos=\"17:1-17:368\"><br></p><p data-sourcepos=\"19:1-19:316\">Initial reports suggest that the first harvest from the pilot buildings is expected within the next few months, with local restaurants already expressing interest in sourcing their ingredients directly from these vertical farms in the sky. The future of urban agriculture in Taguig looks bright, and decidedly green.</p></div></message-content><!----><!----><div _ngcontent-ng-c857235362=\"\" class=\"response-footer gap complete\"><!----><!----><!----><sources-list _ngcontent-ng-c857235362=\"\" class=\"sources-list ng-star-inserted\"><!----></sources-list><!----><!----><!----><!----><!----><!----><!----><!----><!----><!----><!----></div></div><!----><sensitive-memories-banner _ngcontent-ng-c857235362=\"\" _nghost-ng-c3315936682=\"\" class=\"ng-star-inserted\"><!----></sensitive-memories-banner><!----><!----><!----><!----></div><!----></div><div _ngcontent-ng-c2943259502=\"\" class=\"response-container-footer ng-tns-c2943259502-21\"><message-actions _ngcontent-ng-c857235362=\"\" footer=\"\" _nghost-ng-c2477487412=\"\" class=\"ng-tns-c2477487412-23 ng-star-inserted\"><div _ngcontent-ng-c2477487412=\"\" class=\"actions-container-v2 ng-tns-c2477487412-23 simplified-action-bar\"><div _ngcontent-ng-c2477487412=\"\" class=\"buttons-container-v2 ng-tns-c2477487412-23 ng-star-inserted\"><!----><thumb-up-button _ngcontent-ng-c2477487412=\"\" _nghost-ng-c2439826489=\"\" class=\"ng-tns-c2477487412-23 ng-star-inserted\"><br></thumb-up-button></div></div></message-actions></div></div></response-container></div></model-response></div></infinite-scroller></div></chat-window-content><input-container _ngcontent-ng-c562636367=\"\" _nghost-ng-c1505779109=\"\" class=\"ng-tns-c562636367-1 ui-improvements-phase-1 ng-star-inserted input-gradient\"><contextual-actions _ngcontent-ng-c1505779109=\"\" _nghost-ng-c2357284113=\"\" class=\"ng-star-inserted\"><div _ngcontent-ng-c2357284113=\"\" class=\"container hidden ng-star-inserted\"><!----></div><!----></contextual-actions><!----><div _ngcontent-ng-c1505779109=\"\" class=\"input-area-container ng-star-inserted\"><file-drop-indicator _ngcontent-ng-c1505779109=\"\" _nghost-ng-c1111744535=\"\" class=\"ng-tns-c1111744535-4 ng-star-inserted\"><!----></file-drop-indicator><!----><input-area-v2 _ngcontent-ng-c1505779109=\"\" _nghost-ng-c2030685250=\"\" class=\"ng-tns-c2030685250-3 ui-improvements-phase-1 with-toolbox-drawer ng-star-inserted\"><div _ngcontent-ng-c2030685250=\"\" data-node-type=\"input-area\" class=\"input-area ng-tns-c2030685250-3 with-toolbox-drawer\"><!----><!----><!----><div _ngcontent-ng-c2030685250=\"\" xapfileselectordropzone=\"\" class=\"text-input-field ng-tns-c2030685250-3 with-toolbox-drawer height-expanded-past-single-line\"><!----><div _ngcontent-ng-c2030685250=\"\" class=\"text-input-field_textarea-wrapper ng-tns-c2030685250-3\" style=\"--chat-container-height: 785;\"><div _ngcontent-ng-c2030685250=\"\" class=\"text-input-field-main-area ng-tns-c2030685250-3\"><div _ngcontent-ng-c2030685250=\"\" class=\"text-input-field_textarea-inner ng-tns-c2030685250-3\" style=\"height: 24px;\"><!----><!----><rich-textarea _ngcontent-ng-c2030685250=\"\" atmentions=\"\" class=\"text-input-field_textarea ql-container ql-bubble ng-tns-c2030685250-3 ng-untouched ng-valid ng-star-inserted ng-pristine\" _nghost-ng-c8440421=\"\" enterkeyhint=\"send\" dir=\"ltr\" style=\"--textarea-max-rows: 7; --chat-container-height: 785px;\"><div class=\"ql-editor textarea new-input-ui ql-blank\" data-gramm=\"false\" role=\"textbox\" aria-multiline=\"true\" aria-label=\"Enter a prompt here\" data-placeholder=\"Ask Gemini\"></div></rich-textarea></div></div></div></div></div></input-area-v2></div></input-container>', '', 1, '2025-05-09 19:25:20', 'published', 0),
(11, 'Mandaluyong Declares National \"Merienda\" Day Following Unprecedented Kakanin Festival Success', 'mandaluyong-declares-national-merienda-day-following-unprecedented-kakanin-festival-success', 'http://localhost/TSPI/uploads/media/6822022eeeda4_manda.jpg', '<div _ngcontent-ng-c3640214162=\"\" class=\"markdown markdown-main-panel stronger enable-updated-hr-color\" id=\"model-response-message-contentr_de825ff92516e88d\" dir=\"ltr\" style=\"--animation-duration: 400ms; --fade-animation-function: linear; animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; color: rgb(27, 28, 29); columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; font-size: 16px; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important; font-family: &quot;Google Sans Text&quot;, sans-serif !important;\"><p data-sourcepos=\"1:1-1:51\" style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; margin-bottom: 16px; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\"><span style=\"background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); animation: 0s ease 0s 1 normal none running none; appearance: none; border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-weight: 700; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\">Mandaluyong City, Metro Manila, Philippines</span><span style=\"background-color: rgba(0, 0, 0, 0);\"> - In a move that has sent ripples of delight across the nation, the City of Mandaluyong has officially declared the third Friday of every May as National \"Merienda\" Day. This landmark decision comes on the heels of the city\'s highly successful inaugural \"Kakanin Kalayaan\" Festival, a week-long celebration of traditional Filipino rice cakes that drew record crowds and sparked a renewed appreciation for local delicacies.</span></p><p data-sourcepos=\"7:1-7:407\" style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; margin-bottom: 16px; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\">\"The energy and enthusiasm we witnessed during the Kakanin Kalayaan Festival were truly inspiring,\" declared Mandaluyong City Mayor Maria Elena \"Menchie\" Abalos during a press conference held earlier today at the city hall. \"It highlighted not only the rich culinary heritage of our nation but also the unifying power of a shared \'merienda\' experience. We believe this spirit deserves national recognition.\"</p><p data-sourcepos=\"9:1-9:497\" style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; margin-bottom: 16px; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\">The Kakanin Kalayaan Festival, which concluded last weekend, featured an astonishing array of \"kakanin\" from all corners of the Philippines. From the sticky sweetness of <em style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; display: inline; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\">biko</em> and <em style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; display: inline; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\">suman</em> to the vibrant hues of <em style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; display: inline; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\">kutsinta</em> and the comforting warmth of <em style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; display: inline; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\">bibingka</em>, the festival showcased the incredible diversity and artistry of Filipino rice cakes. Local vendors reported unprecedented sales, and cultural performances celebrating the history and significance of these treats captivated attendees.</p><p data-sourcepos=\"11:1-11:279\" style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; margin-bottom: 16px; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\">Social media platforms were abuzz throughout the festival, with the hashtag #KakaninKalayaan trending nationwide. Food bloggers and enthusiasts flocked to Mandaluyong, sharing mouthwatering photos and videos that further fueled the public\'s appetite for these traditional snacks.</p><p data-sourcepos=\"13:1-13:382\" style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; margin-bottom: 16px; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\">The newly declared National \"Merienda\" Day aims to encourage Filipinos across the archipelago to take a moment to savor local snacks and connect with their cultural roots. The Mandaluyong City government plans to collaborate with the National Commission for Culture and the Arts (NCCA) to promote regional \"merienda\" specialties and organize nationwide events on the designated day.</p><p data-sourcepos=\"15:1-15:336\" style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; margin-bottom: 16px; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\">\"This is not just about eating snacks,\" explained Councilor Benjo Abalos, a key proponent of the initiative. \"It\'s about fostering a sense of community, supporting local businesses, and celebrating our unique Filipino identity through our food. We hope National \'Merienda\' Day will become a cherished tradition for generations to come.\"</p><p data-sourcepos=\"17:1-17:370\" style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; margin-bottom: 16px; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\">Reactions to the declaration have been overwhelmingly positive. Many Filipinos have taken to social media to express their excitement and share their favorite \"merienda\" memories. Local bakeries and eateries across the country are already preparing for the inaugural National \"Merienda\" Day next year, anticipating a surge in demand for their sweet and savory offerings.</p><p data-sourcepos=\"19:1-19:329\" style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; margin-bottom: 16px; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\">While some neighboring cities have jokingly expressed friendly rivalry over the newfound national food holiday, the overall sentiment is one of celebration and unity. It seems Mandaluyong has not only satisfied the nation\'s sweet tooth but also sparked a nationwide movement to embrace the simple joy of a shared afternoon snack.</p><p data-sourcepos=\"21:1-21:122\" style=\"animation: 0s ease 0s 1 normal none running none; appearance: none; background: none 0% 0% / auto repeat scroll padding-box border-box rgba(0, 0, 0, 0); border: 0px none rgb(27, 28, 29); inset: auto; clear: none; clip: auto; columns: auto; contain: none; container: none; content: normal; cursor: auto; cx: 0px; cy: 0px; d: none; direction: ltr; fill: rgb(0, 0, 0); filter: none; flex: 0 1 auto; float: none; font-variant-numeric: normal; font-variant-east-asian: normal; font-variant-alternates: normal; font-variant-position: normal; font-variant-emoji: normal; font-size-adjust: none; font-kerning: auto; font-optical-sizing: auto; font-feature-settings: normal; font-variation-settings: normal; font-stretch: normal; gap: normal; hyphens: manual; interactivity: auto; isolation: auto; margin-bottom: 16px; marker: none; mask: none; offset: normal; opacity: 1; order: 0; outline: rgb(27, 28, 29) none 0px; overlay: none; page: auto; perspective: none; position: static; quotes: auto; r: 0px; resize: none; rotate: none; rx: auto; ry: auto; scale: none; speak: normal; stroke: none; transform: none; transition: all; translate: none; visibility: visible; x: 0px; y: 0px; zoom: 1; line-height: 1.15 !important;\"><br></p></div>', '', 1, '2025-05-12 14:14:19', 'published', 0);
INSERT INTO `content` (`id`, `title`, `slug`, `thumbnail`, `content`, `excerpt`, `author_id`, `published_at`, `status`, `vote_score`) VALUES
(12, 'Philippine Midterm Elections Rocked by Unexpected Shifts; Political Dynasties Face New Challenges', 'philippine-midterm-elections-rocked-by-unexpected-shifts-political-dynasties-face-new-challenges', 'http://localhost/TSPI/uploads/media/682215087cd7e_2022-05-07T030018Z_1145557179_RC202U9GNVPK_RTRMADP_3_PHILIPPINES-ELECTION.png', '<p data-sourcepos=\"5:1-5:346\"><strong>Manila, Philippines – May 12, 2025</strong><span class=\"citation-0 recitation citation-end-0\"> – The Philippines has concluded its midterm elections, a day marked by both fervent civic participation and reports of isolated incidents of electoral irregularities.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3136564947=\"\"><sup _ngcontent-ng-c3136564947=\"\" class=\"superscript\" data-turn-source-index=\"1\"><!----></sup></source-footnote></span> <span class=\"citation-1 recitation citation-end-1\">The results, as they begin to trickle in, are signaling potential shifts in the nation\'s political power dynamics.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3136564947=\"\"><sup _ngcontent-ng-c3136564947=\"\" class=\"superscript\" data-turn-source-index=\"2\"><!----></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3246093795=\"\"><!----><span _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\"> &nbsp; <button _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" aria-label=\"Learn More\" aria-controls=\"sources\" class=\"mat-mdc-tooltip-trigger button image-fade-on hide-from-message-actions\" aria-expanded=\"false\" jslog=\"220646;track:generic_click,impression\" aria-describedby=\"cdk-describedby-message-ng-1-26\" cdk-describedby-host=\"ng-1\"><mat-icon _ngcontent-ng-c3246093795=\"\" role=\"img\" class=\"mat-icon notranslate symbol google-symbols mat-ligature-font mat-icon-no-color\" aria-hidden=\"true\" data-mat-icon-type=\"font\" data-mat-icon-name=\"expand_more\" fonticon=\"expand_more\"></mat-icon></button><!----></span><!----><sources-carousel _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" id=\"sources\" _nghost-ng-c1495087998=\"\" class=\"ng-tns-c1495087998-36 hide-from-message-actions ng-star-inserted\" style=\"display: flex; visibility: hidden;\"></sources-carousel></sources-carousel-inline></p><div _ngcontent-ng-c1495087998=\"\" class=\"container ng-tns-c1495087998-36 hide\" jslog=\"220997;BardVeMetadataKey:[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[2,null,1]]\"><!----><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-container ng-tns-c1495087998-36\"><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-content ng-tns-c1495087998-36\"><div _ngcontent-ng-c1495087998=\"\" data-test-id=\"sources-carousel-source\" class=\"sources-carousel-source ng-tns-c1495087998-36 hide ng-star-inserted\"><!----></div><div _ngcontent-ng-c1495087998=\"\" data-test-id=\"sources-carousel-source\" class=\"sources-carousel-source ng-tns-c1495087998-36 hide ng-star-inserted\"><!----></div><!----><!----></div></div><!----></div><!----><!----><!----><!----><p></p><p data-sourcepos=\"7:1-7:357\"><span class=\"citation-2 recitation citation-end-2\">The 2025 elections, which encompassed positions from local government officials to senators, were closely watched, particularly given the ongoing interplay between prominent political families.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3136564947=\"\"><sup _ngcontent-ng-c3136564947=\"\" class=\"superscript\" data-turn-source-index=\"3\"><!----></sup></source-footnote></span> Early results indicate a tighter race than anticipated, with some established political figures facing unexpected challenges from emerging candidates.<sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3246093795=\"\"><!----><span _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\"> &nbsp; <button _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" aria-label=\"Learn More\" aria-controls=\"sources\" class=\"mat-mdc-tooltip-trigger button image-fade-on hide-from-message-actions\" aria-expanded=\"false\" jslog=\"220646;track:generic_click,impression\" aria-describedby=\"cdk-describedby-message-ng-1-26\" cdk-describedby-host=\"ng-1\"><mat-icon _ngcontent-ng-c3246093795=\"\" role=\"img\" class=\"mat-icon notranslate symbol google-symbols mat-ligature-font mat-icon-no-color\" aria-hidden=\"true\" data-mat-icon-type=\"font\" data-mat-icon-name=\"expand_more\" fonticon=\"expand_more\"></mat-icon></button><!----></span><!----><sources-carousel _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" id=\"sources\" _nghost-ng-c1495087998=\"\" class=\"ng-tns-c1495087998-37 hide-from-message-actions ng-star-inserted\" style=\"display: flex; visibility: hidden;\"></sources-carousel></sources-carousel-inline></p><div _ngcontent-ng-c1495087998=\"\" class=\"container ng-tns-c1495087998-37 hide\" jslog=\"220997;BardVeMetadataKey:[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[1,null,1]]\"><!----><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-container ng-tns-c1495087998-37\"><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-content ng-tns-c1495087998-37\"><div _ngcontent-ng-c1495087998=\"\" data-test-id=\"sources-carousel-source\" class=\"sources-carousel-source ng-tns-c1495087998-37 hide ng-star-inserted\"><!----></div><!----><!----></div></div><!----></div><!----><!----><!----><!----><p></p><p data-sourcepos=\"9:1-9:19\"><strong>Key Highlights:</strong></p><ul data-sourcepos=\"11:1-26:0\">\r\n<li data-sourcepos=\"11:1-13:215\"><strong>Senate Race:</strong>\r\n<ul data-sourcepos=\"12:5-13:215\">\r\n<li data-sourcepos=\"12:5-12:246\">The senatorial race has proven to be highly competitive. While some well-known political figures are maintaining their lead, several independent candidates and representatives from smaller political parties have gained significant traction.</li>\r\n<li data-sourcepos=\"13:5-13:215\"><span class=\"citation-3 recitation citation-end-3\">The potential for a shift in the Senate\'s composition has sparked discussions about its implications for future legislation and the balance of power between the executive and legislative branches.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3136564947=\"\"><sup _ngcontent-ng-c3136564947=\"\" class=\"superscript\" data-turn-source-index=\"4\"><!----></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3246093795=\"\"><!----><span _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\"> &nbsp; <button _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" aria-label=\"Learn More\" aria-controls=\"sources\" class=\"mat-mdc-tooltip-trigger button image-fade-on hide-from-message-actions\" aria-expanded=\"false\" jslog=\"220646;track:generic_click,impression\" aria-describedby=\"cdk-describedby-message-ng-1-26\" cdk-describedby-host=\"ng-1\"><mat-icon _ngcontent-ng-c3246093795=\"\" role=\"img\" class=\"mat-icon notranslate symbol google-symbols mat-ligature-font mat-icon-no-color\" aria-hidden=\"true\" data-mat-icon-type=\"font\" data-mat-icon-name=\"expand_more\" fonticon=\"expand_more\"></mat-icon></button><!----></span><!----><sources-carousel _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" id=\"sources\" _nghost-ng-c1495087998=\"\" class=\"ng-tns-c1495087998-38 hide-from-message-actions ng-star-inserted\" style=\"display: flex; visibility: hidden;\"><div _ngcontent-ng-c1495087998=\"\" class=\"container ng-tns-c1495087998-38 hide\" jslog=\"220997;BardVeMetadataKey:[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[1,null,1]]\"><!----><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-container ng-tns-c1495087998-38\"><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-content ng-tns-c1495087998-38\"><div _ngcontent-ng-c1495087998=\"\" data-test-id=\"sources-carousel-source\" class=\"sources-carousel-source ng-tns-c1495087998-38 hide ng-star-inserted\"><!----></div><!----><!----></div></div><!----></div><!----></sources-carousel><!----><!----><!----></sources-carousel-inline></li>\r\n</ul>\r\n</li>\r\n<li data-sourcepos=\"14:1-16:163\"><strong>Local Elections:</strong>\r\n<ul data-sourcepos=\"15:5-16:163\">\r\n<li data-sourcepos=\"15:5-15:130\">Local races across the provinces have seen a mix of traditional political strongholds retaining power and surprising upsets.</li>\r\n<li data-sourcepos=\"16:5-16:163\"><span class=\"citation-4 recitation citation-end-4\">Concerns regarding vote-buying and allegations of electoral fraud have surfaced in certain regions, prompting calls for thorough investigations.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3136564947=\"\"><sup _ngcontent-ng-c3136564947=\"\" class=\"superscript\" data-turn-source-index=\"5\"><!----></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3246093795=\"\"><!----><span _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\"> &nbsp; <button _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" aria-label=\"Learn More\" aria-controls=\"sources\" class=\"mat-mdc-tooltip-trigger button image-fade-on hide-from-message-actions\" aria-expanded=\"false\" jslog=\"220646;track:generic_click,impression\" aria-describedby=\"cdk-describedby-message-ng-1-26\" cdk-describedby-host=\"ng-1\"><mat-icon _ngcontent-ng-c3246093795=\"\" role=\"img\" class=\"mat-icon notranslate symbol google-symbols mat-ligature-font mat-icon-no-color\" aria-hidden=\"true\" data-mat-icon-type=\"font\" data-mat-icon-name=\"expand_more\" fonticon=\"expand_more\"></mat-icon></button><!----></span><!----><sources-carousel _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" id=\"sources\" _nghost-ng-c1495087998=\"\" class=\"ng-tns-c1495087998-39 hide-from-message-actions ng-star-inserted\" style=\"display: flex; visibility: hidden;\"><div _ngcontent-ng-c1495087998=\"\" class=\"container ng-tns-c1495087998-39 hide\" jslog=\"220997;BardVeMetadataKey:[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[1,null,1]]\"><!----><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-container ng-tns-c1495087998-39\"><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-content ng-tns-c1495087998-39\"><div _ngcontent-ng-c1495087998=\"\" data-test-id=\"sources-carousel-source\" class=\"sources-carousel-source ng-tns-c1495087998-39 hide ng-star-inserted\"><!----></div><!----><!----></div></div><!----></div><!----></sources-carousel><!----><!----><!----></sources-carousel-inline></li>\r\n</ul>\r\n</li>\r\n<li data-sourcepos=\"17:1-19:127\"><strong>Political Dynasties:</strong>\r\n<ul data-sourcepos=\"18:5-19:127\">\r\n<li data-sourcepos=\"18:5-18:236\"><span class=\"citation-5 recitation citation-end-5\">The influence of established political dynasties remains a significant factor in Philippine politics.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3136564947=\"\"><sup _ngcontent-ng-c3136564947=\"\" class=\"superscript\" data-turn-source-index=\"6\"><!----></sup></source-footnote></span> However, this election cycle has also highlighted a growing demand for political reform and greater accountability.<sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3246093795=\"\"><!----><span _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\"> &nbsp; <button _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" aria-label=\"Learn More\" aria-controls=\"sources\" class=\"mat-mdc-tooltip-trigger button image-fade-on hide-from-message-actions\" aria-expanded=\"false\" jslog=\"220646;track:generic_click,impression\" aria-describedby=\"cdk-describedby-message-ng-1-26\" cdk-describedby-host=\"ng-1\"><mat-icon _ngcontent-ng-c3246093795=\"\" role=\"img\" class=\"mat-icon notranslate symbol google-symbols mat-ligature-font mat-icon-no-color\" aria-hidden=\"true\" data-mat-icon-type=\"font\" data-mat-icon-name=\"expand_more\" fonticon=\"expand_more\"></mat-icon></button><!----></span><!----><sources-carousel _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" id=\"sources\" _nghost-ng-c1495087998=\"\" class=\"ng-tns-c1495087998-40 hide-from-message-actions ng-star-inserted\" style=\"display: flex; visibility: hidden;\"><div _ngcontent-ng-c1495087998=\"\" class=\"container ng-tns-c1495087998-40 hide\" jslog=\"220997;BardVeMetadataKey:[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[1,null,1]]\"><!----><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-container ng-tns-c1495087998-40\"><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-content ng-tns-c1495087998-40\"><div _ngcontent-ng-c1495087998=\"\" data-test-id=\"sources-carousel-source\" class=\"sources-carousel-source ng-tns-c1495087998-40 hide ng-star-inserted\"><!----></div><!----><!----></div></div><!----></div><!----></sources-carousel><!----><!----><!----></sources-carousel-inline></li>\r\n<li data-sourcepos=\"19:5-19:127\">There are some suprising results where some long standing political families are being defeated by new political figures.</li>\r\n</ul>\r\n</li>\r\n<li data-sourcepos=\"20:1-22:204\"><strong>Technological Integration:</strong>\r\n<ul data-sourcepos=\"21:5-22:204\">\r\n<li data-sourcepos=\"21:5-21:241\"><span class=\"citation-6 recitation citation-end-6\">The Commission on Elections (COMELEC) implemented expanded measures to enhance the transparency and efficiency of the electoral process, including increased use of technology in voter verification and results transmission.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3136564947=\"\"><sup _ngcontent-ng-c3136564947=\"\" class=\"superscript\" data-turn-source-index=\"7\"><!----></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3246093795=\"\"><!----><span _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\"> &nbsp; <button _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" aria-label=\"Learn More\" aria-controls=\"sources\" class=\"mat-mdc-tooltip-trigger button image-fade-on hide-from-message-actions\" aria-expanded=\"false\" jslog=\"220646;track:generic_click,impression\" aria-describedby=\"cdk-describedby-message-ng-1-26\" cdk-describedby-host=\"ng-1\"><mat-icon _ngcontent-ng-c3246093795=\"\" role=\"img\" class=\"mat-icon notranslate symbol google-symbols mat-ligature-font mat-icon-no-color\" aria-hidden=\"true\" data-mat-icon-type=\"font\" data-mat-icon-name=\"expand_more\" fonticon=\"expand_more\"></mat-icon></button><!----></span><!----><sources-carousel _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" id=\"sources\" _nghost-ng-c1495087998=\"\" class=\"ng-tns-c1495087998-41 hide-from-message-actions ng-star-inserted\" style=\"display: flex; visibility: hidden;\"><div _ngcontent-ng-c1495087998=\"\" class=\"container ng-tns-c1495087998-41 hide\" jslog=\"220997;BardVeMetadataKey:[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[1,null,1]]\"><!----><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-container ng-tns-c1495087998-41\"><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-content ng-tns-c1495087998-41\"><div _ngcontent-ng-c1495087998=\"\" data-test-id=\"sources-carousel-source\" class=\"sources-carousel-source ng-tns-c1495087998-41 hide ng-star-inserted\"><!----></div><!----><!----></div></div><!----></div><!----></sources-carousel><!----><!----><!----></sources-carousel-inline></li>\r\n<li data-sourcepos=\"22:5-22:204\"><span class=\"citation-7 recitation citation-end-7\">The introduction of internet voting for overseas voters in certain locations has been seen as a notable advancement, though it has also sparked debates about security and accessibility.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3136564947=\"\"><sup _ngcontent-ng-c3136564947=\"\" class=\"superscript\" data-turn-source-index=\"8\"><!----></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3246093795=\"\"><!----><span _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\"> &nbsp; <button _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" aria-label=\"Learn More\" aria-controls=\"sources\" class=\"mat-mdc-tooltip-trigger button image-fade-on hide-from-message-actions\" aria-expanded=\"false\" jslog=\"220646;track:generic_click,impression\" aria-describedby=\"cdk-describedby-message-ng-1-26\" cdk-describedby-host=\"ng-1\"><mat-icon _ngcontent-ng-c3246093795=\"\" role=\"img\" class=\"mat-icon notranslate symbol google-symbols mat-ligature-font mat-icon-no-color\" aria-hidden=\"true\" data-mat-icon-type=\"font\" data-mat-icon-name=\"expand_more\" fonticon=\"expand_more\"></mat-icon></button><!----></span><!----><sources-carousel _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" id=\"sources\" _nghost-ng-c1495087998=\"\" class=\"ng-tns-c1495087998-42 hide-from-message-actions ng-star-inserted\" style=\"display: flex; visibility: hidden;\"><div _ngcontent-ng-c1495087998=\"\" class=\"container ng-tns-c1495087998-42 hide\" jslog=\"220997;BardVeMetadataKey:[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[1,null,1]]\"><!----><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-container ng-tns-c1495087998-42\"><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-content ng-tns-c1495087998-42\"><div _ngcontent-ng-c1495087998=\"\" data-test-id=\"sources-carousel-source\" class=\"sources-carousel-source ng-tns-c1495087998-42 hide ng-star-inserted\"><!----></div><!----><!----></div></div><!----></div><!----></sources-carousel><!----><!----><!----></sources-carousel-inline></li>\r\n</ul>\r\n</li>\r\n<li data-sourcepos=\"23:1-26:0\"><strong>Civil Society Engagement:</strong>\r\n<ul data-sourcepos=\"24:5-26:0\">\r\n<li data-sourcepos=\"24:5-24:156\">Civil society organizations played a crucial role in monitoring the elections, advocating for clean and fair practices, and providing voter education.</li>\r\n<li data-sourcepos=\"25:5-26:0\"><span class=\"citation-8 recitation citation-end-8\">International election observers were also present, and are expected to release reports on their findings.<source-footnote ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3136564947=\"\"><sup _ngcontent-ng-c3136564947=\"\" class=\"superscript\" data-turn-source-index=\"9\"><!----></sup></source-footnote></span><sources-carousel-inline ng-version=\"0.0.0-PLACEHOLDER\" _nghost-ng-c3246093795=\"\"><!----><span _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" class=\"button-container hide-from-message-actions ng-star-inserted\"> &nbsp; <button _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" aria-label=\"Learn More\" aria-controls=\"sources\" class=\"mat-mdc-tooltip-trigger button image-fade-on hide-from-message-actions\" aria-expanded=\"false\" jslog=\"220646;track:generic_click,impression\" aria-describedby=\"cdk-describedby-message-ng-1-26\" cdk-describedby-host=\"ng-1\"><mat-icon _ngcontent-ng-c3246093795=\"\" role=\"img\" class=\"mat-icon notranslate symbol google-symbols mat-ligature-font mat-icon-no-color\" aria-hidden=\"true\" data-mat-icon-type=\"font\" data-mat-icon-name=\"expand_more\" fonticon=\"expand_more\"></mat-icon></button><!----></span><!----><sources-carousel _ngcontent-ng-c3246093795=\"\" hide-from-message-actions=\"\" id=\"sources\" _nghost-ng-c1495087998=\"\" class=\"ng-tns-c1495087998-43 hide-from-message-actions ng-star-inserted\" style=\"display: flex; visibility: hidden;\"><div _ngcontent-ng-c1495087998=\"\" class=\"container ng-tns-c1495087998-43 hide\" jslog=\"220997;BardVeMetadataKey:[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,[1,null,1]]\"><!----><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-container ng-tns-c1495087998-43\"><div _ngcontent-ng-c1495087998=\"\" class=\"carousel-content ng-tns-c1495087998-43\"><div _ngcontent-ng-c1495087998=\"\" data-test-id=\"sources-carousel-source\" class=\"sources-carousel-source ng-tns-c1495087998-43 hide ng-star-inserted\"><!----></div><!----><!----></div></div><!----></div><!----></sources-carousel><!----><!----><!----></sources-carousel-inline></li>\r\n</ul>\r\n</li>\r\n</ul><p data-sourcepos=\"27:1-27:18\"><strong>Looking Ahead:</strong></p><p data-sourcepos=\"29:1-29:246\">As the nation awaits the official results, the focus is shifting to the potential consequences of the election outcomes. The new composition of the Senate and local governments will shape the Philippines\' political landscape for the coming years.</p><p data-sourcepos=\"31:1-31:166\">The 2025 elections have underscored the dynamic nature of Philippine politics, where traditional power structures are being challenged by calls for change and reform.</p><p data-sourcepos=\"33:1-33:152\">It is important to remember that this article is fictional, and that for accurate information on the philippine elections, to use verified news sources.</p>', '', 1, '2025-05-12 15:34:57', 'published', 1),
(13, 'TSPI 40TH ANNIVERSARY CELEBRATION HIGHLIGHTS GOD’S FAITHFULNESS', 'tspi-40th-anniversary-celebration-highlights-god-s-faithfulness', 'http://localhost/TSPI/uploads/media/68221865c2706_40th-logo-1200x848.png', '<div class=\"video-embed-container\"><iframe src=\"https://www.youtube.com/embed/_FuWYOcfr2k\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"\"></iframe></div><p><p>TSPI gratefully celebrates its 40th anniversary, on October 2021 with the theme “Sustained by God’s faithfulness” inspired by Isaiah 46:4.&nbsp; It is a year-long event that started with weekly virtual but up close and personal fellowship series with the Board of Trustees and TSPI employees.&nbsp; Each Trustee shared why they are in TSPI and how their passion and purpose align with their voluntary service to the organization.&nbsp; Their humble life testimonies and heartfelt messages that inspired the entire TSPI family resonate into the ultimate purpose of serving – to give glory and praises to God!</p><p><br><p>A whole day celebration was held on October 29, 2021, with a Thanksgiving Mass officiated by Rev. Fr. Rhoderick L. Castro, parish priest from National Shrine of Our Lady of Guadalupe, Makati City. In his homily, he cited events in the bible that made forty as a significant number and that indeed TSPI’s 40th year must be celebrated with renewed spirit, grateful heart for the years that passed, and great expectation for the better years ahead.</p><p><br><p>Atty. Lamberto L. Meer, TSPI Chairman, formally opened the celebration by sharing his two important reflections as he reminisced his experience 15-20 years ago in a conference in Uganda, Africa. Firstly, God loves us so much that He will send people to show His love for us. Secondly, God calls us to serve, for our clients, for our colleagues, for each other but ultimately for Him!&nbsp; Just like TSPI being used by God as a channel to show His love to our less fortunate brothers.&nbsp; Each and every employee of TSPI is being empowered to proclaim God’s goodness through delivering its services with love and care.</p><div class=\"video-embed-container\"><iframe src=\"https://www.youtube.com/embed/PAYGwUyW0oQ\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"\"></iframe></div></p><p><p>The story of 85-year old guest Mrs. Belen Marciano, the old wife of late Mr. Avellino Marciano (the first TSPI client) refreshes the memory of how TSPI started its mission. She vividly shared how TSPI trusted and truly cared for them and still considered herself a member even after 40 years. “Ang loan ko ay character loan, walang collateral at napakababa pa ng interest. Pinahiram nila ako dahil may tiwala sila sa akin… Lord led us step-by-step.” She also recalled how they encouraged one another during bible studies.</p><p><br><p>One of the highlights of the celebration was the TSPI client recognition given to loyal clients, model TAAS agents, model client microentrepreneurs &amp; farmers (Unlad Awards) and outstanding client leaders. The representatives of each award category gave thanksgiving messages about how TSPI helped them as well as their co-members transformed their lives and their families.&nbsp; They expressed their gratitude for being trusted with small livelihood/farm loans along with other programs of TSPI that helped improve their family’s quality of life. Likewise, they are grateful for the sense of dignity and self-confidence they gained through exposures to various client center activities, and for their strengthened faith in God through being part of a Christian organization.<br><div class=\"video-embed-container\"><iframe src=\"https://www.youtube.com/embed/s3xcP6t0QKM\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"\"></iframe></div><p>TSPI was also honored by the warm messages and greetings of partners and alliances from the government, private and civic organizations. Moreover, heartfelt messages by respected heads of other microfinance organizations that were established in partnership with TSPI were both humbling and elating&nbsp; &nbsp;– President and CEO Angel L. de Leon Jr. of Taytay sa Kauswagan, Inc. (TSKI); President and CEO Rolando B. Victoria of Alalay sa Kaunlaran, Inc. (ASKI); and President and CEO Alma M. Estolas of Rangtay sa Pagrang-ay Microfinance, Inc. (RPMI).&nbsp; They honored TSPI, being their “mother organization”, for being steadfast in its vision and mission throughout the years and rooted for more fruitful years of its service to the poor.</p><div class=\"video-embed-container\"><iframe src=\"https://www.youtube.com/embed/n4A4urUiVAA\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"\"></iframe></div></p><p><p>In concluding the wonderful celebration, TSPI President Mr. Rene E. Cristobal, reminded everyone of the three “S” of how we can be sustained by God’s faithfulness: (1) We are Stewards – we are only trustees of given position, possession and popularity; (2) We are Servants – we must be good followers as we give not just opportunities but service; and (3) We are Shepherds – we should know our flock whom we serve and should embrace them.&nbsp; As he would always impart to TSPI family, “Ibigin natin ang ating kapwa tulad ng pag-ibig natin sa ating mga sarili sapagkat una tayong inibig ng Diyos nang tayo’y nasa kadiliman pa.”</p><p><br><p>As God said “I am He, I am He who will sustain you. I have made you and I will carry you; I will sustain you and I will rescue you. (Isaiah 46:4) TSPI has been and will always be dwelling on God’s faithful promises – sustained by God’s faithfulness!</p></p></p></p></p></p></p><p><br></p>', '', 1, '2025-05-12 15:51:17', 'published', 1),
(14, 'PLAQUE OF RECOGNITION FROM WATER.ORG PHILIPPINES', 'plaque-of-recognition-from-water-org-philippines', NULL, '<p style=\"margin-bottom: 1.75em; color: rgb(122, 122, 122); font-family: Roboto, sans-serif; font-size: 16px;\"><span style=\"color: rgb(0, 0, 0);\">On July 24, 2019, during Water.org Partner’s Night held at Manila Prince Hotel, Ermita, Manila, TSPI is recognized for its unwavering cooperation with Water.org in strengthening its capacity in developing and implementing its Water Credit Program.&nbsp; TSPI is among the top 3 Water Credit partners of Water.org based on the percentage of clients to the total client base, who are served through affordable financing for improved access to safe water and sanitary toilet facility. As of July 2019, TSPI has served a total of 44,826 clients through its sanitation loan program, with support from Water.org.</span></p><p style=\"text-align: center; margin-bottom: 1.75em; color: rgb(122, 122, 122); font-family: Roboto, sans-serif; font-size: 16px;\"><img src=\"https://tspi.org/wp-content/uploads/2019/08/tspi-awards_1.jpg\"><span style=\"color: rgb(0, 0, 0);\"></span></p><p style=\"margin-bottom: 1.75em; color: rgb(122, 122, 122); font-family: Roboto, sans-serif; font-size: 16px;\"><span style=\"color: rgb(0, 0, 0);\">Water.org is an international NGO based in the USA, working on projects in Africa, Latin America and Asia.&nbsp; Water.org is working with large and medium scale microfinance institutions to develop scalable and sustainable water and sanitation loan products.</span></p>', '', 1, '2025-05-16 07:05:05', 'published', 4);

-- --------------------------------------------------------

--
-- Table structure for table `content_categories`
--

CREATE TABLE `content_categories` (
  `content_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content_categories`
--

INSERT INTO `content_categories` (`content_id`, `category_id`) VALUES
(8, 2),
(8, 3),
(10, 1),
(10, 2),
(11, 1),
(11, 3),
(12, 1),
(12, 3),
(13, 2),
(13, 3),
(14, 8);

-- --------------------------------------------------------

--
-- Table structure for table `content_tags`
--

CREATE TABLE `content_tags` (
  `content_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content_tags`
--

INSERT INTO `content_tags` (`content_id`, `tag_id`) VALUES
(8, 4),
(8, 5),
(10, 4),
(12, 7),
(13, 8);

-- --------------------------------------------------------

--
-- Table structure for table `content_votes`
--

CREATE TABLE `content_votes` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote` tinyint(1) NOT NULL COMMENT '1 for upvote, -1 for downvote',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content_votes`
--

INSERT INTO `content_votes` (`id`, `content_id`, `user_id`, `vote`, `created_at`, `updated_at`) VALUES
(18, 13, 1, 1, '2025-05-16 02:45:48', '2025-05-16 02:45:48'),
(22, 14, 1, 1, '2025-05-16 19:09:31', '2025-05-16 19:09:31'),
(24, 12, 1, 1, '2025-05-17 01:33:10', '2025-05-17 01:33:12'),
(29, 14, 16, 1, '2025-05-19 03:23:06', '2025-05-19 03:23:06'),
(31, 14, 17, 1, '2025-05-22 12:54:56', '2025-05-22 12:54:56');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `verification_code` varchar(32) NOT NULL,
  `expires_at` datetime NOT NULL,
  `new_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `user_id`, `verification_code`, `expires_at`, `new_email`, `created_at`) VALUES
(23, 19, '87fa94aef6df0ed4da73a390f006efa9', '2025-05-29 10:06:26', NULL, '2025-05-28 08:06:26');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `file_path`, `mime_type`, `uploaded_by`, `uploaded_at`) VALUES
(4, 'uploads/media/681e42975ea5c_6.jpg', 'image/jpeg', 1, '2025-05-10 01:59:51'),
(7, 'uploads/media/681e566c6941a_BGC-CREDITS-TO-JESON-CABILIC.jpg', 'image/jpeg', 1, '2025-05-10 03:24:28'),
(8, 'uploads/media/6822022eeeda4_manda.jpg', 'image/jpeg', 1, '2025-05-12 22:14:07'),
(9, 'uploads/media/682215087cd7e_2022-05-07T030018Z_1145557179_RC202U9GNVPK_RTRMADP_3_PHILIPPINES-ELECTION.png', 'image/png', 1, '2025-05-12 23:34:32'),
(10, 'uploads/media/68221865c2706_40th-logo-1200x848.png', 'image/png', 1, '2025-05-12 23:48:53'),
(11, 'uploads/media/6826e36a6d74c_wo-1.jpg', 'image/jpeg', 1, '2025-05-16 15:04:10'),
(13, 'uploads/media/6830f2331a196_chairman.jpg', 'image/jpeg', 1, '2025-05-24 06:09:55');

-- --------------------------------------------------------

--
-- Table structure for table `members_information`
--

CREATE TABLE `members_information` (
  `id` int(11) NOT NULL,
  `fk_user_id` int(11) DEFAULT NULL,
  `branch` varchar(100) NOT NULL,
  `cid_no` varchar(50) NOT NULL,
  `center_no` varchar(50) DEFAULT NULL,
  `blip_mc` varchar(6) DEFAULT NULL,
  `lpip_mc` varchar(6) DEFAULT NULL,
  `lmip_mc` varchar(6) DEFAULT NULL,
  `plans` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`plans`)),
  `classification` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`classification`)),
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `civil_status` varchar(20) DEFAULT NULL,
  `birthdate` date NOT NULL,
  `age` int(11) NOT NULL DEFAULT 0,
  `birth_place` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `cell_phone` varchar(20) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `other_valid_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`other_valid_ids`)),
  `mothers_maiden_last_name` varchar(100) DEFAULT NULL,
  `mothers_maiden_first_name` varchar(100) DEFAULT NULL,
  `mothers_maiden_middle_name` varchar(100) DEFAULT NULL,
  `present_address` varchar(255) DEFAULT NULL,
  `present_brgy_code` varchar(50) DEFAULT NULL,
  `present_zip_code` varchar(20) DEFAULT NULL,
  `permanent_address` varchar(255) DEFAULT NULL,
  `permanent_brgy_code` varchar(50) DEFAULT NULL,
  `permanent_zip_code` varchar(20) DEFAULT NULL,
  `home_ownership` varchar(50) DEFAULT NULL,
  `length_of_stay` int(11) DEFAULT NULL,
  `primary_business` varchar(255) DEFAULT NULL,
  `years_in_business` int(11) DEFAULT NULL,
  `business_address` text DEFAULT NULL,
  `other_income_source_1` varchar(255) DEFAULT NULL,
  `other_income_source_2` varchar(255) DEFAULT NULL,
  `other_income_source_3` varchar(255) DEFAULT NULL,
  `other_income_source_4` varchar(255) DEFAULT NULL,
  `spouse_name` varchar(255) DEFAULT NULL,
  `spouse_birthdate` date DEFAULT NULL,
  `spouse_occupation` varchar(255) DEFAULT NULL,
  `spouse_id_number` varchar(100) DEFAULT NULL,
  `spouse_age` int(11) DEFAULT NULL,
  `beneficiary_fn_1` varchar(100) DEFAULT NULL,
  `beneficiary_ln_1` varchar(100) DEFAULT NULL,
  `beneficiary_mi_1` varchar(10) DEFAULT NULL,
  `beneficiary_birthdate_1` date DEFAULT NULL,
  `beneficiary_gender_1` char(1) DEFAULT NULL,
  `beneficiary_relationship_1` varchar(100) DEFAULT NULL,
  `beneficiary_dependent_1` tinyint(1) NOT NULL DEFAULT 0,
  `beneficiary_fn_2` varchar(100) DEFAULT NULL,
  `beneficiary_ln_2` varchar(100) DEFAULT NULL,
  `beneficiary_mi_2` varchar(10) DEFAULT NULL,
  `beneficiary_birthdate_2` date DEFAULT NULL,
  `beneficiary_gender_2` char(1) DEFAULT NULL,
  `beneficiary_relationship_2` varchar(100) DEFAULT NULL,
  `beneficiary_dependent_2` tinyint(1) NOT NULL DEFAULT 0,
  `beneficiary_fn_3` varchar(100) DEFAULT NULL,
  `beneficiary_ln_3` varchar(100) DEFAULT NULL,
  `beneficiary_mi_3` varchar(10) DEFAULT NULL,
  `beneficiary_birthdate_3` date DEFAULT NULL,
  `beneficiary_gender_3` char(1) DEFAULT NULL,
  `beneficiary_relationship_3` varchar(100) DEFAULT NULL,
  `beneficiary_dependent_3` tinyint(1) NOT NULL DEFAULT 0,
  `beneficiary_fn_4` varchar(100) DEFAULT NULL,
  `beneficiary_ln_4` varchar(100) DEFAULT NULL,
  `beneficiary_mi_4` varchar(10) DEFAULT NULL,
  `beneficiary_birthdate_4` date DEFAULT NULL,
  `beneficiary_gender_4` char(1) DEFAULT NULL,
  `beneficiary_relationship_4` varchar(100) DEFAULT NULL,
  `beneficiary_dependent_4` tinyint(1) NOT NULL DEFAULT 0,
  `beneficiary_fn_5` varchar(100) DEFAULT NULL,
  `beneficiary_ln_5` varchar(100) DEFAULT NULL,
  `beneficiary_mi_5` varchar(10) DEFAULT NULL,
  `beneficiary_birthdate_5` date DEFAULT NULL,
  `beneficiary_gender_5` char(1) DEFAULT NULL,
  `beneficiary_relationship_5` varchar(100) DEFAULT NULL,
  `beneficiary_dependent_5` tinyint(1) NOT NULL DEFAULT 0,
  `trustee_name` varchar(255) DEFAULT NULL,
  `trustee_birthdate` date DEFAULT NULL,
  `trustee_relationship` varchar(100) DEFAULT NULL,
  `member_name` varchar(255) DEFAULT NULL,
  `sig_beneficiary_name` varchar(255) DEFAULT NULL,
  `member_signature` varchar(255) DEFAULT NULL,
  `beneficiary_signature` varchar(255) DEFAULT NULL,
  `disclaimer_agreement` tinyint(1) NOT NULL DEFAULT 1,
  `valid_id_path` varchar(255) DEFAULT NULL,
  `spouse_valid_id_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `io_approved` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `io_name` varchar(255) DEFAULT NULL,
  `io_signature` varchar(255) DEFAULT NULL,
  `io_approval_date` timestamp NULL DEFAULT NULL,
  `io_notes` text DEFAULT NULL,
  `lo_approved` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `lo_name` varchar(255) DEFAULT NULL,
  `lo_signature` varchar(255) DEFAULT NULL,
  `lo_approval_date` timestamp NULL DEFAULT NULL,
  `secretary_approved` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `secretary_name` varchar(100) DEFAULT NULL,
  `secretary_signature` varchar(255) DEFAULT NULL,
  `secretary_comments` text DEFAULT NULL,
  `secretary_approval_date` timestamp NULL DEFAULT NULL,
  `lo_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores member application data with approvals from IO, LO, and secretary';

--
-- Dumping data for table `members_information`
--

INSERT INTO `members_information` (`id`, `fk_user_id`, `branch`, `cid_no`, `center_no`, `blip_mc`, `lpip_mc`, `lmip_mc`, `plans`, `classification`, `first_name`, `middle_name`, `last_name`, `gender`, `civil_status`, `birthdate`, `age`, `birth_place`, `email`, `cell_phone`, `contact_no`, `nationality`, `id_number`, `other_valid_ids`, `mothers_maiden_last_name`, `mothers_maiden_first_name`, `mothers_maiden_middle_name`, `present_address`, `present_brgy_code`, `present_zip_code`, `permanent_address`, `permanent_brgy_code`, `permanent_zip_code`, `home_ownership`, `length_of_stay`, `primary_business`, `years_in_business`, `business_address`, `other_income_source_1`, `other_income_source_2`, `other_income_source_3`, `other_income_source_4`, `spouse_name`, `spouse_birthdate`, `spouse_occupation`, `spouse_id_number`, `spouse_age`, `beneficiary_fn_1`, `beneficiary_ln_1`, `beneficiary_mi_1`, `beneficiary_birthdate_1`, `beneficiary_gender_1`, `beneficiary_relationship_1`, `beneficiary_dependent_1`, `beneficiary_fn_2`, `beneficiary_ln_2`, `beneficiary_mi_2`, `beneficiary_birthdate_2`, `beneficiary_gender_2`, `beneficiary_relationship_2`, `beneficiary_dependent_2`, `beneficiary_fn_3`, `beneficiary_ln_3`, `beneficiary_mi_3`, `beneficiary_birthdate_3`, `beneficiary_gender_3`, `beneficiary_relationship_3`, `beneficiary_dependent_3`, `beneficiary_fn_4`, `beneficiary_ln_4`, `beneficiary_mi_4`, `beneficiary_birthdate_4`, `beneficiary_gender_4`, `beneficiary_relationship_4`, `beneficiary_dependent_4`, `beneficiary_fn_5`, `beneficiary_ln_5`, `beneficiary_mi_5`, `beneficiary_birthdate_5`, `beneficiary_gender_5`, `beneficiary_relationship_5`, `beneficiary_dependent_5`, `trustee_name`, `trustee_birthdate`, `trustee_relationship`, `member_name`, `sig_beneficiary_name`, `member_signature`, `beneficiary_signature`, `disclaimer_agreement`, `valid_id_path`, `spouse_valid_id_path`, `status`, `io_approved`, `io_name`, `io_signature`, `io_approval_date`, `io_notes`, `lo_approved`, `lo_name`, `lo_signature`, `lo_approval_date`, `secretary_approved`, `secretary_name`, `secretary_signature`, `secretary_comments`, `secretary_approval_date`, `lo_notes`, `created_at`) VALUES
(23, 18, 'ALICIA', 'YZM5H1', '000', NULL, NULL, NULL, '[\"BLIP\"]', '[\"TKP\"]', 'RICHMOND LUIZ', 'NACOR', 'AVILA', 'Male', 'Single', '2003-12-17', 21, 'MAKATI CITY', 'ravila.k12042960@umak.edu.ph', '977264948', '', 'PHILIPPINES', '12312312312', '\"12312312312\"', 'NACOR', 'DOR', 'NACOR', 'PASCUA ST', '033', '1649', 'PASCUA ST.', '033', '1649', 'Owned', 18, 'CALL CENTER', 1, 'AYALA', '', '', '', '', '', NULL, '', '', NULL, '', '', '', NULL, '', '', 0, '', '', '', NULL, '', '', 0, '', '', '', NULL, '', '', 0, '', '', '', NULL, '', '', 0, '', '', '', NULL, '', '', 0, '', NULL, '', 'RICHMOND LUIZ N. AVILA', '', 'uploads/signatures/member_1748322472.png', NULL, 1, NULL, NULL, 'approved', 'approved', 'Maria Clara Reyes', 'uploads/signatures/io_23_1748329070.png', '2025-05-27 06:57:50', '', 'approved', 'Jose Rizal Mercado', 'uploads/signatures/lo_23_1748338627.png', '2025-05-27 09:37:07', 'approved', 'Maria Santos', 'uploads/signatures/secretary_sig_23_1748339186.png', '', '2025-05-27 09:46:26', '', '2025-05-27 05:07:52'),
(34, 16, 'TAGUIG', '3O78Q9', '094', '145015', '694889', NULL, '[\"BLIP\",\"LPIP\"]', '[\"TKP\"]', 'RICH DARIEN', 'CAGAYAT', 'CUSTODIO', 'MALE', 'MARRIED', '2003-07-04', 21, 'MANDALUYONG CITY', 'ritscstdio@gmail.com', '9454856152', '', 'FILIPINO', '4154-7632-7483-0794', NULL, 'CAGAYAT', 'CUSTODIO', 'DALAGAN', '626 TEJEROS, MAKATI CITY, PHILIPPINES, 1204', '029', '1204', '626 TEJEROS, MAKATI CITY, PHILIPPINES, 1204', '029', '1204', 'Owned', 21, 'CYCLING BUSINESS', 2, '626 TEJEROS, MAKATI CITY, PHILIPPINES, 1204', '', '', '', '', 'CLAIRE ANN TUBONGBANUA CLARIN', '2003-12-06', 'ACCOUNTANT', 'K12047139', 21, 'CLAIRE ANN', 'CLARIN', 'T', '2003-12-06', 'F', 'SPOUSE', 0, '', '', '', NULL, '', '', 0, '', '', '', NULL, '', '', 0, '', '', '', NULL, '', '', 0, '', '', '', NULL, '', '', 0, 'CLAIRE ANN T. CLARIN', '2003-12-06', 'SPOUSE', 'RICH DARIEN C. CUSTODIO', 'CLAIRE ANN T. CLARIN', 'uploads/signatures/member_1748387101.png', 'uploads/signatures/beneficiary_1748387101.png', 1, 'uploads/valid_ids/id_16_1748387101.png', 'uploads/valid_ids/spouse_id_16_1748387101.png', 'pending', 'approved', 'Maria Clara Reyes', 'uploads/signatures/io_34_1748388582.png', '2025-05-27 23:29:42', '', 'approved', 'Jose Rizal Mercado', 'uploads/signatures/lo_34_1748388566.png', '2025-05-27 23:29:26', 'pending', 'Maria Santos', 'uploads/signatures/secretary_sig_34_1748434929.png', '', '2025-05-28 12:22:09', '', '2025-05-27 23:05:01');

--
-- Triggers `members_information`
--
DELIMITER $$
CREATE TRIGGER `before_member_approval` BEFORE UPDATE ON `members_information` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_member_update` BEFORE UPDATE ON `members_information` FOR EACH ROW BEGIN
    -- If both officers approved, automatically set status to approved
    IF (NEW.io_approved = 'approved' AND NEW.lo_approved = 'approved' AND NEW.secretary_approved = 'approved' AND OLD.status = 'pending') THEN
        SET NEW.status = 'approved';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reset_token` varchar(32) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `action`, `description`, `user_id`, `ip_address`, `created_at`) VALUES
(1, 'role_update', 'Updated user roles from old structure to new structure', NULL, NULL, '2025-05-27 07:24:53'),
(2, 'schema_update', 'Updated administrators table schema to use new role definitions', NULL, NULL, '2025-05-27 07:24:59');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`, `slug`) VALUES
(1, '#story', 'story'),
(2, 'asd', 'asd'),
(3, 'womp', 'womp'),
(4, 'city', 'city'),
(5, 'makati', 'makati'),
(7, 'elections', 'elections'),
(8, 'anniversary', 'anniversary');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'editor',
  `status` enum('active','inactive','banned') DEFAULT 'inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `email`, `role`, `status`, `created_at`, `profile_picture`) VALUES
(1, 'admin', '$2y$10$e3B3u4yaMGw77ausbK6rYOnvVOO4AuhuCx8clnC9ZvQgSntOGCN6C', 'Administrator', 'admin@tspi.org', 'admin', 'inactive', '2025-05-08 14:26:40', 'user_1_1747300417.png'),
(4, 'Jenn', '$2y$10$Vyf7OIaXDlJrUAyINq092emqZCto8Bj7atoKiter.9M0KrtYcU6i.', 'Jenn', 'jennice@gmail.com', 'comment_moderator', 'inactive', '2025-05-09 21:28:31', NULL),
(14, 'eimhios', '$2y$10$VTqCNL0chVwp9g/MEv/NbuDs0VZ0.wn/dQYvUEs0iMOatjtQlBYC6', 'Erich', 'erichdaler@gmail.com', 'user', 'active', '2025-05-14 10:21:10', NULL),
(15, 'jennjen', '$2y$10$wVIcvxdqLnAUK4atNrYqlOgoEdD6ZvrRQ194a.OKZqTSFlqO5OKFu', 'Jennice', 'alaina5escullar.jennice@gmail.com', 'user', 'active', '2025-05-14 13:52:16', NULL),
(16, 'ritscstdio', '$2y$10$0zQNJnXE4jFyWmBm78T1.eP7c1iOmLFrisziv8pHfkYgqV0MP7cQ2', 'Rich Darien', 'ritscstdio@gmail.com', 'user', 'active', '2025-05-18 17:31:42', 'user_16_1748365667.png'),
(17, 'eritscstdio', '$2y$10$0lYJ1oTXoUg879xT3Xs8HOmWRJE27PowtpO3kHg5tl3Q5nm8EoGbW', 'Erich Daler', 'megumin.bakuretsu.osu@gmail.com', 'user', 'active', '2025-05-22 04:20:08', NULL),
(18, 'Luiz1', '$2y$10$LQGi1z/kmPt8f6LbHTXDO.yLbtZUbshx4Wz1xQtZPqh4K2uFWnQ1e', 'Richmond Luiz N. Avila', 'ravila.k12042960@umak.edu.ph', 'user', 'active', '2025-05-27 04:49:07', NULL),
(19, 'fkjxi', '$2y$10$qQhD003kC9AtJeTt.lB3TuXp6ciYaB1mlZZUVOOcca9e19NmKd2h.', 'Erich Daler', 'fakejaxi@gmail.com', 'user', 'inactive', '2025-05-28 08:06:26', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_center_no` (`center_no`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`content_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `comment_votes`
--
ALTER TABLE `comment_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_comment_vote` (`comment_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `content`
--
ALTER TABLE `content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `content_categories`
--
ALTER TABLE `content_categories`
  ADD PRIMARY KEY (`content_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `content_tags`
--
ALTER TABLE `content_tags`
  ADD PRIMARY KEY (`content_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `content_votes`
--
ALTER TABLE `content_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_article_vote` (`content_id`,`user_id`),
  ADD KEY `content_votes_user_fk` (`user_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `members_information`
--
ALTER TABLE `members_information`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cid` (`cid_no`),
  ADD KEY `idx_secretary_approved` (`secretary_approved`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrators`
--
ALTER TABLE `administrators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `comment_votes`
--
ALTER TABLE `comment_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `content`
--
ALTER TABLE `content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `content_votes`
--
ALTER TABLE `content_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `members_information`
--
ALTER TABLE `members_information`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_content_fk` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `comment_votes`
--
ALTER TABLE `comment_votes`
  ADD CONSTRAINT `comment_votes_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content`
--
ALTER TABLE `content`
  ADD CONSTRAINT `content_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `content_categories`
--
ALTER TABLE `content_categories`
  ADD CONSTRAINT `content_categories_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `content_categories_content_fk` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_content_categories_content` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_tags`
--
ALTER TABLE `content_tags`
  ADD CONSTRAINT `fk_content_tags_content` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_content_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `content_votes`
--
ALTER TABLE `content_votes`
  ADD CONSTRAINT `content_votes_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_content_votes_content` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media`
--
ALTER TABLE `media`
  ADD CONSTRAINT `media_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
