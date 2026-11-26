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

        $mark = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/logo-mark.png')));

        return Pdf::loadView('pdf.qr-labels', ['labels' => $labels, 'mark' => $mark])->setPaper('a4')->output();
    }
}
