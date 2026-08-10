<?php

declare(strict_types=1);

use Tests\TestCase;

final class MovimientosTest extends TestCase
{
    private Movimientos $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Movimientos();
    }

    public function test_obtener_todo_devuelve_array(): void
    {
        $result = $this->model->obtenerTodo(10, 0);
        $this->assertIsArray($result);
    }

    public function test_obtener_todo_con_busqueda(): void
    {
        $result = $this->model->obtenerTodo(10, 0, null, 'test');
        $this->assertIsArray($result);
    }

    public function test_obtener_todo_filtrado_por_usuario(): void
    {
        $result = $this->model->obtenerTodo(10, 0, 1);
        $this->assertIsArray($result);

        foreach ($result as $row) {
            $this->assertEquals(1, $row['usuario_id']);
        }
    }

    public function test_obtener_por_modulo_devuelve_array(): void
    {
        $result = $this->model->obtenerPorModulo('auth');
        $this->assertIsArray($result);
    }

    public function test_contar_todos_devuelve_entero(): void
    {
        $total = $this->model->contarTodos();
        $this->assertIsInt($total);
        $this->assertGreaterThanOrEqual(0, $total);
    }

    public function test_contar_todos_con_usuario_devuelve_entero(): void
    {
        $total = $this->model->contarTodos(1);
        $this->assertIsInt($total);
        $this->assertGreaterThanOrEqual(0, $total);
    }

    public function test_registrar_crea_movimiento(): void
    {
        $usuarioId = $this->insertRow('usuarios', [
            'nombre' => 'test_mov_user_' . bin2hex(random_bytes(6)),
            'correo' => 'testmov_' . bin2hex(random_bytes(6)) . '@test.com',
            'password' => password_hash('test123', PASSWORD_DEFAULT),
            'rol' => 'operador',
            'activo' => 1,
        ]);

        $_SESSION['usuario'] = ['id' => $usuarioId];

        $this->model->registrar('test', 'prueba', 'Movimiento de prueba', 1);

        $this->assertDatabaseHas('movimientos', [
            'usuario_id' => $usuarioId,
            'modulo' => 'test',
            'accion' => 'prueba',
            'descripcion' => 'Movimiento de prueba',
            'registro_id' => 1,
        ]);

        // Cleanup
        $this->cleanTable('movimientos', ['usuario_id' => $usuarioId, 'modulo' => 'test']);
        $this->cleanTable('usuarios', ['id' => $usuarioId]);
        unset($_SESSION['usuario']);
    }

    public function test_registrar_no_hace_nada_sin_sesion(): void
    {
        unset($_SESSION['usuario']);

        // Should not throw an error
        $this->model->registrar('test', 'prueba', 'Sin sesion', 1);
        $this->assertTrue(true); // No exception means success
    }
}
