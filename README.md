# Solupyme  
Sistema de Gestión Empresarial para Pymes  

## Descripción del Proyecto
**Solupyme** es un sistema web diseñado para pequeñas y medianas empresas (PyMEs), enfocado en la gestión de inventario, facturación, control de clientes, proveedores y administración de usuarios.  

El objetivo principal es ofrecer una herramienta sencilla, escalable y accesible para la administración de negocios.  
El proyecto puede ejecutarse en entorno local con **XAMPP** y desplegarse en producción en **IONOS Hosting** u otro servidor compatible con PHP y MySQL.  

---

## Características
- Gestión de clientes.  
- Administración de productos e inventario.  
- Facturación con control de anulaciones.  
- Reportes en PDF.  
- Control de usuarios, roles y permisos.  
- Gestión de proveedores y compras.  
- Parámetros de empresa configurables.  

---

## Tecnologías Utilizadas
- **Backend:** PHP 8.x  
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap  
- **Base de Datos:** MySQL 8.0  
- **Servidor Local:** XAMPP  
- **Servidor Web:** IONOS Hosting  

---

## Instalación de XAMPP en Windows
1. Descargar XAMPP desde la página oficial:  
   [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)  
2. Instalar XAMPP seleccionando al menos los siguientes componentes:  
   - Apache  
   - MySQL  
   - PHP  
   - phpMyAdmin  
3. Abrir el **Panel de Control de XAMPP** y arrancar los módulos **Apache** y **MySQL**.  
4. Verificar la instalación abriendo en el navegador:
   http://localhost

## Instalación del Proyecto en Local (XAMPP)
1. Clonar o descargar este repositorio.
   bash:
   - git clone "ruta githum del proyecto"
2. Mover la carpeta del proyecto a:
   - C:\xampp\htdocs\solupyme
3. Crear la base de datos en phpMyAdmin:
   - Acceder a http://localhost/phpmyadmin
   - Crear una base de datos llamada solupyme.
   - Importar el archivo solupyme.sql incluido en el proyecto.

4. Configurar la conexión en config/database.php con tus credenciales de MySQL.
5. Abrir el proyecto en el navegador:
   http://localhost/solupyme

## Estructura del Proyecto
 - Revisar archivo estructura.txt

## Estado del Proyecto
 - Actualmente en desarrollo activo – versión beta.

## Autor
 - Proyecto desarrollado por Ulises Zúniga.

