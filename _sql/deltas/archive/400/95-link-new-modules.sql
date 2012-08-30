-- //

UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Customer');" WHERE `class` = "sprite-ui-scroll-pane-detail";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Order');" WHERE `class` = "sprite-sticky-notes-pin";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Article', { controller: 'Detail', action: 'New' });" WHERE `class` = "sprite-inbox--plus";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Article', { controller: 'List' });" WHERE `class` = "sprite-ui-scroll-pane-list";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.UserManager');" WHERE `class` = "sprite-user-silhouette";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Snippet');" WHERE name LIKE "Neue Templatebasis";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Banner');" WHERE `class` = "sprite-picture";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Form');" WHERE `class` = "sprite-application-form";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Mail');" WHERE `class` = "sprite-mail--pencil";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Supplier');" WHERE `class` = "sprite-truck";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Voucher');" WHERE `class` = "sprite-mail-open-image";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.ProductFeed');" WHERE `class` = "sprite-folder-export";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Premium');" WHERE `class` = "sprite-star";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Vote');" WHERE `class` = "sprite-balloon";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Shipping');" WHERE `class` = "sprite-envelope--arrow";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Overview');" WHERE `class` = "sprite-report-paper";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Analytics');" WHERE `class` = "sprite-chart";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Systeminfo');" WHERE `class` = "sprite-information";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Payment');" WHERE `class` = "sprite-credit-cards";
UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Site');" WHERE `class` = "sprite-documents";
UPDATE `s_core_menu` SET `name` = "Medienverwaltung", `onclick` = "openNewModule('Shopware.apps.MediaManager')", `class` = "sprite-inbox-image" WHERE `class` = "sprite-disk-return-black";

-- //@UNDO

UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('user');" WHERE `class` = "sprite-ui-scroll-pane-detail";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('orderlist');" WHERE `class` = "sprite-sticky-notes-pin";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('articles');" WHERE `class` = "sprite-inbox--plus";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('articlesfast')" WHERE `class` = "sprite-ui-scroll-pane-list";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('auth');" WHERE `class` = "sprite-user-silhouette";
UPDATE `s_core_menu` SET `onclick` = "openAction('snippetOld');" WHERE name LIKE "Neue Templatebasis";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('imagepromo');" WHERE `class` = "sprite-picture";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('support');" WHERE `class` = "sprite-application-form";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('mails');" WHERE `class` = "sprite-mail--pencil";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('supplier');" WHERE `class` = "sprite-truck";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('vouchers');" WHERE `class` = "sprite-mail-open-image";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('search_price');" WHERE `class` = "sprite-folder-export";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('premiums');" WHERE `class` = "sprite-star";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('vote');" WHERE `class` = "sprite-balloon";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('shipping');" WHERE `class` = "sprite-envelope--arrow";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('overview');" WHERE `class` = "sprite-report-paper";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('statistics');" WHERE `class` = "sprite-chart";
UPDATE `s_core_menu` SET `onclick` = "openAction('check');" WHERE `class` = "sprite-information";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('payment');" WHERE `class` = "sprite-credit-cards";
UPDATE `s_core_menu` SET `onclick` = "loadSkeleton('cmsstatic');" WHERE `class` = "sprite-documents";
UPDATE `s_core_menu` SET `name` = "Datei-Archiv", `onclick` = "loadSkeleton('browser')", `class` = "sprite-disk-return-black" WHERE `class` = "sprite-inbox-image";

--