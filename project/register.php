<?php
header('Content-Type: application/json');
require 'db.php';

if (!$DB_OK) {
    echo json_encode(["success" => false, "message" => "Database not connected yet."]);
    exit;
}

$name       = trim($_POST['name'] ?? '');
$college_id = trim($_POST['college_id'] ?? '');
$branch     = trim($_POST['branch'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$event_id   = (int)($_POST['event_id'] ?? 0);

if (!$name || !$college_id || !$branch || !$phone || !$event_id) {
    echo json_encode(["success" => false, "message" => "All fields are required."]);
    exit;
}

$conn->begin_transaction();
try {
    // Lock the event row so two people can't grab the last seat at once
    $stmt = $conn->prepare("SELECT total_seat, booked_seat FROM event WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();

    if (!$event) throw new Exception("Event not found.");
    if ($event['booked_seat'] >= $event['total_seat']) throw new Exception("No seats left for this event.");

    $stmt = $conn->prepare("INSERT INTO registrations (name, college_id, branch, phone, event_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $name, $college_id, $branch, $phone, $event_id);
    $stmt->execute();

    // Get the newly inserted registration ID
    $reg_id = $conn->insert_id;

    $conn->query("UPDATE event SET booked_seat = booked_seat + 1 WHERE id = $event_id");

    $conn->commit();

    $remaining = $event['total_seat'] - ($event['booked_seat'] + 1);

    // Return reg_id in response
    echo json_encode([
        "success" => true,
        "message" => "Registered successfully!",
        "remaining" => $remaining,
        "reg_id" => $reg_id
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
