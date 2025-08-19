CREATE TABLE llx_advancedinventory_product_categories (
rowid           integer AUTO_INCREMENT PRIMARY KEY,
category_name   varchar(255) NOT NULL,
category_code   varchar(50) NOT NULL,
fk_parent       integer DEFAULT NULL,           -- للتسلسل الهرمي
level           integer DEFAULT 0,              -- مستوى الفئة في الهرم
path            varchar(500),                   -- المسار الكامل للفئة
status          integer DEFAULT 1,              -- 0=disabled, 1=enabled
sort_order      integer DEFAULT 0,              -- ترتيب العرض
description     text,
date_creation   datetime NOT NULL,
tms             timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
fk_user_creat   integer NOT NULL,
fk_user_modif   integer,
import_key      varchar(14)
) ENGINE=innodb DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
