<?php
// =============================================
// api/etl_sync.php
// ETL = Extract, Transform, Load
// Copies data from operational OLTP tables into isolated OLAP warehouse tables
// =============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// 1. Establish connections to BOTH separate database engines
require_once '../includes/db.php';    // Operational Source database ($pdo)
require_once '../includes/dw_db.php'; // Data Warehouse Target database ($dw_pdo)

$log = []; 

try {
    // Disable execution timeouts for large ETL operations
    set_time_limit(300);

    // CRITICAL: We run the transaction on the Data Warehouse connection where writes happen!
    $dw_pdo->beginTransaction();

    // =============================================
    // STEP 1: Sync dim_campaign
    // Extract from $pdo (OLTP) -> Load to $dw_pdo (OLAP)
    // =============================================
    $campaigns = $pdo->query("SELECT * FROM campaigns WHERE is_deleted = 0")->fetchAll();

    foreach ($campaigns as $c) {
        // Look up against the OLAP connection ($dw_pdo)
        $check = $dw_pdo->prepare("SELECT campaign_sk FROM dim_campaign WHERE campaign_id = ? LIMIT 1");
        $check->execute([$c['campaign_id']]);
        $exists = $check->fetchColumn();

        if ($exists) {
            $dw_pdo->prepare("
                UPDATE dim_campaign
                SET title = ?, category = ?, goal_amount = ?, start_date = ?, end_date = ?, status = ?
                WHERE campaign_id = ?
            ")->execute([
                $c['title'], $c['category'], $c['goal_amount'],
                $c['start_date'], $c['end_date'], $c['campaign_status'] ?? $c['status'] ?? 'Active',
                $c['campaign_id']
            ]);
        } else {
            $dw_pdo->prepare("
                INSERT INTO dim_campaign (campaign_id, title, category, goal_amount, start_date, end_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $c['campaign_id'], $c['title'], $c['category'],
                $c['goal_amount'], $c['start_date'], $c['end_date'], $c['campaign_status'] ?? $c['status'] ?? 'Active'
            ]);
        }
    }
    $log[] = "dim_campaign: synced " . count($campaigns) . " campaigns";

    // =============================================
    // STEP 2: Sync dim_donor
    // Extract from $pdo (OLTP) -> Load to $dw_pdo (OLAP)
    // =============================================
    $donors = $pdo->query("
        SELECT u.user_id, u.username, u.user_role, u.created_at,
               up.first_name, up.last_name, up.region_state, up.country_code
        FROM users u
        JOIN user_profiles up ON u.user_id = up.user_id
        WHERE u.is_deleted = 0
    ")->fetchAll();

    foreach ($donors as $d) {
        $full_name = $d['first_name'] . ' ' . $d['last_name'];

        $check = $dw_pdo->prepare("SELECT donor_sk FROM dim_donor WHERE user_id = ? LIMIT 1");
        $check->execute([$d['user_id']]);
        $exists = $check->fetchColumn();

        if ($exists) {
            $dw_pdo->prepare("
                UPDATE dim_donor
                SET username = ?, full_name = ?, region_state = ?, user_role = ?
                WHERE user_id = ?
            ")->execute([$d['username'], $full_name, $d['region_state'], $d['user_role'], $d['user_id']]);
        } else {
            $dw_pdo->prepare("
                INSERT INTO dim_donor (user_id, username, full_name, region_state, user_role, joined_date)
                VALUES (?, ?, ?, ?, ?, ?)
            ")->execute([
                $d['user_id'], $d['username'], $full_name,
                $d['region_state'], $d['user_role'], date('Y-m-d', strtotime($d['created_at']))
            ]);
        }
    }
    $log[] = "dim_donor: synced " . count($donors) . " donors";

    // =============================================
    // STEP 2B: AUTO-GENERATE DIM_TIME DATES
    // Prevents foreign key violations if dates don't match
    // =============================================
    $dates_query = $pdo->query("SELECT DISTINCT DATE(created_at) as raw_date FROM donations WHERE created_at IS NOT NULL");
    $dates = $dates_query->fetchAll(PDO::FETCH_COLUMN);

    $stmt_time = $dw_pdo->prepare("
        INSERT IGNORE INTO dim_time 
        (full_date, day_of_month, day_name, month_num, month_name, quarter, year, is_weekend)
        VALUES (:fd, :dom, :dn, :mn, :mname, :q, :y, :w)
    ");

    foreach ($dates as $date) {
        $ts = strtotime($date);
        $day_of_week = date('N', $ts);
        $stmt_time->execute([
            ':fd'    => $date,
            ':dom'   => date('j', $ts),
            ':dn'    => date('l', $ts),
            ':mn'    => date('n', $ts),
            ':mname' => date('F', $ts),
            ':q'     => ceil(date('n', $ts) / 3),
            ':y'     => date('Y', $ts),
            ':w'     => ($day_of_week >= 6) ? 1 : 0
        ]);
    }

    // =============================================
    // STEP 3: Load new donations into fact_donations
    // =============================================
    $existing_donations = $dw_pdo->query("SELECT donation_id FROM fact_donations")->fetchAll(PDO::FETCH_COLUMN);
    
    // Safe query construction to avoid SQL injection and handle empty arrays
    if (!empty($existing_donations)) {
        $placeholders = implode(',', array_fill(0, count($existing_donations), '?'));
        $query_string = "SELECT * FROM donations WHERE payment_status = 'Completed' AND donation_id NOT IN ($placeholders)";
        $stmt_new = $pdo->prepare($query_string);
        $stmt_new->execute($existing_donations);
    } else {
        // If no existing donations, fetch all completed donations
        $query_string = "SELECT * FROM donations WHERE payment_status = 'Completed'";
        $stmt_new = $pdo->prepare($query_string);
        $stmt_new->execute();
    }
    $new_donations = $stmt_new->fetchAll();

    $loaded = 0;
    foreach ($new_donations as $don) {
        $date = date('Y-m-d', strtotime($don['created_at']));

        $t = $dw_pdo->prepare("SELECT time_id FROM dim_time WHERE full_date = ? LIMIT 1");
        $t->execute([$date]);
        $time_id = $t->fetchColumn();
        if (!$time_id) continue; 

        $cs = $dw_pdo->prepare("SELECT campaign_sk FROM dim_campaign WHERE campaign_id = ? LIMIT 1");
        $cs->execute([$don['campaign_id']]);
        $campaign_sk = $cs->fetchColumn();
        if (!$campaign_sk) continue;

        $ds = $dw_pdo->prepare("SELECT donor_sk FROM dim_donor WHERE user_id = ? LIMIT 1");
        $ds->execute([$don['user_id']]);
        $donor_sk = $ds->fetchColumn();
        if (!$donor_sk) continue;

        $pm = $dw_pdo->prepare("SELECT payment_method_id FROM dim_payment_method WHERE method_name = ? LIMIT 1");
        $pm->execute([$don['payment_method']]);
        $payment_method_id = $pm->fetchColumn() ?: 3; // Default fallback to G_Cash

        $dw_pdo->prepare("
            INSERT INTO fact_donations
            (time_id, campaign_sk, donor_sk, payment_method_id, donation_amount, transaction_reference, donation_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $time_id, $campaign_sk, $donor_sk, $payment_method_id,
            $don['amount'], $don['transaction_reference'], $don['donation_id']
        ]);
        $loaded++;
    }
    $log[] = "fact_donations: loaded $loaded new donations";

    // =============================================
    // STEP 4: Rebuild campaign performance summary
    // =============================================
    $dw_pdo->exec("DELETE FROM fact_campaign_performance");

    $dw_pdo->exec("
        INSERT INTO fact_campaign_performance
            (time_id, campaign_sk, total_raised, donor_count, donation_count, avg_donation, goal_amount, progress_pct)
        SELECT
            fd.time_id,
            fd.campaign_sk,
            SUM(fd.donation_amount)                                 AS total_raised,
            COUNT(DISTINCT fd.donor_sk)                             AS donor_count,
            COUNT(fd.fact_id)                                       AS donation_count,
            AVG(fd.donation_amount)                                 AS avg_donation,
            dc.goal_amount,
            ROUND(SUM(fd.donation_amount) / dc.goal_amount * 100, 2) AS progress_pct
        FROM fact_donations fd
        JOIN dim_campaign dc ON fd.campaign_sk = dc.campaign_sk
        GROUP BY fd.time_id, fd.campaign_sk, dc.goal_amount
    ");
    $log[] = "fact_campaign_performance: rebuilt";

    $dw_pdo->commit();
    echo json_encode(['success' => true, 'message' => 'ETL Pipeline executed successfully!', 'log' => $log]);

} catch (Exception $e) {
    if ($dw_pdo->inTransaction()) {
        $dw_pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'ETL failed: ' . $e->getMessage(), 'log' => $log]);
}