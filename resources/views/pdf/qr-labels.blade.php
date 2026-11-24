<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 12mm; }
    body { font-family: DejaVu Sans, sans-serif; color: #14211A; }
    table { width: 100%; border-collapse: separate; border-spacing: 4mm; }
    td { width: 33%; border: 0.3mm dashed #4F8A4B; border-radius: 3mm; padding: 4mm; text-align: center; vertical-align: top; height: 62mm; }
    img { width: 36mm; height: 36mm; }
    .name { font-size: 12pt; font-weight: bold; margin-top: 2mm; }
    .bot { font-size: 8pt; font-style: italic; color: #4F8A4B; }
    .price { font-size: 11pt; margin-top: 1mm; }
    .brand { font-size: 7pt; letter-spacing: 1pt; color: #2D6A3E; margin-top: 1mm; }
</style>
</head>
<body>
<table>
    @foreach ($labels->chunk(3) as $row)
        <tr>
            @foreach ($row as $l)
                <td>
                    <img src="{{ $l['qr'] }}" alt="">
                    <div class="name">{{ $l['name'] }}</div>
                    @if ($l['botanical'])<div class="bot">{{ $l['botanical'] }}</div>@endif
                    <div class="price">{{ $l['price'] }}</div>
                    <div class="brand">WISDOM NURSERY · SCAN TO ORDER</div>
                </td>
            @endforeach
            @for ($i = $row->count(); $i < 3; $i++)<td style="border:0"></td>@endfor
        </tr>
    @endforeach
</table>
</body>
</html>
