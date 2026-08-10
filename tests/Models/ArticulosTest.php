<?php

declare(strict_types=1);

use Tests\TestCase;

final class ArticulosTest extends TestCase
{
    private Articulos $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Articulos();
    }

    public function test_obtener_todo_devuelve_array(): void
    {
        $result = $this->model->obtenerTodo();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('nombre', $result[0]);
    }

    public function test_obtener_por_id_devuelve_articulo_existente(): void
    {
        $articulo = $this->model->obtenerPorId(1);

        $this->assertNotNull($articulo);
        $this->assertEquals(1, $articulo['id']);
        $this->assertNotEmpty($articulo['nombre']);
    }

    public function test_obtener_por_id_devuelve_null_para_id_inexistente(): void
    {
        $articulo = $this->model->obtenerPorId(99999);

        $this->assertFalse($articulo);
    }

    public function test_agregar_articulo_devuelve_exito(): void
    {
        $result = $this->model->agregarArticulo('articulo_test_' . bin2hex(random_bytes(6)), 'descripcion test');

        $this->assertTrue($result['exito']);
        $this->assertGreaterThan(0, $result['id']);

        // Cleanup
        $this->cleanTable('articulos', ['id' => $result['id']]);
    }

    public function test_agregar_articulo_duplicado_devuelve_error(): void
    {
        // Use a known existing article name
        $result = $this->model->agregarArticulo('despertador', 'descripcion test');

        $this->assertFalse($result['exito']);
        $this->assertEquals('duplicado', $result['error']);
    }

    public function test_editar_articulo_devuelve_exito(): void
    {
        // Create test article
        $id = $this->insertRow('articulos', [
            'nombre' => 'articulo_editar_test_' . bin2hex(random_bytes(6)),
            'descripcion' => 'original',
            'usa_codigo_barras' => 0,
        ]);

        $result = $this->model->editarArticulo($id, 'articulo_editar_test_nuevo', 'descripcion editada');

        $this->assertTrue($result['exito']);

        $articulo = $this->model->obtenerPorId($id);
        $this->assertEquals('articulo_editar_test_nuevo', $articulo['nombre']);
        $this->assertEquals('descripcion editada', $articulo['descripcion']);

        // Cleanup
        $this->cleanTable('articulos', ['id' => $id]);
    }

    public function test_editar_articulo_duplicado_devuelve_error(): void
    {
        // Edit article 1 to use the name of article 2 ('capuchas') to trigger duplicate
        $result = $this->model->editarArticulo(1, 'capuchas', 'nueva descripcion');

        $this->assertFalse($result['exito']);
        $this->assertEquals('duplicado', $result['error']);
    }

    public function test_eliminar_articulo_elimina_registro(): void
    {
        $id = $this->insertRow('articulos', [
            'nombre' => 'articulo_eliminar_test_' . bin2hex(random_bytes(6)),
            'descripcion' => 'test',
            'usa_codigo_barras' => 0,
        ]);

        $this->model->eliminarArticulo($id);
        $this->assertDatabaseMissing('articulos', ['id' => $id]);
    }
}
