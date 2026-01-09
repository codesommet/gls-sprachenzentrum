<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificatePublicController extends Controller
{
    public function download(string $token)
    {
        $certificate = Certificate::where('public_token', $token)->firstOrFail();

        $pdf = Pdf::loadView('backoffice.certificates.pdf', compact('certificate'))
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download('certificate-' . $certificate->certificate_number . '.pdf');
    }
}
