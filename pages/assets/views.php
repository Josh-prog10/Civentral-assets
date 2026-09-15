<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/AssetService.php';

use App\Service\AssetService;

$assetService = new AssetService($pdo);

// Use the same permission set as add.php and edit.php
$assetManageKeywords = ['assets_manage', 'asset', 'asset management', 'asset inventory', 'asset inventory tracker'];

// Check access
if (!$isSuperAdmin && !$hasResourceAccess($assetManageKeywords)) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$asset = $assetService->getAsset($id);
if (!$asset) {
    header('Location: ' . $basePath . 'pages/assets/adb.php?error=not_found');
    exit;
}

// ---- Load this asset's category field permissions ----
$category = $asset['category_id'] ? $assetService->getCategory((int)$asset['category_id']) : null;
$fieldConfig = $category['field_config_parsed'] ?? [
    'plate_number'     => false,
    'coordinates'      => true,
    'gis_map'          => true,
    'maintenance'      => true,
    'acquisition_date' => true,
    'notes'            => true,
];
$showPlate        = !empty($fieldConfig['plate_number']);
$showCoordinates  = !empty($fieldConfig['coordinates']);
$showGisMap       = !empty($fieldConfig['gis_map']);
$showMaintenance  = !empty($fieldConfig['maintenance']);
$showAcquisition  = !empty($fieldConfig['acquisition_date']);
$showNotes        = !empty($fieldConfig['notes']);

$logs = $assetService->getMaintenanceLogs($id);
$message = $error = '';

