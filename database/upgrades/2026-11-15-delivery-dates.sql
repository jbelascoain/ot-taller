-- Upgrade para instalaciones existentes: completar fechas de entrega faltantes.
-- Se usa la fecha de última modificación como aproximación; si no existe, el ingreso.
UPDATE work_orders
SET delivery_date = COALESCE(DATE(updated_at), entry_date)
WHERE status = 'ENTREGADO'
  AND delivery_date IS NULL;
