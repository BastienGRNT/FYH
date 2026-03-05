<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    public function generate(string $html, string $filename): void
    {
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream($filename . '.pdf', [
            'Attachment' => true
        ]);
    }
}