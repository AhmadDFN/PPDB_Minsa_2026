<?php

declare(strict_types=1);

namespace App\Http\Controllers\Applicant;

use App\Actions\Registration\GenerateRegistrationPdfAction;
use App\Enums\PdfDocumentType;
use App\Exceptions\RegistrationPdfGenerationException;
use App\Http\Controllers\Controller;
use App\Models\RegistrationPdfVersion;
use App\Services\Registration\RegistrationEditService;
use App\Services\Registration\RegistrationPdfFileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class RegistrationPdfController extends Controller
{
    public function __construct(
        private RegistrationEditService $registrationEditService,
        private RegistrationPdfFileService $fileService
    ) {}

    public function storeDraft(Request $request, GenerateRegistrationPdfAction $action): RedirectResponse
    {
        return $this->generate($request, $action, PdfDocumentType::DRAFT);
    }

    public function storeFinal(Request $request, GenerateRegistrationPdfAction $action): RedirectResponse
    {
        return $this->generate($request, $action, PdfDocumentType::FINAL);
    }

    public function download(Request $request, RegistrationPdfVersion $pdfVersion): Response
    {
        $applicant = $request->user()->applicant()->firstOrFail();
        $ownedVersion = RegistrationPdfVersion::query()
            ->whereKey($pdfVersion->id)
            ->whereHas('registration', fn ($query) => $query->where('applicant_id', $applicant->id))
            ->with('registration')
            ->firstOrFail();

        $contents = $this->fileService->readVerified($ownedVersion);
        if ($contents === null) {
            report(new RuntimeException('A registration PDF could not pass its integrity check.'));
            abort(404);
        }

        $filenamePrefix = $ownedVersion->document_type === PdfDocumentType::FINAL
            ? 'Bukti-Pendaftaran'
            : 'Draf-Pendaftaran';
        $filename = $filenamePrefix.'-v'.$ownedVersion->version_number.'.pdf';

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($contents),
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function generate(
        Request $request,
        GenerateRegistrationPdfAction $action,
        PdfDocumentType $type
    ): RedirectResponse {
        $applicant = $request->user()->applicant()->firstOrFail();
        $registration = $this->registrationEditService->registrationForApplicant($applicant);

        if ($registration === null) {
            return back()->with('error', 'Pilih program pada periode pendaftaran sebelum membuat PDF draf.');
        }

        try {
            $action->execute($registration, $request->user(), $type);
        } catch (ValidationException $exception) {
            $messages = $exception->errors()['pdf'] ?? [];

            return back()->with('error', $messages[0] ?? 'Pendaftaran belum memenuhi syarat untuk membuat PDF.');
        } catch (RegistrationPdfGenerationException $exception) {
            report($exception);

            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('applicant.dashboard')
            ->with('success', $type === PdfDocumentType::DRAFT
                ? 'PDF draf berhasil dibuat.'
                : 'Bukti PDF final berhasil dibuat.');
    }
}
