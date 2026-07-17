# Modelo y Flujo Relacional - Hotel Inventario

> Documento de referencia de la arquitectura de datos y el flujo relacional
> de toda la aplicacion. Incluye:
> 1. Diagrama Entidad-Relacion (ERD) completo
> 2. Capa de vistas analiticas (Dashboard)
> 3. Arquitectura MVC y flujo de peticiones
> 4. Flujo de datos por operacion (escaneo, revision, auditoria)
> 5. Mapa de JOINs reales por modulo
>
> Los diagramas usan sintaxis Mermaid (se renderizan en GitHub / VSCode con la
> extension "Markdown Preview Mermaid Support").

---

## 1. Diagrama Entidad-Relacion (ERD)

Estructura fisica de la base de datos. Las FK son logicas (usadas por la
aplicacion y los JOINs de las vistas); en el dump no estan declaradas como
constraints fisicos.

```mermaid
erDiagram
    usuarios {
        int id PK
        varchar nombre
        varchar correo UK
        varchar password
        enum rol "admin|supervisor|operador"
        timestamp creado_en
        tinyint activo
        timestamp ultimo_login
    }

    habitaciones {
        int id PK
        varchar numero
        varchar tipo "doble|sencilla|superior..."
        text descripcion
        int piso
        enum estado "disponible|ocupada|limpieza|mantenimiento|bloqueada"
    }

    articulos {
        int id PK
        varchar nombre
        text descripcion
        tinyint usa_codigo_barras
    }

    inventario_base {
        int id PK
        varchar tipo_habitacion "= habitaciones.tipo"
        int articulo_id FK
        int cantidad "cantidad esperada"
    }

    inventario {
        int id PK
        int habitacion_id FK
        int articulo_id FK
        int cantidad "cantidad real"
        enum estado "bueno|danado|en_reparacion|perdido"
        text comentarios
        varchar codigo_barras
    }

    inventario_backup {
        int id
        int habitacion_id
        int articulo_id
        int cantidad
        enum estado
        text comentarios
        varchar codigo_barras
    }

    historial_codigos {
        int id PK
        int usuario_id FK
        int inventario_id FK
        timestamp fecha_hora
    }

    movimientos {
        int id PK
        int usuario_id FK
        varchar modulo
        varchar accion
        int registro_id "polimorfico (sin FK)"
        text descripcion
        timestamp fecha
    }

    articulos      ||--o{ inventario_base   : "define (articulo_id)"
    articulos      ||--o{ inventario        : "cataloga (articulo_id)"
    habitaciones   ||--o{ inventario        : "contiene (habitacion_id)"
    habitaciones   ||..o{ inventario_base   : "por tipo (texto)"
    usuarios       ||--o{ historial_codigos : "registra (usuario_id)"
    inventario     ||--o{ historial_codigos : "escanea (inventario_id)"
    usuarios       ||--o{ movimientos       : "audita (usuario_id)"
    inventario     ||..o| inventario_backup : "respaldo (misma estructura)"
```

### Notas de diseno
- **Relacion por texto:** `habitaciones.tipo` <-> `inventario_base.tipo_habitacion`
  (linea punteada). Unico vinculo debil del modelo; recomendable normalizar a
  `tipo_habitacion_id`.
- **`movimientos.registro_id`** es una referencia **polimorfica**: apunta al ID
  de distintas tablas segun `modulo`, por eso no tiene FK real.
- **`inventario_backup`** replica exactamente la estructura de `inventario` como
  copia de respaldo (sin relaciones formales).
- La regla de negocio central: `faltante = base - real`, `sobrante = real - base`.

---

## 2. Capa de vistas analiticas (Dashboard)

Las vistas no son tablas; son consultas derivadas. Flujo de datos hacia el
modulo Dashboard.

```mermaid
flowchart TD
    H[habitaciones]
    A[articulos]
    IB[inventario_base]
    I[inventario]

    H --> VF[vista_faltantes]
    A --> VF
    IB --> VF
    I --> VF

    VF --> VD[vista_dashboard]
    VF --> VEA[vista_estadisticas_articulos]
    VD --> VEP[vista_estadisticas_pisos]
    VF --> FPH[faltantes_por_habitacion]

    VD --> DASH[Modulo Dashboard]
    VEA --> DASH
    VEP --> DASH
```

