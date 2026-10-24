# Registro de alumnos

Aplicación PHP 8.2 y MySQL 8.4 basada en ER.xlsx. Abrir http://localhost/php_js_jquery/ con Apache y MySQL de WAMP activos.

## Instalación con SQL

1. En un equipo de destino sin las tablas del proyecto, abrir phpMyAdmin o MySQL Workbench.
2. Importar `database.sql` completo. El archivo crea y selecciona la base `alumnos` y restaura las cuatro tablas y sus datos actuales.
3. Copiar esta carpeta a `C:/wamp64/www/php_js_jquery/`.
4. Abrir la aplicación e ingresar con `admin@alumnos.local` y la contraseña inicial `Alumno2026!`.

El respaldo incluye un usuario, un alumno, un curso y una asignación. No contiene instrucciones DROP TABLE ni DROP DATABASE. Está preparado para una instalación nueva; no se debe volver a importar sobre tablas ya existentes porque no es un script de combinación de datos. No hace falta un instalador PHP.

La conexión de `db.php` usa MySQL en `127.0.0.1:3306`, base `alumnos`, usuario `root` y contraseña vacía. El hash de contraseña se conserva en el respaldo. `root` es el usuario de MySQL, mientras que `admin@alumnos.local` es el usuario de la aplicación.

## Archivos utilizados

- `index.php`: lista pública y enlaces habilitados según la sesión.
- `login.php`: verifica el correo y la contraseña e inicia la autenticación.
- `logout.php`: limpia la sesión por POST y regresa a la lista.
- `ver.php`: muestra públicamente los datos y cursos del alumno elegido.
- `nuevo.php`: configura el formulario para insertar un alumno.
- `editar_db.php`: configura el formulario para editar al alumno elegido.
- `alumno_form.php`: formulario y procesamiento compartido para altas y edición.
- `delete.php`: confirmación y eliminación del alumno por POST.
- `cursos.php`: consulta de sugerencias en JSON para el autocompletado.
- `app.php`: sesiones, acceso, token CSRF, escape HTML, mensajes y consultas compartidas.
- `db.php`: conexión PDO con MySQL.
- `head.php`: cabecera HTML y hojas de estilo comunes.
- `assets/cursos.js`: agregar y quitar cursos y conectar jQuery UI con `cursos.php`.
- `assets/app.css`: estilos propios para formularios, tablas y sugerencias.
- `assets/bootstrap.min.css`: Bootstrap 5.3.8 para el diseño.
- `assets/jquery.min.js`: jQuery 3.7.1 para eventos, DOM y AJAX.
- `assets/jquery-ui.min.js`: jQuery UI 1.14.1 para el autocompletado.
- `database.sql`: respaldo importable de estructura y datos actuales.
- `ER.xlsx`: modelo y bosquejos de referencia.
- `README.md`: instalación y mapa de archivos.
- `output/pdf/guia_del_proyecto.pdf`: guía detallada para explicar el programa.

Bootstrap, jQuery y jQuery UI son dependencias utilizadas y conservan sus avisos de licencia. Funcionan desde archivos locales sin conexión a Internet.

## Reglas y relaciones

Sin sesión se permite leer la lista y consultar alumnos. Crear, editar, eliminar y consultar sugerencias requieren autenticación. El acceso directo sin sesión a esas rutas redirige al login. Los formularios verifican CSRF, validan en el servidor y escapan los datos al mostrarlos.

El alta y la edición usan transacciones. La clave de `alumno_cursos` es `(id_alumno, id_curso, anio)`. Al borrar un alumno se eliminan sus asignaciones en cascada y se conserva el catálogo de cursos. El año usa `SMALLINT UNSIGNED` para admitir 1900 a 2100; `TINYINT` no permite almacenar 2026. La contraseña usa `VARCHAR(255)` para guardar el hash.

## Limpieza realizada

Se eliminó `consulta.php`, que duplicaba `ver.php`. Se consolidó la edición en `editar_db.php` y se eliminó `edit.php`. Se eliminó `instalar.php` porque la instalación completa se realiza con `database.sql`. Se quitó una comprobación de sesión repetida del formulario compartido; las dos rutas que lo cargan ya exigen autenticación antes de conectarse a MySQL. Las pruebas de verificación se conservaron fuera de esta carpeta de código entregable.
