<?php

require_once __DIR__ . "/../config/database.php";

class Revision {

    private $conn;

    public function __construct() {

        $database = new Database();
        $this->conn = $database->conectar();

    }

public function obtenerFaltantes(
    $piso = null,
    $buscar = '',
    $estado = '',
    $tipo = ''
) {

    $sql = "SELECT *
            FROM vista_faltantes vf
            WHERE vf.estado_habitacion != 'bloqueada'";

    if ($piso !== null) {
        $sql .= " AND vf.piso = :piso";
    }

    if (!empty($buscar)) {
        $sql .= " AND EXISTS (
                    SELECT 1
                    FROM vista_faltantes vf_buscar
                    WHERE vf_buscar.estado_habitacion != 'bloqueada'
                        AND vf_buscar.piso = vf.piso
                        AND vf_buscar.numero = vf.numero
                        AND (
                            vf_buscar.numero LIKE :buscar
                            OR vf_buscar.articulo LIKE :buscar
                        )
                )";
    }

    if (!empty($tipo)) {
        $sql .= " AND LOWER(vf.tipo) = :tipo";
    }

    if (!empty($estado)) {
        if ($estado === 'faltante') {
            $sql .= " AND EXISTS (
                        SELECT 1
                        FROM vista_faltantes vf_estado
                        WHERE vf_estado.estado_habitacion != 'bloqueada'
                            AND vf_estado.piso = vf.piso
                            AND vf_estado.numero = vf.numero
                        GROUP BY vf_estado.piso, vf_estado.numero
                        HAVING SUM(CASE WHEN vf_estado.faltantes > 0 THEN 1 ELSE 0 END) > 0
                    )";
        } elseif ($estado === 'sobrante') {
            $sql .= " AND EXISTS (
                        SELECT 1
                        FROM vista_faltantes vf_estado
                        WHERE vf_estado.estado_habitacion != 'bloqueada'
                            AND vf_estado.piso = vf.piso
                            AND vf_estado.numero = vf.numero
                        GROUP BY vf_estado.piso, vf_estado.numero
                        HAVING SUM(CASE WHEN vf_estado.faltantes > 0 THEN 1 ELSE 0 END) = 0
                            AND SUM(CASE WHEN vf_estado.sobrantes > 0 THEN 1 ELSE 0 END) > 0
                    )";
        } elseif ($estado === 'completa') {
            $sql .= " AND EXISTS (
                        SELECT 1
                        FROM vista_faltantes vf_estado
                        WHERE vf_estado.estado_habitacion != 'bloqueada'
                            AND vf_estado.piso = vf.piso
                            AND vf_estado.numero = vf.numero
                        GROUP BY vf_estado.piso, vf_estado.numero
                        HAVING SUM(CASE WHEN vf_estado.faltantes > 0 THEN 1 ELSE 0 END) = 0
                            AND SUM(CASE WHEN vf_estado.sobrantes > 0 THEN 1 ELSE 0 END) = 0
                    )";
        }
    }

    $sql .= " ORDER BY vf.piso, vf.numero";

    $stmt = $this->conn->prepare($sql);

    if ($piso !== null) {
        $stmt->bindValue(':piso', $piso, PDO::PARAM_INT);
    }

    if (!empty($buscar)) {
        $stmt->bindValue(':buscar', "%$buscar%");
    }

    if (!empty($tipo)) {
        $stmt->bindValue(':tipo', strtolower($tipo));
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

        public function obtenerPisos() {

        $sql = "SELECT DISTINCT piso
                FROM vista_faltantes
                WHERE estado_habitacion != 'bloqueada'
                ORDER BY piso";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);

    }

}
