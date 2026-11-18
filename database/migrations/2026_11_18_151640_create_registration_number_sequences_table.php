<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registration_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 15);
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->unsignedBigInteger('last_sequence')->default(0);
            $table->timestamps();

            $table->unique(['academic_year', 'program_id'], 'reg_number_seq_scope_unique');
        });

        $existingSequences = DB::table('registration_sequences')
            ->join(
                'admission_period_programs',
                'registration_sequences.admission_period_program_id',
                '=',
                'admission_period_programs.id',
            )
            ->join('admission_periods', 'admission_period_programs.admission_period_id', '=', 'admission_periods.id')
            ->select('admission_periods.academic_year', 'admission_period_programs.program_id')
            ->selectRaw('MAX(registration_sequences.last_sequence) AS last_sequence')
            ->groupBy('admission_periods.academic_year', 'admission_period_programs.program_id')
            ->get();

        /** @var array<string, array{academic_year: string, program_id: int, last_sequence: int}> $sequenceCounters */
        $sequenceCounters = [];
        foreach ($existingSequences as $existingSequence) {
            $academicYear = str_replace('/', '-', (string) $existingSequence->academic_year);
            $programId = (int) $existingSequence->program_id;
            $scopeKey = $academicYear.':'.$programId;

            if (! isset($sequenceCounters[$scopeKey])) {
                $sequenceCounters[$scopeKey] = [
                    'academic_year' => $academicYear,
                    'program_id' => $programId,
                    'last_sequence' => (int) $existingSequence->last_sequence,
                ];

                continue;
            }

            $sequenceCounters[$scopeKey]['last_sequence'] = max(
                $sequenceCounters[$scopeKey]['last_sequence'],
                (int) $existingSequence->last_sequence,
            );
        }

        foreach ($sequenceCounters as $sequenceCounter) {
            DB::table('registration_number_sequences')->insert([
                'academic_year' => $sequenceCounter['academic_year'],
                'program_id' => $sequenceCounter['program_id'],
                'last_sequence' => $sequenceCounter['last_sequence'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_number_sequences');
    }
};
