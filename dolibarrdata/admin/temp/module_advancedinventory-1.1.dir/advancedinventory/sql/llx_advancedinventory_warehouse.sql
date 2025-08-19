-- ===================================================================
-- Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
--
-- Table for Advanced Inventory Warehouses
-- ===================================================================

CREATE TABLE llx_advancedinventory_warehouse (
                                                 rowid           integer AUTO_INCREMENT PRIMARY KEY,
                                                 ref             varchar(128) NOT NULL,
                                                 label           varchar(255) NOT NULL,
                                                 description     text,
                                                 address         varchar(255),
                                                 zip             varchar(25),
                                                 town            varchar(50),
                                                 fk_country      integer DEFAULT 0,
                                                 phone           varchar(20),
                                                 fax             varchar(20),
                                                 email           varchar(128),
                                                 fk_parent       integer DEFAULT NULL,      -- للتسلسل الهرمي (مخزن > قاعة > رف > صندوق)
                                                 warehouse_type  varchar(50) DEFAULT 'main', -- main, hall, shelf, box
                                                 status          integer DEFAULT 1,          -- 0=disabled, 1=enabled
                                                 note_public     text,
                                                 note_private    text,
                                                 date_creation   datetime NOT NULL,
                                                 tms             timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                                 fk_user_creat   integer NOT NULL,
                                                 fk_user_modif   integer,
                                                 import_key      varchar(14)
) ENGINE=innodb DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
ALTER TABLE llx_advancedinventory_warehouse ADD INDEX idx_advancedinventory_warehouse_ref (ref);
ALTER TABLE llx_advancedinventory_warehouse ADD INDEX idx_advancedinventory_warehouse_fk_parent (fk_parent);
ALTER TABLE llx_advancedinventory_warehouse ADD INDEX idx_advancedinventory_warehouse_status (status);
ALTER TABLE llx_advancedinventory_warehouse ADD UNIQUE INDEX uk_advancedinventory_warehouse_ref (ref);

-- Foreign keys
ALTER TABLE llx_advancedinventory_warehouse ADD CONSTRAINT fk_advancedinventory_warehouse_fk_parent FOREIGN KEY (fk_parent) REFERENCES llx_advancedinventory_warehouse(rowid);
ALTER TABLE llx_advancedinventory_warehouse ADD CONSTRAINT fk_advancedinventory_warehouse_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_advancedinventory_warehouse ADD CONSTRAINT fk_advancedinventory_warehouse_fk_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user(rowid);
