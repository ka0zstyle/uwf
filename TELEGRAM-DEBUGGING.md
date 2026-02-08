# Telegram Webhook - Solución de Problemas

## Si los mensajes aún no llegan desde Telegram

### Checklist de Verificación:

#### 1. Verificar Configuración del Webhook
```bash
# Consultar el estado del webhook
curl "https://api.telegram.org/bot<TU_BOT_TOKEN>/getWebhookInfo"
```

**Debe mostrar:**
- `"url": "https://tudominio.com/chat_engine.php"`
- `"pending_update_count": 0` (o número bajo)
- `"last_error_date"` no debe estar presente

**Si no está configurado o hay errores:**
```bash
# Configurar el webhook
curl -X POST "https://api.telegram.org/bot<TU_BOT_TOKEN>/setWebhook?url=https://tudominio.com/chat_engine.php"
```

#### 2. Verificar que el servidor acepta webhooks
```bash
# Probar el endpoint manualmente
curl -X POST "https://tudominio.com/chat_engine.php" \
  -H "Content-Type: application/json" \
  -d '{
    "message": {
      "chat": {"id": TU_CHAT_ID},
      "text": "test",
      "message_id": 123,
      "date": 1234567890
    }
  }'
```

#### 3. Revisar Logs del Servidor
```bash
# Ver logs en tiempo real
tail -f /var/log/php_errors.log | grep chat_engine

# O logs de Apache
tail -f /var/log/apache2/error.log | grep chat_engine
```

**Buscar estas líneas cuando envías un mensaje desde Telegram:**
- `chat_engine webhook received:` (confirma que el webhook llega)
- `chat_engine: Processing message from chat_id:` (confirma que se procesa)
- `chat_engine: Sending to last user:` (muestra a quién se envía)
- `chat_engine: ✓ Admin message saved successfully` (confirma éxito)

#### 4. Verificar secrets.php
```php
// Asegúrate de que estén definidos:
define('TG_TOKEN', 'tu_token_real_aqui');
define('TG_ADMIN_ID', 'tu_chat_id_aqui');
define('DB_HOST', 'localhost');
define('DB_USER', 'usuario_bd');
define('DB_PASS', 'contraseña_bd');
define('DB_NAME', 'nombre_bd');
```

#### 5. Probar Manualmente
```bash
# 1. Envía un mensaje desde el sitio web
# 2. Deberías recibir notificación en Telegram con formato:
#    👤 *Nuevo mensaje de:* `email@example.com:`
#    Contenido del mensaje

# 3. Responde en Telegram (simplemente escribe tu respuesta)
# 4. Revisa los logs para ver si se guardó:
tail -f /var/log/php_errors.log | grep "✓ Admin message saved"
```

### Problemas Comunes y Soluciones

#### Problema: "webhook not set"
**Solución:** Configurar el webhook con el comando curl de arriba

#### Problema: "SSL certificate problem"
**Solución:** Asegúrate de que tu sitio use HTTPS válido (Let's Encrypt)

#### Problema: "TG_ADMIN_ID not defined"
**Solución:** Verifica que secrets.php está en la ruta correcta: `/home/sistemx/secrets.php`

#### Problema: "No recent user found"
**Solución:** Primero debe haber al menos un usuario que haya enviado un mensaje desde el sitio

#### Problema: Mensajes no aparecen en el sitio
**Solución:** 
- Verifica que la base de datos esté funcionando
- Revisa que el chat esté abierto en el sitio (polling activo)
- Espera ~3 segundos (intervalo de polling)

### Debugging Avanzado

#### Activar más logging en chat_engine.php
Agrega al inicio del archivo:
```php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
```

#### Ver todos los webhooks pendientes
```bash
curl "https://api.telegram.org/bot<TU_BOT_TOKEN>/getUpdates"
```

#### Eliminar webhook y volver a configurar
```bash
# Eliminar
curl "https://api.telegram.org/bot<TU_BOT_TOKEN>/deleteWebhook"

# Esperar 5 segundos
sleep 5

# Configurar de nuevo
curl -X POST "https://api.telegram.org/bot<TU_BOT_TOKEN>/setWebhook?url=https://tudominio.com/chat_engine.php"
```

### Flujo Correcto Esperado

1. **Usuario envía mensaje desde sitio:**
   - Se guarda en BD (tabla `chat_messages`, sender='user')
   - Se envía notificación a Telegram
   - Log: `chat_engine: Message sent to Telegram successfully`

2. **Admin recibe en Telegram:**
   - Formato: `👤 *Nuevo mensaje de:* `email@example.com:`...`

3. **Admin responde en Telegram:**
   - Webhook recibe el mensaje
   - Log: `chat_engine webhook received:`
   - Log: `chat_engine: Processing message from chat_id:`
   - Log: `chat_engine: Sending to last user: email@example.com`
   - Se guarda en BD (sender='admin')
   - Log: `chat_engine: ✓ Admin message saved successfully for email@example.com`

4. **Usuario ve respuesta:**
   - Chat frontend hace polling cada 3 segundos
   - Carga mensajes nuevos de la BD
   - Muestra respuesta del admin

### Comando Útil para Testing

```bash
#!/bin/bash
# Guardar como telegram_debug.sh

echo "=== VERIFICANDO CONFIGURACIÓN DE TELEGRAM ==="
echo ""

# 1. Verificar webhook
echo "1. Estado del Webhook:"
curl -s "https://api.telegram.org/bot$1/getWebhookInfo" | python3 -m json.tool
echo ""

# 2. Ver logs recientes
echo "2. Logs Recientes:"
tail -20 /var/log/php_errors.log | grep chat_engine
echo ""

# 3. Verificar BD
echo "3. Últimos mensajes en BD:"
mysql -u usuario -p -e "SELECT email, sender, LEFT(message, 50) as message, created_at FROM chat_messages ORDER BY created_at DESC LIMIT 5;"
echo ""

echo "=== FIN DE DIAGNÓSTICO ==="
```

**Uso:**
```bash
chmod +x telegram_debug.sh
./telegram_debug.sh TU_BOT_TOKEN
```

### Resumen de Cambios Realizados

El código ahora es más simple:
- ✅ Solo un método: enviar al último usuario
- ✅ Sin formatos complicados
- ✅ Email con dos puntos: `email@example.com:`
- ✅ Sin mensajes de instrucciones

**Si después de verificar todo lo anterior los mensajes aún no llegan, proporciona:**
1. Los logs del servidor cuando envías un mensaje desde Telegram
2. La respuesta de `getWebhookInfo`
3. Cualquier error que aparezca en los logs
