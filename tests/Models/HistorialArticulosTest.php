<?php

declare(strict_types=1);

use Tests\TestCase;

final class HistorialArticulosTest extends TestCase
{
    private HistorialArticulos $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new HistorialArticulos();
    }

    public function test_obtener_por_inventario_id_devuelve_array(): void
    {
        $result = $this->model->obtenerPorInventarioId(1);
        $this->assertIsArray($result);
    }

    public function test_agregar_crea_registro(): void
    {
        $articuloId = $this->insertRow('articulos', [
            'nombre' => 'articulo_hist_test',
            'descripcion' => 'test',
            'usa_codigo_barras' => 0,
        ]);

        $habitacionId = $this->insertRow('habitaciones', [
            'numero' => (string)random_int(9000, 9999),
            'tipo' => 'sencilla',
            'descripcion' => '',
            'piso' => 1,
            'estado' => 'disponible',
        ]);

        $inventarioId = $this->insertRow('inventario', [
            'habitacion_id' => $habitacionId,
            'articulo_id' => $articuloId,
            'cantidad' => 1,
            'estado' => 'bueno',
            'comentarios' => '',
            'codigo_barras' => null,
        ]);

        $id = $this->model->agregar($inventarioId, '2024-01-01', 'Nota de prueba');

        $this->assertGreaterThan(0, $id);
        $this->assertDatabaseHas('historial_articulos', ['id' => $id, 'inventario_id' => $inventarioId, 'nota' => 'Nota de prueba']);

        // Cleanup
        $this->cleanTable('historial_articulos', ['id' => $id]);
        $this->cleanTable('inventario', ['id' => $inventarioId]);
        $this->cleanTable('habitaciones', ['id' => $habitacionId]);
        $this->cleanTable('articulos', ['id' => $articuloId]);
    }

    public function test_editar_actualiza_registro(): void
    {
        $articuloId = $this->insertRow('articulos', [
            'nombre' => 'articulo_hist_edit_test',
            'descripcion' => 'test',
            'usa_codigo_barras' => 0,
        ]);

        $habitacionId = $this->insertRow('habitaciones', [
            'numero' => (string)random_int(9000, 9999),
            'tipo' => 'sencilla',
            'descripcion' => '',
            'piso' => 1,
            'estado' => 'disponible',
        ]);

        $inventarioId = $this->insertRow('inventario', [
            'habitacion_id' => $habitacionId,
            'articulo_id' => $articuloId,
            'cantidad' => 1,
            'estado' => 'bueno',
            'comentarios' => '',
            'codigo_barras' => null,
        ]);

        $id = $this->model->agregar($inventarioId, '2024-01-01', 'Nota original');

        $rowCount = $this->model->editar($id, '2024-06-15', 'Nota editada');

        $this->assertEquals(1, $rowCount);

        $row = $this->model->obtenerPorId($id);
        $this->assertEquals('2024-06-15', $row['fecha']);
        $this->assertEquals('Nota editada', $row['nota']);

        // Cleanup
        $this->cleanTable('historial_articulos', ['id' => $id]);
        $this->cleanTable('inventario', ['id' => $inventarioId]);
        $this->cleanTable('habitaciones', ['id' => $habitacionId]);
        $this->cleanTable('articulos', ['id' => $articuloId]);
    }

    public function test_eliminar_elimina_registro(): void
    {
        $articuloId = $this->insertRow('articulos', [
            'nombre' => 'articulo_hist_del_test',
            'descripcion' => 'test',
            'usa_codigo_barras' => 0,
        ]);

        $habitacionId = $this->insertRow('habitaciones', [
            'numero' => (string)random_int(9000, 9999),
            'tipo' => 'sencilla',
            'descripcion' => '',
            'piso' => 1,
            'estado' => 'disponible',
        ]);

        $inventarioId = $this->insertRow('inventario', [
            'habitacion_id' => $habitacionId,
            'articulo_id' => $articuloId,
            'cantidad' => 1,
            'estado' => 'bueno',
            'comentarios' => '',
            'codigo_barras' => null,
        ]);

        $id = $this->model->agregar($inventarioId, '2024-01-01', 'Nota para eliminar');

        $rowCount = $this->model->eliminar($id);

        $this->assertEquals(1, $rowCount);
        $this->assertDatabaseMissing('historial_articulos', ['id' => $id]);

        // Cleanup
        $this->cleanTable('inventario', ['id' => $inventarioId]);
        $this->cleanTable('habitaciones', ['id' => $habitacionId]);
        $this->cleanTable('articulos', ['id' => $articuloId]);
    }

    public function test_obtener_por_id_devuelve_registro(): void
    {
        $articuloId = $this->insertRow('articulos', [
            'nombre' => 'articulo_hist_get_test',
            'descripcion' => 'test',
            'usa_codigo_barras' => 0,
        ]);

        $habitacionId = $this->insertRow('habitaciones', [
            'numero' => (string)random_int(9000, 9999),
            'tipo' => 'sencilla',
            'descripcion' => '',
            'piso' => 1,
            'estado' => 'disponible',
        ]);

        $inventarioId = $this->insertRow('inventario', [
            'habitacion_id' => $habitacionId,
            'articulo_id' => $articuloId,
            'cantidad' => 1,
            'estado' => 'bueno',
            'comentarios' => '',
            'codigo_barras' => null,
        ]);

        $id = $this->model->agregar($inventarioId, '2024-01-01', 'Nota para obtener');

        $row = $this->model->obtenerPorId($id);

        $this->assertNotNull($row);
        $this->assertEquals($inventarioId, $row['inventario_id']);
        $this->assertEquals('Nota para obtener', $row['nota']);

        // Cleanup
        $this->cleanTable('historial_articulos', ['id' => $id]);
        $this->cleanTable('inventario', ['id' => $inventarioId]);
        $this->cleanTable('habitaciones', ['id' => $habitacionId]);
        $this->cleanTable('articulos', ['id' => $articuloId]);
    }

    public function test_obtener_por_id_devuelve_null_para_id_inexistente(): void
    {
        $row = $this->model->obtenerPorId(99999);
        $this->assertFalse($row);
    }
}
