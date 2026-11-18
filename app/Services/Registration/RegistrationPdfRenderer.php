<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Enums\PdfDocumentType;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;

class RegistrationPdfRenderer
{
    /**
     * @param  array<string, mixed>  $documentData
     */
    public function render(
        PdfDocumentType $type,
        array $documentData,
        ?string $verificationUrl = null
    ): string {
        $qrCodeDataUri = $verificationUrl === null
            ? null
            : 'data:image/svg+xml;base64,'.base64_encode(
                (new Writer(new ImageRenderer(new RendererStyle(160, 2), new SvgImageBackEnd)))
                    ->writeString($verificationUrl)
            );

        $view = match ($type) {
            PdfDocumentType::DRAFT => 'pdf.registration.draft',
            PdfDocumentType::FINAL => 'pdf.registration.final',
        };

        $pdf = Pdf::loadView($view, [
            'document' => $documentData,
            'qrCodeDataUri' => $qrCodeDataUri,
            'verificationUrl' => $verificationUrl,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', false)
            ->setOption('chroot', storage_path('app/private'));

        $pdf->addInfo([
            'Title' => $type === PdfDocumentType::DRAFT
                ? 'Draf Pendaftaran MIN 1 Kota Madiun'
                : 'Bukti Pendaftaran MIN 1 Kota Madiun',
            'Author' => config('ppdb.pdf.school_name'),
            'Subject' => $type->label(),
        ]);
        $pdf->render();

        $canvas = $pdf->getCanvas();
        $font = $pdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $canvas->page_text(460, 820, 'Halaman {PAGE_NUM} dari {PAGE_COUNT}', $font, 8, [0.25, 0.25, 0.25]);

        if ($type === PdfDocumentType::DRAFT) {
            $canvas->page_text(78, 440, 'DRAFT - BUKAN BUKTI RESMI', $font, 34, [0.82, 0.82, 0.82], 0, 0, 45);
        }

        return $pdf->output(['compress' => 0]);
    }
}
