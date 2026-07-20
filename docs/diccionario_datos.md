# Diccionario de Datos - Hotel Inventario

> Documentacion formal de la estructura de la base de datos del sistema
> Hotel Inventario, alojada en MariaDB/MySQL a traves de XAMPP.
> Motor: InnoDB. Codificacion: utf8mb4 / utf8mb4_general_ci.
>
> La numeracion de tablas (9.3.x) es continua respecto al documento
> "Modelo Relacional" (que abarca de la 9.3.1 a la 9.3.9).

---

## Modelo Relacional

### Descripcion tecnica de la logica de la base de datos

El sistema **Hotel Inventario** se apoya sobre una base de datos relacional
gestionada mediante **MariaDB/MySQL**, desplegada localmente a traves del
entorno **XAMPP**. El motor de almacenamiento utilizado es **InnoDB**, lo que
garantiza el soporte de transacciones, la integridad referencial y el bloqueo a
nivel de fila; el juego de caracteres empleado es `utf8mb4` con la colacion
`utf8mb4_general_ci`, asegurando la correcta representacion de acentos y
caracteres especiales propios del idioma espanol.

La arquitectura de datos se organiza en torno a **tres entidades maestras**
-`habitaciones`, `articulos` y `usuarios`- y una **entidad transaccional
central** -`inventario`- que actua como el nucleo operativo del sistema.
Alrededor de estas se disponen tablas de configuracion, respaldo y auditoria que
complementan la logica del negocio.

#### Entidades maestras

La tabla **`habitaciones`** representa el catalogo fisico del hotel. Cada
registro almacena el numero de habitacion, su tipo (doble, sencilla, superior,
entre otros), el piso en el que se ubica y su estado operativo (disponible,
ocupada, limpieza, mantenimiento o bloqueada). El campo `tipo` cumple una
funcion determinante, pues es el criterio que vincula cada habitacion con la
plantilla de inventario que le corresponde.

La tabla **`articulos`** constituye el catalogo general de bienes inventariables
del hotel (television, telefono, silla, aire acondicionado, etc.). Un atributo
relevante es `usa_codigo_barras`, que indica si el articulo debe ser rastreado
individualmente mediante escaneo de codigo de barras.

La tabla **`usuarios`** administra el acceso al sistema y define el control de
permisos mediante el campo `rol`, que distingue entre los perfiles
**administrador**, **supervisor** y **operador**. Cada usuario mantiene ademas
metadatos de gestion como la fecha de creacion, el ultimo inicio de sesion y su
estado de actividad.

#### Logica de inventario: plantilla vs. realidad

El corazon del modelo radica en la comparacion entre lo **esperado** y lo
**real**, lo cual se resuelve mediante dos tablas complementarias:

- La tabla **`inventario_base`** define la *plantilla* o dotacion estandar que
  deberia tener cada **tipo** de habitacion. Relaciona un `tipo_habitacion` con
  un `articulo_id` y establece la cantidad esperada. Esta relacion con
  `habitaciones` no se realiza mediante una llave numerica, sino a traves de la
  coincidencia textual entre `habitaciones.tipo` e
  `inventario_base.tipo_habitacion`, lo que permite aplicar una misma
  configuracion a todas las habitaciones de un mismo tipo.

- La tabla **`inventario`** registra el estado *real* de cada habitacion: que
  articulos posee efectivamente, en que cantidad, en que estado de conservacion
  (bueno, danado, en reparacion o perdido) y, cuando aplica, su codigo de
  barras. Cada registro se relaciona con `habitaciones` mediante `habitacion_id`
  y con `articulos` mediante `articulo_id`.

La confrontacion de ambas tablas permite derivar, en tiempo real, los conceptos
de **faltante** (cuando la cantidad base supera a la real) y **sobrante** (cuando
la real supera a la base). Este calculo no se persiste, sino que se resuelve
mediante **vistas SQL** (`vista_faltantes`, `vista_dashboard`,
`vista_estadisticas_articulos` y `vista_estadisticas_pisos`), que alimentan
directamente el modulo de reportes y el tablero de control (*dashboard*) sin
duplicar informacion en el almacenamiento.

#### Entidades de trazabilidad y respaldo

