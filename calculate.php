<?php
require_once 'config.php';
require_once 'auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }

$startDate  = $_POST['start_date'];
$totalCount = (int) $_POST['total_count'];
$days       = $_POST['days'] ?? [];

if (empty($days)) die("⚠️ Pilih minimal 1 hari makan!");
sort($days);

$schedules = [];
$current   = new DateTime($startDate);
$sequence  = 0;

while ($sequence < $totalCount) {
    if (in_array((int) $current->format('w'), $days)) {
        $sequence++;
        $schedules[] = [
            'date' => $current->format('Y-m-d'),
            'day'  => $current->format('l'),
            'seq'  => $sequence
        ];
    }
    $current->modify('+1 day');
}

$endDate  = end($schedules)['date'];
$endMonth = date('F Y', strtotime($endDate));
$userId   = $_SESSION['user_id'];

$inserted = supabaseInsert('caterings', [
    'user_id'      => $userId,
    'total_count'  => $totalCount,
    'start_date'   => $startDate,
    'end_date'     => $endDate,
    'days_of_week' => implode(',', $days),
    'end_month'    => $endMonth
]);

$cateringId = $inserted[0]['id'];

$rows = [];
foreach ($schedules as $s) {
    $rows[] = [
        'catering_id'   => $cateringId,
        'schedule_date' => $s['date'],
        'day_name'      => $s['day'],
        'sequence'      => $s['seq'],
        'status'        => 'pending'
    ];
}
supabaseInsert('catering_schedules', $rows);

header("Location: dashboard.php?id=$cateringId");
exit;