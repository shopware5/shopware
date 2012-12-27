TRUNCATE TABLE s_core_countries_states;

set @deutschland = (SELECT id FROM s_core_countries WHERE countryiso='DE');

INSERT INTO `s_core_countries_states` (`id`, `countryID`, `name`, `shortcode`, `position`, `active`) VALUES
(2, @deutschland, 'Niedersachsen', 'NI', 0, 1),
(3, @deutschland, 'Nordrhein-Westfalen', 'NW', 0, 1),
(5, @deutschland, 'Baden-Württemberg', 'BW', 0, 1),
(6, @deutschland, 'Bayern', 'BY', 0, 1),
(7, @deutschland, 'Berlin', 'BE', 0, 1),
(8, @deutschland, 'Brandenburg', 'BB', 0, 1),
(9, @deutschland, 'Bremen', 'HB', 0, 1),
(10, @deutschland, 'Hamburg', 'HH', 0, 1),
(11, @deutschland, 'Hessen', 'HE', 0, 1),
(12, @deutschland, 'Mecklenburg-Vorpommern', 'MV', 0, 1),
(13, @deutschland, 'Rheinland-Pfalz', 'RP', 0, 1),
(14, @deutschland, 'Saarland', 'SL', 0, 1),
(15, @deutschland, 'Sachsen', 'SN', 0, 1),
(16, @deutschland, 'Sachsen-Anhalt', 'ST', 0, 1),
(17, @deutschland, 'Schleswig-Holstein', 'SH', 0, 1),
(18, @deutschland, 'Thüringen', 'TH', 0, 1);

set @usa = (SELECT id FROM s_core_countries WHERE countryiso='US');


INSERT INTO `s_core_countries_states` (`id`, `countryID`, `name`, `shortcode`, `position`, `active`) VALUES
(20, @usa, 'Alabama', 'AL', 0, 1),
(21, @usa, 'Alaska', 'AK', 0, 1),
(22, @usa, 'Arizona', 'AZ', 0, 1),
(23, @usa, 'Arkansas', 'AR', 0, 1),
(24, @usa, 'Kalifornien', 'CA', 0, 1),
(25, @usa, 'Colorado', 'CO', 0, 1),
(26, @usa, 'Connecticut', 'CT', 0, 1),
(27, @usa, 'Delaware', 'DE', 0, 1),
(28, @usa, 'Florida', 'FL', 0, 1),
(29, @usa, 'Georgia', 'GA', 0, 1),
(30, @usa, 'Hawaii', 'HI', 0, 1),
(31, @usa, 'Idaho', 'ID', 0, 1),
(32, @usa, 'Illinois', 'IL', 0, 1),
(33, @usa, 'Indiana', 'IN', 0, 1),
(34, @usa, 'Iowa', 'IA', 0, 1),
(35, @usa, 'Kansas', 'KS', 0, 1),
(36, @usa, 'Kentucky', 'KY', 0, 1),
(37, @usa, 'Louisiana', 'LA', 0, 1),
(38, @usa, 'Maine', 'ME', 0, 1),
(39, @usa, 'Maryland', 'MD', 0, 1),
(40, @usa, 'Massachusetts', 'MA', 0, 1),
(41, @usa, 'Michigan', 'MI', 0, 1),
(42, @usa, 'Minnesota', 'MN', 0, 1),
(43, @usa, 'Mississippi', 'MS', 0, 1),
(44, @usa, 'Missouri', 'MO', 0, 1),
(45, @usa, 'Montana', 'MT', 0, 1),
(46, @usa, 'Nebraska', 'NE', 0, 1),
(47, @usa, 'Nevada', 'NV', 0, 1),
(48, @usa, 'New Hampshire', 'NH', 0, 1),
(49, @usa, 'New Jersey', 'NJ', 0, 1),
(50, @usa, 'New Mexico', 'NM', 0, 1),
(51, @usa, 'New York', 'NY', 0, 1),
(52, @usa, 'North Carolina', 'NC', 0, 1),
(53, @usa, 'North Dakota', 'ND', 0, 1),
(54, @usa, 'Ohio', 'OH', 0, 1),
(55, @usa, 'Oklahoma', 'OK', 0, 1),
(56, @usa, 'Oregon', 'OR', 0, 1),
(57, @usa, 'Pennsylvania', 'PA', 0, 1),
(58, @usa, 'Rhode Island', 'RI', 0, 1),
(59, @usa, 'South Carolina', 'SC', 0, 1),
(60, @usa, 'South Dakota', 'SD', 0, 1),
(61, @usa, 'Tennessee', 'TN', 0, 1),
(62, @usa, 'Texas', 'TX', 0, 1),
(63, @usa, 'Utah', 'UT', 0, 1),
(64, @usa, 'Vermont', 'VT', 0, 1),
(65, @usa, 'Virginia', 'VA', 0, 1),
(66, @usa, 'Washington', 'WA', 0, 1),
(67, @usa, 'West Virginia', 'WV', 0, 1),
(68, @usa, 'Wisconsin', 'WI', 0, 1),
(69, @usa, 'Wyoming', 'WY', 0, 1);

-- //@UNDO
TRUNCATE TABLE s_core_countries_states;