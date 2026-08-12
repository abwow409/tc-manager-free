Sistema de Gestión de Cuentas Seguro para TrinityCore – Documentación del Sistema

1. Resumen del Sistema
Este sistema es una plataforma web de gestión y seguridad de cuentas diseñada para TrinityCore (emulador de World of Warcraft). Admite el sistema de cuentas Battle.net y ofrece funcionalidades completas: registro, activación, inicio de sesión, recuperación de contraseña, configuración de seguridad, recarga de puntos y tienda de puntos. El sistema se integra profundamente con las bases de datos auth, characters y world de TrinityCore, y se comunica con el servidor del juego mediante la interfaz SOAP, permitiendo la gestión en línea de cuentas, personajes, objetos y más.

Desarrollado con PHP 8+, sigue una arquitectura MVC e incorpora mecanismos de seguridad robustos (protección CSRF, prevención de fijación de sesión, lista negra de IP, limitación de tasa, registro de auditoría, exigencia de contraseñas seguras, etc.). También admite múltiples idiomas (chino, inglés, francés, ruso, etc.) y múltiples pasarelas de pago (Stripe, YiPay, etc.), lo que lo hace adecuado para servidores privados de WoW de tamaño pequeño o mediano.

2. Arquitectura Técnica y Estructura de Directorios

2.1 Pila Tecnológica

Componente	Elección Tecnológica
Lenguaje Backend	PHP 8.0+
Base de Datos	MySQL / MariaDB (compartida con auth/characters/world de TrinityCore)
Comunicación	SOAP (interacción con worldserver)
Frontend	HTML5 + CSS3 + JavaScript nativo (responsive, sin frameworks)
Correo Electrónico	PHPMailer (SMTP)
Pasarelas de Pago	Stripe (tarjeta de crédito), YiPay (pagos agregados), con interfaces reservadas para PayPal/WeChat/Alipay
Extensiones Cripto	OpenSSL, GMP (o BC Math)
Gestión de Sesiones	Sesiones PHP + persistencia en BD (soporta expulsión de múltiples dispositivos)
2.2 Estructura de Directorios (archivos clave)

text
/
├── config/
│   └── config.php                  # Configuración unificada (BD, SOAP, pagos, correo, parámetros de seguridad, etc.)
├── includes/
│   ├── Database.php                # Clase singleton de BD, crea tablas adicionales automáticamente
│   ├── Security.php                # Núcleo de seguridad: CSRF, hash de contraseñas, lista negra IP, bloqueo de login, etc.
│   ├── Session.php                 # Gestión de sesiones: login, logout, recordarme, control de multisession
│   ├── AuditLogger.php             # Sistema de registro de auditoría (escribe en tabla audit_logs)
│   ├── RateLimiter.php             # Límite de frecuencia de peticiones (por IP/operación)
│   ├── Recaptcha.php               # Integración con Google reCAPTCHA
│   ├── EmailService.php            # Servicio de correo (basado en PHPMailer)
│   ├── SOAPClient.php              # Cliente SOAP para TrinityCore (ejecuta comandos GM)
│   ├── SRP6.php / TrinitySRP6.php  # Validador SRP6 (compatible con el sistema de contraseñas de TrinityCore)
│   ├── languages.php               # Clase de soporte multilenguaje
│   ├── functions.php               # Funciones auxiliares globales (auto-carga, carga de configuración, etc.)
│   └── footer.php                  # Pie de página común (incluye estadísticas de página)
├── vendor/                         # Dependencias de Composer (PHPMailer, Stripe SDK, etc.)
├── languages/                      # Archivos de idioma (subdirectorios cn/en/fr/...)
├── auth.sql                        # Estructuras de tablas adicionales (puntos, artículos de tienda, tokens de activación, sesiones, etc.)
├── login.php                       # Página de inicio de sesión
├── register.php                    # Página de registro (llama a SOAP para crear cuenta Battle.net)
├── activate.php                    # Activación de cuenta (mediante token de correo)
├── resend_activation.php           # Reenvío de correo de activación
├── forgot_password.php             # Recuperación de contraseña (por correo o preguntas de seguridad)
├── reset_password.php              # Restablecimiento de contraseña mediante token
├── profile.php                     # Perfil de usuario (muestra personajes, tiempo en línea, canje de puntos)
├── security_settings.php           # Configuración de seguridad (cambiar contraseña, gestionar sesiones, establecer preguntas de seguridad)
├── points_shop.php                 # Tienda de puntos (objetos, subida de nivel, oro, permisos GM)
├── topup.php                       # Recarga de puntos (múltiples pasarelas de pago)
└── logout.php                      # Cerrar sesión
3. Módulos Funcionales Principales