El modelo incorpora dos mecanismos de trazabilidad. La tabla
**`historial_codigos`** deja constancia de cada consulta realizada por escaneo de
codigo de barras, relacionando el usuario que la ejecuto (`usuario_id`) con el
registro de inventario consultado (`inventario_id`) y la marca temporal
correspondiente. Por su parte, la tabla **`movimientos`** funciona como una
**bitacora de auditoria general**: registra toda accion de creacion, edicion o
eliminacion efectuada en cualquier modulo, almacenando el usuario responsable,
el modulo afectado, el tipo de accion y una descripcion legible. Su campo
`registro_id` opera de forma **polimorfica**, ya que apunta al identificador de
distintas tablas segun el modulo de origen, razon por la cual no se define como
llave foranea.

Finalmente, la tabla **`inventario_backup`** replica la estructura de
`inventario` con el proposito de conservar copias de seguridad de los datos
operativos.

#### Integridad referencial

Las relaciones entre entidades se implementan como **llaves logicas**
administradas desde la capa de aplicacion (PHP/PDO) y explotadas mediante
operaciones `JOIN` en las consultas y vistas. Este enfoque centraliza la
validacion de la integridad en los controladores y modelos del patron MVC,
manteniendo la coherencia entre las tablas maestras y las transaccionales a lo
largo de todo el ciclo de vida de la informacion.

---

## Introduccion al Diccionario de Datos

El presente **Diccionario de Datos** constituye la documentacion formal y de
referencia de la estructura interna de la base de datos del sistema
**Hotel Inventario**. Su objetivo es describir de manera precisa, ordenada y
exhaustiva cada uno de los objetos que componen el modelo de datos, sirviendo
como herramienta indispensable tanto para el mantenimiento y la evolucion del
sistema como para la incorporacion de nuevos desarrolladores al proyecto.

Para cada tabla se documentan los siguientes atributos:

- **Campo:** nombre tecnico de la columna.
- **Tipo de dato:** tipo y longitud del valor (INT, VARCHAR, TEXT, ENUM,
  TIMESTAMP, TINYINT), conforme a la sintaxis de MariaDB/MySQL.
- **Nulo:** indica si el campo admite NULL o si su captura es obligatoria.
- **Llave:** rol del campo (PK = Llave Primaria, FK = Llave Foranea,
  UK = Llave Unica).
- **Valor por defecto:** valor asignado automaticamente cuando no se especifica.
- **Descripcion:** significado y uso funcional de la columna.

Las llaves foraneas se administran a nivel de la capa de aplicacion mediante
consultas relacionales (JOIN), por lo que se documentan como relaciones logicas
del modelo.

---

## Tablas Maestras

### 1. Tabla `usuarios`

Almacena las cuentas de acceso al sistema y define el control de permisos
mediante roles. Es la entidad responsable de la autenticacion y del registro de
responsabilidad en las operaciones de auditoria.

| Campo | Tipo de dato | Nulo | Llave | Valor por defecto | Descripcion |
|---|---|---|---|---|---|
| `id` | INT(11) | NO | PK | AUTO_INCREMENT | Identificador unico del usuario. Clave primaria autoincremental. |
| `nombre` | VARCHAR(100) | NO | UK | - | Nombre del usuario. Debe ser unico en todo el sistema. |
| `correo` | VARCHAR(50) | NO | UK | - | Correo electronico de acceso. Unico; credencial de inicio de sesion. |
| `password` | VARCHAR(255) | NO | - | - | Contrasena cifrada mediante hash (bcrypt / password_hash). |
| `rol` | ENUM('admin','supervisor','operador') | NO | - | 'operador' | Perfil de permisos del usuario dentro del sistema. |
| `creado_en` | TIMESTAMP | NO | - | current_timestamp() | Fecha y hora de creacion de la cuenta. |
| `activo` | TINYINT(1) | NO | - | 1 | Estado de la cuenta: 1 = activa, 0 = deshabilitada. |
| `ultimo_login` | TIMESTAMP | SI | - | NULL | Fecha y hora del ultimo inicio de sesion exitoso. |

<p align="center"><em>Tabla 9.3.10. Diccionario de datos de la tabla usuarios.</em></p>

**Indices:** PK (`id`) - UNIQUE `unique_nombre` (`nombre`) - UNIQUE `unique_correo` (`correo`).

---

### 2. Tabla `habitaciones`

Constituye el catalogo fisico del hotel. Cada registro representa una habitacion
individual y su `tipo` determina la plantilla de inventario que le corresponde.

