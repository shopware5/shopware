-- //

ALTER TABLE `s_campaigns_html` CHANGE `parentID` `parentID` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE `s_campaigns_containers` CHANGE `promotionID` `promotionID` INT( 11 ) NULL DEFAULT NULL ;

-- //@UNDO

--