<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Factories\UserFactory;
use Tests\TestCase;

class UserFactoryTest extends TestCase
{
    public function test_user_model_exposes_its_factory(): void
    {
        $this->assertTrue(method_exists(User::class, 'factory'));
    }

    public function test_factory_uses_the_custom_user_name_attribute(): void
    {
        $user = (new User)->fill((new UserFactory)->definition());

        $this->assertNotEmpty($user->nome);
    }
}