| Campo | Tipo de dato | Nulo | Llave | Valor por defecto | Descripcion |
|---|---|---|---|---|---|
| `id` | INT(11) | NO | PK | AUTO_INCREMENT | Identificador unico de la habitacion. Clave primaria autoincremental. |
| `numero` | VARCHAR(10) | SI | UK | NULL | Numero identificador de la habitacion (ej. '103', '214'). Unico. |
| `tipo` | VARCHAR(50) | SI | - | NULL | Categoria de la habitacion (doble, sencilla, superior). Vincula con inventario_base.tipo_habitacion. |
| `descripcion` | TEXT | SI | - | NULL | Observaciones o notas adicionales sobre la habitacion. |
| `piso` | INT(11) | SI | - | NULL | Piso en el que se ubica la habitacion. |
| `estado` | ENUM('disponible','ocupada','limpieza','mantenimiento','bloqueada') | SI | Indice | 'disponible' | Estado operativo actual de la habitacion. |

<p align="center"><em>Tabla 9.3.11. Diccionario de datos de la tabla habitaciones.</em></p>

**Indices:** PK (`id`) - UNIQUE `unique_numero` (`numero`) - KEY `idx_estado` (`estado`).

---

### 3. Tabla `articulos`

Catalogo general de bienes inventariables del hotel. Cada articulo puede aparecer
tanto en la plantilla base como en el inventario real de las habitaciones.

| Campo | Tipo de dato | Nulo | Llave | Valor por defecto | Descripcion |
|---|---|---|---|---|---|
| `id` | INT(11) | NO | PK | AUTO_INCREMENT | Identificador unico del articulo. Clave primaria autoincremental. |
| `nombre` | VARCHAR(100) | SI | UK | NULL | Nombre del articulo (ej. 'television', 'silla'). Unico. |
| `descripcion` | TEXT | SI | - | NULL | Descripcion detallada o especificaciones del articulo. |
| `usa_codigo_barras` | TINYINT(1) | NO | - | 0 | Indica si el articulo se rastrea por codigo de barras: 1 = si, 0 = no. |

<p align="center"><em>Tabla 9.3.12. Diccionario de datos de la tabla articulos.</em></p>

**Indices:** PK (`id`) - UNIQUE `unique_articulos` (`nombre`).

---

## Tablas de Configuracion y Transaccionales

### 4. Tabla `inventario_base`

Define la **plantilla o dotacion estandar** que deberia tener cada tipo de
habitacion. Es la referencia contra la que se comparan las existencias reales
para determinar faltantes y sobrantes.

| Campo | Tipo de dato | Nulo | Llave | Valor por defecto | Descripcion |
|---|---|---|---|---|---|
| `id` | INT(11) | NO | PK | AUTO_INCREMENT | Identificador unico del registro base. Clave primaria autoincremental. |
| `tipo_habitacion` | VARCHAR(50) | NO | UK (compuesta) | - | Tipo de habitacion al que aplica. Relacion logica con habitaciones.tipo. |
| `articulo_id` | INT(11) | NO | FK / UK (compuesta) | - | Referencia al articulo (articulos.id) que compone la plantilla. |
| `cantidad` | INT(11) | NO | - | 1 | Cantidad esperada del articulo para ese tipo de habitacion. |

<p align="center"><em>Tabla 9.3.13. Diccionario de datos de la tabla inventario_base.</em></p>

**Indices:** PK (`id`) - UNIQUE `unique_tipo_articulo` (`tipo_habitacion`, `articulo_id`) - KEY `articulo_id` - KEY `idx_tipo_articulo`.
**Relaciones:** `articulo_id` -> `articulos.id`. La restriccion unica compuesta impide duplicar un mismo articulo dentro de un mismo tipo de habitacion.

---

### 5. Tabla `inventario`

Entidad **transaccional central** del sistema. Registra el estado real del
inventario de cada habitacion: que articulos posee, en que cantidad y en que
condicion.

| Campo | Tipo de dato | Nulo | Llave | Valor por defecto | Descripcion |
|---|---|---|---|---|---|
| `id` | INT(11) | NO | PK | AUTO_INCREMENT | Identificador unico del registro de inventario. Clave primaria autoincremental. |
| `habitacion_id` | INT(11) | SI | FK / UK (compuesta) | NULL | Referencia a la habitacion (habitaciones.id) donde se ubica el articulo. |
| `articulo_id` | INT(11) | SI | FK / UK (compuesta) | NULL | Referencia al articulo (articulos.id) inventariado. |
| `cantidad` | INT(11) | SI | - | 0 | Cantidad real del articulo presente en la habitacion. |
| `estado` | ENUM('bueno','danado','en_reparacion','perdido') | SI | - | 'bueno' | Estado de conservacion del articulo. |
| `comentarios` | TEXT | SI | - | NULL | Observaciones especificas del registro (ej. 'Silla chica'). |
| `codigo_barras` | VARCHAR(50) | SI | - | NULL | Codigo de barras individual del articulo, cuando aplica. |

