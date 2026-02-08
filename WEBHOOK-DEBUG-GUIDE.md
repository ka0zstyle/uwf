# Guía de Diagnóstico - Webhook de Telegram NO Funciona

## Estado: DEBUGGING ACTIVADO ✅

Se ha agregado logging detallado en `chat_engine.php` para rastrear cada paso del webhook.

---

## Paso 1: Verificar que el Webhook está Configurado

```bash
# Reemplaza <TU_BOT_TOKEN> con tu token real
curl "https://api.telegram.org/bot<TU_BOT_TOKEN>/getWebhookInfo"
```

**Debe mostrar:**
- `"url": "https://tudominio.com/chat_engine.php"`
- `"pending_update_count": 0` (o número bajo)
- Sin errores en `last_error_message`

**Si no está configurado:**
```bash
curl -X POST "https://api.telegram.org/bot<TU_BOT_TOKEN>/setWebhook?url=https://tudominio.com/chat_engine.php"
```

---

## Paso 2: Verificar Logs del Servidor

Los logs ahora tienen prefijos específicos para identificar cada paso:

```bash
# Ver logs en tiempo real
tail -f /var/log/php_errors.log | grep "chat_engine"

# O si usas Apache
tail -f /var/log/apache2/error.log | grep "chat_engine"
```

### Logs que Deberías Ver

#### Cuando un USUARIO envía mensaje:
```
chat_engine POST: User message received - email=usuario@email.com, message=...
chat_engine POST: ✓ User message saved to database
chat_engine POST: Sending notification to Telegram...
chat_engine POST: ✓ Telegram notification sent
```

#### Cuando ADMIN responde desde Telegram:
```
chat_engine WEBHOOK: Raw input received: {...}
chat_engine WEBHOOK: JSON decoded, is_array=YES
chat_engine WEBHOOK: Message detected - chat_id=12345678, text=...
chat_engine WEBHOOK: TG_ADMIN_ID defined=YES, value=12345678
chat_engine WEBHOOK: Admin message confirmed, processing...
chat_engine WEBHOOK: Found last user - target=usuario@email.com
chat_engine WEBHOOK: Attempting INSERT - email=usuario@email.com, message=...
chat_engine WEBHOOK: ✓✓✓ SUCCESS - Message inserted into database for usuario@email.com
```

---

## Paso 3: Identificar el Problema

### Problema A: Webhook NO llega al servidor

**Síntoma:** No ves ningún log "chat_engine WEBHOOK" cuando respondes

**Causas posibles:**
1. Webhook no configurado en Telegram
2. URL incorrecta
3. SSL/HTTPS inválido
4. Firewall bloqueando

**Solución:**
```bash
# 1. Verificar webhook
curl "https://api.telegram.org/bot<TOKEN>/getWebhookInfo"

# 2. Reconfigurar
curl -X POST "https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://tudominio.com/chat_engine.php"

# 3. Verificar que el servidor responde
curl -I https://tudominio.com/chat_engine.php
```

### Problema B: Webhook llega pero TG_ADMIN_ID no coincide

**Síntoma:** Log dice "Not from admin - incoming=X, expected=Y"

**Causas posibles:**
1. TG_ADMIN_ID incorrecto en secrets.php
2. Respondiendo desde cuenta incorrecta

**Solución:**
1. Verifica tu chat_id correcto:
```bash
# Envía mensaje a tu bot y luego:
curl "https://api.telegram.org/bot<TOKEN>/getUpdates"
# Busca "chat":{"id": XXXXXX}
```

2. Actualiza secrets.php con el chat_id correcto:
```php
define('TG_ADMIN_ID', '12345678'); // Tu chat_id real
```

### Problema C: No encuentra email del usuario

**Síntoma:** Log dice "ERROR - No users found in database"

**Causas posibles:**
1. No hay usuarios en la tabla
2. Tabla vacía o nombres incorrectos

**Solución:**
```bash
# Conectarse a MySQL y verificar
mysql -u usuario -p nombre_bd

# Ver usuarios
SELECT email, sender, message, created_at 
FROM chat_messages 
WHERE sender='user' 
ORDER BY created_at DESC 
LIMIT 5;

# Si está vacío, envía un mensaje de prueba desde el sitio web primero
```

### Problema D: INSERT falla

**Síntoma:** Log dice "✗✗✗ EXECUTE FAILED" o "✗✗✗ PREPARE FAILED"

**Causas posibles:**
1. Tabla no existe
2. Columnas incorrectas
3. Permisos de base de datos

