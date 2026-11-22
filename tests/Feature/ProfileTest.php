<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_admin_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Admin Baru',
                'email' => 'adminbaru@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Admin Baru', $user->name);
        $this->assertSame('adminbaru@example.com', $user->email);
    }

    public function test_kasir_can_update_profile_without_email(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this
            ->actingAs($kasir)
            ->patch('/profile', [
                'name' => 'Nama Kasir Baru',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $kasir->refresh();

        $this->assertSame('Nama Kasir Baru', $kasir->name);
    }

    public function test_admin_can_delete_their_account(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_kasir_cannot_delete_their_account(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this
            ->actingAs($kasir)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response->assertStatus(403);
        $this->assertNotNull($kasir->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
