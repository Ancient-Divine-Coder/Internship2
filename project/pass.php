<?php
require 'db.php';

$reg_id = (int)($_GET['id'] ?? 0);

if (!$DB_OK || $reg_id <= 0) {
    die("Invalid Pass Request.");
}

$stmt = $conn->prepare("
    SELECT r.id AS reg_id, r.event_id, r.name, r.college_id, r.branch, e.name AS event_name 
    FROM registrations r 
    JOIN event e ON r.event_id = e.id 
    WHERE r.id = ?
");
$stmt->bind_param("i", $reg_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Pass not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pass - REG #<?= $data['reg_id'] ?></title>
    <style>
        body {
            background: #0b0e27;
            color: #f1f0ff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .pass-card {
            width: 650px;
            background: #151833;
            border: 2px solid #00e5c7;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 229, 199, 0.2);
            position: relative;
            box-sizing: border-box;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #00e5c7;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            color: #00e5c7;
            letter-spacing: 1px;
        }
        .security-badge {
            background: rgba(255, 182, 72, 0.15);
            border: 1px solid #ffb648;
            color: #ffb648;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .item {
            background: rgba(11, 14, 39, 0.6);
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid rgba(138, 140, 179, 0.2);
        }
        .label {
            font-size: 11px;
            color: #8a8cb3;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .value {
            font-size: 18px;
            font-weight: 600;
            color: #f1f0ff;
        }
        .value.highlight {
            color: #ffb648;
            font-size: 20px;
        }
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 12px;
            color: #8a8cb3;
        }
        .actions {
            margin-top: 25px;
        }
        .print-btn {
            background: linear-gradient(90deg, #00e5c7, #ffb648);
            color: #0b0e27;
            border: none;
            padding: 12px 28px;
            font-weight: bold;
            font-size: 15px;
            border-radius: 8px;
            cursor: pointer;
        }
        @media print {
            .actions { display: none; }
            body { background: #fff; color: #000; }
            .pass-card { border-color: #000; background: #fff; color: #000; }
            .value { color: #000; }
            .item { background: #f0f0f0; }
        }
    </style>
</head>
<body>

    <div class="pass-card">
        <div class="header">
            <div class="title">PRAVAH 2026 PASS</div>
            <div class="security-badge">REG #<?= $data['reg_id'] ?> | EV #<?= $data['event_id'] ?></div>
        </div>

        <div class="grid">
            <div class="item">
                <div class="label">Attendee Name</div>
                <div class="value"><?= htmlspecialchars(strtoupper($data['name'])) ?></div>
            </div>
            <div class="item">
                <div class="label">College ID</div>
                <div class="value"><?= htmlspecialchars($data['college_id']) ?></div>
            </div>
            <div class="item">
                <div class="label">Branch</div>
                <div class="value"><?= htmlspecialchars($data['branch']) ?></div>
            </div>
            <div class="item">
                <div class="label">Event Name</div>
                <div class="value highlight"><?= htmlspecialchars($data['event_name']) ?></div>
            </div>
        </div>

        <div class="footer">
            📌 Valid only when presented alongside official College ID card.
        </div>
    </div>

    <div class="actions">
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save Pass</button>
    </div>

</body>
</html>