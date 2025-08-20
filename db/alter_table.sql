ALTER TABLE equipamentos ADD COLUMN toner_info TEXT;
ALTER TABLE equipamentos ADD COLUMN is_datacard INTEGER DEFAULT 0;
ALTER TABLE equipamentos ADD COLUMN location TEXT;