<p align="center"><em>Tabla 9.3.14. Diccionario de datos de la tabla inventario.</em></p>

**Indices:** PK (`id`) - UNIQUE `uq_habitacion_articulo` (`habitacion_id`, `articulo_id`) - KEY `articulo_id` - KEY `idx_habitacion_articulo`.
**Relaciones:** `habitacion_id` -> `habitaciones.id`; `articulo_id` -> `articulos.id`. La restriccion unica compuesta garantiza que un articulo no se registre dos veces en la misma habitacion.

---

### 6. Tabla `inventario_backup`

Tabla de **respaldo** que replica la estructura de `inventario` para conservar
copias de seguridad de los datos operativos. No participa en relaciones formales
ni posee restricciones de unicidad.

| Campo | Tipo de dato | Nulo | Llave | Valor por defecto | Descripcion |
|---|---|---|---|---|---|
| `id` | INT(11) | NO | - | 0 | Identificador del registro respaldado (copiado de inventario.id). No autoincremental. |
| `habitacion_id` | INT(11) | SI | - | NULL | Copia de la habitacion asociada al registro original. |
| `articulo_id` | INT(11) | SI | - | NULL | Copia del articulo asociado al registro original. |
| `cantidad` | INT(11) | SI | - | 0 | Copia de la cantidad registrada. |
| `estado` | ENUM('bueno','danado','en_reparacion','perdido') | SI | - | 'bueno' | Copia del estado de conservacion. |
| `comentarios` | TEXT | SI | - | NULL | Copia de las observaciones del registro. |
| `codigo_barras` | VARCHAR(50) | SI | - | NULL | Copia del codigo de barras del registro. |

<p align="center"><em>Tabla 9.3.15. Diccionario de datos de la tabla inventario_backup.</em></p>

**Indices:** ninguno (tabla plana de respaldo).

---

## Tablas de Trazabilidad y Auditoria

### 7. Tabla `historial_codigos`

Deja constancia de cada **consulta por escaneo de codigo de barras**, vinculando
al usuario que la realizo con el registro de inventario consultado y el instante
exacto de la operacion.

| Campo | Tipo de dato | Nulo | Llave | Valor por defecto | Descripcion |
|---|---|---|---|---|---|
| `id` | INT(11) | NO | PK | AUTO_INCREMENT | Identificador unico del registro de historial. Clave primaria autoincremental. |
| `usuario_id` | INT(11) | NO | FK | - | Referencia al usuario (usuarios.id) que ejecuto el escaneo. |
| `inventario_id` | INT(11) | NO | FK | - | Referencia al registro de inventario (inventario.id) consultado. |
| `fecha_hora` | TIMESTAMP | NO | - | current_timestamp() | Fecha y hora en que se realizo la consulta. |

<p align="center"><em>Tabla 9.3.16. Diccionario de datos de la tabla historial_codigos.</em></p>

**Indices:** PK (`id`) - KEY `usuario_id` - KEY `inventario_id`.
**Relaciones:** `usuario_id` -> `usuarios.id`; `inventario_id` -> `inventario.id`.

---

### 8. Tabla `movimientos`

Funciona como **bitacora de auditoria general** del sistema. Registra toda accion
de creacion, edicion o eliminacion efectuada en cualquier modulo, con fines de
rastreo y responsabilidad.

| Campo | Tipo de dato | Nulo | Llave | Valor por defecto | Descripcion |
|---|---|---|---|---|---|
| `id` | INT(11) | NO | PK | AUTO_INCREMENT | Identificador unico del movimiento. Clave primaria autoincremental. |
| `usuario_id` | INT(11) | NO | FK | - | Referencia al usuario (usuarios.id) que ejecuto la accion. |
| `modulo` | VARCHAR(50) | NO | - | - | Modulo del sistema donde ocurrio la accion (ej. 'articulos', 'habitaciones', 'auth'). |
| `accion` | VARCHAR(30) | NO | - | - | Tipo de operacion realizada (ej. 'crear', 'editar', 'eliminar', 'login', 'logout'). |
| `registro_id` | INT(11) | SI | - (polimorfico) | NULL | Identificador del registro afectado. Referencia polimorfica: apunta a distintas tablas segun modulo, por lo que no es FK. |
| `descripcion` | TEXT | NO | - | - | Descripcion legible de la accion realizada. |
| `fecha` | TIMESTAMP | NO | - | current_timestamp() | Fecha y hora en que se registro el movimiento. |

