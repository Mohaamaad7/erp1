CREATE TABLE llx_advancedinventory_product_types (
                                                     rowid           integer AUTO_INCREMENT PRIMARY KEY,
                                                     type_name       varchar(100) NOT NULL,          -- محلي، مستورد
                                                     type_code       varchar(20) NOT NULL,           -- LOCAL, IMPORTED
                                                     code_prefix     varchar(10) NOT NULL,           -- بادئة الكود
                                                     status          integer DEFAULT 1,              -- 0=disabled, 1=enabled
                                                     description     text,
                                                     date_creation   datetime NOT NULL,
                                                     tms             timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                                     fk_user_creat   integer NOT NULL,
                                                     fk_user_modif   integer,
                                                     import_key      varchar(14)
) ENGINE=innodb DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
ALTER TABLE llx_advancedinventory_product_types ADD UNIQUE INDEX uk_advinv_type_code (type_code);
ALTER TABLE llx_advancedinventory_product_types ADD INDEX idx_advinv_type_status (status);

-- Foreign keys
ALTER TABLE llx_advancedinventory_product_types ADD CONSTRAINT fk_advinv_type_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_advancedinventory_product_types ADD CONSTRAINT fk_advinv_type_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user(rowid);
