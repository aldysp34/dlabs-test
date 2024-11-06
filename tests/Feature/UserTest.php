<?

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();  // Menggunakan factory untuk membuat pengguna
        $this->token = JWTAuth::fromUser($this->user);  // Membuat JWT token untuk user
    }

    /** @test */
    public function it_can_fetch_users()
    {
        $response = $this->getJson('/api/users', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'email', 'age', 'membership_status']
                     ]
                 ]);
    }

    /** @test */
    public function it_can_create_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'age' => 30,
            'membership_status' => 'active',
        ];

        $response = $this->postJson('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'message' => 'User created successfully'
                ]);
    }

    /** @test */
    public function it_can_update_user()
    {
        $userToUpdate = User::factory()->create();

        $userData = [
            'name' => 'Updated Name',
            'email' => 'updatedemail@example.com',
            'age' => 35,
            'membership_status' => 'inactive',
        ];

        $response = $this->putJson("/api/users/{$userToUpdate->id}", $userData, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'User updated successfully'
                ]);
    }

    /** @test */
    public function it_can_delete_user()
    {
        $userToDelete = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$userToDelete->id}", [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'User deleted successfully'
                ]);
    }

    /** @test */
    public function it_can_login_and_get_token()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure(['token']);
    }

    /** @test */
    public function it_returns_error_if_login_fails()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'error' => 'Unauthorized'
                ]);
    }

    /** @test */
    public function it_validates_required_fields_for_create_user()
    {
        $response = $this->postJson('/api/users', [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password', 'age', 'membership_status']);
    }



}
