UPDATE `s_core_menu` SET `name` = 'E-Mail Benachrichtigung', `onclick` = '', `class` = 'sprite-mail-forward', controller = 'Notification' WHERE `name` like 'E-Mail Benachrichtigung*';

-- //@UNDO

UPDATE `s_core_menu` SET `name` = 'E-Mail Benachrichtigung*', `onclick` = 'loadSkeleton("notificationStat");', `class` = 'loadSkeleton(''''notificationStat'''');', controller = 'Notifications' WHERE `name` like 'E-Mail Benachrichtigung';
