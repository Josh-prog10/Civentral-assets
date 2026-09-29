<?php
declare(strict_types=1);

namespace App\Service;

use PDO;
use PDOException;
use RuntimeException;

class AssetService
{
    private PDO $db;

    // Default field permissions when a category has no config stored.
    // plate_number = false because only vehicles need it.
    private const DEFAULT_FIELD_CONFIG = [
        'plate_number'     => false,
        'coordinates'      => true,
        'gis_map'          => true,
        'maintenance'      => true,
        'acquisition_date' => true,
        'notes'            => true,
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // -------- Categories --------
    public function getCategories(): array
    {
        $stmt = $this->db->query("SELECT * FROM asset_categories ORDER BY name");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['field_config_parsed'] = $this->parseFieldConfig($row['field_config'] ?? null);
        }
        return $rows;
    }

    public function getCategory(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM asset_categories WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($row) {
            $row['field_config_parsed'] = $this->parseFieldConfig($row['field_config'] ?? null);
        }
        return $row;
    }

    public function addCategory(string $name, string $description, string $icon = 'fa-cube', array $fieldConfig = []): bool
    {
        $config = array_merge(self::DEFAULT_FIELD_CONFIG, $fieldConfig);
        $stmt = $this->db->prepare(
            "INSERT INTO asset_categories (name, description, icon, field_config) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$name, $description, $icon, json_encode($config)]);
    }

    public function updateCategory(int $id, string $name, string $description, string $icon, array $fieldConfig = []): bool
    {
        $config = array_merge(self::DEFAULT_FIELD_CONFIG, $fieldConfig);
        $stmt = $this->db->prepare(
            "UPDATE asset_categories SET name = ?, description = ?, icon = ?, field_config = ? WHERE id = ?"
        );
        return $stmt->execute([$name, $description, $icon, json_encode($config), $id]);
    }

    public function deleteCategory(int $id): bool
    {
        $check = $this->db->prepare("SELECT COUNT(*) FROM assets WHERE category_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            return false; // category in use
        }
        $stmt = $this->db->prepare("DELETE FROM asset_categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function parseFieldConfig(?string $raw): array
    {
        if (empty($raw)) {
            return self::DEFAULT_FIELD_CONFIG;
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? array_merge(self::DEFAULT_FIELD_CONFIG, $decoded) : self::DEFAULT_FIELD_CONFIG;
    }

    // -------- Assets --------
    public function getAssets(array $filters = []): array
    {
        $sql = "SELECT a.*, c.name as category_name, a.created_by as creator_name
                FROM assets a
            LEFT JOIN asset_categories c ON a.category_id = c.id";
        $where = [];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = "a.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }
        if (!empty($filters['condition'])) {
            $where[] = "a.condition = ?";
            $params[] = $filters['condition'];
        }
        if (!empty($filters['lifecycle_status'])) {
            $where[] = "a.lifecycle_status = ?";
            $params[] = $filters['lifecycle_status'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(a.name LIKE ? OR a.description LIKE ? OR a.location_text LIKE ?)";
            $search = "%" . $filters['search'] . "%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        if (!empty($filters['maintenance_due'])) {
            $where[] = "a.next_maintenance_date <= CURDATE() AND a.lifecycle_status = 'active'";
        }

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY a.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAsset(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, c.name as category_name, a.created_by as creator_name
            FROM assets a
            LEFT JOIN asset_categories c ON a.category_id = c.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addAsset(array $data): bool
    {
        $sql = "INSERT INTO assets (
            category_id, name, description, plate_number, location_text, latitude, longitude,
            `condition`, acquisition_date, lifecycle_status,
            last_maintenance_date, next_maintenance_date,
            maintenance_interval_months, notes, created_by, updated_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['plate_number'] ?? null,
            $data['location_text'],
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $data['condition'] ?? 'good',
            $data['acquisition_date'] ?? null,
            $data['lifecycle_status'] ?? 'active',
            $data['last_maintenance_date'] ?? null,
            $data['next_maintenance_date'] ?? null,
            (int)($data['maintenance_interval_months'] ?? 0),
            $data['notes'] ?? null,
            $data['created_by'],
            $data['updated_by']
        ]);
    }

    public function updateAsset(int $id, array $data): bool
    {
        $sql = "UPDATE assets SET
            category_id = ?, name = ?, description = ?, plate_number = ?, location_text = ?,
            latitude = ?, longitude = ?, `condition` = ?, acquisition_date = ?,
            lifecycle_status = ?, last_maintenance_date = ?, next_maintenance_date = ?,
            maintenance_interval_months = ?, notes = ?, updated_by = ?
            WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['plate_number'] ?? null,
            $data['location_text'],
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $data['condition'] ?? 'good',
            $data['acquisition_date'] ?? null,
            $data['lifecycle_status'] ?? 'active',
            $data['last_maintenance_date'] ?? null,
            $data['next_maintenance_date'] ?? null,
            (int)($data['maintenance_interval_months'] ?? 0),
            $data['notes'] ?? null,
            $data['updated_by'],
            $id
        ]);
    }

    public function deleteAsset(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM assets WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // -------- Maintenance Logs --------
    public function getMaintenanceLogs(int $assetId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM asset_maintenance_logs WHERE asset_id = ? ORDER BY maintenance_date DESC");
        $stmt->execute([$assetId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMaintenanceLog(int $assetId, string $maintenanceDate, string $description, ?string $performedBy, ?float $cost, ?string $nextMaintenanceDate): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO asset_maintenance_logs (asset_id, maintenance_date, description, performed_by, cost, next_maintenance_date)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$assetId, $maintenanceDate, $description, $performedBy, $cost, $nextMaintenanceDate]);

            // Update asset dates
            $update = $this->db->prepare("UPDATE assets SET last_maintenance_date = ?, next_maintenance_date = ? WHERE id = ?");
            $update->execute([$maintenanceDate, $nextMaintenanceDate, $assetId]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to add maintenance log: " . $e->getMessage());
        }
    }

    // -------- Dashboard Stats --------
    public function getStats(): array
    {
        $stats = [];

        $stmt = $this->db->query("SELECT COUNT(*) FROM assets");
        $stats['total'] = (int)$stmt->fetchColumn();

        $stmt = $this->db->query("SELECT `condition`, COUNT(*) as count FROM assets GROUP BY `condition`");
        $conditionCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $stats['good'] = (int)($conditionCounts['good'] ?? 0);
        $stats['fair'] = (int)($conditionCounts['fair'] ?? 0);
        $stats['poor'] = (int)($conditionCounts['poor'] ?? 0);
        $stats['damaged'] = (int)($conditionCounts['damaged'] ?? 0);
        $stats['under_repair'] = (int)($conditionCounts['under_repair'] ?? 0);

        $stmt = $this->db->query("SELECT lifecycle_status, COUNT(*) as count FROM assets GROUP BY lifecycle_status");
        $lifecycleCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $stats['active'] = (int)($lifecycleCounts['active'] ?? 0);
        $stats['retired'] = (int)($lifecycleCounts['retired'] ?? 0);
        $stats['disposed'] = (int)($lifecycleCounts['disposed'] ?? 0);

        $stmt = $this->db->query("SELECT COUNT(*) FROM assets WHERE next_maintenance_date <= CURDATE() AND lifecycle_status = 'active'");
        $stats['maintenance_due'] = (int)$stmt->fetchColumn();

        return $stats;
    }

    // -------- Maintenance Statistics (for dashboard chart) --------
    /**
     * Returns buckets of maintenance activity for the given period.
     *
     * @param string $period today|week|month|year
     * @return array{period:string,labels:array<int,string>,counts:array<int,int>,costs:array<int,float>}
     */
    public function getMaintenanceStats(string $period = 'week'): array
    {
        $period = strtolower($period);
        if (!in_array($period, ['today', 'week', 'month', 'year'], true)) {
            $period = 'week';
        }

        $labels = [];
        $counts = [];
        $costs  = [];

        if ($period === 'today') {
            // Hourly buckets for the current day (00:00 → 23:00)
            $stmt = $this->db->prepare("
                SELECT HOUR(maintenance_date) AS bucket,
                       COUNT(*) AS cnt,
                       COALESCE(SUM(cost), 0) AS total
                FROM asset_maintenance_logs
                WHERE DATE(maintenance_date) = CURDATE()
                GROUP BY HOUR(maintenance_date)
            ");
            $stmt->execute();
            $map = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $map[(int)$r['bucket']] = ['cnt' => (int)$r['cnt'], 'cost' => (float)$r['total']];
            }
            for ($h = 0; $h < 24; $h++) {
                $labels[] = sprintf('%02d:00', $h);
                $counts[] = $map[$h]['cnt']  ?? 0;
                $costs[]  = $map[$h]['cost'] ?? 0.0;
            }
        } elseif ($period === 'week') {
            // Last 7 days
            $stmt = $this->db->prepare("
                SELECT DATE(maintenance_date) AS bucket,
                       COUNT(*) AS cnt,
                       COALESCE(SUM(cost), 0) AS total
                FROM asset_maintenance_logs
                WHERE maintenance_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                  AND maintenance_date <  DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                GROUP BY DATE(maintenance_date)
            ");
            $stmt->execute();
            $map = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $map[$r['bucket']] = ['cnt' => (int)$r['cnt'], 'cost' => (float)$r['total']];
            }
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $labels[] = date('D', strtotime($date));
                $counts[] = $map[$date]['cnt']  ?? 0;
                $costs[]  = $map[$date]['cost'] ?? 0.0;
            }
        } elseif ($period === 'month') {
            // Last 30 days
            $stmt = $this->db->prepare("
                SELECT DATE(maintenance_date) AS bucket,
                       COUNT(*) AS cnt,
                       COALESCE(SUM(cost), 0) AS total
                FROM asset_maintenance_logs
                WHERE maintenance_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
                  AND maintenance_date <  DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                GROUP BY DATE(maintenance_date)
            ");
            $stmt->execute();
            $map = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $map[$r['bucket']] = ['cnt' => (int)$r['cnt'], 'cost' => (float)$r['total']];
            }
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $labels[] = date('M d', strtotime($date));
                $counts[] = $map[$date]['cnt']  ?? 0;
                $costs[]  = $map[$date]['cost'] ?? 0.0;
            }
        } else { // year
            // Last 12 months
            $stmt = $this->db->prepare("
                SELECT DATE_FORMAT(maintenance_date, '%Y-%m') AS bucket,
                       COUNT(*) AS cnt,
                       COALESCE(SUM(cost), 0) AS total
                FROM asset_maintenance_logs
                WHERE maintenance_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                GROUP BY DATE_FORMAT(maintenance_date, '%Y-%m')
            ");
            $stmt->execute();
            $map = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $map[$r['bucket']] = ['cnt' => (int)$r['cnt'], 'cost' => (float)$r['total']];
            }
            for ($i = 11; $i >= 0; $i--) {
                $key = date('Y-m', strtotime("-$i months"));
                $labels[] = date('M Y', strtotime($key . '-01'));
                $counts[] = $map[$key]['cnt']  ?? 0;
                $costs[]  = $map[$key]['cost'] ?? 0.0;
            }
        }

        return [
            'period' => $period,
            'labels' => $labels,
            'counts' => $counts,
            'costs'  => $costs,
        ];
    }
}