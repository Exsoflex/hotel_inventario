<?php

declare(strict_types=1);

use Tests\TestCase;

final class HistorialCodigosTest extends TestCase
{
    private HistorialCodigos $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new HistorialCodigos();
    }

    public function test_buscar_por_codigo_devuelve_articulo_existente(): void
    {
        $articuloId = $this->insertRow('articulos', [
            'nombre' => 'articulo_buscar_test_' . bin2hex(random_bytes(6)),
            'descripcion' => 'test',
            'usa_codigo_barras' => 1,
        ]);

        $habitacionId = $this->insertRow('habitaciones', [
            'numero' => (string)random_int(9000, 9999),
            'tipo' => 'sencilla',
            'descripcion' => '',
            'piso' => 1,
            'estado' => 'disponible',
        ]);

        $codigo = 'COD_TEST_' . bin2hex(random_bytes(6));
        $inventarioId = $this->insertRow('inventario', [
            'habitacion_id' => $habitacionId,
            'articulo_id' => $articuloId,
            'cantidad' => 1,
            'estado' => 'bueno',
            'comentarios' => '',
            'codigo_barras' => $codigo,
        ]);

        $result = $this->model->buscarPorCodigo($codigo);

        $this->assertNotNull($result);
        $this->assertEquals($codigo, $result['codigo_barras']);
        $this->assertEquals('999', $result['habitacion']);

        // Cleanup
        $this->cleanTable('inventario', ['id' => $inventarioId]);
        $this->cleanTable('habitaciones', ['id' => $habitacionId]);
        $this->cleanTable('articulos', ['id' => $articuloId]);
    }

    public function test_buscar_por_codigo_devuelve_false_para_codigo_inexistente(): void
    {
        $result = $this->model->buscarPorCodigo('COD_INEXISTENTE_99999');
        $this->assertFalse($result);
    }

    public function test_registrar_crea_registro(): void
    {
        $usuarioId = $this->insertRow('usuarios', [
            'nombre' => 'test_user_' . bin2hex(random_bytes(6)),
            'correo' => 'test_' . bin2hex(random_bytes(6)) . '@test.com',
            'password' => password_hash('test123', PASSWORD_DEFAULT),
            'rol' => 'operador',
            'activo' => 1,
        ]);

        $articuloId = $this->insertRow('articulos', [
            'nombre' => 'articulo_reg_test_' . bin2hex(random_bytes(6)),
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

        $result = $this->model->registrar($usuarioId, $inventarioId);

        $this->assertTrue($result['exito']);
        $this->assertGreaterThan(0, $result['id']);
        $this->assertDatabaseHas('historial_codigos', ['usuario_id' => $usuarioId, 'inventario_id' => $inventarioId]);

        // Cleanup
        $this->cleanTable('historial_codigos', ['id' => $result['id']]);
        $this->cleanTable('inventario', ['id' => $inventarioId]);
        $this->cleanTable('habitaciones', ['id' => $habitacionId]);
        $this->cleanTable('articulos', ['id' => $articuloId]);
        $this->cleanTable('usuarios', ['id' => $usuarioId]);
    }

    public function test_obtener_todo_devuelve_array(): void
    {
        $result = $this->model->obtenerTodo();
        $this->assertIsArray($result);
    }

    public function test_obtener_todo_filtrado_por_usuario(): void
    {
        $result = $this->model->obtenerTodo(1);
        $this->assertIsArray($result);

        foreach ($result as $row) {
            $this->assertEquals(1, $row['usuario_id']);
        }
    }

    public function test_obtener_por_id_devuelve_registro(): void
    {
        $usuarioId = $this->insertRow('usuarios', [
            'nombre' => 'test_user_' . bin2hex(random_bytes(6)),
            'correo' => 'test_' . bin2hex(random_bytes(6)) . '@test.com',
            'password' => password_hash('test123', PASSWORD_DEFAULT),
            'rol' => 'operador',
            'activo' => 1,
        ]);

        $articuloId = $this->insertRow('articulos', [
            'nombre' => 'articulo_reg_test_' . bin2hex(random_bytes(6)),
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

        $registro = $this->model->registrar($usuarioId, $inventarioId);
        $historialId = $registro['id'];

        $row = $this->model->obtenerPorId($historialId);

        $this->assertNotNull($row);
        $this->assertEquals($usuarioId, $row['usuario_id']);
        $this->assertEquals($inventarioId, $row['inventario_id']);

        // Cleanup
        $this->cleanTable('historial_codigos', ['id' => $historialId]);
        $this->cleanTable('inventario', ['id' => $inventarioId]);
        $this->cleanTable('habitaciones', ['id' => $habitacionId]);
        $this->cleanTable('articulos', ['id' => $articuloId]);
        $this->cleanTable('usuarios', ['id' => $usuarioId]);
    }

    public function test_eliminar_elimina_registro(): void
    {
        $usuarioId = $this->insertRow('usuarios', [
            'nombre' => 'test_user_' . bin2hex(random_bytes(6)),
            'correo' => 'test_' . bin2hex(random_bytes(6)) . '@test.com',
            'password' => password_hash('test123', PASSWORD_DEFAULT),
            'rol' => 'operador',
            'activo' => 1,
        ]);

        $articuloId = $this->insertRow('articulos', [
            'nombre' => 'articulo_reg_test_' . bin2hex(random_bytes(6)),
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

        $registro = $this->model->registrar($usuarioId, $inventarioId);
        $historialId = $registro['id'];

        $this->model->eliminar($historialId);
        $this->assertDatabaseMissing('historial_codigos', ['id' => $historialId]);

        // Cleanup
        $this->cleanTable('inventario', ['id' => $inventarioId]);
        $this->cleanTable('habitaciones', ['id' => $habitacionId]);
        $this->cleanTable('articulos', ['id' => $articuloId]);
        $this->cleanTable('usuarios', ['id' => $usuarioId]);
    }
}
