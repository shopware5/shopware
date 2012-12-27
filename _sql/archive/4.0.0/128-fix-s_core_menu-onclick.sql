UPDATE s_core_menu SET onclick="createShopwareVersionMessage()", class="sprite-shopware-logo" WHERE name="Über Shopware";
UPDATE s_core_menu SET onclick="createBetaMessage()" WHERE name="Blog*";

-- //@UNDO

UPDATE s_core_menu SET onclick="window.Growl('{release}<br />(c)2010-2011 shopware AG');", class="ico2 information_frame" WHERE name="Über Shopware";
UPDATE s_core_menu SET onclick="Ext.MessageBox.alert('{s name=title/missing_in_beta namespace=backend/index/view/menu}{/s}', '{s name=content/missing_in_beta namespace=backend/index/view/menu}{/s}');" WHERE name="Blog*";
