UPDATE `s_addon_premiums` SET ordernumber='SW2001', ordernumber_export='PR2047' WHERE ordernumber='PR2047';
UPDATE `s_addon_premiums` SET ordernumber='SW2047', ordernumber_export='PR2049' WHERE ordernumber='PR2049';

-- //@UNDO

UPDATE `s_addon_premiums` SET ordernumber='PR2047', ordernumber_export='SW2001' WHERE ordernumber='SW2001';
UPDATE `s_addon_premiums` SET ordernumber='PR2049', ordernumber_export='SW2047' WHERE ordernumber='SW2047';
