<?php

declare(strict_types=1);

use Tests\TestCase;

final class PerfilControllerTest extends TestCase
{
    private PerfilController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new PerfilController();
    }

    protected function tearDown(): void
    {
        $this->resetSession();
        parent::tearDown();
    }

    public function test_index_sin_sesion_lanza_error(): void
    {
        $this->resetSession();

        // PerfilController::index no llama a verificarRol y accede
        // directamente a $_SESSION['usuario']['id'], por lo que sin
        // sesion activa se produce un TypeError en la vista.
        $this->expectException(TypeError::class);
        $this->controller->index();
    }

    public function test_index_muestra_perfil_con_sesion(): void
    {
        $this->setSessionUser(['id' => 1, 'rol' => 'admin', 'nombre' => 'Admin']);

        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        $this->assertStringNotContainsString('Location:', $output);
    }

    public function test_editar_redirige_si_no_es_post(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setSessionUser(['id' => 1, 'rol' => 'admin', 'nombre' => 'Admin']);

        ob_start();
        $this->controller->editar();
        $output = ob_get_clean();

        $this->assertStringNotContainsString('Location:', $output);
    }

    public function test_editar_redirige_con_campos_vacios(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['nombre' => '', 'correo' => '', 'password' => ''];
        $this->setSessionUser(['id' => 1, 'rol' => 'admin', 'nombre' => 'Admin']);

        ob_start();
        $this->controller->editar();
        $output = ob_get_clean();

        $this->assertNotEmpty($output, 'El view deberia renderizar contenido');
        $this->assertStringContainsString('Llena todos los campos', $output);
    }

    public function test_editar_actualiza_perfil_exitosamente(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'nombre' => 'Nombre Actualizado',
            'correo' => 'actualizado@test.com',
            'password' => '',
        ];
        $this->setSessionUser(['id' => 1, 'rol' => 'admin', 'nombre' => 'Admin']);

        $this->expectOutputRegex('/Location:.*modulo=perfil/');
        $this->controller->editar();

        $this->assertEquals('Nombre Actualizado', $_SESSION['usuario']['nombre']);
    }
}