3.1 Registro y Activación de Cuentas

Flujo de registro: El usuario ingresa correo y contraseña → el sistema llama a SOAP bnetaccount create para crear una cuenta Battle.net → vincula automáticamente la cuenta de juego (tabla account) y guarda el campo email → genera un token de activación (válido 24 h) → envía correo de activación mediante SMTP.

Mecanismo de activación: El usuario hace clic en el enlace del correo → se verifica la validez del token → se escribe el hash de la contraseña temporal en account.passwd → el token se marca como usado y la cuenta queda habilitada.

3.2 Inicio de Sesión y Gestión de Sesiones

Inicio de sesión: Soporta correo + contraseña (verificación SHA1, compatible con sha_pass_hash o passwd de TrinityCore).

Recordarme: Basado en la tabla remember_me_tokens, se renueva automáticamente por 30 días.

Seguridad de sesión: Cada inicio genera un ID de sesión único, registrando IP, User‑Agent y última actividad; permite ver y revocar sesiones en otros dispositivos.

Bloqueo de cuenta: Tras un número configurable de fallos consecutivos (por defecto 5), la cuenta se bloquea 30 minutos.

3.3 Recuperación y Restablecimiento de Contraseña

Método 1: Recibir un enlace de restablecimiento por correo (válido 60 minutos, uso único).

Método 2: Autenticarse mediante preguntas de seguridad predefinidas (al menos 3) y establecer una nueva contraseña directamente.

Ambos métodos llaman a SOAP bnetaccount set password para actualizar la contraseña y sincronizar account.passwd.

3.4 Página de Configuración de Seguridad

Cambiar contraseña: Requiere verificar la contraseña actual, se actualiza vía SOAP.

Gestionar sesiones activas: Lista todos los dispositivos conectados; permite cerrar una sesión individual o todas excepto la actual.

Establecer preguntas de seguridad: El usuario puede definir de 3 a 5 preguntas y respuestas personalizadas (almacenadas con hash) para recuperación auxiliar.

3.5 Perfil de Usuario e Información de Personajes

Muestra información de la cuenta Battle.net, nivel GM y lista de cuentas de juego asociadas.

Conecta con la base de datos characters para mostrar todos los personajes (nombre, raza, clase, nivel, dinero, mapa, estado en línea, tiempo total en línea, etc.).

Proporciona función “Unstuck”: teletransporta al personaje al punto de inicio de su raza/clase (actualiza coordenadas directamente en la BD, sin SOAP).

3.6 Sistema de Puntos (Créditos)

Obtención de puntos:

Canje de tiempo en línea (campo totaltime) – puntos por hora configurable, con horas mínimas requeridas.

Compra mediante recarga (ver siguiente sección).

Gasto de puntos:

Canje de objetos: Lee artículos de points_shop_items (ID, cantidad, precio) y los envía por correo al personaje seleccionado.

Subida de nivel: Eleva al personaje al nivel objetivo configurado (ej. 90), requiere que el personaje esté fuera de línea.

Compra de oro: Añade una cantidad especificada de oro (en cobre, con protección contra desbordamiento) al personaje.

Compra de permisos GM: Otorga nivel GM 1 a la cuenta de juego mediante account_access (RealmID = -1 para todos los reinos).

