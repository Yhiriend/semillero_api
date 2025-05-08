<?php

namespace Tests\Modules\Events;

use App\Modules\Events\Models\EventModel;
use App\Modules\Users\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventModuleTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = UserModel::factory()->create(['role' => 'Coordinador de Eventos']);
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function test_can_list_all_events()
    {
        EventModel::factory()->count(3)->create();
        
        $response = $this->getJson('/api/events');
        
        $response->assertStatus(200)
                 ->assertJsonCount(3)
                 ->assertJsonStructure([
                     '*' => ['id', 'nombre', 'descripcion', 'fecha_inicio', 'fecha_fin']
                 ]);
    }

    /** @test */
    public function test_can_retrieve_single_event()
    {
        $event = EventModel::factory()->create([
            'nombre' => 'Evento de Ejemplo'
        ]);

        $response = $this->getJson("/api/events/{$event->id}");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $event->id,
                     'nombre' => 'Evento de Ejemplo'
                 ]);
    }

    /** @test */
    public function test_can_create_new_event()
    {
        $payload = [
            'nombre' => 'Nuevo Evento',
            'descripcion' => 'Descripción del evento',
            'coordinador_id' => $this->user->id,
            'fecha_inicio' => '2024-01-01 09:00:00',
            'fecha_fin' => '2024-01-02 18:00:00',
            'ubicacion' => 'Auditorio Principal'
        ];

        $response = $this->postJson('/api/events', $payload);
        
        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'nombre', 'fecha_inicio'])
                 ->assertJson(['nombre' => 'Nuevo Evento']);
        
        $this->assertDatabaseHas('Evento', ['nombre' => 'Nuevo Evento']);
    }

    /** @test */
    public function test_can_update_existing_event()
    {
        $event = EventModel::factory()->create();
        
        $updateData = [
            'nombre' => 'Evento Actualizado',
            'descripcion' => 'Nueva descripción'
        ];

        $response = $this->putJson("/api/events/{$event->id}", $updateData);
        
        $response->assertStatus(200)
                 ->assertJson(['nombre' => 'Evento Actualizado']);
        
        $this->assertDatabaseHas('Evento', [
            'id' => $event->id,
            'nombre' => 'Evento Actualizado'
        ]);
    }

    /** @test */
    public function test_can_delete_event()
    {
        $event = EventModel::factory()->create();
        
        $response = $this->deleteJson("/api/events/{$event->id}");
        
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Event deleted successfully']);
        
        $this->assertDatabaseMissing('Evento', ['id' => $event->id]);
    }
}