UPDATE `s_core_menu` SET `onclick` = 'window.open("http://wiki.shopware.de/Shopware4-Beta-Guide_detail_813.html","Shopware Wiki","width=1024,height=600,scrollbars=yes")' WHERE `name` = 'Beta-FAQ';

-- //@UNDO

UPDATE `s_core_menu` SET `onclick` = 'window.open(''http://www.shopware.de/wiki'',''Shopware'',''width=800,height=550,scrollbars=yes'')' WHERE `s_core_menu`.`id` =41;
