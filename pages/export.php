<?php
include('../mysql/db.php');
session_start();

if (!isset($_SESSION['name'])) {
    header('Location: ../index.php');
    exit();
}

// Get search/filter params if any
$search = $_GET['search'] ?? '';
$searchParam = "%$search%";

$sql = "SELECT s.last_name, s.first_name, s.middle_name, s.lrn, 
               g.name as grade_name, sec.name as section_name,
               s.city, s.contact_number, s.student_type
        FROM students s
        LEFT JOIN grade_levels g ON s.grade_level_id = g.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        WHERE s.first_name LIKE ? OR s.last_name LIKE ? 
              OR s.middle_name LIKE ? OR s.lrn LIKE ?
        ORDER BY s.last_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
$stmt->execute();
$result = $stmt->get_result();


$rows = [];
$rows[] = ['Last Name', 'First Name', 'Middle Name', 'LRN', 
           'Grade', 'Section', 'City', 'Contact', 'Status'];

while ($row = $result->fetch_assoc()) {
    $rows[] = [
        $row['last_name'],
        $row['first_name'],
        $row['middle_name'],
        $row['lrn'],
        $row['grade_name'],
        $row['section_name'],
        $row['city'],
        $row['contact_number'],
        $row['student_type']
    ];
}

// Output as CSV or exell
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="students_report.csv"');

$output = fopen('php://output', 'w');
foreach ($rows as $row) {
    fputcsv($output, $row);
}
fclose($output);
exit();
?>