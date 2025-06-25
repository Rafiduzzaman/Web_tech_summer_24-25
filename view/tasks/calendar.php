<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar | Your App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }
        .day-header {
            font-weight: bold;
            text-align: center;
            padding: 10px;
            background: #f0f0f0;
        }
        .day {
            border: 1px solid #ddd;
            min-height: 100px;
            padding: 5px;
        }
        .task-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
        .high { background: #dc3545; }
        .medium { background: #fd7e14; }
        .low { background: #28a745; }
    </style>
</head>
<body>
    <div class="calendar-header">
        <h1><?php echo date('F Y'); ?></h1>
        <div>
            <button>Today</button>
            <button>Week</button>
            <button>Month</button>
        </div>
    </div>

    <div class="calendar-grid">
        <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day): ?>
        <div class="day-header"><?php echo $day; ?></div>
        <?php endforeach; ?>

        <?php for ($i = 0; $i < 35; $i++): ?>
        <div class="day">
            <?php if ($i >= 3 && $i < 33): ?>
            <div><?php echo $i - 2; ?></div>
            <div class="task-dot high"></div>
            <div class="task-dot medium"></div>
            <?php endif; ?>
        </div>
        <?php endfor; ?>
    </div>
</body>
</html>