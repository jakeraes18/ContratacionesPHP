**🚀 Aplicación de Seguimiento de Contrataciones con Airtable y PHP Local**
===========================================================================

Esta es una aplicación web sencilla desarrollada en PHP que se conecta a una base de datos de Airtable para mostrar y gestionar un sistema de seguimiento de contrataciones. Está diseñada para ejecutarse en un entorno de desarrollo local utilizando **XAMPP**.

### **✨ Características Principales**

*   **Visualización de Lista**: Muestra una tabla interactiva con los aplicantes activos o en proceso directamente desde tu base de Airtable.
    
*   **Vista de Detalle**: Permite ver información detallada de cada aplicante al hacer clic en su nombre.
    
*   **Actualización de Estado**: Facilita el cambio del "Estado del Proceso" de un aplicante desde la interfaz web, con actualizaciones en tiempo real en Airtable.
    
*   **Conexión Segura**: Utiliza los **Personal Access Tokens (PAT)** de Airtable para una autenticación robusta y controlada.
    
*   **Código Organizado**: La lógica PHP, la estructura HTML y los estilos CSS están separados en archivos dedicados (index.php, style.css) para un mantenimiento y escalabilidad óptimos.
    

### **📋 Requisitos Previos**

Antes de configurar el proyecto, asegúrate de tener lo siguiente:

