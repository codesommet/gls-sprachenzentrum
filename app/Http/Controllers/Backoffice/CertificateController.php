<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Certificates\StoreCertificateRequest;
use App\Http\Requests\Backoffice\Certificates\UpdateCertificateRequest;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificateController extends Controller
{
    /**
     * Score configs per certificate type.
     */
    private const SCORE_CONFIGS = [
        'b2' => [
            'reading'        => 75,
            'grammar'        => 30,
            'listening'      => 75,
            'writing'        => 45,
            'presentation'   => 25,
            'discussion'     => 25,
            'problemsolving' => 25,
            'written_max'    => 225,
            'oral_max'       => 75,
        ],
        'a2' => [
            'reading'   => 25,  // Lesen
            'listening' => 25,  // Hören
            'writing'   => 25,  // Schreiben
            'speaking'  => 25,  // Sprechen
        ],
    ];

    /**
     * INDEX
     */
    public function index()
    {
        $certificates = Certificate::latest()->get();

        return view('backoffice.certificates.index', compact('certificates'));
    }

    /**
     * CREATE VIEW
     */
    public function create()
    {
        return view('backoffice.certificates.create', [
            'scoreConfigs' => self::SCORE_CONFIGS,
        ]);
    }

    /**
     * STORE CERTIFICATE
     */
    public function store(StoreCertificateRequest $request)
    {
        $data = $this->hydrateScores($request->validated());

        Certificate::create($data);

        return redirect()->route('backoffice.certificates.index')->with('success', 'Certificat ajouté avec succès.');
    }

    /**
     * SHOW
     */
    public function show(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        return view('backoffice.certificates.show', compact('certificate'));
    }

    /**
     * EDIT
     */
    public function edit(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        return view('backoffice.certificates.edit', [
            'certificate' => $certificate,
            'scoreConfigs' => self::SCORE_CONFIGS,
        ]);
    }

    /**
     * UPDATE CERTIFICATE
     */
    public function update(UpdateCertificateRequest $request, string $id)
    {
        $certificate = Certificate::findOrFail($id);

        $data = $this->hydrateScores($request->validated());

        $certificate->update($data);

        return redirect()->route('backoffice.certificates.index')->with('success', 'Certificat mis à jour avec succès.');
    }

    /**
     * DELETE CERTIFICATE
     */
    public function destroy(string $id)
    {
        Certificate::findOrFail($id)->delete();

        return redirect()->route('backoffice.certificates.index')->with('success', 'Certificat supprimé avec succès.');
    }

    /**
     * PDF EXPORT + QR CODE
     */
    public function pdf(string $id)
    {
        $certificate = Certificate::findOrFail($id);

        $url = route('certificates.public.download', [
            'token' => $certificate->public_token,
        ]);

        $qrSvg = QrCode::format('svg')
            ->size(180)
            ->margin(1)
            ->generate($url);

        $qrCodeBase64 = base64_encode($qrSvg);

        $pdf = Pdf::loadView('backoffice.certificates.pdf', [
                'certificate' => $certificate,
                'qrCodeBase64' => $qrCodeBase64,
                'qrUrl' => $url,
            ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download('certificate-' . $certificate->certificate_number . '.pdf');
    }

    /**
     * BULK PDF EXPORT (ID range → single PDF, one certificate per page)
     */
    public function exportBulkPdf(Request $request)
    {
        $request->validate([
            'from_id' => 'required|integer|min:1',
            'to_id'   => 'required|integer|min:1|gte:from_id',
        ]);

        $certificates = Certificate::whereBetween('id', [$request->from_id, $request->to_id])
            ->orderBy('id')
            ->get();

        if ($certificates->isEmpty()) {
            return redirect()->route('backoffice.certificates.index')
                ->with('error', 'Aucun certificat trouvé dans cette plage d\'IDs.');
        }

        $certificatesData = $certificates->map(function ($certificate) {
            $url = route('certificates.public.download', [
                'token' => $certificate->public_token,
            ]);

            $qrSvg = QrCode::format('svg')
                ->size(180)
                ->margin(1)
                ->generate($url);

            return [
                'certificate'   => $certificate,
                'qrCodeBase64'  => base64_encode($qrSvg),
                'qrUrl'         => $url,
            ];
        });

        $pdf = Pdf::loadView('backoffice.certificates.pdf-bulk', [
                'certificatesData' => $certificatesData,
            ])
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $filename = 'certificates-' . $request->from_id . '-to-' . $request->to_id . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * ---------------------------------------------------------
     *   SCORE NORMALIZATION — type-aware (A2 vs B2)
     * ---------------------------------------------------------
     */
    private function hydrateScores(array $data): array
    {
        $type = $data['certificate_type'] ?? 'b2';
        $config = self::SCORE_CONFIGS[$type] ?? self::SCORE_CONFIGS['b2'];

        if ($type === 'a2') {
            return $this->hydrateA2($data, $config);
        }

        return $this->hydrateB2($data, $config);
    }

    private function hydrateA2(array $data, array $config): array
    {
        $data['reading_max'] = $config['reading'];
        $data['listening_max'] = $config['listening'];
        $data['writing_max'] = $config['writing'];
        $data['speaking_max'] = $config['speaking'];

        $data['reading_score'] = min($data['reading_score'], $config['reading']);
        $data['listening_score'] = min($data['listening_score'], $config['listening']);
        $data['writing_score'] = min($data['writing_score'], $config['writing']);
        $data['speaking_score'] = min($data['speaking_score'], $config['speaking']);

        // Nullify B2-only fields
        $data['grammar_score'] = null;
        $data['grammar_max'] = null;
        $data['presentation_score'] = null;
        $data['presentation_max'] = null;
        $data['discussion_score'] = null;
        $data['discussion_max'] = null;
        $data['problemsolving_score'] = null;
        $data['problemsolving_max'] = null;
        $data['written_total'] = null;
        $data['written_max'] = null;
        $data['oral_total'] = null;
        $data['oral_max'] = null;

        return $data;
    }

    private function hydrateB2(array $data, array $config): array
    {
        $data['reading_max'] = $config['reading'];
        $data['grammar_max'] = $config['grammar'];
        $data['listening_max'] = $config['listening'];
        $data['writing_max'] = $config['writing'];

        $data['presentation_max'] = $config['presentation'];
        $data['discussion_max'] = $config['discussion'];
        $data['problemsolving_max'] = $config['problemsolving'];

        $data['written_max'] = $config['written_max'];
        $data['oral_max'] = $config['oral_max'];

        $data['reading_score'] = min($data['reading_score'], $config['reading']);
        $data['grammar_score'] = min($data['grammar_score'], $config['grammar']);
        $data['listening_score'] = min($data['listening_score'], $config['listening']);
        $data['writing_score'] = min($data['writing_score'], $config['writing']);

        $data['presentation_score'] = min($data['presentation_score'], $config['presentation']);
        $data['discussion_score'] = min($data['discussion_score'], $config['discussion']);
        $data['problemsolving_score'] = min($data['problemsolving_score'], $config['problemsolving']);

        $data['written_total'] = $data['reading_score'] + $data['grammar_score']
                               + $data['listening_score'] + $data['writing_score'];

        $data['oral_total'] = $data['presentation_score'] + $data['discussion_score']
                            + $data['problemsolving_score'];

        // Nullify A2-only fields
        $data['speaking_score'] = null;
        $data['speaking_max'] = null;

        return $data;
    }
}
