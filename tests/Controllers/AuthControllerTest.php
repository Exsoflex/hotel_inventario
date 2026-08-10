<?php

declare(strict_types=1);

use Tests\TestCase;

final class AuthControllerTest extends TestCase
{
    private AuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AuthController();
    }

    protected function tearDown(): void
    {
        $this->resetSession();
        parent::tearDown();
    }

    public function test_index_redirige_si_ya_sesion_activa(): void
    {
        $this->setSessionUser(['id' => 1, 'rol' => 'admin']);

        $this->expectOutputRegex('/Location:.*modulo=dashboard/');
        $this->controller->index();
    }

    public function test_index_no_redirige_sin_sesion(): void
    {
        $this->resetSession();

        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        $this->assertStringNotContainsString('Location:', $output);
    }

    public function test_login_redirige_con_campos_vacios(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['login' => '', 'password' => ''];

        $this->expectOutputRegex('/Location:.*error=campos/');
        $this->controller->login();
    }

    public function test_login_redirige_con_usuario_inexistente(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['login' => 'noexiste', 'password' => 'test'];

        $this->expectOutputRegex('/Location:.*error=usuario/');
        $this->controller->login();
    }

    public function test_login_redirige_con_password_incorrecto(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['login' => 'hectorlucioholguin@gmail.com', 'password' => 'wrongpass'];

        $this->expectOutputRegex('/Location:.*error=password/');
        $this->controller->login();
    }

    public function test_login_exitoso_crea_sesion_y_redirige(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['login' => 'hectorlucioholguin@gmail.com', 'password' => '777'];

        $this->expectOutputRegex('/Location:.*modulo=dashboard/');
        $this->controller->login();

        $this->assertArrayHasKey('usuario', $_SESSION);
        $this->assertEquals(1, $_SESSION['usuario']['id']);
    }

    public function test_logout_destruye_sesion_y_redirige(): void
    {
        $this->setSessionUser(['id' => 1, 'rol' => 'admin', 'nombre' => 'Test']);

        $this->expectOutputRegex('/Location:.*modulo=auth/');
        $this->controller->logout();

        $this->assertEmpty($_SESSION);
    }
}
