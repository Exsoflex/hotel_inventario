# Tabla de Requerimientos del Sistema de Inventario Hotelero

## 9.1 Comunicación

La reunión de capacitación con la gerencia operativa se considera una técnica
de recolección de datos secundaria, ya que a partir de la experiencia acumulada
en el uso del sistema se detectaron nuevas necesidades de información y control
que no estaban contempladas en la versión inicial. Esta técnica permitió
identificar requerimientos asociados al seguimiento histórico de los artículos,
la formalización de perfiles de acceso diferenciados y la ampliación de la
clasificación de estados en el inventario.

---

Este documento describe los requerimientos funcionales y no funcionales del
sistema, identificados a partir del analisis del codigo fuente (controladores,
modelos y vistas). Cada requerimiento incluye su identificador, descripcion,
entrada (datos de entrada) y salida (resultado esperado).

---

## Modulo: Autenticacion

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RF-01 | Iniciar sesion de usuario | POST: `login`, `password` | Sesion creada (`$_SESSION['usuario']`) + redireccion a dashboard; o error (`campos`/`usuario`/`password`) |
| RF-02 | Cerrar sesion | GET (accion logout) | Sesion destruida + redireccion a login |
| RF-03 | Gestion de permisos de usuario | Roles: `admin`, `supervisor`, `operador` | Admin: acceso total; Supervisor: operaciones de edicion/creacion/eliminacion; Operador: solo consulta (solo lectura) |

## Modulo: Dashboard

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RF-04 | Ver resumen general del inventario | GET: `buscar` (opcional) | Vista con habitaciones, faltantes/sobrantes, estadisticas por piso y por articulo |
| RF-05 | Exportar dashboard a Excel | GET: `buscar` (opcional) | Archivo `Estadisticas.xlsx` con tablas y 4 graficas (estado, articulos faltantes, faltantes por piso, estado por piso) |

## Modulo: Inventario (por habitacion)

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RF-06 | Listar inventario agrupado por habitacion | GET: `piso`, `buscar`, `estado`, `articulos` | Vista HTML del inventario por habitacion |
| RF-07 | Consultar inventario via AJAX | GET: `piso`, `buscar`, `estado`, `articulos` | JSON agrupado por habitacion |
| RF-08 | Agregar articulo al inventario | POST: `habitacion_id`, `articulo_id`, `cantidad`, `estado` (`bueno`/`dañado`/`en_reparacion`/`perdido`/`nuevo`), `comentarios`, `codigo_barras` | Nuevo registro + movimiento log + redireccion con mensaje `agregado`/error |
| RF-09 | Editar registro de inventario | GET/POST: `id`, `habitacion_id`, `articulo_id`, `cantidad`, `estado` (`bueno`/`dañado`/`en_reparacion`/`perdido`/`nuevo`), `comentarios`, `codigo_barras` | Registro actualizado + movimiento log + redireccion `editado`/error |
| RF-10 | Eliminar registro de inventario | GET: `id` | Registro eliminado (incluyendo estado `nuevo`) + movimiento log + redireccion `eliminado` |
| RF-11 | Exportar inventario a Excel | GET: filtros (piso/buscar/estado/articulos) | Archivo `inventario.xlsx` agrupado por habitacion (incluye estado `nuevo`) |

## Modulo: Inventario Base (catalogo por tipo de habitacion)

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RF-12 | Listar inventario base | GET: `buscar` | Vista de catalogo por tipo de habitacion |
| RF-13 | Agregar articulo al inventario base | POST: `tipo`, `articulo_id`, `cantidad` | Registro creado + log; error si duplicado |
| RF-14 | Editar inventario base | GET/POST: `id`, `tipo`, `articulo_id`, `cantidad` | Registro actualizado + log; error si duplicado |
| RF-15 | Eliminar inventario base | GET: `id` | Registro eliminado + log |
| RF-16 | Exportar inventario base a Excel | GET: `buscar` | Archivo `inventario_base.xlsx` |

## Modulo: Revision (comparacion actual vs base)

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RF-17 | Listar revision de habitaciones | GET: `piso`, `buscar` | Vista de diferencias (faltantes/sobrantes) por habitacion |
| RF-18 | Consultar revision via AJAX | GET: `piso`, `buscar`, `estado`, `tipo` | JSON con faltantes/sobrantes agrupado por habitacion |
| RF-19 | Exportar reporte de revision a Excel | GET: `buscar`, `estado`, `tipo` | Archivo `revision.xlsx` con actual/base/diferencia/estado por habitacion |

## Modulo: Articulos (catalogo)

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RF-20 | Listar articulos | GET: `buscar` | Vista de catalogo de articulos |
| RF-21 | Agregar articulo | POST: `nombre`, `descripcion` | Articulo creado + log; error si duplicado |
| RF-22 | Editar articulo | GET/POST: `id`, `nombre`, `descripcion` | Articulo actualizado + log; error si duplicado |
| RF-23 | Eliminar articulo | GET: `id` | Articulo eliminado + log |
| RF-24 | Exportar articulos a Excel | GET: `buscar` | Archivo `articulos.xlsx` |

