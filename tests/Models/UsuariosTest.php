<?php

declare(strict_types=1);

use Tests\TestCase;

final class UsuariosTest extends TestCase
{
    private Usuarios $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Usuarios();
    }

    public function test_obtener_todo_devuelve_array(): void
    {
        $result = $this->model->obtenerTodo();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_obtener_por_id_devuelve_usuario_existente(): void
    {
        $usuario = $this->model->obtenerPorId(1);
        $this->assertNotNull($usuario);
        $this->assertEquals(1, $usuario['id']);
    }

    public function test_obtener_por_id_devuelve_false_para_id_inexistente(): void
    {
        $usuario = $this->model->obtenerPorId(99999);
        $this->assertFalse($usuario);
    }

    public function test_agregar_usuario_devuelve_exito(): void
    {
        $result = $this->model->agregarUsuario(
            'Test User ' . bin2hex(random_bytes(6)),
            'testuser_' . bin2hex(random_bytes(6)) . '@test.com',
            'password123',
            'operador'
        );

        $this->assertTrue($result['exito']);
        $this->assertGreaterThan(0, $result['id']);

        $this->cleanTable('usuarios', ['id' => $result['id']]);
    }

    public function test_agregar_usuario_duplicado_devuelve_error(): void
    {
        $result = $this->model->agregarUsuario(
            'Test Duplicate',
            'hectorlucioholguin@gmail.com',
            'password123',
            'operador'
        );

        $this->assertFalse($result['exito']);
        $this->assertEquals('duplicado', $result['error']);
    }

    public function test_editar_usuario_devuelve_exito(): void
    {
        $id = $this->insertRow('usuarios', [
            'nombre' => 'Edit User ' . bin2hex(random_bytes(6)),
            'correo' => 'edit_' . bin2hex(random_bytes(6)) . '@test.com',
            'password' => password_hash('pass123', PASSWORD_DEFAULT),
            'rol' => 'operador',
            'activo' => 1,
        ]);

        $result = $this->model->editarUsuario($id, 'Edit User Updated ' . bin2hex(random_bytes(6)), 'editupdated_' . bin2hex(random_bytes(6)) . '@test.com', 'supervisor', 1);

        $this->assertTrue($result['exito']);

        $usuario = $this->model->obtenerPorId($id);
        $this->assertEquals('supervisor', $usuario['rol']);

        $this->cleanTable('usuarios', ['id' => $id]);
    }

    public function test_editar_usuario_duplicado_devuelve_error(): void
    {
        $id = $this->insertRow('usuarios', [
            'nombre' => 'Edit User Dup ' . bin2hex(random_bytes(6)),
            'correo' => 'editdup_' . bin2hex(random_bytes(6)) . '@test.com',
            'password' => password_hash('pass123', PASSWORD_DEFAULT),
            'rol' => 'operador',
            'activo' => 1,
        ]);

        $result = $this->model->editarUsuario($id, 'Edit User Dup', 'hectorlucioholguin@gmail.com', 'operador', 1);

        $this->assertFalse($result['exito']);
        $this->assertEquals('duplicado', $result['error']);

        $this->cleanTable('usuarios', ['id' => $id]);
    }

    public function test_eliminar_usuario_elimina_registro(): void
    {
        $id = $this->insertRow('usuarios', [
            'nombre' => 'Delete User ' . bin2hex(random_bytes(6)),
            'correo' => 'del_' . bin2hex(random_bytes(6)) . '@test.com',
            'password' => password_hash('pass123', PASSWORD_DEFAULT),
            'rol' => 'operador',
            'activo' => 1,
        ]);

        $this->model->eliminarUsuario($id);
        $this->assertDatabaseMissing('usuarios', ['id' => $id]);
    }

    public function test_cambiar_estado_actualiza_activo(): void
    {
        $id = $this->insertRow('usuarios', [
            'nombre' => 'Estado User ' . bin2hex(random_bytes(6)),
            'correo' => 'estado_' . bin2hex(random_bytes(6)) . '@test.com',
            'password' => password_hash('pass123', PASSWORD_DEFAULT),
            'rol' => 'operador',
            'activo' => 1,
        ]);

        $this->model->cambiarEstado($id, 0);

        $usuario = $this->model->obtenerPorId($id);
        $this->assertEquals(0, $usuario['activo']);

        $this->cleanTable('usuarios', ['id' => $id]);
    }

    public function test_contar_administradores_activos_devuelve_entero(): void
    {
        $total = $this->model->contarAdministradoresActivos();
        $this->assertIsInt($total);
        $this->assertGreaterThanOrEqual(0, $total);
    }

    public function test_obtener_nombre_usuario_devuelve_nombre(): void
    {
        $nombre = $this->model->obtenerNombreUsuario(1);
        $this->assertNotEmpty($nombre);
    }

    public function test_obtener_nombre_usuario_devuelve_fallback_para_id_inexistente(): void
    {
        $nombre = $this->model->obtenerNombreUsuario(99999);
        $this->assertStringContainsString('usuario', $nombre);
    }

    public function test_editar_perfil_devuelve_exito_con_password(): void
    {
        $id = $this->insertRow('usuarios', [
            'nombre' => 'Perfil User ' . bin2hex(random_bytes(6)),
            'correo' => 'perfil_' . bin2hex(random_bytes(6)) . '@test.com',
            'password' => password_hash('oldpass', PASSWORD_DEFAULT),
            'rol' => 'operador',
            'activo' => 1,
        ]);

        $result = $this->model->editarPerfil($id, 'Perfil Updated ' . bin2hex(random_bytes(6)), 'perfilupd_' . bin2hex(random_bytes(6)) . '@test.com', 'newpass');

        $this->assertTrue($result['exito']);

        $usuario = $this->model->obtenerPorId($id);
        $this->assertNotEquals(password_hash('oldpass', PASSWORD_DEFAULT), $usuario['password']);

        $this->cleanTable('usuarios', ['id' => $id]);
    }

    public function test_editar_perfil_devuelve_exito_sin_password(): void
    {
        $id = $this->insertRow('usuarios', [
            'nombre' => 'Perfil NoPass ' . bin2hex(random_bytes(6)),
            'correo' => 'perfilnp_' . bin2hex(random_bytes(6)) . '@test.com',
            'password' => password_hash('oldpass', PASSWORD_DEFAULT),
            'rol' => 'operador',
            'activo' => 1,
        ]);

        $result = $this->model->editarPerfil($id, 'Perfil NoPass Updated ' . bin2hex(random_bytes(6)), 'perfilnpupd_' . bin2hex(random_bytes(6)) . '@test.com');

        $this->assertTrue($result['exito']);

        $this->cleanTable('usuarios', ['id' => $id]);
    }
}