| Vista | Deriva de | Calcula |
|---|---|---|
| `vista_faltantes` | 4 tablas base | faltantes/sobrantes por habitacion+articulo |
| `vista_dashboard` | vista_faltantes | totales y estado por habitacion |
| `vista_estadisticas_articulos` | vista_faltantes | faltantes agrupados por articulo |
| `vista_estadisticas_pisos` | vista_dashboard | conteos por piso |
| `faltantes_por_habitacion` | tablas base | vista auxiliar de faltantes |

---

## 3. Arquitectura MVC y flujo de peticiones

Toda peticion pasa por un patron Controller -> Model -> Database, y las acciones
de escritura registran auditoria en `movimientos`.

```mermaid
flowchart LR
    subgraph Cliente
        V[Vistas / JS]
    end
    subgraph Controllers
        C[*Controller.php]
    end
    subgraph Models
        M[*.php Model]
        MOV[Movimientos Model]
    end
    DB[(MySQL)]

    V -->|HTTP request| C
    C -->|logica + validacion| M
    M -->|PDO SQL| DB
    C -.->|accion escritura| MOV
    MOV -->|INSERT movimientos| DB
    DB -->|resultset| M
    M --> C
    C -->|render / JSON| V
```

---

## 4. Flujo de datos por operacion

### 4.1 Escaneo de codigo de barras (Historial de codigos)

```mermaid
sequenceDiagram
    participant U as Usuario
    participant HC as HistorialCodigosController
    participant M as HistorialCodigos (Model)
    participant DB as MySQL

    U->>HC: escanea codigo_barras
    HC->>M: buscarPorCodigo(codigo)
    M->>DB: SELECT inventario JOIN articulos JOIN habitaciones
    DB-->>M: articulo + habitacion
    M-->>HC: datos del item
    HC->>M: registrar(usuario_id, inventario_id)
    M->>DB: INSERT historial_codigos
    DB-->>U: confirmacion + detalle
```

### 4.2 Revision de inventario (faltantes/sobrantes)

```mermaid
flowchart TD
    R[RevisionController] --> VF[consulta vista_faltantes]
    VF --> HAB[habitaciones no bloqueadas]
    VF --> BASE[compara inventario_base vs inventario]
    BASE --> CALC{diferencia}
    CALC -->|base > real| FALT[faltantes]
    CALC -->|real > base| SOB[sobrantes]
    CALC -->|iguales| OK[completo]
```

### 4.3 Auditoria (cualquier escritura)

```mermaid
flowchart LR
    OP[Crear / Editar / Eliminar<br/>en cualquier modulo] --> REG[Movimientos->registrar]
    REG --> INS[(INSERT movimientos<br/>usuario_id, modulo, accion,<br/>registro_id, descripcion)]
    INS --> AUD[Modulo Movimientos<br/>lista auditoria con JOIN usuarios]
```

---

## 5. Mapa de JOINs reales por modulo

Relaciones efectivamente ejecutadas en las consultas del codigo.

```mermaid
flowchart TB
    subgraph Inventario
        I1[inventario] --- H1[habitaciones]
        I1 --- A1[articulos]
    end

    subgraph HistorialCodigos
        HC[historial_codigos] --- U2[usuarios]
        HC --- I2[inventario]
        I2 --- A2[articulos]
        I2 --- H2[habitaciones]
    end

    subgraph Movimientos
        M[movimientos] --- U3[usuarios]
    end

    subgraph InventarioBase
        IB[inventario_base] --- A3[articulos]
    end
```

| Modulo | Tablas / Vistas | JOINs principales |
|---|---|---|
| Auth / Perfil | usuarios, movimientos | - |
| Usuarios | usuarios, movimientos | - |
| Habitaciones | habitaciones, movimientos | - |
| Articulos | articulos, movimientos | - |
| Inventario Base | inventario_base, articulos, movimientos | inventario_base.articulo_id = articulos.id |
| Inventario | inventario, habitaciones, articulos, movimientos | inventario -> habitaciones, inventario -> articulos |
| Revision | vista_faltantes, habitaciones | (via vista) |
| Historial Codigos | historial_codigos, usuarios, inventario, articulos, habitaciones | hc -> usuarios, hc -> inventario -> articulos + habitaciones |
| Movimientos | movimientos, usuarios | movimientos.usuario_id = usuarios.id |
| Dashboard | vista_dashboard, vista_estadisticas_articulos, vista_estadisticas_pisos | (via vistas sobre vista_faltantes) |