## Modulo: Habitaciones (catalogo)

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RF-25 | Listar habitaciones | GET: `buscar` | Vista de habitaciones |
| RF-26 | Agregar habitacion | POST: `piso`, `numero`, `tipo`, `descripcion`, `estado` | Habitacion creada + log; error si duplicado |
| RF-27 | Editar habitacion | GET/POST: `id`, `piso`, `numero`, `tipo`, `descripcion`, `estado` | Habitacion actualizada + log; error si duplicado |
| RF-28 | Eliminar habitacion | GET: `id` | Habitacion eliminada + log |
| RF-29 | Exportar habitaciones a Excel | GET: `buscar` | Archivo `habitaciones.xlsx` |

## Modulo: Usuarios

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RF-30 | Listar usuarios (solo admin) | Sesion activa con rol `admin` | Vista de usuarios |
| RF-31 | Crear usuario (solo admin) | POST: `nombre`, `correo`, `password`, `rol` | Usuario creado + log; error si duplicado |
| RF-32 | Editar usuario (solo admin) | GET/POST: `id`, `nombre`, `correo`, `rol`, `activo` | Usuario actualizado + log; error si duplicado |
| RF-33 | Eliminar usuario (solo admin) | GET: `id` | Usuario eliminado + log |
| RF-34 | Activar usuario (solo admin) | GET: `id` | Estado cambiado a activo + log |
| RF-35 | Desactivar usuario (solo admin) | GET: `id` (no propio) | Estado cambiado a inactivo + log |

## Modulo: Perfil

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RF-36 | Ver perfil propio | Sesion activa | Vista con datos del usuario y sus movimientos |
| RF-37 | Editar perfil propio | POST: `nombre`, `correo`, `password` (opcional) | Perfil actualizado + sesion y log actualizados |

## Modulo: Movimientos (Bitacora/Auditoria)

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RF-38 | Listar bitacora de movimientos | GET: `pagina` | Vista paginada (20/por pagina); admin ve todos, otros solo los suyos |

## Modulo: Historial de Codigos de Barras

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RF-39 | Listar historial de busquedas | Sesion (admin ve todo, otros lo propio) | Vista del historial de codigos escaneados |
| RF-40 | Buscar por codigo de barras | POST: `codigo` | Registra busqueda en historial + redireccion a inventario filtrado por el codigo; error si vacio/no encontrado |
| RF-41 | Eliminar registro de historial | GET: `id` | Registro eliminado + log |

## Modulo: Historial de Articulos

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RF-42 | Ver historial de articulo | GET: vista con `inventario_id` | Modal/vista con entradas de historial del articulo (fecha, nota, acciones) |
| RF-43 | Consultar historial via AJAX | GET: `inventario_id` | JSON con registros de historial ordenados por fecha descendente |
| RF-44 | Agregar entrada al historial | POST: `inventario_id`, `fecha`, `nota` | Registro creado + movimiento log + JSON con exito |
| RF-45 | Editar entrada del historial | POST: `id`, `fecha`, `nota` | Registro actualizado + movimiento log + JSON con exito |
| RF-46 | Eliminar entrada del historial | POST: `id` (solo admin) | Registro eliminado + movimiento log + JSON con exito |

## Requisitos No Funcionales / Transversales

| ID | Descripcion | Entrada | Salida |
|----|-------------|---------|--------|
| RNF-01 | Registro automatico de auditoria | Acciones CRUD/login/logout/exportar | Entrada en tabla `movimientos` (modulo, accion, descripcion) |
| RNF-02 | Validacion de datos de entrada | Campos de formularios | Filtrado (`FILTER_VALIDATE_INT`) y validacion de no vacios antes de guardar |
| RNF-03 | Prevencion de duplicados | Datos unicos (articulo/habitacion/usuario) | Mensaje de error `duplicado` al intentar repetir |
| RNF-04 | Exportacion estandarizada a Excel | Datos de cada modulo | Archivos `.xlsx` con estilos, bordes y fechas (PhpSpreadsheet) |
| RNF-05 | Seguridad de contrasenas | Registro/login | Almacenamiento con `password_hash` / verificacion con `password_verify` |

---

## Resumen

- **Total de requerimientos funcionales:** 46 (RF-01 a RF-46)
- **Total de requerimientos no funcionales:** 5 (RNF-01 a RNF-05)
- **Modulos cubiertos:** 12 (Autenticacion, Dashboard, Inventario, Inventario
  Base, Revision, Articulos, Habitaciones, Usuarios, Perfil, Movimientos,
  Historial de Codigos de Barras, Historial de Articulos)
- **Roles del sistema:** `admin` (acceso total), `supervisor` (edicion/creacion/eliminacion), `operador` (solo lectura)
