ALTER TABLE zzc_category add COLUMN category_img VARCHAR(128);
ALTER TABLE zzc_goods add COLUMN goods_sales int DEFAULT 0;
ALTER TABLE zzc_goods_attr MODIFY COLUMN attr_price int;