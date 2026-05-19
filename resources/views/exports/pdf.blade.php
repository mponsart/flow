<h1 style="font-size: 2rem; color: #6366f1;">Rapport Mensuel</h1>
<table style="width: 100%; margin-top: 2rem; border-collapse: collapse;">
    <thead>
        <tr style="background: #23232a; color: #fff;">
            <th style="padding: 8px; border: 1px solid #18181b;">KPI</th>
            <th style="padding: 8px; border: 1px solid #18181b;">Valeur</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $k => $v)
            <tr>
                <td style="padding: 8px; border: 1px solid #18181b;">{{ $k }}</td>
                <td style="padding: 8px; border: 1px solid #18181b;">{{ $v }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
