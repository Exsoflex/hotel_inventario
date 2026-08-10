<?php

declare(strict_types=1);

use Tests\TestCase;

final class HistorialCodigosControllerTest extends TestCase
{
    private HistorialCodigosController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new HistorialCodigosController();
    }

    protected function tearDown(): void
    {
        $this->resetSession();
        parent::tearDown();
    }

    public function test_index_redirige_sin_sesion(): void
    {
        $this->resetSession();

        $this->expectOutputRegex('/Location:.*modulo=dashboard/');
        $this->controller->index();
    }

    public function test_buscar_redirige_si_no_es_post(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->setSessionUser(['id' => 1, 'rol' => 'admin']);

        $this->expectOutputRegex('/Location:.*modulo=historial_codigos/');
        $this->controller->buscar();
    }

    public function test_buscar_redirige_si_codigo_vacio(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['codigo' => '   '];
        $this->setSessionUser(['id' => 1, 'rol' => 'admin']);

        $this->expectOutputRegex('/Location:.*error=vacio/');
        $this->controller->buscar();
    }

    public function test_buscar_redirige_si_codigo_no_encontrado(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['codigo' => 'COD_INEXISTENTE'];
        $this->setSessionUser(['id' => 1, 'rol' => 'admin']);

        $this->expectOutputRegex('/Location:.*error=no_encontrado/');
        $this->controller->buscar();
    }

    public function test_eliminar_redirige_sin_sesion(): void
    {
        $this->resetSession();

        $this->expectOutputRegex('/Location:.*modulo=dashboard/');
        $this->controller->eliminar();
    }

    public function test_eliminar_redirige_sin_rol_autorizado(): void
    {
        $this->setSessionUser(['id' => 1, 'rol' => 'operador']);

        $this->expectOutputRegex('/Location:.*modulo=dashboard/');
        $this->controller->eliminar();
    }
}