Todas las transacciones se registran en points_transactions con seguimiento de estado (pendiente/éxito/fallido).

3.7 Recarga de Puntos (Integración de Pagos)

Configuración: Activar y configurar cada pasarela en config.php.

Pasarelas soportadas:

Stripe: Usa flujo PaymentIntent, el frontend renderiza Stripe Elements, el backend confirma el pago y añade puntos automáticamente.

YiPay (pagos agregados): Genera firma, redirige a la plataforma de pagos y gestiona notificaciones asíncronas (notify) y retornos síncronos (return).

Control de tasa: Cada pasarela puede tener su propio tipo de cambio (1 CNY = X puntos), con valor global por defecto de 100.

Seguridad: Todas las llamadas de retorno verifican firmas y validan que el pedido coincida con el usuario, evitando falsificaciones.

3.8 Auditoría y Registros

Registro de auditoría: Guarda operaciones críticas (inicio de sesión, registro, cambio de contraseña, canje de objetos, revocación de sesiones, etc.) en la tabla audit_logs, con IP, User‑Agent y detalles en JSON.

Registros de inicio de sesión: Almacena cada intento (éxito/fallo) para análisis de seguridad.

Límite de tasa: Basado en IP y tipo de operación (ej. registro, restablecimiento de contraseña) para prevenir ataques de fuerza bruta.

4. Mecanismos de Seguridad Detallados

Capa de Protección	Medidas Específicas
Capa de Transporte	Fuerza HTTPS (configurable) para evitar ataques MITM.
Autenticación	Contraseñas con hash SHA1 (compatible con TrinityCore nativo) o SRP6; interfaz reservada para 2FA.
Seguridad de Sesión	ID de sesión regenerado periódicamente; vinculado a IP y User‑Agent; cookies HttpOnly, SameSite=Strict; sesiones persistidas en BD, con expiración y cierre forzado.
Protección CSRF	Cada formulario incluye un token aleatorio (Security::generateCSRFToken) que se verifica al enviar.
Filtrado de Entradas	Salida escapada con htmlspecialchars; consultas SQL con sentencias preparadas (mysqli).
Fortaleza de Contraseña	Exige al menos 8 caracteres, con mayúsculas, minúsculas, dígitos y caracteres especiales; lista negra de contraseñas débiles incorporada.
Límite de Tasa	RateLimiter usa Redis o registros en BD para limitar registro, restablecimiento de contraseña, etc. (por defecto 5 por hora).
Lista Negra de IP	Incluye automáticamente IPs que violan repetidamente (ej. >10 errores de contraseña), con caducidad configurable.
Protección de Inicio	Bloqueo de cuenta tras demasiados fallos (30 minutos) para evitar fuerza bruta.
Mecanismo de Activación	Las cuentas deben activarse por correo antes de acceder al panel web; el token es de un solo uso y válido 24 h.
Control de Acceso	Todas las páginas restringidas (perfil, tienda, etc.) verifican la sesión y redirigen a usuarios no autenticados.
Registro de Auditoría	Todas las operaciones sensibles se registran en audit_logs para su posterior investigación.
Comunicación SOAP	Usa credenciales independientes (usuario/contraseña) para comunicarse con worldserver; se recomienda TLS.
5. Diseño de Base de Datos (Tablas Adicionales)
Además de las tablas nativas de TrinityCore, el sistema añade las siguientes tablas (ver auth.sql):

