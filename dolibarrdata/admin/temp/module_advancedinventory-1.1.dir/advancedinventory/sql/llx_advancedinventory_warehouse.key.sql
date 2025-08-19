-- ===================================================================
-- Copyright (C) 2025 Muhammad Abd ElRazik <mohaamaad7@gmail.com>
--
-- Keys for llx_advancedinventory_warehouse
-- ===================================================================

ALTER TABLE llx_advancedinventory_warehouse ADD INDEX idx_advancedinventory_warehouse_ref (ref);
ALTER TABLE llx_advancedinventory_warehouse ADD INDEX idx_advancedinventory_warehouse_fk_parent (fk_parent);
ALTER TABLE llx_advancedinventory_warehouse ADD INDEX idx_advancedinventory_warehouse_status (status);
ALTER TABLE llx_advancedinventory_warehouse ADD UNIQUE INDEX uk_advancedinventory_warehouse_ref (ref);

ALTER TABLE llx_advancedinventory_warehouse ADD CONSTRAINT fk_advancedinventory_warehouse_fk_parent FOREIGN KEY (fk_parent) REFERENCES llx_advancedinventory_warehouse(rowid);
ALTER TABLE llx_advancedinventory_warehouse ADD CONSTRAINT fk_advancedinventory_warehouse_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_advancedinventory_warehouse ADD CONSTRAINT fk_advancedinventory_warehouse_fk_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user(rowid);
