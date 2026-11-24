<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Collection;

/** A4 sheet of QR labels, each linking to its plant page (proposal 08: QR code labels). */
class QrLabels
{
    public static function pdf(Collection $plants): string
    {
        $qr = new QRCode(new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'scale' => 8,
            'outputBase64' => true,
            'quietzoneSize' => 1,
        ]));

        $labels = $plants->map(fn ($p) => [
            'name' => $p->name,
            'botanical' => $p->botanical_name,
            'price' => ksh($p->price),
            'url' => $url = route('plants.show', $p),
            'qr' => $qr->render($url),
        ]);

        return Pdf::loadView('pdf.qr-labels', ['labels' => $labels])->setPaper('a4')->output();
    }
}
