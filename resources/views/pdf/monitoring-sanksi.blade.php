@php use Illuminate\Support\Str; use App\Support\DataIO; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Monitoring Sanksi' }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 9px; color: #1e293b; margin: 0; }
        h1 { font-size: 15px; margin: 0 0 2px; color: #047857; }
        .meta { font-size: 8px; color: #64748b; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #047857; color: #fff; text-align: left; padding: 5px 6px; font-size: 8px; text-transform: uppercase; }
        td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; font-size: 8px; vertical-align: top; }
        tr:nth-child(even) td { background: #f8fafc; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; font-size: 7px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $title ?? 'Monitoring Sanksi' }}</h1>
    <div class="meta">DLH Kota Palu — Dicetak {{ now()->format('d M Y H:i') }} — Total {{ count($rows) }} baris</div>
    <table>
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ Str::headline($column) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($columns as $column)
                        <td>{{ DataIO::displayValue($row->{$column} ?? null) }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Portal Operasional DLH Kota Palu</div>
</body>
</html>
