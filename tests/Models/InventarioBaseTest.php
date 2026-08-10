<?php

declare(strict_types=1);

use Tests\TestCase;

final class InventarioBaseTest extends TestCase
{
    private Inventario_base $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Inventario_base();
    }

    public function test_obtener_todo_devuelve_array(): void
    {
        $result = $this->model->obtenerTodo();
        $this->assertIsArray($result);
    }

    public function test_obtener_articulos_devuelve_array(): void
    {
        $result = $this->model->obtenerArticulos();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_obtener_por_id_devuelve_registro_existente(): void
    {
        $inserted = $this->model->agregarInventario_base('sencilla', 1, 10);
        $this->assertTrue($inserted['exito']);

        $result = $this->model->obtenerPorId($inserted['id']);
        $this->assertNotNull($result);
        $this->assertEquals($inserted['id'], $result['id']);

        $this->cleanTable('inventario_base', ['id' => $inserted['id']]);
    }

    public function test_obtener_por_id_devuelve_null_para_id_inexistente(): void
    {
        $result = $this->model->obtenerPorId(99999);
        $this->assertFalse($result);
    }

    public function test_obtener_nombre_articulo_devuelve_nombre(): void
    {
        $nombre = $this->model->obtenerNombreArticulo(1);
        $this->assertEquals('despertador', $nombre);
    }

    public function test_obtener_nombre_articulo_devuelve_fallback(): void
    {
        $nombre = $this->model->obtenerNombreArticulo(99999);
        $this->assertStringContainsString('artículo', $nombre);
    }

    public function test_agregar_inventario_base_devuelve_exito(): void
    {
        $result = $this->model->agregarInventario_base('sencilla', 1, 10);

        $this->assertTrue($result['exito']);
        $this->assertGreaterThan(0, $result['id']);

        $this->cleanTable('inventario_base', ['id' => $result['id']]);
    }

    public function test_agregar_inventario_base_duplicado_devuelve_error(): void
    {
        $result = $this->model->agregarInventario_base('sencilla', 1, 10);
        $this->assertTrue($result['exito']);

        $result2 = $this->model->agregarInventario_base('sencilla', 1, 5);
        $this->assertFalse($result2['exito']);
        $this->assertEquals('duplicado', $result2['error']);

        $this->cleanTable('inventario_base', ['id' => $result['id']]);
    }

    public function test_editar_inventario_base_devuelve_exito(): void
    {
        $result = $this->model->agregarInventario_base('doble', 1, 10);
        $id = $result['id'];

        $editResult = $this->model->editarInventario_base($id, 'doble', 1, 20);

        $this->assertTrue($editResult['exito']);

        $row = $this->model->obtenerPorId($id);
        $this->assertEquals(20, $row['cantidad']);

        $this->cleanTable('inventario_base', ['id' => $id]);
    }

    public function test_eliminar_inventario_base_elimina_registro(): void
    {
        $result = $this->model->agregarInventario_base('superior', 1, 10);
        $id = $result['id'];

        $this->model->eliminarInventario_base($id);
        $this->assertDatabaseMissing('inventario_base', ['id' => $id]);
    }
}
