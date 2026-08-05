<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
</head>

<body>

    <h2>Welcome to Flow CRM 🎉</h2>

    <p>Hello {{ $organization->name }},</p>

    <p>Your organization has been created successfully.</p>

    <table border="1" cellpadding="8" cellspacing="0">

        <tr>
            <th>Organization</th>
            <td>{{ $organization->name }}</td>
        </tr>

        <tr>
            <th>Owner</th>
            <td>{{ $user->first_name }} {{ $user->last_name }}</td>
        </tr>

        <tr>
            <th>Email</th>
            <td>{{ $user->email }}</td>
        </tr>

    </table>

    <br>

    <p>
        You can now login to syncwith  CRM using your owner credentials.
    </p>

</body>

</html>