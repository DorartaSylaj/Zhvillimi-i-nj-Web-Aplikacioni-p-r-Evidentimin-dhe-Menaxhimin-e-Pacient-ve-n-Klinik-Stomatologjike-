<!DOCTYPE html>
<html>
<head>
    <title>Lista e Pacientëve</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>Lista e Pacientëve</h2>
    <table>
        <thead>
            <tr>
                <th>Emri</th>
                <th>Mbiemri</th>
                <th>Data e Lindjes</th>
                <th>Simptoma</th>
                <th>Data e Vizitës</th>
            </tr>
        </thead>
        <tbody>
            @foreach($patients as $patient)
            <tr>
                <td>{{ $patient->first_name }}</td>
                <td>{{ $patient->last_name }}</td>
                <td>{{ $patient->date_of_birth }}</td>
                <td>{{ $patient->symptom }}</td>
                <td>{{ $patient->visit_date }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
