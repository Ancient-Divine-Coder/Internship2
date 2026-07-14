<?php
require 'db.php';

// Fixed fest info per event (not stored in DB — your `event` table only holds id/name/seats).
// Match by event name to fill in badge, description, venue, time, and fee.
$eventDetails = [
    'Tech Talk' => ['category' => 'Talks & Seminars', 'description' => 'Learn from industry experts about the latest trends in technology. Interactive Q&A session included.', 'venue' => 'Main Auditorium', 'time_slot' => '9:00 AM - 11:00 AM', 'entry_fee' => 'FREE', 'image' => 'images/events/tech-talk.jpg'],
    'Cyber Security Workshop' => ['category' => 'Workshop', 'description' => 'Hands-on workshop covering cybersecurity basics, threats, and defense mechanisms.', 'venue' => 'Computer Lab A', 'time_slot' => '11:30 AM - 1:30 PM', 'entry_fee' => '200', 'image' => 'images/events/cyber-security.jpg'],
    'Mystery Seekers' => ['category' => 'Games', 'description' => 'A thrilling mystery game where you solve puzzles and unlock secrets. Team-based competition.', 'venue' => 'Game Zone', 'time_slot' => '2:00 PM - 4:00 PM', 'entry_fee' => '150', 'image' => 'images/events/mystery-seekers.jpg'],
    'Silent DJ' => ['category' => 'Music & Dance', 'description' => 'Experience music through personal wireless headphones. Three DJ channels to choose from simultaneously.', 'venue' => 'Dance Floor', 'time_slot' => '4:30 PM - 6:30 PM', 'entry_fee' => '300', 'image' => 'images/events/silent-dj.jpg'],
    'Sprint Brawls' => ['category' => 'Sports', 'description' => 'High-energy relay races and sprint competitions. Show off your speed and athleticism!', 'venue' => 'Sports Ground', 'time_slot' => '5:00 PM - 7:00 PM', 'entry_fee' => 'FREE', 'image' => 'images/events/sprint-brawls.jpg'],
    'Fun Games' => ['category' => 'Games & Entertainment', 'description' => 'Board games, card games, and outdoor games. Prizes for winners! Bring your friends!', 'venue' => 'Community Hall', 'time_slot' => '3:00 PM - 6:00 PM', 'entry_fee' => '100', 'image' => 'images/events/fun-games.jpg'],
    'Celebrity Night' => ['category' => 'Special Event', 'description' => 'Join us for an exclusive evening with renowned personalities. Meet & greet, photo sessions, and autographs included!', 'venue' => 'Main Ground', 'time_slot' => '7:00 PM Onwards', 'entry_fee' => '500', 'image' => 'images/events/celebrity-night.jpg'],
];
$defaultDetails = ['category' => '', 'description' => '', 'venue' => '', 'time_slot' => '', 'entry_fee' => 'FREE', 'image' => 'images/events/default.jpg'];

$events = [];
if ($DB_OK) {
    $result = $conn->query("SELECT id, name, total_seat, booked_seat FROM event");
    while ($row = $result->fetch_assoc()) {
        $row = array_merge($row, $eventDetails[$row['name']] ?? $defaultDetails);
        $events[] = $row;
    }
}

