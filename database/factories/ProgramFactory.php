<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'code' => 'REGULER_'.fake()->unique()->lexify('???'),
            'name' => 'Program '.fake()->word(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function unggulan(): static
    {
        return $this->state(fn () => [
            'code' => 'UNGGULAN',
            'name' => 'Program Unggulan',
            'description' => 'Program pengembangan tahfidz intensif dan penguatan bahasa asing.',
            'is_active' => true,
        ]);
    }

    public function reguler(): static
    {
        return $this->state(fn () => [
            'code' => 'REGULER',
            'name' => 'Program Reguler',
            'description' => 'Program pendidikan dasar MI terintegrasi nilai-nilai keislaman.',
            'is_active' => true,
        ]);
    }
}
