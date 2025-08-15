-- ===================================================================
-- Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
--
-- Table for stock by location
-- ===================================================================

CREATE TABLE llx_advancedinventory_stock_location (
                                                      rowid               integer AUTO_INCREMENT PRIMARY KEY,
                                                      fk_product          integer NOT NULL,                    -- المنتج
                                                      fk_warehouse        integer NOT NULL,                    -- المخزن/الموقع
                                                      qty                 double(24,8) DEFAULT 0,              -- الكمية المتاحة
                                                      qty_reserved        double(24,8) DEFAULT 0,              -- الكمية المحجوزة
                                                      qty_in_transit      double(24,8) DEFAULT 0,              -- الكمية في الطريق
                                                      last_movement_date  datetime,                            -- تاريخ آخر حركة
                                                      last_inventory_date datetime,                            -- تاريخ آخر جرد
                                                      location_code       varchar(128),                        -- كود الموقع الدقيق
                                                      batch_number        varchar(128),                        -- رقم الدفعة
                                                      serial_number       varchar(128),                        -- الرقم التسلسلي
                                                      expiry_date         date,                                -- تاريخ انتهاء الصلاحية
                                                      manufacturing_date  date,                                -- تاريخ الإنتاج
                                                      date_creation       datetime NOT NULL,
                                                      tms                 timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                                      fk_user_creat       integer NOT NULL,
                                                      fk_user_modif       integer,
                                                      import_key          varchar(14)
) ENGINE=innodb DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
ALTER TABLE llx_advancedinventory_stock_location ADD INDEX idx_advancedinventory_stock_loc_fk_product (fk_product);
ALTER TABLE llx_advancedinventory_stock_location ADD INDEX idx_advancedinventory_stock_loc_fk_warehouse (fk_warehouse);
ALTER TABLE llx_advancedinventory_stock_location ADD INDEX idx_advancedinventory_stock_loc_batch (batch_number);
ALTER TABLE llx_advancedinventory_stock_location ADD INDEX idx_advancedinventory_stock_loc_expiry (expiry_date);

-- Foreign keys
ALTER TABLE llx_advancedinventory_stock_location ADD CONSTRAINT fk_advancedinventory_stock_loc_fk_product FOREIGN KEY (fk_product) REFERENCES llx_product(rowid) ON DELETE CASCADE;
ALTER TABLE llx_advancedinventory_stock_location ADD CONSTRAINT fk_advancedinventory_stock_loc_fk_warehouse FOREIGN KEY (fk_warehouse) REFERENCES llx_advancedinventory_warehouse(rowid);
ALTER TABLE llx_advancedinventory_stock_location ADD CONSTRAINT fk_advancedinventory_stock_loc_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_advancedinventory_stock_location ADD CONSTRAINT fk_advancedinventory_stock_loc_fk_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user(rowid);
