-- Run this ONLY if you already imported the earlier schema.sql and have a reservations table without "ref".
USE kaapi_katte;
ALTER TABLE reservations ADD COLUMN ref VARCHAR(12) NULL AFTER id;
UPDATE reservations SET ref = CONCAT('KK-', LPAD(id, 6, '0')) WHERE ref IS NULL;
ALTER TABLE reservations MODIFY ref VARCHAR(12) NOT NULL, ADD UNIQUE (ref);
