# Skills Management Feature

## Descripción / Description

### Español
Esta funcionalidad permite gestionar dinámicamente las habilidades mostradas en la sección de servicios del sitio web a través de una interfaz de administración integrada en la ventana de chat.

### English
This feature allows you to dynamically manage the skills displayed in the services section of the website through an admin interface integrated into the chat window.

## Características / Features

✅ **Interfaz Modal Intuitiva** / Intuitive Modal Interface
- Acceso fácil desde el botón de trofeo (🏆) en el chat
- Diseño responsivo que funciona en móviles y escritorio

✅ **Gestión Completa de Habilidades** / Complete Skills Management
- Agregar nuevas habilidades / Add new skills
- Eliminar habilidades existentes / Delete existing skills
- Soporte bilingüe (Inglés/Español) / Bilingual support (English/Spanish)
- Porcentajes personalizables (0-100%) / Customizable percentages (0-100%)

✅ **Persistencia de Datos** / Data Persistence
- Almacenamiento en archivo JSON
- Las habilidades se mantienen entre sesiones / Skills persist across sessions
- Actualización automática en el sitio web / Automatic website update

## Cómo Usar / How to Use

### Acceder al Administrador / Access the Admin Panel

1. Abre la ventana de chat en la esquina inferior derecha / Open the chat window in the bottom right corner
2. Haz clic en el botón del trofeo (🏆) en el encabezado del chat / Click the trophy button (🏆) in the chat header
3. Ingresa la contraseña de administrador cuando se solicite / Enter the admin password when prompted
   - **Contraseña predeterminada / Default password**: `ultrawebforge2024`
   - ⚠️ **IMPORTANTE**: Cambia esta contraseña en producción / IMPORTANT: Change this password in production

### Agregar una Habilidad / Add a Skill

1. En el modal de gestión de habilidades, completa el formulario:
   - **Nombre (Inglés)** / Name (English): Nombre de la habilidad en inglés
   - **Nombre (Español)** / Name (Spanish): Nombre de la habilidad en español
   - **Porcentaje** / Percentage: Nivel de competencia (0-100)

2. Haz clic en "Agregar" / Click "Add"
3. La habilidad aparecerá inmediatamente en la lista / The skill will appear immediately in the list
4. Recarga la página principal para ver los cambios / Reload the main page to see the changes

### Eliminar una Habilidad / Delete a Skill

1. En la lista de habilidades actuales, localiza la habilidad a eliminar
2. Haz clic en el botón de eliminar (🗑️) / Click the delete button (🗑️)
3. Confirma la eliminación / Confirm deletion
4. Recarga la página principal para ver los cambios / Reload the main page to see the changes

## Archivos del Sistema / System Files

### Backend / Backend
- `skills_manager.php` - API REST para CRUD de habilidades / REST API for skills CRUD
- `skills_loader.php` - Función helper para cargar habilidades / Helper function to load skills
- `data/skills.json` - Almacenamiento de datos (auto-generado) / Data storage (auto-generated)

### Frontend / Frontend
- `assets/js/skills-manager.js` - Lógica del modal y operaciones / Modal logic and operations
- `assets/css/skills-manager.css` - Estilos del modal / Modal styles

### Integración / Integration
- `index.php` - Página principal actualizada para cargar habilidades dinámicamente / Main page updated to load skills dynamically

## Seguridad / Security

### Cambiar la Contraseña / Change Password

⚠️ **Es CRÍTICO cambiar la contraseña predeterminada en producción** / It is CRITICAL to change the default password in production

1. Edita el archivo `skills_manager.php`
2. Encuentra la línea:
   ```php
   define('ADMIN_PASSWORD', 'ultrawebforge2024');
   ```
3. Cambia `'ultrawebforge2024'` por tu contraseña segura / Change `'ultrawebforge2024'` to your secure password
4. Guarda el archivo / Save the file

### Recomendaciones de Seguridad / Security Recommendations

- ✅ Usa una contraseña fuerte (mínimo 12 caracteres) / Use a strong password (minimum 12 characters)
- ✅ Combina letras, números y símbolos / Combine letters, numbers, and symbols
- ✅ No compartas la contraseña / Don't share the password
- ✅ Considera implementar autenticación más robusta en el futuro / Consider implementing more robust authentication in the future

## Estructura de Datos / Data Structure

Las habilidades se almacenan en formato JSON:

```json
[
  {
    "id": 1,
    "name_en": "Website Development",
    "name_es": "Desarrollo de Sitios Web",
    "percentage": 84
  },
  {
    "id": 2,
    "name_en": "SEO & Marketing",
    "name_es": "SEO y Marketing",
    "percentage": 88
  }
]
```

## Solución de Problemas / Troubleshooting

### La contraseña no funciona / Password doesn't work
- Verifica que estés usando la contraseña correcta definida en `skills_manager.php`
- Asegúrate de no tener espacios al inicio o final / Make sure there are no leading or trailing spaces

### Los cambios no aparecen en el sitio / Changes don't appear on the site
- Recarga la página con `Ctrl+F5` (Windows) o `Cmd+Shift+R` (Mac) para limpiar el caché
- Verifica que el archivo `data/skills.json` exista y tenga permisos de lectura

### Error al guardar / Error saving
- Verifica que el directorio `data/` tenga permisos de escritura (755 o 775)
- Asegúrate de que el servidor web pueda crear archivos en ese directorio

## Personalización / Customization

### Estilos del Modal / Modal Styles
Edita `assets/css/skills-manager.css` para personalizar colores, tamaños, etc.

### Límites de Habilidades / Skills Limits
Por defecto, puedes agregar ilimitadas habilidades. Para limitar el número, edita `skills_manager.php`.

### Barras de Progreso / Progress Bars
Los colores y estilos de las barras se pueden modificar en `assets/css/uwf-main.css` buscando `.progress-skill-bar`.

## Soporte / Support

Para reportar problemas o solicitar mejoras, contacta al equipo de desarrollo.

---

**Versión / Version**: 1.0.0  
**Última actualización / Last update**: Febrero 2026 / February 2026