// --- CSRF token ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($isSuperAdmin || $hasResourceAccess($assetManageKeywords))) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } elseif (isset($_POST['add_maintenance'])) {
        $maintenanceDate = $_POST['maintenance_date'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $performedBy = trim($_POST['performed_by'] ?? '');
        $cost = isset($_POST['cost']) && $_POST['cost'] !== '' ? (float)$_POST['cost'] : null;
        $nextMaintenance = $_POST['next_maintenance_date'] ?: null;

        if (empty($maintenanceDate) || empty($description)) {
            $error = "Maintenance date and description are required.";
        } else {
            try {
                if ($assetService->addMaintenanceLog($id, $maintenanceDate, $description, $performedBy, $cost, $nextMaintenance)) {
                    $message = "Maintenance log added.";
                    $asset = $assetService->getAsset($id);
                    $logs = $assetService->getMaintenanceLogs($id);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $error = "Failed to add maintenance log.";
                }
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Asset #' . $id;
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    #map { height: 320px; border-radius: 0.5rem; border: 1px solid #cbd5e1; }
    .dark #map { border-color: #334155; }
</style>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-cube text-brand-medium mr-3"></i><?= htmlspecialchars($asset['name']) ?>
            </h1>
            <div class="flex gap-3">
                <?php if ($isSuperAdmin || $hasResourceAccess($assetManageKeywords)): ?>
                    <a href="<?= $basePath ?>pages/assets/edit.php?id=<?= $asset['id'] ?>" class="text-sm font-bold bg-brand-dark hover:bg-[#0e4f62] text-white px-4 py-2 rounded-lg transition shadow-sm">
                        <i class="fa-solid fa-pen mr-1"></i> Edit
                    </a>
                <?php endif; ?>
                <a href="<?= $basePath ?>pages/assets/adb.php" class="text-sm font-bold text-brand-medium hover:text-brand-dark dark:hover:text-brand-light transition">Back</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Asset Details -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-3">
                <h2 class="font-bold text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">Details</h2>
                <div><span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Category:</span> <?= htmlspecialchars((string)$asset['category_name']) ?></div>
                <div><span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Description:</span> <?= nl2br(htmlspecialchars((string)$asset['description'])) ?></div>

                <?php if ($showPlate): ?>
                    <div><span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">License Plate:</span> <?= htmlspecialchars((string)($asset['plate_number'] ?? 'N/A')) ?></div>
                <?php endif; ?>

                <div><span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Location:</span> <?= htmlspecialchars((string)$asset['location_text']) ?></div>
                <div>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Condition:</span>
                    <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full
                        <?php
                        $conditionClass = match ($asset['condition']) {
                            'good' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                            'fair' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                            'poor' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                            'damaged' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                            'under_repair' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                        };
                        echo $conditionClass;
                        ?>">
                        <?= ucfirst(str_replace('_',' ', (string)$asset['condition'])) ?>
                    </span>
                </div>
                <div><span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Lifecycle Status:</span> <?= ucfirst((string)$asset['lifecycle_status']) ?></div>

                <?php if ($showAcquisition): ?>
                    <div><span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Acquisition Date:</span> <?= $asset['acquisition_date'] ? date('F d, Y', strtotime($asset['acquisition_date'])) : 'N/A' ?></div>
                <?php endif; ?>

                <?php if ($showMaintenance): ?>
                    <div><span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Last Maintenance:</span> <?= $asset['last_maintenance_date'] ? date('F d, Y', strtotime($asset['last_maintenance_date'])) : 'N/A' ?></div>
                    <div><span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Next Maintenance:</span> <?= $asset['next_maintenance_date'] ? date('F d, Y', strtotime($asset['next_maintenance_date'])) : 'N/A' ?>
                        <?php if ($asset['next_maintenance_date'] && strtotime($asset['next_maintenance_date']) <= time() && $asset['lifecycle_status'] == 'active'): ?>
                            <span class="text-rose-500 text-xs font-bold ml-1"><i class="fa-solid fa-triangle-exclamation"></i> Due</span>
                        <?php endif; ?>
                    </div>
                    <div><span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Maintenance Interval:</span> <?= (int)$asset['maintenance_interval_months'] ?> months</div>
                <?php endif; ?>

                <?php if ($showNotes): ?>
                    <div><span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Notes:</span> <?= nl2br(htmlspecialchars((string)$asset['notes'])) ?></div>
                <?php endif; ?>

                <div><span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Created:</span> <?= date('F d, Y H:i', strtotime($asset['created_at'])) ?> by <?= htmlspecialchars((string)$asset['creator_name']) ?></div>
            </div>

            <!-- Map (only if GIS map enabled for this category) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
                <h2 class="font-bold text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2 mb-3">Location Map</h2>
                <?php if ($showGisMap && $asset['latitude'] && $asset['longitude']): ?>
                    <div id="map"></div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var lat = <?= $asset['latitude'] ?>;
                            var lng = <?= $asset['longitude'] ?>;
                            var map = L.map('map').setView([lat, lng], 15);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '© OpenStreetMap'
                            }).addTo(map);
                            L.marker([lat, lng]).addTo(map);
                        });
                    </script>
                <?php elseif (!$showGisMap): ?>
                    <p class="text-slate-500 dark:text-slate-400 italic">GIS map is not enabled for this category.</p>
                <?php elseif (!$showCoordinates): ?>
                    <p class="text-slate-500 dark:text-slate-400 italic">Coordinates are not enabled for this category.</p>
                <?php else: ?>
                    <p class="text-slate-500 dark:text-slate-400">No coordinates provided.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($showMaintenance): ?>
        <!-- Maintenance Logs (only if maintenance enabled for this category) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mt-6">
            <h2 class="font-bold text-slate-800 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2 mb-4">Maintenance History</h2>

            <?php if ($isSuperAdmin || $hasResourceAccess($assetManageKeywords)): ?>
                <div class="mb-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Add Maintenance Log</h3>
                    <form method="post" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Date</label>
                            <input type="date" name="maintenance_date" required class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Performed By</label>
                            <input type="text" name="performed_by" class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Cost (₱)</label>
                            <input type="number" name="cost" step="0.01" class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Next Maintenance Date</label>
                            <input type="date" name="next_maintenance_date" class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">Description</label>
                            <textarea name="description" rows="2" required class="w-full px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" name="add_maintenance" class="px-4 py-1.5 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-sm rounded-lg transition">Add Log</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($logs): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-2 text-left">Date</th>
                                <th class="px-4 py-2 text-left">Description</th>
                                <th class="px-4 py-2 text-left">Performed By</th>
                                <th class="px-4 py-2 text-left">Cost</th>
                                <th class="px-4 py-2 text-left">Next Maintenance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-300"><?= date('M d, Y', strtotime($log['maintenance_date'])) ?></td>
                                    <td class="px-4 py-2 text-slate-600 dark:text-slate-400"><?= htmlspecialchars($log['description']) ?></td>
                                    <td class="px-4 py-2 text-slate-600 dark:text-slate-400"><?= htmlspecialchars((string)$log['performed_by']) ?></td>
                                    <td class="px-4 py-2 text-slate-600 dark:text-slate-400"><?= $log['cost'] ? '₱' . number_format((float)$log['cost'], 2) : '-' ?></td>
                                    <td class="px-4 py-2 text-slate-600 dark:text-slate-400"><?= $log['next_maintenance_date'] ? date('M d, Y', strtotime($log['next_maintenance_date'])) : 'N/A' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-slate-500 dark:text-slate-400">No maintenance logs recorded.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>