<p align="center"><em>Tabla 9.3.17. Diccionario de datos de la tabla movimientos.</em></p>

**Indices:** PK (`id`) - KEY `usuario_id`.
**Relaciones:** `usuario_id` -> `usuarios.id`.

---

## Objetos Derivados (Vistas)

Las siguientes vistas no almacenan datos fisicamente; se calculan en tiempo de
ejecucion a partir de las tablas base y alimentan los modulos de revision,
reporteria y tablero de control.

### 9. Vista `vista_faltantes`

Vista base de calculo. Confronta la plantilla (`inventario_base`) con las
existencias reales (`inventario`) por cada combinacion de habitacion y articulo.
Excluye habitaciones en estado 'bloqueada'.

| Campo | Tipo de dato | Descripcion |
|---|---|---|
| `habitacion_id` | INT(11) | Identificador de la habitacion. |
| `numero` | VARCHAR(10) | Numero de la habitacion. |
| `tipo` | VARCHAR(50) | Tipo de la habitacion. |
| `piso` | INT(11) | Piso de la habitacion. |
| `estado_habitacion` | ENUM | Estado operativo de la habitacion. |
| `articulo_id` | INT(11) | Identificador del articulo. |
| `articulo` | VARCHAR(100) | Nombre del articulo. |
| `cantidad_base` | INT(11) | Cantidad esperada segun la plantilla. |
| `cantidad_actual` | INT(11) | Cantidad real registrada. |
| `faltantes` | DECIMAL | GREATEST(base - real, 0). Unidades faltantes. |
| `sobrantes` | DECIMAL | GREATEST(real - base, 0). Unidades sobrantes. |
| `estado_articulo` | ENUM | Estado de conservacion del articulo real. |

<p align="center"><em>Tabla 9.3.18. Diccionario de datos de la vista vista_faltantes.</em></p>

---

### 10. Vista `vista_dashboard`

Agrega `vista_faltantes` a nivel de habitacion. Es la fuente principal del
tablero de control y del reporte exportable.

| Campo | Tipo de dato | Descripcion |
|---|---|---|
| `habitacion_id` | INT(11) | Identificador de la habitacion. |
| `numero` | VARCHAR(10) | Numero de la habitacion. |
| `tipo` | VARCHAR(50) | Tipo de la habitacion. |
| `piso` | INT(11) | Piso de la habitacion. |
| `estado_habitacion` | ENUM | Estado operativo de la habitacion. |
| `total_base` | DECIMAL | Suma de las cantidades esperadas. |
| `total_faltantes` | DECIMAL | Suma total de unidades faltantes. |
| `total_sobrantes` | DECIMAL | Suma total de unidades sobrantes. |
| `estado_inventario` | VARCHAR | Clasificacion: 'completo', 'incompleto', 'sobrante' o 'mixto'. |
| `articulos_faltantes` | TEXT | Lista concatenada de articulos faltantes con su cantidad. |
| `articulos_sobrantes` | TEXT | Lista concatenada de articulos sobrantes con su cantidad. |

<p align="center"><em>Tabla 9.3.19. Diccionario de datos de la vista vista_dashboard.</em></p>

---

### 11. Vista `vista_estadisticas_articulos`

Resume los faltantes agrupados por articulo, para identificar los bienes con
mayor deficit en el hotel.

| Campo | Tipo de dato | Descripcion |
|---|---|---|
| `articulo` | VARCHAR(100) | Nombre del articulo. |
| `total_faltantes` | DECIMAL | Suma total de unidades faltantes del articulo. |
| `habitaciones_afectadas` | BIGINT | Numero de habitaciones distintas con faltante de ese articulo. |

<p align="center"><em>Tabla 9.3.20. Diccionario de datos de la vista vista_estadisticas_articulos.</em></p>

---

### 12. Vista `vista_estadisticas_pisos`

Resume el estado del inventario a nivel de piso, para el analisis comparativo
entre plantas del hotel.

| Campo | Tipo de dato | Descripcion |
|---|---|---|
| `piso` | INT(11) | Numero de piso. |
| `total_habitaciones` | BIGINT | Total de habitaciones consideradas en el piso. |
| `habitaciones_con_faltantes` | DECIMAL | Cantidad de habitaciones con al menos un faltante. |
| `habitaciones_completas` | DECIMAL | Cantidad de habitaciones sin faltantes. |

<p align="center"><em>Tabla 9.3.21. Diccionario de datos de la vista vista_estadisticas_pisos.</em></p>
