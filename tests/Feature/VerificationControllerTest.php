<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class VerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        // Mock the user authentication
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_invalid_recipient()
    {
        $file = $this->getFile('invalid_recipient_name.oa');
        $response = $this->postJson('/api/verify', ['json_file' => $file]);
        $response->assertOk()->assertJson(['issuer' => 'Accredify', 'result' => 'invalid_recipient']);

        $file = $this->getFile('invalid_recipient_email.oa');
        $response = $this->postJson('/api/verify', ['json_file' => $file]);
        $response->assertOk()->assertJson(['issuer' => 'Accredify', 'result' => 'invalid_recipient']);

        $this->assertDatabaseHas('verification_results', [
            'user_id' => $this->user->id,
            'file_type' => 'JSON',
            'verification_result' => 'invalid_recipient'
        ]);
    }

    public function test_invalid_issuer()
    {
        $file = $this->getFile('invalid_issuer_name.oa');
        $response = $this->postJson('/api/verify', ['json_file' => $file]);
        $response->assertOk()->assertJson(['issuer' => '', 'result' => 'invalid_issuer']);

        $file = $this->getFile('invalid_issuer_key.oa');
        $response = $this->postJson('/api/verify', ['json_file' => $file]);
        $response->assertOk()->assertJson(['issuer' => 'Accredify', 'result' => 'invalid_issuer']);

        $file = $this->getFile('invalid_issuer_location.oa');
        $response = $this->postJson('/api/verify', ['json_file' => $file]);
        $response->assertOk()->assertJson(['issuer' => 'Accredify', 'result' => 'invalid_issuer']);

        $this->assertDatabaseHas('verification_results', [
            'user_id' => $this->user->id,
            'file_type' => 'JSON',
            'verification_result' => 'invalid_issuer'
        ]);
    }

    public function test_invalid_signature()
    {
        $file = $this->getFile('invalid_signature_no_hash.oa');
        $response = $this->postJson('/api/verify', ['json_file' => $file]);
        $response->assertOk()->assertJson(['issuer' => 'Accredify', 'result' => 'invalid_signature']);

        $file = $this->getFile('invalid_signature.oa');
        $response = $this->postJson('/api/verify', ['json_file' => $file]);
        $response->assertOk()->assertJson(['issuer' => 'Accredify', 'result' => 'invalid_signature']);

        $this->assertDatabaseHas('verification_results', [
            'user_id' => $this->user->id,
            'file_type' => 'JSON',
            'verification_result' => 'invalid_signature'
        ]);
    }

    public function test_verified()
    {
        $file = $this->getFile('verified.oa');
        $response = $this->postJson('/api/verify', ['json_file' => $file]);
        $response->assertOk()->assertJson(['issuer' => 'Accredify', 'result' => 'verified']);

        // Assert the database has the record
        $this->assertDatabaseHas('verification_results', [
            'user_id' => $this->user->id,
            'file_type' => 'JSON',
            'verification_result' => 'verified'
        ]);
    }

    public function test_get_verification_results()
    {
        // Make a GET request to the endpoint
        $response = $this->getJson('/api/results');

        // Assertions
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'user_id', 'file_type', 'verification_result', 'created_at', 'updated_at']
        ]);
    }

    private function getFile($name): UploadedFile
    {
        $pathToFile = base_path('tests/test_files/'.$name);

        return new UploadedFile(
            $pathToFile,
            $name,
            'application/json',
            null,
            true
        );
    }
}