**Solución:**
```sql
-- Verificar estructura de tabla
DESCRIBE chat_messages;

-- Debe tener estas columnas:
-- email VARCHAR(255)
-- message TEXT
-- sender VARCHAR(10)
-- created_at DATETIME

-- Si no existe, crear:
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    sender ENUM('user', 'admin') NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_email (email),
    INDEX idx_sender_created (sender, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Problema E: Mensaje guardado pero no aparece en chat

**Síntoma:** Log dice "✓✓✓ SUCCESS" pero usuario no ve respuesta

**Causas posibles:**
1. Frontend no hace polling
2. Email diferente
3. Cache del navegador

**Solución:**
1. Verifica que el mensaje está en BD:
```sql
SELECT * FROM chat_messages 
WHERE sender='admin' 
ORDER BY created_at DESC 
LIMIT 5;
```

2. Verifica el email coincide:
```sql
SELECT email, sender, message 
FROM chat_messages 
WHERE email='usuario@email.com' 
ORDER BY created_at DESC;
```

3. Limpia cache del navegador (Ctrl+F5)

---

## Paso 4: Test Manual con Curl

Prueba el webhook directamente:

```bash
# Reemplaza los valores según tu configuración
curl -X POST "https://tudominio.com/chat_engine.php" \
  -H "Content-Type: application/json" \
  -d '{
    "message": {
      "chat": {"id": 12345678},
      "text": "test@example.com:Mensaje de prueba",
      "message_id": 1,
      "date": 1234567890
    }
  }'
```

Luego verifica los logs:
```bash
tail -20 /var/log/php_errors.log | grep "chat_engine WEBHOOK"
```

---

## Paso 5: Usar Script de Test

Usa el script `test_webhook.php` incluido:

```bash
# Sintaxis
php test_webhook.php [email] [mensaje]

# Ejemplos
php test_webhook.php "usuario@email.com" "Hola desde test"
php test_webhook.php "test@test.com" "Esto es una prueba"
```

---

## Checklist de Verificación

- [ ] Webhook configurado en Telegram (getWebhookInfo)
- [ ] URL es HTTPS válido
- [ ] TG_ADMIN_ID coincide con tu chat_id
- [ ] Tabla `chat_messages` existe y tiene estructura correcta
- [ ] Hay al menos un usuario en la tabla (envía mensaje desde web)
- [ ] Los logs muestran "✓✓✓ SUCCESS" cuando respondes
- [ ] El mensaje aparece en la base de datos
- [ ] El frontend hace polling (revisa cada 3 segundos)

---

## Logs de Ejemplo Correctos

### Flujo Completo Exitoso

```
# 1. Usuario envía mensaje
[timestamp] chat_engine POST: User message received - email=cliente@empresa.com, message=Hola necesito ayuda
[timestamp] chat_engine POST: ✓ User message saved to database
[timestamp] chat_engine POST: Sending notification to Telegram...
[timestamp] chat_engine POST: ✓ Telegram notification sent

# 2. Admin responde desde Telegram
[timestamp] chat_engine WEBHOOK: Raw input received: {"update_id":123456789,"message":{"message_id":1,"from":...
[timestamp] chat_engine WEBHOOK: JSON decoded, is_array=YES
[timestamp] chat_engine WEBHOOK: Message detected - chat_id=12345678, text=Claro, te ayudo
[timestamp] chat_engine WEBHOOK: TG_ADMIN_ID defined=YES, value=12345678
[timestamp] chat_engine WEBHOOK: Admin message confirmed, processing...
[timestamp] chat_engine WEBHOOK: No email in message, looking for last user...
[timestamp] chat_engine WEBHOOK: Found last user - target=cliente@empresa.com
[timestamp] chat_engine WEBHOOK: Attempting INSERT - email=cliente@empresa.com, message=Claro, te ayudo
[timestamp] chat_engine WEBHOOK: ✓✓✓ SUCCESS - Message inserted into database for cliente@empresa.com

# 3. Frontend carga mensajes
[timestamp] chat_engine: Loading messages for cliente@empresa.com
```

---

## Próximos Pasos

1. **Ejecuta el checklist** punto por punto
2. **Revisa los logs** después de cada acción
3. **Identifica en qué paso falla** usando los logs detallados
4. **Aplica la solución** correspondiente según la sección "Identificar el Problema"
5. **Prueba de nuevo** hasta ver "✓✓✓ SUCCESS" en los logs

---

## Comandos Útiles

```bash
# Ver últimos 50 logs relacionados con chat_engine
tail -50 /var/log/php_errors.log | grep "chat_engine"

# Ver logs en tiempo real
tail -f /var/log/php_errors.log | grep "chat_engine"

# Buscar errores específicos
grep "chat_engine WEBHOOK.*✗" /var/log/php_errors.log

# Buscar éxitos
grep "chat_engine WEBHOOK.*✓✓✓" /var/log/php_errors.log

# Ver estructura de tabla
mysql -u usuario -p -e "DESCRIBE chat_messages" nombre_bd

# Ver últimos mensajes
mysql -u usuario -p -e "SELECT email, sender, LEFT(message, 50) as msg, created_at FROM chat_messages ORDER BY created_at DESC LIMIT 10" nombre_bd
```

---

**Última actualización:** 2026-02-08  
**Estado:** Debugging activado con logs detallados
