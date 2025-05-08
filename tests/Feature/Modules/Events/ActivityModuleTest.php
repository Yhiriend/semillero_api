<?php

namespace Tests\Modules\Events;

use App\Modules\Activities\Models\ActivityModel;
use App\Modules\Events\Models\EventModel;
use App\Modules\Users\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityModuleTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = UserModel::factory()->create();
        $this->event = EventModel::factory()->create();
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function test_can_list_activities_for_an_event()
    {
        ActivityModel::factory()->count(2)->create([
            'evento_id' => $this->event->id
        ]);

        $response = $this->getJson("/api/events/{$this->event->id}/activities");
        
        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonStructure([
                     '*' => ['id', 'titulo', 'evento_id', 'estado']
                 ]);
    }

    /** @test */
    public function test_can_retrieve_single_activity_for_event()
    {
        $activity = ActivityModel::factory()->create([
            'evento_id' => $this->event->id,
            'titulo' => 'Actividad Especial'
        ]);

        $response = $this->getJson("/api/events/{$this->event->id}/activities/{$activity->id}");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $activity->id,
                     'titulo' => 'Actividad Especial'
                 ]);
    }

    /** @test */
    public function test_can_create_activity_within_event()
    {
        $payload = [
            'titulo' => 'Nueva Actividad',
            'descripcion' => 'Descripción de la actividad',
            'semillero_id' => 1,
            'proyecto_id' => 1,
            'fecha_inicio' => '2024-01-01 10:00:00',
            'fecha_fin' => '2024-01-01 12:00:00',
            'estado' => 'pendiente'
        ];

        $response = $this->postJson("/api/events/{$this->event->id}/activities", $payload);
        
        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'titulo', 'evento_id'])
                 ->assertJson(['titulo' => 'Nueva Actividad']);
        
        $this->assertDatabaseHas('Actividad', [
            'titulo' => 'Nueva Actividad',
            'evento_id' => $this->event->id
        ]);
    }

    /** @test */
    public function test_can_update_activity_details()
    {
        $activity = ActivityModel::factory()->create(['evento_id' => $this->event->id]);
        
        $updateData = [
            'titulo' => 'Actividad Actualizada',
            'estado' => 'completado'
        ];

        $response = $this->putJson("/api/events/{$this->event->id}/activities/{$activity->id}", $updateData);
        
        $response->assertStatus(200)
                 ->assertJson(['titulo' => 'Actividad Actualizada']);
        
        $this->assertDatabaseHas('Actividad', [
            'id' => $activity->id,
            'titulo' => 'Actividad Actualizada',
            'estado' => 'completado'
        ]);
    }

    /** @test */
    public function test_can_remove_activity_from_event()
    {
        $activity = ActivityModel::factory()->create(['evento_id' => $this->event->id]);
        
        $response = $this->deleteJson("/api/events/{$this->event->id}/activities/{$activity->id}");
        
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Activity deleted successfully']);
        
        $this->assertDatabaseMissing('Actividad', ['id' => $activity->id]);
    }
}