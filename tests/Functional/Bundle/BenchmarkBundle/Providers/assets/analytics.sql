SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_statistics_visitors;

INSERT INTO `s_statistics_visitors` (`shopID`, `datum`, `pageimpressions`, `uniquevisits`, `deviceType`) VALUES
    (1, CURDATE(), 1, 5, 'desktop'),
    (1, CURDATE(), 2, 6, 'mobile'),
    (1, CURDATE() - INTERVAL 1 DAY, 3, 7, 'desktop'),
    (1, CURDATE() - INTERVAL 1 DAY, 4, 8, 'mobile'),
    (2, CURDATE() - INTERVAL 2 DAY, 123, 456, 'mobile'),
    (2, CURDATE() - INTERVAL 1 DAY, 9, 10, 'mobile');

SET FOREIGN_KEY_CHECKS=1;
