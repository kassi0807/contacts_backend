<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $photo =  UploadedFile::fake()->image('photo.jpg')->size(500);
        $photo->storeAs('public/photos', $photo->hashName());

        return [
            'name' => fake()->name($gender = 'male'|'female'),
            'company' => fake()->company(),
            'photo' => $photo->hashName(),
            'address' => fake()->address(),
            'email' => fake()->email(),
            'phone' => fake()->phoneNumber()
        ];
    }
}
