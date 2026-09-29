<?php
declare(strict_types=1);

namespace App\Service;

use PDO;
use PDOException;
use RuntimeException;

class CemeteryService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // -------- Cemeteries --------
    public function getCemeteries(): array
    {
        $stmt = $this->db->query("SELECT * FROM cemeteries ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCemetery(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM cemeteries WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addCemetery(string $name, string $description, string $address, ?float $lat, ?float $lng): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO cemeteries (name, description, address, latitude, longitude) VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$name, $description, $address, $lat, $lng]);
    }

    public function updateCemetery(int $id, string $name, string $description, string $address, ?float $lat, ?float $lng): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE cemeteries SET name=?, description=?, address=?, latitude=?, longitude=? WHERE id=?"
        );
        return $stmt->execute([$name, $description, $address, $lat, $lng, $id]);
    }

    public function deleteCemetery(int $id): bool
    {
        $check = $this->db->prepare("SELECT COUNT(*) FROM cemetery_lots WHERE cemetery_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM cemeteries WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // -------- Lots --------
    public function getLots(?string $status = null, ?int $cemeteryId = null): array
    {
        $sql = "SELECT l.*, c.name AS cemetery_name 
                FROM cemetery_lots l
                JOIN cemeteries c ON l.cemetery_id = c.id";
        $params = [];
        $conditions = [];
        if ($status !== null) {
            $conditions[] = "l.status = ?";
            $params[] = $status;
        }
        if ($cemeteryId !== null) {
            $conditions[] = "l.cemetery_id = ?";
            $params[] = $cemeteryId;
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        $sql .= " ORDER BY c.name, l.section, l.row_num, l.lot_number";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLot(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM cemetery_lots WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addLot(string $section, string $row, string $number, string $status, string $notes, int $cemeteryId): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO cemetery_lots (section, row_num, lot_number, status, notes, cemetery_id) VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$section, $row, $number, $status, $notes, $cemeteryId]);
    }

    public function updateLot(int $id, string $section, string $row, string $number, string $status, string $notes, int $cemeteryId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE cemetery_lots SET section=?, row_num=?, lot_number=?, status=?, notes=?, cemetery_id=? WHERE id=?"
        );
        return $stmt->execute([$section, $row, $number, $status, $notes, $cemeteryId, $id]);
    }

    public function deleteLot(int $id): bool
    {
        $check = $this->db->prepare("SELECT COUNT(*) FROM cemetery_burials WHERE lot_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM cemetery_lots WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAvailabilitySummary(): array
    {
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM cemetery_lots GROUP BY status");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableLotsCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM cemetery_lots WHERE status = 'available'");
        return (int)$stmt->fetchColumn();
    }

    // -------- Deceased --------
    public function getDeceased(string $search = ''): array
    {
        $sql = "SELECT d.*, 
                    b.id AS burial_id, 
                    l.id AS lot_id, 
                    c.name AS cemetery_name
                FROM cemetery_deceased d
                LEFT JOIN cemetery_burials b ON d.id = b.deceased_id
                LEFT JOIN cemetery_lots l ON b.lot_id = l.id
                LEFT JOIN cemeteries c ON l.cemetery_id = c.id";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE d.first_name LIKE ? OR d.last_name LIKE ?";
            $params = ["%$search%", "%$search%"];
        }
        $sql .= " ORDER BY d.last_name, d.first_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns only deceased who do NOT have a burial record yet.
     * Used to populate the "Select Deceased" dropdown in the burial form.
     */
    public function getUnburiedDeceased(): array
    {
        $sql = "SELECT d.*
                FROM cemetery_deceased d
                LEFT JOIN cemetery_burials b ON d.id = b.deceased_id
                WHERE b.id IS NULL
                ORDER BY d.last_name, d.first_name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if a deceased person already has a burial record.
     * Optionally ignore a specific burial id (for updates).
     */
    public function isDeceasedBuried(int $deceasedId, int $ignoreBurialId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM cemetery_burials WHERE deceased_id = ?";
        $params = [$deceasedId];
        if ($ignoreBurialId > 0) {
            $sql .= " AND id != ?";
            $params[] = $ignoreBurialId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getDeceasedById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM cemetery_deceased WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addDeceased(string $firstName, string $lastName, ?string $dob, ?string $dod, string $notes): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO cemetery_deceased (first_name, last_name, date_of_birth, date_of_death, notes) VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$firstName, $lastName, $dob, $dod, $notes]);
    }

    public function updateDeceased(int $id, string $firstName, string $lastName, ?string $dob, ?string $dod, string $notes): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE cemetery_deceased SET first_name=?, last_name=?, date_of_birth=?, date_of_death=?, notes=? WHERE id=?"
        );
        return $stmt->execute([$firstName, $lastName, $dob, $dod, $notes, $id]);
    }

    public function deleteDeceased(int $id): bool
    {
        $check = $this->db->prepare("SELECT COUNT(*) FROM cemetery_burials WHERE deceased_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM cemetery_deceased WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // -------- Burials --------
    public function getBurials(): array
    {
        $sql = "SELECT b.*,
                       l.section, l.row_num, l.lot_number,
                       c.name AS cemetery_name,
                       d.first_name, d.last_name, d.date_of_death
                FROM cemetery_burials b
                JOIN cemetery_lots l ON b.lot_id = l.id
                JOIN cemeteries c ON l.cemetery_id = c.id
                JOIN cemetery_deceased d ON b.deceased_id = d.id
                ORDER BY b.burial_date DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBurial(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM cemetery_burials WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addBurial(int $lotId, int $deceasedId, string $burialDate, string $notes): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "INSERT INTO cemetery_burials (lot_id, deceased_id, burial_date, notes) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$lotId, $deceasedId, $burialDate, $notes]);

            $update = $this->db->prepare("UPDATE cemetery_lots SET status = 'occupied' WHERE id = ?");
            $update->execute([$lotId]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to record burial: " . $e->getMessage());
        }
    }

    public function updateBurial(int $id, int $lotId, int $deceasedId, string $burialDate, string $notes): bool
    {
        $old = $this->getBurial($id);
        if (!$old) {
            return false;
        }
        $oldLotId = (int)$old['lot_id'];

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "UPDATE cemetery_burials SET lot_id=?, deceased_id=?, burial_date=?, notes=? WHERE id=?"
            );
            $stmt->execute([$lotId, $deceasedId, $burialDate, $notes, $id]);

            $check = $this->db->prepare("SELECT COUNT(*) FROM cemetery_burials WHERE lot_id = ? AND id != ?");
            $check->execute([$oldLotId, $id]);
            if ($check->fetchColumn() == 0) {
                $release = $this->db->prepare("UPDATE cemetery_lots SET status = 'available' WHERE id = ?");
                $release->execute([$oldLotId]);
            }

            $occupy = $this->db->prepare("UPDATE cemetery_lots SET status = 'occupied' WHERE id = ?");
            $occupy->execute([$lotId]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to update burial: " . $e->getMessage());
        }
    }

    public function deleteBurial(int $id): bool
    {
        $burial = $this->getBurial($id);
        if (!$burial) {
            return false;
        }
        $lotId = (int)$burial['lot_id'];

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("DELETE FROM cemetery_burials WHERE id = ?");
            $stmt->execute([$id]);

            $release = $this->db->prepare("UPDATE cemetery_lots SET status = 'available' WHERE id = ?");
            $release->execute([$lotId]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to delete burial: " . $e->getMessage());
        }
    }

    // -------- Dashboard Helpers --------
    public function getRecentBurials(int $limit = 5): array
    {
        $sql = "SELECT b.*, d.first_name, d.last_name, l.section, l.row_num, l.lot_number, c.name AS cemetery_name
                FROM cemetery_burials b
                JOIN cemetery_deceased d ON b.deceased_id = d.id
                JOIN cemetery_lots l ON b.lot_id = l.id
                JOIN cemeteries c ON l.cemetery_id = c.id
                ORDER BY b.burial_date DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Statistical summary for the dashboard chart.
     */
    public function getStatisticalSummary(): array
    {
        $burialSql = "
            SELECT
                COALESCE(SUM(CASE WHEN DATE(burial_date) = CURDATE() THEN 1 ELSE 0 END), 0) AS today,
                COALESCE(SUM(CASE WHEN YEARWEEK(burial_date, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END), 0) AS week,
                COALESCE(SUM(CASE WHEN YEAR(burial_date) = YEAR(CURDATE())
                                   AND MONTH(burial_date) = MONTH(CURDATE()) THEN 1 ELSE 0 END), 0) AS month,
                COALESCE(SUM(CASE WHEN YEAR(burial_date) = YEAR(CURDATE()) THEN 1 ELSE 0 END), 0) AS year
            FROM cemetery_burials
        ";

        $deceasedSql = "
            SELECT
                COALESCE(SUM(CASE WHEN DATE(date_of_death) = CURDATE() THEN 1 ELSE 0 END), 0) AS today,
                COALESCE(SUM(CASE WHEN YEARWEEK(date_of_death, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END), 0) AS week,
                COALESCE(SUM(CASE WHEN YEAR(date_of_death) = YEAR(CURDATE())
                                   AND MONTH(date_of_death) = MONTH(CURDATE()) THEN 1 ELSE 0 END), 0) AS month,
                COALESCE(SUM(CASE WHEN YEAR(date_of_death) = YEAR(CURDATE()) THEN 1 ELSE 0 END), 0) AS year
            FROM cemetery_deceased
        ";

        $burialsRaw  = $this->db->query($burialSql)->fetch(PDO::FETCH_ASSOC)   ?: [];
        $deceasedRaw = $this->db->query($deceasedSql)->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'burials' => [
                'today' => (int)($burialsRaw['today'] ?? 0),
                'week'  => (int)($burialsRaw['week']  ?? 0),
                'month' => (int)($burialsRaw['month'] ?? 0),
                'year'  => (int)($burialsRaw['year']  ?? 0),
            ],
            'deceased' => [
                'today' => (int)($deceasedRaw['today'] ?? 0),
                'week'  => (int)($deceasedRaw['week']  ?? 0),
                'month' => (int)($deceasedRaw['month'] ?? 0),
                'year'  => (int)($deceasedRaw['year']  ?? 0),
            ],
        ];
    }
}