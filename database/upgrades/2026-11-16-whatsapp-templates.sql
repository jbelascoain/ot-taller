-- Upgrade para instalaciones existentes: mensajes de WhatsApp por etapa.
SET NAMES utf8mb4;
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('whatsapp_quote_template', 'Hola {cliente}, el presupuesto de la orden {orden} ({moto}) está listo para aprobar. Total: {total}.'),
    ('whatsapp_ready_template', 'Hola {cliente}, tu moto {moto} ya está lista para retirar. Te esperamos en {taller}. Orden {orden}.');

-- Quita la frase final del mensaje de presupuesto en instalaciones que ya tenían el texto anterior.
UPDATE settings
SET setting_value = 'Hola {cliente}, el presupuesto de la orden {orden} ({moto}) está listo para aprobar. Total: {total}.'
WHERE setting_key = 'whatsapp_quote_template'
  AND setting_value = 'Hola {cliente}, el presupuesto de la orden {orden} ({moto}) está listo para aprobar. Total: {total}. Cualquier duda, respondé este mensaje.';
