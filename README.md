# Proyecto de Gestión de Empleados

## Requisitos

- PHP >= 7.4
- Composer
- Servidor web (XAMPP, Apache, etc.)
- Base de datos MySQL

## Instalación

1. **Clona el repositorio:**
   ```sh
   git clone <URL_DEL_REPOSITORIO>
   ```

2. **Instala las dependencias de PHP (incluyendo Dompdf):**
   ```sh
   composer install
   ```

3. **Configura la base de datos:**
   - Crea una base de datos en MySQL.
   - Importa el archivo SQL de estructura y datos si está disponible.
   - Copia el archivo `.env.example` a `.env` y configura tus credenciales de base de datos, o edita el archivo de configuración correspondiente.

4. **Configura permisos de carpetas:**
   - Asegúrate de que las carpetas de almacenamiento de archivos y fotos tengan permisos de escritura.

5. **Inicia el servidor:**
   - Si usas XAMPP, coloca la carpeta en `htdocs` y accede desde `http://localhost/app`.

## Notas

- No olvides instalar las dependencias cada vez que descargues el proyecto.
- El directorio `/vendor` no se incluye en el repositorio, se genera con `composer install`.
- Si tienes problemas con Dompdf, revisa que esté correctamente instalado en `/vendor`.

## Comandos útiles

- Instalar dependencias: `composer install`
- Actualizar dependencias: `composer update`

---
