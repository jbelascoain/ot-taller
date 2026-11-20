-- Upgrade para instalaciones existentes: próximo servicio en la orden.
ALTER TABLE work_orders
    ADD COLUMN next_service TEXT NULL AFTER notes;
