<?php
declare(strict_types=1);

namespace App\Service;

use PDO;
use PDOException;
use RuntimeException;

class LguService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // -------- Internal: auto-cancel expired pending reservations --------
    private function cancelExpiredPending(): int
    {
        return $this->db->exec(
            "UPDATE lgu_reservations
            SET status = 'cancelled', cancelled_reason = 'expired'
            WHERE status = 'pending'
            AND end_datetime < NOW()"
        );
    }

    // -------- Internal: auto-complete approved reservations whose time has passed --------
    private function completeExpiredApproved(): int
    {
        return $this->db->exec(
            "UPDATE lgu_reservations
            SET status = 'completed'
            WHERE status = 'approved'
            AND end_datetime < NOW()"
        );
    }

    // -------- Internal: run all time-based status transitions --------
    private function runAutoTransitions(): void
    {
        $this->cancelExpiredPending();
        $this->completeExpiredApproved();
    }

    public function autoCancelExpiredPendingReservations(): int
    {
        return $this->cancelExpiredPending();
    }

    public function autoCompleteExpiredApprovedReservations(): int
    {
        return $this->completeExpiredApproved();
    }

    // -------- Facilities --------
    public function getFacilities(bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM lgu_facilities";
        if ($activeOnly) {
            $sql .= " WHERE status = 'active'";
        }
        $sql .= " ORDER BY name";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFacility(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM lgu_facilities WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addFacility(string $name, string $type, string $description, int $capacity, string $location, string $status = 'active'): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO lgu_facilities (name, type, description, capacity, location, status) VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$name, $type, $description, $capacity, $location, $status]);
    }

    public function updateFacility(int $id, string $name, string $type, string $description, int $capacity, string $location, string $status): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE lgu_facilities SET name=?, type=?, description=?, capacity=?, location=?, status=? WHERE id=?"
        );
        return $stmt->execute([$name, $type, $description, $capacity, $location, $status, $id]);
    }

    public function deleteFacility(int $id): bool
    {
        $check = $this->db->prepare("SELECT COUNT(*) FROM lgu_reservations WHERE facility_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            return false;
        }
        return $this->db->prepare("DELETE FROM lgu_facilities WHERE id = ?")->execute([$id]);
    }

    // -------- Reservations --------
    public function getReservations(array $filters = []): array
    {
        $this->runAutoTransitions();

        $sql = "SELECT r.*, f.name as facility_name, f.type as facility_type,
                    COALESCE(r.user_name, CONCAT('User #', r.user_id)) as user_name
                FROM lgu_reservations r
                JOIN lgu_facilities f ON r.facility_id = f.id";

        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = "r.user_id = ?";
            $params[] = (int)$filters['user_id'];
        }
        if (!empty($filters['facility_id'])) {
            $where[] = "r.facility_id = ?";
            $params[] = (int)$filters['facility_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['payment_status'])) {
            $where[] = "r.payment_status = ?";
            $params[] = $filters['payment_status'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "r.start_datetime >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "r.end_datetime <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['start_date_from'])) {
            $where[] = "r.start_datetime >= ?";
            $params[] = $filters['start_date_from'];
        }
        if (!empty($filters['start_date_to'])) {
            $where[] = "r.start_datetime <= ?";
            $params[] = $filters['start_date_to'];
        }

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY r.start_datetime DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReservation(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, f.name as facility_name, COALESCE(r.user_name, CONCAT('User #', r.user_id)) as user_name
            FROM lgu_reservations r
            JOIN lgu_facilities f ON r.facility_id = f.id
            WHERE r.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addReservation(int $facilityId, ?int $userId, string $userName, string $purpose, string $description, string $start, string $end): bool
    {
        $this->runAutoTransitions();

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM lgu_reservations
            WHERE facility_id = ?
            AND status NOT IN ('rejected','cancelled','completed')
            AND (start_datetime < ? AND end_datetime > ?)"
        );
        $stmt->execute([$facilityId, $end, $start]);
        if ($stmt->fetchColumn() > 0) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO lgu_reservations (facility_id, user_id, user_name, purpose, description, start_datetime, end_datetime)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$facilityId, $userId, $userName, $purpose, $description, $start, $end]);
    }

    public function updateReservationStatus(int $id, string $status): bool
    {
        // 'completed' can be set explicitly but is normally applied automatically
        $allowed = ['pending', 'approved', 'rejected', 'cancelled', 'completed'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        // Fetch the current row so we can validate the transition
        $check = $this->db->prepare(
            "SELECT status, end_datetime, payment_status
             FROM lgu_reservations
             WHERE id = ?"
        );
        $check->execute([$id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        // Terminal states: cancelled (manual or auto-expired), rejected,
        // or completed reservations can no longer be modified.
        if (in_array($row['status'], ['cancelled', 'rejected', 'completed'], true)) {
            return false;
        }

        if ($status === 'approved') {
            // Block approving a reservation whose event window has fully passed
            if (strtotime((string)$row['end_datetime']) < time()) {
                return false;
            }
            // Block approving until payment is recorded
            if (($row['payment_status'] ?? 'unpaid') !== 'paid') {
                return false;
            }
        }

        if ($status === 'cancelled') {
            $stmt = $this->db->prepare(
                "UPDATE lgu_reservations
                SET status = ?, cancelled_reason = 'manual'
                WHERE id = ?"
            );
        } else {
            $stmt = $this->db->prepare(
                "UPDATE lgu_reservations SET status = ? WHERE id = ?"
            );
        }
        return $stmt->execute([$status, $id]);
    }

    public function deleteReservation(int $id): bool
    {
        return $this->db->prepare("DELETE FROM lgu_reservations WHERE id = ?")->execute([$id]);
    }

    // -------- Payments & Receipts --------

    /**
     * Record a payment for a reservation.
     *
     * When the reservation is still pending AND the event window has not
     * fully passed, the reservation is automatically approved as part of
     * the same atomic UPDATE.
     *
     * @return array{success:bool, receipt_number:string, message:string}
     */
    public function recordPayment(int $reservationId, float $amount, string $method, string $reference, ?string $notes = null): array
    {
        $allowedMethods = ['cash', 'gcash', 'bank_transfer', 'check', 'other'];

        if ($amount <= 0) {
            return ['success' => false, 'receipt_number' => '', 'message' => 'Amount must be greater than zero.'];
        }
        if (!in_array($method, $allowedMethods, true)) {
            return ['success' => false, 'receipt_number' => '', 'message' => 'Invalid payment method.'];
        }

        $stmt = $this->db->prepare(
            "SELECT id, status, payment_status, end_datetime
             FROM lgu_reservations
             WHERE id = ?"
        );
        $stmt->execute([$reservationId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            return ['success' => false, 'receipt_number' => '', 'message' => 'Reservation not found.'];
        }
        if (in_array($res['status'], ['cancelled', 'rejected', 'completed'], true)) {
            return ['success' => false, 'receipt_number' => '', 'message' => 'Cannot record payment for a cancelled, rejected, or completed reservation.'];
        }
        if (($res['payment_status'] ?? 'unpaid') === 'paid') {
            return ['success' => false, 'receipt_number' => '', 'message' => 'This reservation has already been paid.'];
        }

        // Auto-approve only when the reservation is still pending and the
        // event window has not already fully passed.
        $autoApprove = ($res['status'] === 'pending')
            && (strtotime((string)$res['end_datetime']) >= time());

        $receiptNumber = $this->generateReceiptNumber();

        if ($autoApprove) {
            $stmt = $this->db->prepare(
                "UPDATE lgu_reservations
                 SET payment_status = 'paid',
                     payment_amount = ?,
                     payment_method = ?,
                     payment_reference = ?,
                     payment_notes = ?,
                     receipt_number = ?,
                     paid_at = NOW(),
                     status = 'approved'
                 WHERE id = ?"
            );
        } else {
            $stmt = $this->db->prepare(
                "UPDATE lgu_reservations
                 SET payment_status = 'paid',
                     payment_amount = ?,
                     payment_method = ?,
                     payment_reference = ?,
                     payment_notes = ?,
                     receipt_number = ?,
                     paid_at = NOW()
                 WHERE id = ?"
            );
        }

        $ok = $stmt->execute([$amount, $method, $reference, $notes, $receiptNumber, $reservationId]);
        if (!$ok) {
            return ['success' => false, 'receipt_number' => '', 'message' => 'Failed to record payment.'];
        }

        return [
            'success'        => true,
            'receipt_number' => $receiptNumber,
            'message'        => $autoApprove
                ? 'Payment recorded and reservation automatically approved.'
                : 'Payment recorded successfully.',
        ];
    }

    public function generateReceiptNumber(): string
    {
        do {
            $candidate = 'RCPT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM lgu_reservations WHERE receipt_number = ?");
            $stmt->execute([$candidate]);
        } while ((int)$stmt->fetchColumn() > 0);

        return $candidate;
    }

    public function getReceipt(int $reservationId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*, f.name AS facility_name, f.location AS facility_location, f.type AS facility_type
             FROM lgu_reservations r
             JOIN lgu_facilities f ON r.facility_id = f.id
             WHERE r.id = ?"
        );
        $stmt->execute([$reservationId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['receipt_number'])) {
            return null;
        }
        return $row;
    }

    // -------- Availability --------
    public function getAvailability(int $facilityId, string $date): array
    {
        $this->runAutoTransitions();

        $start = $date . " 00:00:00";
        $end = $date . " 23:59:59";
        $stmt = $this->db->prepare(
            "SELECT r.*, COALESCE(r.user_name, CONCAT('User #', r.user_id)) as user_name
            FROM lgu_reservations r
            WHERE r.facility_id = ?
            AND r.status NOT IN ('rejected','cancelled','completed')
            AND (r.start_datetime <= ? AND r.end_datetime >= ?)
            ORDER BY r.start_datetime"
        );
        $stmt->execute([$facilityId, $end, $start]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------- Dashboard Stats --------
    public function getStats(): array
    {
        $this->runAutoTransitions();

        $stats = [];
        $stmt = $this->db->query("SELECT COUNT(*) FROM lgu_facilities WHERE status = 'active'");
        $stats['active_facilities'] = (int)$stmt->fetchColumn();

        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM lgu_reservations WHERE DATE(start_datetime) = ? AND status NOT IN ('rejected','cancelled')");
        $stmt->execute([$today]);
        $stats['today_reservations'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM lgu_reservations WHERE status = 'pending'");
        $stats['pending_approvals'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM lgu_reservations WHERE status = 'pending' AND payment_status <> 'paid'");
        $stats['pending_payments'] = (int)$stmt->fetchColumn();

        $nextWeek = date('Y-m-d', strtotime('+7 days'));
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM lgu_reservations WHERE DATE(start_datetime) BETWEEN ? AND ? AND status = 'approved'");
        $stmt->execute([$today, $nextWeek]);
        $stats['upcoming_events'] = (int)$stmt->fetchColumn();

        return $stats;
    }

    // -------- Dashboard trend (reservations per day) --------
    /**
     * Returns one row per day for the last N days, with counts per status.
     * Gaps are filled with zeros so the chart always has a continuous axis.
     *
     * @return array<int, array{d:string,total:int,approved:int,pending:int,rejected:int,cancelled:int,completed:int}>
     */
    public function getReservationsTrend(int $days = 30, ?int $facilityId = null): array
    {
        $days  = max(1, $days);
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));

        $sql = "SELECT DATE(start_datetime) AS d,
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'approved'  THEN 1 ELSE 0 END) AS approved,
                    SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = 'rejected'  THEN 1 ELSE 0 END) AS rejected,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
                FROM lgu_reservations
                WHERE start_datetime >= ?";

        $params = [$start . ' 00:00:00'];

        if ($facilityId !== null && $facilityId > 0) {
            $sql .= " AND facility_id = ?";
            $params[] = $facilityId;
        }

        $sql .= " GROUP BY DATE(start_datetime) ORDER BY d ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byDate = [];
        foreach ($rows as $r) {
            $byDate[$r['d']] = $r;
        }

        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $d = date('Y-m-d', strtotime($start . " +{$i} days"));
            $out[] = $byDate[$d] ?? [
                'd'         => $d,
                'total'     => 0,
                'approved'  => 0,
                'pending'   => 0,
                'rejected'  => 0,
                'cancelled' => 0,
                'completed' => 0,
            ];
        }
        return $out;
    }
}