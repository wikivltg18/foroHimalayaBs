<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\UserGoogleAccount;
use App\Services\GoogleCalendarService;
use Mockery;

class GoogleCalendarIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_calendars_requires_authentication()
    {
        $response = $this->getJson('/google/calendars');
        $response->assertStatus(302); // Redirect to login
    }

    public function test_redirect_to_google_oauth()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/google/redirect');
        
        $response->assertStatus(302);
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    public function test_list_calendars_shows_view_when_account_connected()
    {
        $user = User::factory()->create();
        
        // Create a mock Google account
        UserGoogleAccount::create([
            'user_id' => $user->id,
            'google_user_id' => 'google-123',
            'email' => 'test@gmail.com',
            'access_token' => json_encode(['access_token' => 'token', 'expires_in' => 3600]),
            'refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addHour(),
        ]);

        // Mock GoogleCalendarService
        $this->mock(GoogleCalendarService::class, function ($mock) {
            $mock->shouldReceive('listCalendars')
                ->once()
                ->andReturn([
                    [
                        'id' => 'primary',
                        'summary' => 'My Calendar',
                        'description' => null,
                        'primary' => true,
                        'accessRole' => 'owner',
                    ]
                ]);
        });

        $response = $this->actingAs($user)->get('/google/calendars');
        
        $response->assertStatus(200);
        $response->assertViewIs('google.calendars');
        $response->assertViewHas('calendars');
    }

    public function test_set_default_calendar()
    {
        $user = User::factory()->create();
        
        $account = UserGoogleAccount::create([
            'user_id' => $user->id,
            'google_user_id' => 'google-123',
            'email' => 'test@gmail.com',
            'access_token' => json_encode(['access_token' => 'token']),
            'refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($user)->post('/google/calendars', [
            'calendar_id' => 'work-calendar-id'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('user_google_accounts', [
            'user_id' => $user->id,
            'calendar_id' => 'work-calendar-id',
        ]);
    }

    public function test_requires_calendar_id_to_set_default()
    {
        $user = User::factory()->create();
        
        UserGoogleAccount::create([
            'user_id' => $user->id,
            'google_user_id' => 'google-123',
            'email' => 'test@gmail.com',
            'access_token' => json_encode(['access_token' => 'token']),
            'refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($user)->post('/google/calendars', []);

        $response->assertSessionHasErrors('calendar_id');
    }
}
