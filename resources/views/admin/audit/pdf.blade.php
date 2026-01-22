<!DOCTYPE html>
<html>

<head>
    <title>{{ $type }} Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        p {
            margin-top: 0;
            color: #666;
        }
    </style>
</head>

<body>
    <h1>{{ $type }} Report</h1>
    <p>Date Range: {{ $dateRange }}</p>

    <table>
        <thead>
            <tr>
                @if(count($data) > 0)
                    @foreach(array_keys($data[0]) as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                @else
                    <th>No Data</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="100%">No records found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>