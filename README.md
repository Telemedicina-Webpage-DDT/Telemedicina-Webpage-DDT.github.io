# Telemedicina-Webpage-DDT.github.io
Página Web para el curso de Telemedicina.

La página web fue realizada con Wordpress, apache y mysql a partir de XAMPP. Se utilizaron distintos plugins como Woocomerce para realizar las funcionalidades.

En cuanto a las imágenes DICOM, se utilizó una librería de código abierto llamada Cornerstone y se utilizó el servidor orthanc para servir las imagenes DICOM directamente en localhost, esto con el fin de que se pudiera obtener la imagen .dcm a partir del web image loader de Cornerstone.

Debido a que se tienen, en este caso, dos instancias trabajando en localhost (la página web a través de XAMPP y el servidor orthanc que funciona localhost:8042) y se deben compartir archivos entre ellos, esto es, Cornerstone descarga el archivo .dcm del servidor y luego muestra la imagen, se debe activar la opción CORS de Google Chrome.

CORS (Cross Origin Resource Sharing) permite que recursos generalmente restringidos (imagenes, javascript, fuentes, etc) de una página web, puedan ser utilizados por otra página web en un dominio distinto al de la primera. 

Firefox no permite actualmente trabajar con CORS, pero Chrome sí. Para activarlo se debe localizar la carpeta en la que se encuentra instalado chrome.exe, abrir la consola de comandos en esa carpeta y colocar
`chrome.exe --disable-web-security` 

## Referencias
* https://github.com/chafey/cornerstone
* http://www.orthanc-server.com/
* https://www.apachefriends.org/es/index.html
* https://en.wikipedia.org/wiki/Cross-origin_resource_sharing
