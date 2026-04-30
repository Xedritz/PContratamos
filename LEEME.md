# 📦 Sitio Web — Contratamos Complementos

Guía rápida para subir y mantener el sitio en **Hostinger**.

---

## 📁 Archivos incluidos

| Archivo            | Qué es                                                  |
|--------------------|---------------------------------------------------------|
| `index.html`       | Página principal del sitio                              |
| `enviar.php`       | Recibe los formularios y envía los correos              |
| `logo.png`         | Logo principal (fondo transparente)                     |
| `logo-white.png`   | Logo en blanco (para el footer oscuro)                  |
| `LEEME.md`         | Este archivo (no es necesario subirlo)                  |

---

## 🚀 Subir a Hostinger (primera vez)

1. Inicie sesión en **hPanel** de Hostinger
2. Vaya a **Archivos → Administrador de archivos**
3. Entre a la carpeta **`public_html`**
4. Suba TODOS los archivos (excepto este LEEME.md):
   - `index.html`
   - `enviar.php`
   - `logo.png`
   - `logo-white.png`

¡Eso es todo! El sitio queda funcionando inmediatamente en su dominio.

---

## ⚙️ Configuración del envío de correos

Para que los formularios envíen correo correctamente, **debe crear un correo "remitente"** en Hostinger:

### Paso 1 — Crear el correo remitente
1. En hPanel vaya a **Correos electrónicos → Cuentas de correo**
2. Click en **Crear cuenta de correo**
3. Cree esta cuenta:
   - **Correo:** `no-responder@contratamoscomplementos.com`
   - **Contraseña:** la que usted prefiera (no se usará para enviar, solo es requerida)
4. Guarde

### Paso 2 — Verificar que funciona
1. Abra el sitio en el navegador
2. Llene el formulario de "Cotización" con datos de prueba
3. Envíe
4. Revise la bandeja de `gerencia@contratamoscomplementos.com` — debe llegar el correo en menos de 1 minuto
5. Repita con el formulario de "Hoja de vida" → debe llegar a `gestionhumana@contratamoscomplementos.com`

> **¿No llegó el correo?** Revise la carpeta de SPAM. Si ahí está, márquelo como "No es spam" para que las próximas lleguen a la bandeja principal.

---

## ✏️ Cómo modificar datos del sitio

### Cambiar números de WhatsApp, correos o redes sociales

Abra `index.html` y busque al inicio el bloque que dice:

```javascript
const CONFIG = {
  whatsapp: { ... },
  correos: { ... },
  redesSociales: { ... },
};
```

Solo modifique los valores entre comillas. **No borre comillas, comas, ni los nombres de las propiedades.**

### Cambiar destinatarios de los correos
Si en el futuro quiere que las cotizaciones lleguen a otro correo, debe cambiarlo en **DOS lugares**:

1. En `index.html` → bloque `CONFIG.correos`
2. En `enviar.php` → variables `$CORREO_COTIZACION` y `$CORREO_HOJA_VIDA`

Ambos deben tener exactamente el mismo correo.

---

## 🔄 Actualizar el sitio (cambios futuros)

1. En el File Manager de Hostinger, **suba el archivo modificado** sobrenviendo el anterior
2. Listo — los cambios son inmediatos

> **Tip iPhone:** después de cada actualización, en su celular abra Safari y mantenga presionado el botón de recargar → "Recargar sin caché". Sin esto, podría seguir viendo la versión vieja por horas.

---

## 📰 Agregar / quitar noticias y ofertas laborales

Abra `index.html` y busque `NEWS_ITEMS = [`. Verá una lista de objetos así:

```javascript
{
  type: 'oferta',                    // 'oferta', 'noticia' o 'comunicado'
  date: '2026-04-15',                // formato AAAA-MM-DD
  title: 'Título del aviso',
  excerpt: 'Descripción corta...',
  meta: 'Etiqueta lateral',
  link: '#formularios'               // o '#' si no tiene link
}
```

- **Agregar uno:** copie un objeto completo, péguelo y modifique los valores. No olvide la coma al final.
- **Eliminar uno:** borre el objeto completo (incluyendo las llaves `{}` y la coma).

---

## 🆘 Soporte

- **Problema con el correo no llega:** revise SPAM, verifique que creó la cuenta `no-responder@`
- **Problema con el sitio en sí:** asegúrese que subió TODOS los archivos a `public_html`
- **Problema en celular:** borre el caché de Safari (mencionado arriba)
