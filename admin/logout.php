<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logout | E-Land Admin</title>
    <style>
        :root {
            --primary: #0f6b3c;
            --primary-dark: #0a4d2c;
            --accent: #d4a017;
            --bg: #f6f2e8;
            --surface: #ffffff;
            --text: #1f2a1f;
            --muted: #64748b;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #eef7ef 0%, var(--bg) 100%);
            color: var(--text);
            padding: 24px;
        }

        .logout-card {
            width: 100%;
            max-width: 440px;
            background: var(--surface);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
            text-align: center;
        }

        .icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            font-size: 28px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
        }

        h2 {
            margin: 0 0 10px;
            font-size: 1.4rem;
        }

        p {
            margin: 0 0 22px;
            color: var(--muted);
            line-height: 1.6;
        }

        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 999px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
        }

        .btn-secondary {
            background: #eef2f7;
            color: #334155;
        }
    </style>
</head>
<body>
    <div class="logout-card">
        <div class="icon">🔒</div>
        <h2>Log out of your admin account?</h2>
        <p>You will be returned to the secure admin sign-in page once you confirm.</p>
        <div class="actions">
            <a class="btn btn-secondary" href="dashboard.php">Cancel</a>
            <form method="post" style="display:inline;">
                <button class="btn btn-primary" type="submit">Yes, log me out</button>
            </form>
        </div>
    </div>
</body>
</html>
