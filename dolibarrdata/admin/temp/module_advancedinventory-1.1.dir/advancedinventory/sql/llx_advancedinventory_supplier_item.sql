-- ===================================================================
-- Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
--
-- Table for multiple suppliers per item
-- ===================================================================

CREATE TABLE llx_advancedinventory_supplier_item (
                                                     rowid               integer AUTO_INCREMENT PRIMARY KEY,
                                                     fk_product          integer NOT NULL,                    -- ربط مع المنتج
                                                     fk_soc              integer NOT NULL,                    -- ربط مع المورد
                                                     supplier_part_num   varchar(128),                        -- رقم القطعة عند المورد
                                                     supplier_label      varchar(255),                        -- وصف المورد للقطعة
                                                     lead_time_days      integer DEFAULT 0,                   -- مدة التوريد بالأيام
                                                     min_order_qty       double(24,8) DEFAULT 1,              -- أقل كمية للطلب
                                                     price               double(24,8) DEFAULT 0,              -- سعر الشراء
                                                     fk_multicurrency    integer,                             -- العملة
                                                     multicurrency_price double(24,8) DEFAULT 0,              -- السعر بالعملة الأجنبية
                                                     is_default          integer DEFAULT 0,                   -- المورد الافتراضي
                                                     last_order_date     date,                                -- تاريخ آخر طلب
                                                     quality_rating      integer DEFAULT 0,                   -- تقييم الجودة (1-5)
                                                     delivery_rating     integer DEFAULT 0,                   -- تقييم التسليم (1-5)
                                                     status              integer DEFAULT 1,                   -- 0=inactive, 1=active
                                                     note_public         text,
                                                     note_private        text,
                                                     date_creation       datetime NOT NULL,
                                                     tms                 timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                                     fk_user_creat       integer NOT NULL,
                                                     fk_user_modif       integer,
                                                     import_key          varchar(14)
) ENGINE=innodb DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes
ALTER TABLE llx_advancedinventory_supplier_item ADD INDEX idx_advancedinventory_supplier_item_fk_product (fk_product);
ALTER TABLE llx_advancedinventory_supplier_item ADD INDEX idx_advancedinventory_supplier_item_fk_soc (fk_soc);
ALTER TABLE llx_advancedinventory_supplier_item ADD INDEX idx_advancedinventory_supplier_item_supplier_part (supplier_part_num);
ALTER TABLE llx_advancedinventory_supplier_item ADD UNIQUE INDEX uk_advancedinventory_supplier_item (fk_product, fk_soc);

-- Foreign keys
ALTER TABLE llx_advancedinventory_supplier_item ADD CONSTRAINT fk_advancedinventory_supplier_item_fk_product FOREIGN KEY (fk_product) REFERENCES llx_product(rowid) ON DELETE CASCADE;
ALTER TABLE llx_advancedinventory_supplier_item ADD CONSTRAINT fk_advancedinventory_supplier_item_fk_soc FOREIGN KEY (fk_soc) REFERENCES llx_societe(rowid);
ALTER TABLE llx_advancedinventory_supplier_item ADD CONSTRAINT fk_advancedinventory_supplier_item_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_advancedinventory_supplier_item ADD CONSTRAINT fk_advancedinventory_supplier_item_fk_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user(rowid);