*   **XAMPP Instalado**: Un entorno de desarrollo web local que incluye el servidor Apache y PHP. Puedes descargarlo desde [https://www.apachefriends.org/es/index.html](https://www.apachefriends.org/es/index.html).
    
*   **Base de Datos en Airtable**: Una base de datos de Airtable configurada con la siguiente estructura de tablas y campos. Es **crucial** que los nombres coincidan **exactamente** (respetando mayúsculas, minúsculas y espacios):
    
    *   Aplicantes (tu tabla principal con campos como "Nombre Completo", "Estado del Proceso", "Puesto al que Aplica", etc.)
        
    *   Puestos
        
    *   Departamentos
        
    *   Clientes
        
    *   Proyectos
        
    *   Contratos
        
*   **Personal Access Token (PAT) de Airtable**: Un token generado desde tu cuenta de Airtable con los permisos específicos de **lectura** (data.records:read) y **escritura** (data.records:write) para tu base de datos de contrataciones.
    
*   **Base ID de Airtable**: El identificador único de tu base de datos de Airtable (una cadena que comienza con app...).
    

### **⚙️ Configuración del Proyecto**

Sigue estos pasos para poner en marcha la aplicación en tu entorno local:

#### **1\. Descarga y Ubicación de los Archivos**

1.  **Descarga los archivos** del proyecto: index.php y style.css.
    
2.  **Crea una nueva carpeta** para tu proyecto dentro del directorio htdocs de tu instalación de XAMPP. Por ejemplo:
    
    *   C:\\xampp\\htdocs\\airtable\_app\\ (en Windows)
        
    *   /Applications/XAMPP/htdocs/airtable\_app/ (en macOS)
        
3.  **Copia los archivos** index.php **y** style.css dentro de esta nueva carpeta airtable\_app.
    

#### **2\. Configuración de Credenciales de Airtable en** index.php

1.  Abre el archivo index.php en tu editor de texto preferido.
    
2.  Busca la sección // --- CONFIGURACIÓN DE AIRTABLE --- (aproximadamente en la línea 7).
    
3.  **Reemplaza los valores de los placeholders** con tus credenciales reales de Airtable:
    
    *   define( 'AIRTABLE\_API\_KEY', 'TU\_API\_KEY\_DE\_AIRTABLE' );
        
        *   Sustituye 'TU\_API\_KEY\_DE\_AIRTABLE' por tu **Personal Access Token** completo (el que empieza con pat...).
            
    *   define( 'AIRTABLE\_BASE\_ID', 'TU\_BASE\_ID\_DE\_AIRTABLE' );
        
        *   Sustituye 'TU\_BASE\_ID\_DE\_AIRTABLE' por tu **Base ID** (el que empieza con app...).
            
    *   **Verifica los Nombres de las Tablas**: Asegúrate de que los nombres definidos en las constantes (AIRTABLE\_TABLE\_APLICANTES, AIRTABLE\_TABLE\_PUESTOS, etc.) coincidan **EXACTAMENTE** (incluyendo mayúsculas, minúsculas y cualquier espacio) con los nombres de tus tablas en Airtable.
        
4.  **Guarda el archivo** index.php.
    

#### **3\. Configuración de Campos Lookup en Airtable**

Para que la aplicación muestre los nombres legibles de los elementos enlazados (como el nombre del puesto, departamento, etc.) en lugar de sus IDs internos de Airtable, debes configurar campos de tipo "Lookup" en tu tabla Aplicantes.

1.  Abre tu base de "Seguimiento de Contrataciones" en Airtable.
    
2.  Ve a la tabla Aplicantes.
    
3.  Para cada campo que enlaza a otra tabla (ej. "Puesto al que Aplica", "Departamento", "Cliente Asociado", "Proyecto Asociado", "Contrato Generado"), añade un **nuevo campo** de tipo Lookup.
    
4.  Configura cada campo Lookup de la siguiente manera:
    
    *   **"Field to look up from"**: Selecciona el campo enlazado correspondiente (ej. "Puesto al que Aplica").
        
    *   **"Field to display"**: Selecciona el campo que contiene el nombre legible en la tabla enlazada (ej. "Nombre del Puesto" en la tabla Puestos).
        
5.  Nombra estos nuevos campos Lookup de forma clara, por ejemplo: "Puesto al que Aplica (Nombre)", "Departamento (Nombre)", etc.
    

### **▶️ Cómo Ejecutar la Aplicación**

1.  **Inicia XAMPP**: Abre el Panel de Control de XAMPP.
    
2.  **Inicia Apache y MySQL**: Haz clic en los botones "Start" junto a "Apache" y "MySQL". Asegúrate de que ambos módulos estén en verde.
    
3.  **Abre tu Navegador Web**: Puedes usar Chrome, Firefox, Edge, etc.
    
4.  http://localhost/airtable\_app/
    
5.  **Interactúa con la Aplicación**:
    
    *   Deberías ver una tabla con tus aplicantes (aquellos con estado "Pendiente" o "En Proceso" por defecto).
        
    *   Haz clic en el nombre de un aplicante para ver sus detalles.
        
    *   En la vista de detalle, puedes cambiar el "Estado del Proceso" y hacer clic en "Actualizar Estado" para guardar los cambios en Airtable.
        

### **💡 Solución de Problemas Comunes**

Si encuentras algún problema, revisa estos puntos:

*   **Página en blanco o Errores PHP**:
    
    *   Asegúrate de que **Apache** esté iniciado en XAMPP.
        
    *   Verifica que el archivo index.php esté correctamente ubicado en C:\\xampp\\htdocs\\airtable\_app\\ (o su equivalente).
        
    *   Revisa el archivo php.ini de XAMPP (accede desde el Panel de Control de XAMPP: Config -> PHP (php.ini)) y asegúrate de que la extensión extension=curl no tenga un punto y coma (;) al principio. Después de cualquier cambio en php.ini, es **esencial reiniciar Apache**.
        
    *   Consulta el log de errores de Apache para mensajes detallados: En el Panel de Control de XAMPP, haz clic en Logs junto a Apache y selecciona Apache (error.log). Busca entradas recientes que mencionen "cURL Error" o "Airtable API Error".
        
*   **"No hay contrataciones activas o en proceso que coincidan con los criterios."**:
    
    *   Esto indica que la conexión a Airtable funciona, pero no se encontraron registros que cumplan con el filtro por defecto.
        
    *   **Verifica tus credenciales de Airtable** (AIRTABLE\_API\_KEY y AIRTABLE\_BASE\_ID) en index.php. Un error de escritura o un espacio extra es una causa común.
        
    *   **Verifica los nombres de tus tablas y campos** en index.php (ej. AIRTABLE\_TABLE\_APLICANTES, {Estado del Proceso}). Deben coincidir **EXACTAMENTE** con los de tu base de Airtable (mayúsculas, minúsculas, espacios).
        
    *   Asegúrate de tener registros en tu tabla Aplicantes en Airtable y que al menos algunos tengan el "Estado del Proceso" configurado como "Pendiente" o "En Proceso".
        
*   **Estilos Faltantes (la página se ve sin diseño)**:
    
    *   Asegúrate de que el archivo style.css esté en la misma carpeta que index.php.
        
    *   Verifica que la línea  esté correctamente en la sección de tu index.php.
        

¡Esperamos que disfrutes usando esta aplicación para gestionar tus contrataciones! Si tienes alguna pregunta, no dudes en consultar.