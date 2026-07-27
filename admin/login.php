<?php
session_start();

if (!empty($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Login</title>
    <style>
        :root {
            --primary: #0f6b3c;
            --primary-dark: #0a4d2c;
            --accent: #d4a017;
            --accent-soft: #f6e8b8;
            --bg: #f5f0e6;
            --surface: #ffffff;
            --text: #1f2a1f;
            --muted: #64748b;
            --border: #e2ddd0;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                linear-gradient(135deg, rgba(15, 107, 60, 0.16), rgba(212, 160, 23, 0.12)),
                radial-gradient(circle at top left, #fdf8e8 0%, var(--bg) 45%, #efe6d1 100%);
            color: var(--text);
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .login-card {
            width: 100%;
            max-width: 450px;
            background: var(--surface);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.14);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(15, 107, 60, 0.04), rgba(212, 160, 23, 0.03));
            pointer-events: none;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            font-size: 24px;
            background: linear-gradient(135deg, var(--primary), #1a8d4d);
            color: #fff;
            box-shadow: 0 10px 22px rgba(15, 107, 60, 0.2);
        }

        .brand h2 {
            margin: 0 0 4px;
            font-size: 1.2rem;
            color: var(--primary-dark);
        }

        .brand p {
            margin: 0;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .tag {
            display: inline-block;
            margin-bottom: 14px;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: #8a6505;
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text);
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background: #fcfbf8;
        }

        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(15, 107, 60, 0.12);
        }

        button {
            width: 100%;
            padding: 13px 16px;
            border: none;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
            position: relative;
            z-index: 1;
        }

        button:hover {
            background: linear-gradient(135deg, var(--primary-dark), #c7920f);
        }

        .hint {
            margin-top: 16px;
            text-align: center;
            color: var(--muted);
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
        }

        .hint strong {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <span class="tag">Kenyan land administration</span>
        <div class="brand">
            <div class="brand-icon">🌍</div>
            <div>
                <h2>E-Land Admin</h2>
                <p>Secure access to land project records</p>
            </div>
        </div>

        <form action="authenticate.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="hint"><strong>Trusted access</strong> for land records, ownership updates, and verification tasks.</div>
    </div>
</body>
</html>