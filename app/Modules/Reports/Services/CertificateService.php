<?php

namespace App\Modules\Reports\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Log;

class CertificateService
{
    /**
     * Generate a participation certificate
     *
     * @param array $data
     * @return string
     * @throws Exception
     */
    public function generateParticipationCertificate(array $data)
    {
        try {
            // Valores por defecto
            $defaults = [
                'autor'      => 'Nombre del estudiante',
                'documento'  => '00000000',
                'expedida'   => 'Ciudad',
                'fecha'      => Carbon::now()->locale('es')->isoFormat('LL'),
                'codigo'     => strtoupper(substr(md5(uniqid(rand(), true)), 0, 10)),
                'semillero'  => 'Nombre del Semillero',
                'proyecto'   => 'Nombre del Proyecto'
            ];
            $data = array_merge($defaults, $data);

            // Generar el PDF
            $pdf = PDF::loadView('certificates.participation', $data);
            $pdf->setPaper('letter', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'defaultFont' => 'sans-serif'
            ]);

            // Guardar el PDF
            $fileName = 'certificado_' . time() . '.pdf';
            Storage::disk('local')->put('certificates/' . $fileName, $pdf->output());

            return $pdf;
        } catch (Exception $e) {
            Log::error('Error generando certificado: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
} 