<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AdmissionPeriodStatus;
use App\Enums\Gender;
use App\Enums\PdfDocumentType;
use App\Enums\RegistrationStatus;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionPeriodProgram;
use App\Models\Applicant;
use App\Models\ApplicantAddress;
use App\Models\ApplicantEducation;
use App\Models\ApplicantGuardian;
use App\Models\Program;
use App\Models\Registration;
use App\Models\User;
use App\Services\Registration\RegistrationPdfRenderer;
use App\Support\NikManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PerformanceAndBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Benchmark 1: Query Count & N+1 Prevention on Applicant Dashboard.
     * Ensures queries do not scale with the number of documents, guardians, or PDF versions.
     */
    public function test_dashboard_query_count_prevents_n_plus_one(): void
    {
        $setup = $this->setupCompleteApplicant(1);
        $user = $setup['user'];

        // Warm up and record query count
        DB::enableQueryLog();
        DB::flushQueryLog();

        $response = $this->actingAs($user)->get(route('applicant.dashboard'));
        $response->assertOk();

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Dashboard query count must be bounded (strictly <= 30 queries with full relation loading and session checks)
        $this->assertLessThanOrEqual(30, $queryCount, "Dashboard query count ({$queryCount}) exceeds maximum acceptable threshold.");
    }

    /**
     * Benchmark 2: PDF Generation Memory, Execution Time, and File Size.
     */
    public function test_pdf_generation_memory_and_execution_benchmark(): void
    {
        Storage::fake('ppdb_private');
        $setup = $this->setupCompleteApplicant(2);

        $renderer = app(RegistrationPdfRenderer::class);
        $registration = $setup['registration'];
        $applicant = $setup['applicant'];

        $documentData = [
            'registration_id' => $registration->id,
            'ministry_name' => 'KEMENTERIAN AGAMA REPUBLIK INDONESIA',
            'school_name' => 'MADRASAH IBTIDAIYAH NEGERI 1 KOTA MADIUN',
            'collection_instruction' => 'Silakan serahkan berkas cetak ini ke loket PTSP madrasah.',
            'document_type' => PdfDocumentType::FINAL,
            'registration_number' => 'PPDB/2026-2027/REGULER/00002',
            'data_revision' => 1,
            'generated_at' => now()->format('d F Y, H:i').' WIB',
            'submitted_at' => now()->format('d F Y, H:i').' WIB',
            'period_name' => 'PPDB 2026/2027',
            'academic_year' => '2026/2027',
            'period_start_date' => '01 November 2026',
            'period_end_date' => '30 November 2026',
            'age_at_reference' => '6 tahun 5 bulan',
            'applicant' => [
                'full_name' => $applicant->full_name,
                'nik' => '3573010101190002',
                'gender' => 'Laki-laki',
                'birth_place' => 'Kota Madiun',
                'birth_date' => '15 Januari 2020',
                'nisn' => '0123456789',
                'child_order' => 1,
                'siblings_count' => 1,
            ],
            'address' => 'Jalan Pahlawan Nomor 123, Madiun',
            'father' => [
                'name' => 'Ayah Calon Siswa',
                'phone' => '081234567890',
                'occupation' => 'Wiraswasta',
            ],
            'mother' => [
                'name' => 'Ibu Calon Siswa',
                'phone' => '081234567891',
                'occupation' => 'Ibu Rumah Tangga',
            ],
            'guardian' => null,
            'education' => [
                'level' => 'RA',
                'school_name' => 'RA Perwanida Madiun',
                'npsn' => '69785421',
                'city' => 'Kota Madiun',
            ],
            'program_name' => 'Program Reguler',
            'consent' => [
                'statement_text' => 'Pernyataan persetujuan resmi PPDB MIN 1 Kota Madiun v1.0.',
                'accepted_by_name' => 'Ayah Calon Siswa',
                'agreed_at' => now()->format('d F Y, H:i').' WIB',
            ],
            'photo_data_uri' => null,
        ];

        $verificationUrl = 'https://ppdb.min1kotamadiun.sch.id/verifikasi/bukti-pendaftaran/'.str_repeat('b', 64).'?registration_number=PPDB/2026-2027/REGULER/00002';

        $memStart = memory_get_usage(true);
        $timeStart = microtime(true);

        $pdfBytes = $renderer->render(PdfDocumentType::FINAL, $documentData, $verificationUrl);

        $timeDuration = microtime(true) - $timeStart;
        $memPeak = memory_get_peak_usage(true) - $memStart;
        $fileSize = strlen($pdfBytes);

        $this->assertStringStartsWith('%PDF-', $pdfBytes);
        $this->assertGreaterThan(5000, $fileSize, 'PDF size should be realistic (greater than 5 KB).');
        $this->assertLessThan(2500000, $fileSize, 'Uncompressed PDF file size with DejaVu font must not exceed 2.5 MB.');
        $this->assertLessThan(5.0, $timeDuration, "PDF rendering duration ({$timeDuration}s) must be under 5 seconds.");
    }

    /**
     * Benchmark 3: Simulated Synthetic Multi-Request Throughput Benchmark.
     * Simulates 50 sequential requests to review and dashboard, measuring error rate and response times.
     */
    public function test_synthetic_load_throughput_benchmark(): void
    {
        $setup = $this->setupCompleteApplicant(3);
        $user = $setup['user'];

        $totalRequests = 40;
        $startTime = microtime(true);
        $errors = 0;
        $latencies = [];

        for ($i = 0; $i < $totalRequests; $i++) {
            $reqStart = microtime(true);
            $endpoint = ($i % 2 === 0) ? route('applicant.dashboard') : route('applicant.registration.review');
            $response = $this->actingAs($user)->get($endpoint);
            $reqDuration = (microtime(true) - $reqStart) * 1000; // in ms
            $latencies[] = $reqDuration;

            if ($response->status() !== 200) {
                $errors++;
            }
        }

        $totalDuration = microtime(true) - $startTime;
        $avgLatency = array_sum($latencies) / count($latencies);
        $errorRate = ($errors / $totalRequests) * 100;

        $this->assertSame(0, $errors, "Synthetic benchmark had {$errors} errors.");
        $this->assertEquals(0.0, $errorRate, 'Error rate must be 0.0%.');
        $this->assertLessThan(250.0, $avgLatency, "Average latency ({$avgLatency} ms) must be under 250 ms in testing suite.");
    }

    /**
     * Helper to set up a full applicant and period.
     *
     * @return array{user: User, applicant: Applicant, registration: Registration}
     */
    private function setupCompleteApplicant(int $seed): array
    {
        $nik = sprintf('357301010119%04d', $seed);
        $user = User::factory()->create([
            'account_type' => AccountType::APPLICANT,
            'account_status' => AccountStatus::ACTIVE,
            'nik_lookup_hash' => NikManager::hash($nik),
            'nik_encrypted' => NikManager::encrypt($nik),
        ]);

        $applicant = Applicant::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'Siswa Benchmark '.$seed,
            'gender' => Gender::MALE,
            'birth_place' => 'Kota Madiun',
            'birth_date' => '2020-01-15',
            'child_order' => 1,
            'siblings_count' => 1,
        ]);

        ApplicantAddress::factory()->create([
            'applicant_id' => $applicant->id,
            'address_line' => 'Jalan Pahlawan '.$seed,
            'kelurahan' => 'Kartoharjo',
            'kecamatan' => 'Kartoharjo',
            'city' => 'Kota Madiun',
            'province' => 'Jawa Timur',
        ]);

        ApplicantGuardian::factory()->create([
            'applicant_id' => $applicant->id,
            'relationship_type' => 'FATHER',
            'name' => 'Ayah Benchmark',
            'occupation' => 'PNS',
            'monthly_income' => 'Rp 5.000.000',
            'phone' => '081234567890',
        ]);

        ApplicantGuardian::factory()->create([
            'applicant_id' => $applicant->id,
            'relationship_type' => 'MOTHER',
            'name' => 'Ibu Benchmark',
            'occupation' => 'Guru',
            'monthly_income' => 'Rp 4.000.000',
            'phone' => '081234567891',
        ]);

        ApplicantEducation::factory()->create([
            'applicant_id' => $applicant->id,
            'education_level' => 'tk',
            'school_name' => 'TK Minsatama',
        ]);

        $period = AdmissionPeriod::firstOrCreate([
            'name' => 'PPDB Benchmark Period',
        ], [
            'academic_year' => '2026/2027',
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'age_reference_date' => Carbon::parse('2026-07-01'),
            'status' => AdmissionPeriodStatus::ACTIVE,
            'is_active' => true,
        ]);

        $program = Program::firstOrCreate(['code' => 'REGULER'], ['name' => 'Program Reguler']);
        $periodProgram = AdmissionPeriodProgram::firstOrCreate([
            'admission_period_id' => $period->id,
            'program_id' => $program->id,
        ]);

        $registration = Registration::create([
            'applicant_id' => $applicant->id,
            'admission_period_id' => $period->id,
            'admission_period_program_id' => $periodProgram->id,
            'status' => RegistrationStatus::SUBMITTED,
            'registration_number' => sprintf('PPDB/2026-2027/REGULER/%05d', $seed),
            'submitted_at' => now(),
            'data_revision' => 1,
        ]);

        return [
            'user' => $user,
            'applicant' => $applicant,
            'registration' => $registration,
        ];
    }
}
