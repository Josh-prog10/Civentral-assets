<?php
declare(strict_types=1);

namespace App\Service;

use PDO;
use PDOException;
use RuntimeException;

class WaterDrainageService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // -------- Categories --------
    public function getCategories(): array
    {
        $stmt = $this->db->query("SELECT * FROM wd_request_categories ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategory(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM wd_request_categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addCategory(string $name, string $description): bool
    {
        $stmt = $this->db->prepare("INSERT INTO wd_request_categories (name, description) VALUES (?, ?)");
        return $stmt->execute([$name, $description]);
    }

    public function updateCategory(int $id, string $name, string $description): bool
    {
        $stmt = $this->db->prepare("UPDATE wd_request_categories SET name=?, description=? WHERE id=?");
        return $stmt->execute([$name, $description, $id]);
    }

    public function deleteCategory(int $id): bool
    {
        // Check if used in requests
        $check = $this->db->prepare("SELECT COUNT(*) FROM wd_requests WHERE category_id = ?");
        $check->execute([$id]);
        if ($check->fetchColumn() > 0) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM wd_request_categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // -------- Requests --------
    public function getRequests(array $filters = []): array
    {
        $sql = "SELECT r.*, c.name as category_name, 
                    r.user_id as submitter_name,
                    r.assigned_to as assigned_name
                FROM wd_requests r
                LEFT JOIN wd_request_categories c ON r.category_id = c.id
            ";
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = "r.user_id = ?";
            $params[] = (int)$filters['user_id'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = "r.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['assigned_to'])) {
            $where[] = "r.assigned_to = ?";
            $params[] = (int)$filters['assigned_to'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "r.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "r.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        if (isset($filters['limit']) && is_numeric($filters['limit'])) {
            // We'll add LIMIT at the end
            $limit = (int)$filters['limit'];
        } else {
            $limit = null;
        }

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY r.created_at DESC";
        if ($limit !== null) {
            $sql .= " LIMIT " . $limit;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRequest(int $id): ?array
    {
        $stmt = $this->db->prepare(
                "SELECT r.*, c.name as category_name,
                    r.user_id as submitter_name,
                    r.assigned_to as assigned_name
            FROM wd_requests r
            LEFT JOIN wd_request_categories c ON r.category_id = c.id
            WHERE r.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function addRequest(int $categoryId, int $userId, string $title, string $description, string $locationText, ?float $lat, ?float $lng): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO wd_requests (category_id, user_id, title, description, location_text, latitude, longitude)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$categoryId, $userId, $title, $description, $locationText, $lat, $lng]);
    }

    public function updateRequest(int $id, int $categoryId, string $title, string $description, string $locationText, ?float $lat, ?float $lng): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE wd_requests SET category_id=?, title=?, description=?, location_text=?, latitude=?, longitude=? WHERE id=?"
        );
        return $stmt->execute([$categoryId, $title, $description, $locationText, $lat, $lng, $id]);
    }

    public function updateRequestStatus(int $id, string $status): bool
    {
        $allowed = ['pending', 'assigned', 'in_progress', 'resolved', 'rejected'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE wd_requests SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function assignRequest(int $id, ?int $assignedTo): bool
    {
        if ($assignedTo === null) {
            $stmt = $this->db->prepare("UPDATE wd_requests SET assigned_to = NULL, status = 'pending' WHERE id = ?");
            return $stmt->execute([$id]);
        } else {
            $stmt = $this->db->prepare("UPDATE wd_requests SET assigned_to = ?, status = 'assigned' WHERE id = ?");
            return $stmt->execute([$assignedTo, $id]);
        }
    }

    public function addAdminNote(int $id, string $note): bool
    {
        $current = $this->getRequest($id);
        if (!$current) {
            return false;
        }
        $oldNote = $current['admin_notes'] ?? '';
        $newNote = date('Y-m-d H:i:s') . " - " . $note . "\n" . $oldNote;
        $stmt = $this->db->prepare("UPDATE wd_requests SET admin_notes = ? WHERE id = ?");
        return $stmt->execute([$newNote, $id]);
    }

    public function deleteRequest(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM wd_requests WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // -------- Stats --------
    public function getStats(): array
    {
        $stats = [];
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM wd_requests GROUP BY status");
        $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $stats['total'] = array_sum($statusCounts);
        $stats['pending'] = (int)($statusCounts['pending'] ?? 0);
        $stats['assigned'] = (int)($statusCounts['assigned'] ?? 0);
        $stats['in_progress'] = (int)($statusCounts['in_progress'] ?? 0);
        $stats['resolved'] = (int)($statusCounts['resolved'] ?? 0);
        $stats['rejected'] = (int)($statusCounts['rejected'] ?? 0);

        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM wd_requests WHERE DATE(created_at) = ?");
        $stmt->execute([$today]);
        $stats['today_new'] = (int)$stmt->fetchColumn();

        return $stats;
    }

    /**
     * Returns trend data for the requests line chart.
     * Periods: today (hourly), week (last 7 days), month (last 30 days), year (12 months).
     */
    public function getTrendData(string $period = 'week'): array
    {
        if (!in_array($period, ['today', 'week', 'month', 'year'], true)) {
            $period = 'week';
        }

        $labels = [];
        $data   = [];

        if ($period === 'today') {
            for ($h = 0; $h < 24; $h++) {
                $labels[] = sprintf('%02d:00', $h);
                $data[$h] = 0;
            }
            $stmt = $this->db->prepare(
                "SELECT HOUR(created_at) AS k, COUNT(*) AS c
                 FROM wd_requests
                 WHERE DATE(created_at) = ?
                 GROUP BY HOUR(created_at)"
            );
            $stmt->execute([date('Y-m-d')]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[(int)$row['k']] = (int)$row['c'];
            }
            $data = array_values($data);

        } elseif ($period === 'week') {
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-{$i} days"));
                $labels[] = date('D', strtotime($d));
                $data[$d] = 0;
            }
            $stmt = $this->db->prepare(
                "SELECT DATE(created_at) AS k, COUNT(*) AS c
                 FROM wd_requests
                 WHERE DATE(created_at) BETWEEN ? AND ?
                 GROUP BY DATE(created_at)"
            );
            $stmt->execute([date('Y-m-d', strtotime('-6 days')), date('Y-m-d')]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (isset($data[$row['k']])) {
                    $data[$row['k']] = (int)$row['c'];
                }
            }
            $data = array_values($data);

        } elseif ($period === 'month') {
            for ($i = 29; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-{$i} days"));
                $labels[] = date('M j', strtotime($d));
                $data[$d] = 0;
            }
            $stmt = $this->db->prepare(
                "SELECT DATE(created_at) AS k, COUNT(*) AS c
                 FROM wd_requests
                 WHERE DATE(created_at) BETWEEN ? AND ?
                 GROUP BY DATE(created_at)"
            );
            $stmt->execute([date('Y-m-d', strtotime('-29 days')), date('Y-m-d')]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (isset($data[$row['k']])) {
                    $data[$row['k']] = (int)$row['c'];
                }
            }
            $data = array_values($data);

        } else { // year
            $year = (int)date('Y');
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = date('M', mktime(0, 0, 0, $m, 1, $year));
                $data[$m] = 0;
            }
            $stmt = $this->db->prepare(
                "SELECT MONTH(created_at) AS k, COUNT(*) AS c
                 FROM wd_requests
                 WHERE YEAR(created_at) = ?
                 GROUP BY MONTH(created_at)"
            );
            $stmt->execute([$year]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[(int)$row['k']] = (int)$row['c'];
            }
            $data = array_values($data);
        }

        return [
            'period' => $period,
            'labels' => $labels,
            'data'   => $data,
            'total'  => array_sum($data),
        ];
    }

    // -------- Staff Users --------
    public function getStaffUsers(): array
    {
        require_once __DIR__ . '/../config/proxy.php';

        $apiBaseUrl = getenv('EXPO_PUBLIC_API_BASE_URL') ?: 'https://civentral.tech/api/employee';
        $result = \proxyRequest(rtrim($apiBaseUrl, '/') . '/users.php', 'GET', null);
        if ($result['code'] < 200 || $result['code'] >= 300 || empty($result['body'])) {
            return [];
        }

        $users = $result['body']['data'] ?? $result['body']['users'] ?? [];
        if (!is_array($users)) {
            return [];
        }

        $staff = [];
        foreach ($users as $user) {
            $userId = $user['id'] ?? $user['user_id'] ?? null;
            if ($userId === null) {
                continue;
            }

            $fullName = $user['full_name'] ?? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            $staff[] = [
                'id' => (int)$userId,
                'full_name' => $fullName !== '' ? $fullName : 'User #' . (int)$userId
            ];
        }

        usort($staff, static fn(array $left, array $right): int => strcasecmp($left['full_name'], $right['full_name']));
        return $staff;
    }

    public function addAttachment(int $requestId, array $file, string $uploadDir): int|false
    {
        $stmt = $this->db->prepare("SELECT id FROM wd_requests WHERE id = ?");
        $stmt->execute([$requestId]);
        if (!$stmt->fetch()) {
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mimeType, $allowedMimes, true)) {
            return false;
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            return false;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetPath = rtrim($uploadDir, '/') . '/' . $newFilename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return false;
        }

        $relativePath = 'uploads/water/' . $newFilename;
        $stmt = $this->db->prepare(
            "INSERT INTO wd_request_attachments (request_id, filename, original_name, file_path, mime_type, size)
            VALUES (?, ?, ?, ?, ?, ?)"
        );
        $success = $stmt->execute([
            $requestId,
            $newFilename,
            $file['name'],
            $relativePath,
            $mimeType,
            $file['size']
        ]);

        return $success ? (int)$this->db->lastInsertId() : false;
    }

    public function getAttachments(int $requestId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM wd_request_attachments WHERE request_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$requestId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAttachment(int $attachmentId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM wd_request_attachments WHERE id = ?");
        $stmt->execute([$attachmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function deleteAttachment(int $attachmentId): bool
    {
        $attachment = $this->getAttachment($attachmentId);
        if (!$attachment) {
            return false;
        }

        $fullPath = __DIR__ . '/../public/' . $attachment['file_path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $stmt = $this->db->prepare("DELETE FROM wd_request_attachments WHERE id = ?");
        return $stmt->execute([$attachmentId]);
    }
}