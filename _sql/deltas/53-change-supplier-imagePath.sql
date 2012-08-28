-- //
UPDATE s_articles_supplier SET s_articles_supplier.img=concat('images/supplier/',img)  WHERE img NOT LIKE 'images/supplier/%' AND img != '' AND img  NOT LIKE  'media/image/%';
-- //@UNDO
UPDATE s_articles_supplier SET img= replace(img,'images/supplier/','');