Nombre de Tabla	Propósito
account_activation_tokens	Almacena tokens de activación de registro (con hash de contraseña temporal)
password_reset_tokens	Almacena tokens de restablecimiento de contraseña (un solo uso, 60 min)
password_reset_limits	Registra conteos de solicitudes de restablecimiento por IP/usuario (para límite de tasa)
user_security_questions	Almacena preguntas de seguridad del usuario (ID de pregunta y hash de respuesta)
user_2fa	Almacena claves secretas TOTP (reservado)
remember_me_tokens	Tokens de “recordarme” (inicio de sesión persistente)
account_sessions	Registros de sesiones activas (para gestión de múltiples dispositivos)
login_logs	Registros de intentos de inicio de sesión
audit_logs	Registros de auditoría (detalles en JSON)
rate_limits	Registros genéricos de límite de tasa
ip_blacklist	Lista negra de IP (con caducidad)
user_points	Saldo y estadísticas de puntos del usuario
points_shop_items	Configuración de artículos de la tienda (ID, precio, stock, categoría, etc.)
points_transactions	Registro de transacciones de puntos (canjes, recargas, intercambio de tiempo)
6. Instrucciones de Configuración (config.php)
El archivo de configuración contiene las siguientes secciones principales:

6.1 Conexiones a Base de Datos (database / characters_database / world_database)

Conecta a las bases de datos auth, characters y world respectivamente, con soporte para hosts y puertos independientes.

6.2 Configuración SOAP

php
$config['soap'] = [
    'host' => '127.0.0.1',      // Dirección SOAP de worldserver
    'port' => 7878,             // Puerto por defecto
    'username' => '3#1',        // Formato `account_id#realm_id`
    'password' => '...',        // Contraseña SOAP (coincide con worldserver.conf)
    'timeout' => 30,
    'debug' => false,
];
6.3 Pasarelas de Pago (stripe / yipay / paypal / wechat / alipay)

Cada pasarela tiene su propio interruptor de activación, claves, tipo de cambio y entorno (sandbox).

YiPay soporta firma MD5.

6.4 Servicio de Correo

Usa SMTP para enviar correos de activación, restablecimiento de contraseña, alertas de seguridad, etc.

Soporta Gmail, QQ Mail, etc. (requiere contraseñas específicas de aplicación).

6.5 Parámetros de Seguridad

min_password_length, max_login_attempts, lockout_duration_minutes, session_lifetime, remember_me_lifetime, etc.

Activar/desactivar reCAPTCHA, 2FA (reservado).

6.6 Puntos y Tienda

points_per_hour (tasa de canje de tiempo en línea), min_exchange_hours.

level_boost_target (nivel objetivo para subidas de nivel).

Categorías de artículos: level_boost, gold, gm_level, artículos normales.

7. Requisitos del Entorno de Despliegue

7.1 Entorno del Servidor

PHP: Versión 8.2 (la versión gratuita requiere exactamente 8.2)

MySQL: 8.0+ / MariaDB 12+

Servidor Web: Apache / Nginx

Extensiones PHP requeridas: mysqli, session, curl, soap (obligatorio), gd, json, mbstring, gmp, sg11, Imagick

Composer: 2.0+

Instalación de dependencias:

bash
composer require phpmailer/phpmailer
composer require stripe/stripe-php   # si se activa Stripe
7.2 Configuración de TrinityCore

worldserver.conf debe habilitar SOAP:

text
SOAP.Enabled = 1
SOAP.Port = 7878
SOAP.Redirect = 0
La base de datos auth debe tener la tabla battlenet_accounts (proporcionada por TrinityCore).

La tabla account debe incluir un campo email (el sistema lo añadirá automáticamente si falta).

7.3 Instalación de Dependencias
Usar Composer como se indicó arriba.

7.4 Permisos de Archivos

config/config.php debe tener permisos 600 o 640 (solo lectura).

Los directorios de registros (si no se usa auditoría en BD) deben tener permisos de escritura.

Los directorios de subida (si existen) requieren controles de acceso adecuados.

7.5 Red y Seguridad

Se recomienda encarecidamente habilitar HTTPS (establecer require_https = true en la configuración).

Configurar el firewall para abrir solo los puertos 80/443; restringir el puerto SOAP (7878) a localhost.

Actualizar regularmente PHP y las extensiones.

8. Ejemplos de Flujo de Uso

8.1 Registro de Nuevo Usuario

Visitar /register.php, completar correo y contraseña.

El sistema llama a SOAP para crear una cuenta Battle.net, genera una cuenta de juego y envía un correo de activación.

