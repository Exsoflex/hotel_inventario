<?php

declare(strict_types=1);

use Tests\TestCase;

final class InventarioTest extends TestCase
{
    private Inventario $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Inventario();
    }

    public function test_obtener_todo_devuelve_array(): void
    {
        $result = $this->model->obtenerTodo();
        $this->assertIsArray($result);
    }

    public function test_obtener_todo_con_filtros(): void
    {
        $result = $this->model->obtenerTodo(null, '103', '', '');
        $this->assertIsArray($result);
    }

    public function test_obtener_habitaciones_devuelve_array(): void
    {
        $result = $this->model->obtenerHabitaciones();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_obtener_pisos_devuelve_array(): void
    {
        $result = $this->model->obtenerPisos();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_obtener_articulos_devuelve_array(): void
    {
        $result = $this->model->obtenerArticulos();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_obtener_articulo_por_id_devuelve_articulo(): void
    {
        $articulo = $this->model->obtenerArticuloPorId(1);
        $this->assertNotNull($articulo);
        $this->assertEquals(1, $articulo['id']);
    }

    public function test_obtener_por_id_devuelve_inventario_existente(): void
    {
        $inventario = $this->model->obtenerPorId(2);
        $this->assertNotNull($inventario);
        $this->assertEquals(2, $inventario['id']);
    }

    public function test_obtener_numero_habitacion_devuelve_numero(): void
    {
        $numero = $this->model->obtenerNumeroHabitacion(1);
        $this->assertEquals('103', $numero);
    }

    public function test_obtener_numero_habitacion_devuelve_fallback_para_id_inexistente(): void
    {
        $numero = $this->model->obtenerNumeroHabitacion(99999);
        $this->assertStringContainsString('habitación', $numero);
    }

    public function test_obtener_nombre_articulo_devuelve_nombre(): void
    {
        $nombre = $this->model->obtenerNombreArticulo(1);
        $this->assertEquals('despertador', $nombre);
    }

    public function test_obtener_nombre_articulo_devuelve_fallback_para_id_inexistente(): void
    {
        $nombre = $this->model->obtenerNombreArticulo(99999);
        $this->assertStringContainsString('artículo', $nombre);
    }

    public function test_agregar_inventario_devuelve_exito(): void
    {
        $habitacionId = $this->insertRow('habitaciones', [
            'numero' => (string)random_int(9000, 9999),
            'tipo' => 'sencilla',
            'descripcion' => '',
            'piso' => 1,
            'estado' => 'disponible',
        ]);

        $result = $this->model->agregarInventario($habitacionId, 1, 5, 'bueno', 'test', 'COD123');

        $this->assertTrue($result['exito']);
        $this->assertGreaterThan(0, $result['id']);

        // Cleanup
        $this->cleanTable('inventario', ['id' => $result['id']]);
        $this->cleanTable('habitaciones', ['id' => $habitacionId]);
    }

    public function test_agregar_inventario_duplicado_devuelve_error(): void
    {
        $result = $this->model->agregarInventario(1, 1, 5, 'bueno', 'test', 'COD_DUP');

        $this->assertFalse($result['exito']);
        $this->assertEquals('duplicado', $result['error']);
    }

    public function test_editar_inventario_devuelve_exito(): void
    {
        $habitacionId = $this->insertRow('habitaciones', [
            'numero' => (string)random_int(9000, 9999),
            'tipo' => 'sencilla',
            'descripcion' => '',
            'piso' => 1,
            'estado' => 'disponible',
        ]);

        $result = $this->model->agregarInventario($habitacionId, 1, 5, 'bueno', 'test', 'COD_EDIT');
        $inventarioId = $result['id'];

        $editResult = $this->model->editarInventario($inventarioId, $habitacionId, 1, 10, 'dañado', 'editado', 'COD_EDIT2');

        $this->assertTrue($editResult['exito']);

        // Cleanup
        $this->cleanTable('inventario', ['id' => $inventarioId]);
        $this->cleanTable('habitaciones', ['id' => $habitacionId]);
    }

    public function test_eliminar_inventario_elimina_registro(): void
    {
        $habitacionId = $this->insertRow('habitaciones', [
            'numero' => (string)random_int(9000, 9999),
            'tipo' => 'sencilla',
            'descripcion' => '',
            'piso' => 1,
            'estado' => 'disponible',
        ]);

        $result = $this->model->agregarInventario($habitacionId, 1, 5, 'bueno', 'test', 'COD_DEL');
        $inventarioId = $result['id'];

        $this->model->eliminarInventario($inventarioId);
        $this->assertDatabaseMissing('inventario', ['id' => $inventarioId]);

        // Cleanup
        $this->cleanTable('habitaciones', ['id' => $habitacionId]);
    }
}
