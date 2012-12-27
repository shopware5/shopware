UPDATE `s_core_menu` SET `name` = 'Newsletter', `onclick` = '', `controller`='NewsletterManager' WHERE  `name` LIKE 'Newsletter (Campaigns)%';

-- //@UNDO

UPDATE `s_core_menu` SET `name` = 'Newsletter (Campaigns)*', `onclick` = 'loadSkeleton(''mailcampaigns'')', `controller`='Newsletter' WHERE  `name` LIKE 'Newsletter%' AND `controller` = 'NewsletterManager';
