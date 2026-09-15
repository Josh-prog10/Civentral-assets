<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/AssetService.php';

use App\Service\AssetService;

$assetService = new AssetService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['assets_manage', 'asset', 'asset management', 'asset inventory', 'asset inventory tracker'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$asset = $assetService->getAsset($id);
if (!$asset) {
    header('Location: ' . $basePath . 'pages/assets/adb.php?error=not_found');
    exit;
}

$categories = $assetService->getCategories();
$message = $error = '';

// --- CSRF token ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        $data = [
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'plate_number' => trim($_POST['plate_number'] ?? ''),
            'location_text' => trim($_POST['location_text'] ?? ''),
            'latitude' => isset($_POST['latitude']) && $_POST['latitude'] !== '' ? (float)$_POST['latitude'] : null,
            'longitude' => isset($_POST['longitude']) && $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null,
            'condition' => $_POST['condition'] ?? 'good',
            'acquisition_date' => $_POST['acquisition_date'] ?: null,
            'lifecycle_status' => $_POST['lifecycle_status'] ?? 'active',
            'last_maintenance_date' => $_POST['last_maintenance_date'] ?: null,
            'next_maintenance_date' => $_POST['next_maintenance_date'] ?: null,
            'maintenance_interval_months' => (int)($_POST['maintenance_interval_months'] ?? 0),
            'notes' => trim($_POST['notes'] ?? ''),
            'updated_by' => $headerUser['id']
        ];

        if (empty($data['category_id']) || empty($data['name']) || empty($data['location_text'])) {
            $error = "Category, Name, and Location are required.";
        } else {
            try {
                if ($assetService->updateAsset($id, $data)) {
                    $message = "Asset updated successfully!";
                    $asset = $assetService->getAsset($id);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $error = "Failed to update asset.";
                }
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Edit Asset';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    #map { height: 280px; border-radius: 0.5rem; border: 1px solid #cbd5e1; }
    .dark #map { border-color: #334155; }
</style>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight mb-6">
            <i class="fa-solid fa-pen-to-square text-brand-medium mr-3"></i>Edit Asset
        </h1>

        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm">
            <form method="post" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <!-- Category -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Category *</label>
                    <select name="category_id" id="category_id" required class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="">Select category</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($asset['category_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Name -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Asset Name *</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($asset['name']) ?>" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none" placeholder="e.g. Laptop #123">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none" placeholder="Optional details"><?= htmlspecialchars((string)$asset['description']) ?></textarea>
                </div>

                <!-- Plate Number (conditional) -->
                <div id="field-plate_number" class="hidden">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">License Plate (for vehicles)</label>
                    <input type="text" name="plate_number" value="<?= htmlspecialchars((string)($asset['plate_number'] ?? '')) ?>" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none" placeholder="e.g. ABC-1234">
                </div>

                <!-- Location -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Location *</label>
                    <input type="text" name="location_text" required value="<?= htmlspecialchars($asset['location_text']) ?>" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none" placeholder="e.g. Building A, Room 202">
                </div>

                <!-- Coordinates (conditional - lat/lng inputs) -->
                <div id="field-coordinates">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Coordinates (optional)</label>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="number" step="any" name="latitude" id="latitude" value="<?= $asset['latitude'] ?? '' ?>" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none" placeholder="Latitude">
                        <input type="number" step="any" name="longitude" id="longitude" value="<?= $asset['longitude'] ?? '' ?>" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none" placeholder="Longitude">
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Enter latitude/longitude manually, or use the map below.</p>
                </div>

                <!-- GIS Map (conditional - the map widget itself) -->
                <div id="field-gis_map">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">GIS Map</label>
                    <div id="map"></div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Click on the map to set coordinates.</p>
                </div>

                <!-- Condition -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Condition</label>
                    <select name="condition" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="good" <?= ($asset['condition'] == 'good') ? 'selected' : '' ?>>Good</option>
                        <option value="fair" <?= ($asset['condition'] == 'fair') ? 'selected' : '' ?>>Fair</option>
                        <option value="poor" <?= ($asset['condition'] == 'poor') ? 'selected' : '' ?>>Poor</option>
                        <option value="damaged" <?= ($asset['condition'] == 'damaged') ? 'selected' : '' ?>>Damaged</option>
                        <option value="under_repair" <?= ($asset['condition'] == 'under_repair') ? 'selected' : '' ?>>Under Repair</option>
                    </select>
                </div>

                <!-- Lifecycle Status -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Lifecycle Status</label>
                    <select name="lifecycle_status" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        <option value="active" <?= ($asset['lifecycle_status'] == 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="retired" <?= ($asset['lifecycle_status'] == 'retired') ? 'selected' : '' ?>>Retired</option>
                        <option value="disposed" <?= ($asset['lifecycle_status'] == 'disposed') ? 'selected' : '' ?>>Disposed</option>
                    </select>
                </div>

                <!-- Acquisition Date (conditional) -->
                <div id="field-acquisition_date">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Acquisition Date</label>
                    <input type="date" name="acquisition_date" value="<?= $asset['acquisition_date'] ?>" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                </div>

                <!-- Maintenance (conditional) -->
                <div id="field-maintenance" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Last Maintenance Date</label>
                            <input type="date" name="last_maintenance_date" value="<?= $asset['last_maintenance_date'] ?>" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Next Maintenance Date</label>
                            <input type="date" name="next_maintenance_date" value="<?= $asset['next_maintenance_date'] ?>" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Maintenance Interval (months)</label>
                        <input type="number" name="maintenance_interval_months" min="0" value="<?= $asset['maintenance_interval_months'] ?>" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none" placeholder="0 = none">
                    </div>
                </div>

                <!-- Notes (conditional) -->
                <div id="field-notes">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none" placeholder="Any additional information"><?= htmlspecialchars((string)$asset['notes']) ?></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-6 py-2 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-sm rounded-lg transition shadow-sm">
                        <i class="fa-solid fa-check mr-1"></i> Update Asset
                    </button>
                    <a href="<?= $basePath ?>pages/assets/views.php?id=<?= $asset['id'] ?>" class="px-6 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-sm rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Per-category field permissions, injected from PHP (category_id => config)
    var CATEGORY_FIELD_CONFIG = <?= json_encode(array_column($categories, 'field_config_parsed', 'id')) ?>;
    var DEFAULT_FIELD_CONFIG = {
        plate_number: false,
        coordinates: true,
        gis_map: true,
        maintenance: true,
        acquisition_date: true,
        notes: true
    };

    document.addEventListener('DOMContentLoaded', function() {
        // ---- Map setup ----
        var initLat = <?= json_encode((float)($asset['latitude'] ?? 12.8797)) ?>;
        var initLng = <?= json_encode((float)($asset['longitude'] ?? 121.7740)) ?>;
        var map = L.map('map').setView([initLat, initLng], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var marker = null;
        if (!isNaN(initLat) && !isNaN(initLng) && initLat != 0 && initLng != 0) {
            marker = L.marker([initLat, initLng]).addTo(map);
            map.setView([initLat, initLng], 15);
        }

        function setMarker(lat, lng) {
            if (marker) marker.setLatLng([lat, lng]);
            else marker = L.marker([lat, lng]).addTo(map);
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        }
        map.on('click', function(e) { setMarker(e.latlng.lat, e.latlng.lng); });

        var latInput = document.getElementById('latitude');
        var lngInput = document.getElementById('longitude');
        function updateMapFromInputs() {
            var lat = parseFloat(latInput.value);
            var lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                map.setView([lat, lng], 15);
                setMarker(lat, lng);
            }
        }
        latInput.addEventListener('change', updateMapFromInputs);
        lngInput.addEventListener('change', updateMapFromInputs);

        // ---- Field permission toggling (edit mode keeps existing values) ----
        function toggleField(key, show) {
            var el = document.getElementById('field-' + key);
            if (!el) return;
            el.classList.toggle('hidden', !show);
        }
        function applyFieldConfig() {
            var catId = document.getElementById('category_id').value;
            var cfg = (catId && CATEGORY_FIELD_CONFIG[catId]) ? CATEGORY_FIELD_CONFIG[catId] : DEFAULT_FIELD_CONFIG;

            toggleField('plate_number',     !!cfg.plate_number);
            toggleField('coordinates',      !!cfg.coordinates);
            toggleField('gis_map',          !!cfg.gis_map);
            toggleField('maintenance',      !!cfg.maintenance);
            toggleField('acquisition_date', !!cfg.acquisition_date);
            toggleField('notes',            !!cfg.notes);

            if (cfg.gis_map) {
                setTimeout(function() { map.invalidateSize(); }, 50);
            }
        }
        document.getElementById('category_id').addEventListener('change', applyFieldConfig);
        applyFieldConfig();
    });
</script>

<?php include $basePath . 'includes/footer.php'; ?>