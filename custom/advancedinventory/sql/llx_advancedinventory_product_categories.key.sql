-- Indexes
ALTER TABLE llx_advancedinventory_product_categories ADD INDEX idx_advinv_cat_parent (fk_parent);
ALTER TABLE llx_advancedinventory_product_categories ADD INDEX idx_advinv_cat_code (category_code);
ALTER TABLE llx_advancedinventory_product_categories ADD INDEX idx_advinv_cat_level (level);
ALTER TABLE llx_advancedinventory_product_categories ADD UNIQUE INDEX uk_advinv_cat_code (category_code);

-- Foreign keys
ALTER TABLE llx_advancedinventory_product_categories ADD CONSTRAINT fk_advinv_cat_parent FOREIGN KEY (fk_parent) REFERENCES llx_advancedinventory_product_categories(rowid) ON DELETE CASCADE;
ALTER TABLE llx_advancedinventory_product_categories ADD CONSTRAINT fk_advinv_cat_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_advancedinventory_product_categories ADD CONSTRAINT fk_advinv_cat_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user(rowid);
