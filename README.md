🚀 Aplicación de Seguimiento de Contrataciones con Airtable y PHP Local
Esta es una aplicación web sencilla desarrollada en PHP que se conecta a una base de datos de Airtable para mostrar y gestionar un sistema de seguimiento de contrataciones. Está diseñada para ejecutarse en un entorno de desarrollo local utilizando XAMPP.

✨ Características
Visualización de Lista: Muestra una tabla con los aplicantes activos o en proceso desde Airtable.

Vista de Detalle: Al hacer clic en un aplicante, se muestra una tarjeta con todos sus detalles.

Actualización de Estado: Permite cambiar el "Estado del Proceso" de un aplicante directamente desde la interfaz web, actualizando la base de Airtable en tiempo real.

Conexión Segura: Utiliza la API de Airtable con Personal Access Tokens (PAT) para una autenticación segura.

Código Organizado: Lógica PHP separada del HTML y estilos CSS en un archivo externo para facilitar el mantenimiento.

📋 Requisitos Previos
Antes de comenzar, asegúrate de tener instalado y configurado lo siguiente:

XAMPP: Un entorno de desarrollo web local que incluye Apache (servidor web) y PHP. Puedes descargarlo desde https://www.apachefriends.org/es/index.html.

Base de Datos en Airtable: Una base de datos de Airtable configurada con las siguientes tablas y campos (nombres exactos, incluyendo mayúsculas/minúsculas):

Aplicantes (tabla principal con campos como "Nombre Completo", "Estado del Proceso", "Puesto al que Aplica", etc.)

Puestos

Departamentos

Clientes

Proyectos

Contratos

Personal Access Token (PAT) de Airtable: Necesitarás un token con permisos de lectura (data.records:read) y escritura (data.records:write) para tu base de Airtable.

Base ID de Airtable: El identificador único de tu base de datos de Airtable.

⚙️ Configuración del Proyecto
Sigue estos pasos para configurar y ejecutar la aplicación en tu entorno local con XAMPP:

1. Clonar o Descargar el Proyecto
Descarga los archivos del proyecto (index.php y style.css).

Crea una carpeta para tu proyecto dentro del directorio htdocs de XAMPP. Por ejemplo:

C:\xampp\htdocs\airtable_app\ (Windows)

/Applications/XAMPP/htdocs/airtable_app/ (macOS)

Copia los archivos index.php y style.css dentro de esta nueva carpeta airtable_app.

2. Configurar Credenciales de Airtable
Abre el archivo index.php en tu editor de texto preferido.

Busca la sección // --- CONFIGURACIÓN DE AIRTABLE --- (aproximadamente en la línea 7).

Reemplaza los placeholders con tus credenciales reales de Airtable:

define( 'AIRTABLE_API_KEY', 'TU_API_KEY_DE_AIRTABLE' );

Cambia 'TU_API_KEY_DE_AIRTABLE' por tu Personal Access Token (el token largo que empieza con pat...).

define( 'AIRTABLE_BASE_ID', 'TU_BASE_ID_DE_AIRTABLE' );

Cambia 'TU_BASE_ID_DE_AIRTABLE' por tu Base ID (el ID que empieza con app...).

Verifica los Nombres de las Tablas: Asegúrate de que los nombres definidos en las constantes (AIRTABLE_TABLE_APLICANTES, AIRTABLE_TABLE_PUESTOS, etc.) coincidan EXACTAMENTE (respetando mayúsculas, minúsculas y espacios) con los nombres de tus tablas en tu base de Airtable.

Guarda el archivo index.php.

3. Configurar Airtable (Campos Lookup)
Para que los nombres de los puestos, departamentos, clientes y proyectos se muestren correctamente en la aplicación (en lugar de IDs), debes configurar campos de tipo "Lookup" en tu tabla Aplicantes en Airtable.

Abre tu base de "Seguimiento de Contrataciones" en Airtable.

Ve a la tabla Aplicantes.

Para cada campo enlazado (ej. "Puesto al que Aplica", "Departamento", "Cliente Asociado", "Proyecto Asociado", "Contrato Generado"), añade un nuevo campo de tipo Lookup.

Configura cada campo Lookup para:

"Field to look up from": Selecciona el campo enlazado correspondiente (ej. "Puesto al que Aplica").

"Field to display": Selecciona el campo que contiene el nombre legible en la tabla enlazada (ej. "Nombre del Puesto" en la tabla Puestos).

Nombra estos nuevos campos Lookup de forma clara (ej. "Puesto al que Aplica (Nombre)", "Departamento (Nombre)").

▶️ Cómo Ejecutar la Aplicación
Inicia XAMPP: Abre el Panel de Control de XAMPP.

Inicia Apache y MySQL: Haz clic en los botones "Start" junto a "Apache" y "MySQL". Asegúrate de que ambos estén en verde.

Abre tu Navegador: Abre tu navegador web preferido (Chrome, Firefox, Edge).

Accede a la Aplicación: En la barra de direcciones, escribe la siguiente URL y presiona Enter:

http://localhost/airtable_app/

Interactúa con la Aplicación:

Deberías ver una tabla con tus aplicantes (aquellos con estado "Pendiente" o "En Proceso" por defecto).

Haz clic en el nombre de un aplicante para ver sus detalles.

En la vista de detalle, puedes cambiar el "Estado del Proceso" y hacer clic en "Actualizar Estado" para guardar los cambios en Airtable.

troubleshooting Solución de Problemas Comunes
Página en blanco o Errores PHP:

Asegúrate de que Apache esté iniciado en XAMPP.

Verifica que el archivo index.php esté en la carpeta airtable_app dentro de htdocs.

Revisa el archivo php.ini de XAMPP (Config -> PHP (php.ini) en el Panel de Control de XAMPP) y asegúrate de que la extensión extension=curl no tenga un punto y coma (;) al principio. Después de cualquier cambio en php.ini, reinicia Apache.

Revisa el log de errores de Apache: En el Panel de Control de XAMPP, haz clic en "Logs" junto a Apache y selecciona "Apache (error.log)". Busca mensajes de error.

"No hay contrataciones activas o en proceso que coincidan con los criterios.":

Esto indica que la conexión a Airtable funciona, pero no se encontraron registros que cumplan con el filtro por defecto.

Verifica tus credenciales de Airtable (AIRTABLE_API_KEY y AIRTABLE_BASE_ID) en index.php. Un error de escritura o un espacio extra es común.

Verifica los nombres de tus tablas y campos en index.php (ej. AIRTABLE_TABLE_APLICANTES, {Estado del Proceso}). Deben coincidir EXACTAMENTE con los de tu base de Airtable (mayúsculas, minúsculas, espacios).

Asegúrate de tener registros en tu tabla Aplicantes en Airtable y que al menos algunos tengan el "Estado del Proceso" configurado como "Pendiente" o "En Proceso".

Estilos Faltantes (la página se ve sin diseño):

Asegúrate de que el archivo style.css esté en la misma carpeta que index.php.

Verifica que la línea <link rel="stylesheet" href="style.css"> esté correctamente en la sección <head> de tu index.php.

¡Esperamos que disfrutes usando esta aplicación para gestionar tus contrataciones! Si tienes alguna pregunta, no dudes en consultar.