<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdmissionPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPeriodCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_period_card_shows_the_real_active_period(): void
    {
        AdmissionPeriod::factory()->active()->create([
            'name' => 'Periode Uji',
            'academic_year' => '2031/2032',
            'start_date' => '2031-01-10 00:00:00',
            'end_date' => '2031-02-15 23:59:59',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Periode Uji')
            ->assertSee('2031/2032')
            ->assertDontSee('Aisyah Humaira Putri');
    }

    public function test_period_card_has_an_honest_empty_state(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Belum ada periode aktif')
            ->assertDontSee('Aisyah Humaira Putri');
    }
}