El usuario hace clic en el enlace de activación → la cuenta se activa y puede iniciar sesión en el panel web.

8.2 Inicio de Sesión y Obtención de Puntos

Visitar /login.php, ingresar correo y contraseña, opcionalmente marcar “Recordarme”.

Tras el inicio, ir a /profile.php para ver la lista de personajes y el saldo de puntos.

En la sección “Centro de Puntos”, ingresar el número de horas en línea para canjear por puntos (consume el totaltime del personaje).

8.3 Gasto de Puntos

Hacer clic en “Tienda de Puntos” para ir a /points_shop.php.

Navegar por los artículos (objetos, subida de nivel, oro, permisos GM).

Seleccionar un artículo y un personaje destino, hacer clic en “Canjear”.

El sistema deduce los puntos, realiza la acción correspondiente (envía correo con objeto, actualiza nivel, añade oro, etc.) y registra la transacción.

8.4 Recarga de Puntos

Visitar /topup.php, ingresar el monto a recargar.

Elegir un método de pago (Stripe/YiPay, etc.).

Completar el pago; el sistema añade automáticamente los puntos a la cuenta.

8.5 Configuración de Seguridad

En /security_settings.php, cambiar contraseña, gestionar sesiones, establecer preguntas de seguridad.

Las preguntas de seguridad sirven como método alternativo de verificación para recuperar la contraseña.

9. Extensión y Personalización

Añadir una nueva pasarela de pago: Agregar configuración en config.php, implementar el enrutamiento y manejo de callbacks en topup.php.

Añadir nuevos tipos de productos: Extender la lógica de canje en points_shop.php con nuevas ramas de category.

Multi-idioma: Añadir archivos de idioma en languages/ y heredar la clase Language.

2FA: El sistema ya reserva la tabla user_2fa y esbozos de interfaz – integrar una biblioteca TOTP (ej. robthree/twofactorauth) para habilitarlo.

10. Mantenimiento y Monitoreo

Visualización de registros: Las tablas audit_logs y login_logs proporcionan un historial detallado; se puede construir una interfaz de administración para mostrarlos.

Limpieza periódica: El sistema incluye AuditLogger::cleanOldLogs($days) para eliminar registros antiguos regularmente.

Mantenimiento de BD: Optimizar periódicamente las tablas de sesiones y tokens eliminando registros expirados.

Actualizaciones de seguridad: Mantener PHP y las dependencias de Composer actualizadas con los últimos parches.

11. Preguntas Frecuentes (FAQ)

P: El registro falla con “SOAP service unavailable”.
R: Verificar que worldserver esté en ejecución, que la configuración SOAP sea correcta y que el firewall permita el puerto 7878 (preferiblemente solo acceso local).

P: No se recibe el correo de activación.
R: Revisar la configuración SMTP y los registros de correo; los usuarios pueden usar la función “Reenviar correo de activación”.

P: No se ven los personajes tras iniciar sesión.
R: Confirmar que la configuración de characters_database es correcta y que la cuenta Battle.net realmente tiene personajes.

P: No se recibe el objeto tras canjear puntos.
R: Verificar que las tablas mail e item_instance de la base de datos de personajes se hayan insertado correctamente; asegurarse de que el buzón del personaje no esté lleno.

P: Fallo al restablecer la contraseña.
R: Asegurarse de que SOAP esté disponible y la cuenta exista; si se usan preguntas de seguridad, verificar que el hash de la respuesta coincida (distingue mayúsculas/minúsculas).

12. Versión y Soporte

Versión actual: Basada en TrinityCore 12.x (soporta 11.0 Dragonflight y versiones anteriores).

Compatibilidad: Teóricamente compatible con todas las ramas de TrinityCore que usen el sistema de cuentas Battle.net (pueden requerirse ajustes menores en nombres de campos).

Soporte técnico: Consultar los foros oficiales de TrinityCore o la documentación del sistema; utilizar los registros de error detallados para solucionar problemas.