// Pull out the Celebrity Night event separately for its special section
$celebrity = null;
foreach ($events as $ev) {
    if ($ev['name'] === 'Celebrity Night') { $celebrity = $ev; break; }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pravah 2026</title>
    <link rel="stylesheet" href="styl.css">
</head>

<body>
    <div class="background-fixed"></div>
    <section class="hero-section">
        <div class="wrap">

            <div class="hero">
                <div class="eyebrow">SKIT PRESENTS</div>
                <h1>PRAVAH 2026</h1>
                <div class="date">20 JULY 2026 · 9:00 AM ONWARDS</div>

                <div class="countdown" id="countdown">
                    <div class="unit">
                        <div class="num" id="days">00</div>
                        <div class="label">Days</div>
                    </div>
                    <div class="unit">
                        <div class="num" id="hours">00</div>
                        <div class="label">Hours</div>
                    </div>
                    <div class="unit">
                        <div class="num" id="mins">00</div>
                        <div class="label">Mins</div>
                    </div>
                    <div class="unit">
                        <div class="num" id="secs">00</div>
                        <div class="label">Secs</div>
                    </div>
                </div>
            </div>
    </section>
    <section class="registration-section">
        <div class="wrap">
            <h2 class="section-title">Events</h2>

            <?php if (!$DB_OK): ?>
                <div class="setup-notice">⚠ Database NOT connected. Check db.php — your host/user/pass/dbname are likely wrong. Error: <?= htmlspecialchars($conn->connect_error ?? 'unknown') ?></div>
            <?php elseif (empty($events)): ?>
                <div class="setup-notice">⚠ Connected to the database fine, but the `event` table has 0 rows. Go insert your 7 events.</div>
            <?php endif; ?>



            <div class="events-grid">
                <?php foreach ($events as $ev):
                    $left = $ev['total_seat'] - $ev['booked_seat'];
                    $pct = $ev['total_seat'] > 0 ? round(($ev['booked_seat'] / $ev['total_seat']) * 100) : 0;
                    $entry_fee = isset($ev['entry_fee']) ? $ev['entry_fee'] : 'FREE';
                ?>
                    <div class="event-card" data-id="<?= $ev['id'] ?>">
                        <div class="event-image">
                            <img src="<?= htmlspecialchars($ev['image']) ?>" alt="<?= htmlspecialchars($ev['name']) ?>">
                        </div>
                        <div class="event-card-content">
                            <h3><?= htmlspecialchars($ev['name']) ?></h3>
                            <div class="tagline"><?= htmlspecialchars($ev['category']) ?></div>
                            <div class="event-info">
                                <div class="info-item">
                                    <span class="label">Entry Fee:</span>
                                    <span class="value"><?= is_numeric($entry_fee) ? '₹' . $entry_fee : $entry_fee ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="label">Available Spots:</span>
                                    <span class="value <?= $left > 0 ? 'available' : 'full' ?>"><?= $left > 0 ? $left : 'Full' ?></span>
                                </div>
                            </div>
                            <div class="gauge">
                                <div class="gauge-fill" style="width:<?= $pct ?>%"></div>
                            </div>
                            <div class="seat-text">
                                <span class="<?= $left > 0 ? 'left' : 'full' ?>"><?= $left > 0 ? "$left left" : "Full" ?></span>
                                <span><?= $ev['total_seat'] ?> total</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="wrap">
            <div class="form-card" id="regFormCard">
                <h2 class="section-title" style="margin-top:0">Register</h2>
                <form id="regForm">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>

                    <label for="college_id">College ID</label>
                    <input type="text" id="college_id" name="college_id" required>

                    <label for="branch">Branch</label>
                    <input type="text" id="branch" name="branch" required>

                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" required>

                    <label for="event_id">Event</label>
                    <select id="event_id" name="event_id" required>
                        <option value="">Select an event</option>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= $ev['id'] ?>"><?= htmlspecialchars($ev['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="submit-btn" id="submitBtn">Register</button>
                    <div class="form-msg" id="formMsg"></div>
                </form>
            </div>
        </div>
    </section>
    <section class="Events-section">
        <div class="wrap">
            <!-- Detailed Events Section -->
            <h2 class="section-title" style="margin-top: 0; margin-bottom: 30px;">📋 Event Details</h2>
            <div class="detailed-events-section">
                <?php foreach ($events as $ev):
                    if ($ev['name'] === 'Celebrity Night') continue; // shown separately below
                    $left = $ev['total_seat'] - $ev['booked_seat'];
                    $fee = is_numeric($ev['entry_fee']) ? '₹' . $ev['entry_fee'] : $ev['entry_fee'];
                ?>
                <div class="detailed-event-card">
                    <div class="event-header">
                        <h3><?= htmlspecialchars($ev['name']) ?></h3>
                        <span class="event-badge"><?= htmlspecialchars($ev['category']) ?></span>
                    </div>
                    <div class="event-body">
                        <p class="event-description"><?= htmlspecialchars($ev['description']) ?></p>
                        <div class="event-meta">
                            <div class="meta-item">
                                <span class="meta-label">⏰ Time:</span>
                                <span class="meta-value"><?= htmlspecialchars($ev['time_slot']) ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">📍 Venue:</span>
                                <span class="meta-value"><?= htmlspecialchars($ev['venue']) ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">👥 Total Seats:</span>
                                <span class="meta-value"><?= $ev['total_seat'] ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">💰 Entry Fee:</span>
                                <span class="meta-value fee"><?= $fee ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="event-footer">
                        <div class="availability">
                            <span class="available-count" data-id="<?= $ev['id'] ?>"><?= $left > 0 ? $left : 'Full' ?></span>
                            <span class="available-text">Seats Available</span>
                        </div>
                        <button class="register-btn" data-event-id="<?= $ev['id'] ?>" data-event-name="<?= htmlspecialchars($ev['name']) ?>">Register Now</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php if ($celebrity): $celeb_left = $celebrity['total_seat'] - $celebrity['booked_seat']; ?>
    <section class="celebrity-section">
        <div class="wrap">
            <!-- Celebrity Night Special Event -->
            <h2 class="section-title" style="margin-top: 0; text-align: center;">✨ Special Event ✨</h2>
            <div class="celebrity-section">
                <div class="celebrity-card">
                    <div class="celebrity-image">
                        <img src="images/ceb.jpg" alt="Celebrity Night">
                    </div>
                    <div class="celebrity-content">
                        <h3><?= htmlspecialchars($celebrity['name']) ?></h3>
                        <p class="celebrity-description"><?= htmlspecialchars($celebrity['description']) ?></p>
                        <div class="celebrity-details">
                            <div class="detail-item">
                                <span class="icon">🎭</span>
                                <span class="text">Meet Celebrity Guests</span>
                            </div>
                            <div class="detail-item">
                                <span class="icon">📸</span>
                                <span class="text">Photo Opportunities</span>
                            </div>
                            <div class="detail-item">
                                <span class="icon">✍️</span>
                                <span class="text">Autographs & Signatures</span>
                            </div>
                            <div class="detail-item">
                                <span class="icon">🍿</span>
                                <span class="text">Refreshments Included</span>
                            </div>
                        </div>
                        <div class="celebrity-footer">
                            <div class="fee-section">
                                <span class="fee-label">Entry Fee:</span>
                                <span class="fee-amount">₹<?= htmlspecialchars($celebrity['entry_fee']) ?></span>
                            </div>
                            <div class="availability-section">
                                <span class="seats-label">Limited Seats:</span>
                                <span class="seats-available" data-id="<?= $celebrity['id'] ?>"><?= $celeb_left > 0 ? "$celeb_left Remaining" : "Full" ?></span>
                            </div>
                            <button class="register-btn celebrity-register-btn" data-event-id="<?= $celebrity['id'] ?>" data-event-name="<?= htmlspecialchars($celebrity['name']) ?>">Register Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <script src="script.js"></script>
</body>

</html>