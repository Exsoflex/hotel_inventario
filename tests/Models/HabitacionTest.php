<?php

declare(strict_types=1);

use Tests\TestCase;

final class HabitacionTest extends TestCase
{
    private Habitacion $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Habitacion();
    }

    public function test_obtener_todo_devuelve_array(): void
    {
        $result = $this->model->obtenerTodo();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_obtener_por_id_devuelve_habitacion_existente(): void
    {
        $habitacion = $this->model->obtenerPorId(1);
        $this->assertNotNull($habitacion);
        $this->assertEquals(1, $habitacion['id']);
    }

    public function test_obtener_por_id_devuelve_null_para_id_inexistente(): void
    {
        $habitacion = $this->model->obtenerPorId(99999);
        $this->assertFalse($habitacion);
    }

    public function test_agregar_habitacion_devuelve_exito(): void
    {
        $result = $this->model->agregarHabitacion(1, '9999', 'sencilla', 'Test', 'disponible');

        $this->assertTrue($result['exito']);
        $this->assertGreaterThan(0, $result['id']);

        $this->cleanTable('habitaciones', ['id' => $result['id']]);
    }

    public function test_agregar_habitacion_duplicada_devuelve_error(): void
    {
        $result = $this->model->agregarHabitacion(1, '103', 'sencilla', 'Test', 'disponible');
        $this->assertFalse($result['exito']);
        $this->assertEquals('duplicado', $result['error']);
    }

    public function test_editar_habitacion_devuelve_exito(): void
    {
        $id = $this->insertRow('habitaciones', [
            'numero' => (string)random_int(9000, 9999),
            'tipo' => 'sencilla',
            'descripcion' => 'original',
            'piso' => 1,
            'estado' => 'disponible',
        ]);

        $result = $this->model->editarHabitacion($id, 2, '889', 'doble', 'editada', 'ocupada');

        $this->assertTrue($result['exito']);

        $habitacion = $this->model->obtenerPorId($id);
        $this->assertEquals('889', $habitacion['numero']);
        $this->assertEquals('doble', $habitacion['tipo']);
        $this->assertEquals('editada', $habitacion['descripcion']);

        $this->cleanTable('habitaciones', ['id' => $id]);
    }

    public function test_eliminar_habitacion_elimina_registro(): void
    {
        $id = $this->insertRow('habitaciones', [
            'numero' => (string)random_int(9000, 9999),
            'tipo' => 'sencilla',
            'descripcion' => 'test',
            'piso' => 1,
            'estado' => 'disponible',
        ]);

        $this->model->eliminarHabitacion($id);
        $this->assertDatabaseMissing('habitaciones', ['id' => $id]);
    }
}
