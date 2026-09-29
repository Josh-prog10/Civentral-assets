<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/CemeteryService.php';

use App\Service\CemeteryService;

$cemeteryService = new CemeteryService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['cemetery'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$message = $error = '';
if (empty($_SESSION['cemetery_csrf_token'])) {
    $_SESSION['cemetery_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['cemetery_csrf_token'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        if (isset($_POST['add'])) {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $lat = $_POST['latitude'] !== '' ? (float)$_POST['latitude'] : null;
            $lng = $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;
            if ($name === '') {
                $error = 'Name is required.';
            } else {
                try {
                    if ($cemeteryService->addCemetery($name, $description, $address, $lat, $lng)) {
                        $message = 'Cemetery added.';
                    } else {
                        $error = 'Add failed.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['update'])) {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $lat = $_POST['latitude'] !== '' ? (float)$_POST['latitude'] : null;
            $lng = $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;
            if ($id < 1 || $name === '') {
                $error = 'Valid ID and name required.';
            } else {
                try {
                    if ($cemeteryService->updateCemetery($id, $name, $description, $address, $lat, $lng)) {
                        $message = 'Cemetery updated.';
                    } else {
                        $error = 'Update failed.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['delete'])) {
            $id = (int)($_POST['id'] ?? 0);
            if ($id < 1) {
                $error = 'Invalid ID.';
            } else {
                try {
                    if ($cemeteryService->deleteCemetery($id)) {
                        $message = 'Cemetery deleted.';
                    } else {
                        $error = 'Cannot delete a cemetery that has lots.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

$cemeteries = $cemeteryService->getCemeteries();

$pageTitle = 'Cemeteries';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-map-location-dot text-brand-medium mr-3"></i>Cemeteries
            </h1>
            <a href="<?= $basePath ?>pages/cemetery/cdb.php" class="text-sm font-bold text-brand-medium hover:text-brand-dark dark:hover:text-brand-light transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Map -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm mb-8">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Cemetery Locations</h2>
            <div id="cemeteryMap" style="height: 400px; border-radius: 12px; overflow: hidden;"></div>
        </div>

        <!-- Add / Edit Form -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm mb-8">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4" id="formTitle">Add Cemetery</h2>
            <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="cemeteryForm">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="id" id="editId" value="0">
                <input type="text" name="name" id="name" placeholder="Cemetery Name" required class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <input type="text" name="address" id="address" placeholder="Address" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <input type="text" name="description" id="description" placeholder="Description" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <input type="number" step="any" name="latitude" id="latitude" placeholder="Latitude" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <input type="number" step="any" name="longitude" id="longitude" placeholder="Longitude" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                <div class="flex gap-2 col-span-full">
                    <button type="submit" name="add" id="submitAdd" class="px-4 py-2 bg-brand-dark hover:bg-[#0e4f62] text-white font-bold text-sm rounded-lg transition shadow-sm">
                        <i class="fa-solid fa-plus mr-1"></i> Add
                    </button>
                    <button type="submit" name="update" id="submitUpdate" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg transition shadow-sm hidden">
                        <i class="fa-solid fa-pen mr-1"></i> Update
                    </button>
                    <button type="button" id="cancelEdit" class="px-4 py-2 bg-slate-300 dark:bg-slate-700 text-slate-800 dark:text-white font-bold text-sm rounded-lg hover:bg-slate-400 dark:hover:bg-slate-600 transition hidden">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        <!-- List -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Name</th>
                            <th class="px-6 py-3 text-left">Address</th>
                            <th class="px-6 py-3 text-left">Description</th>
                            <th class="px-6 py-3 text-left">Coordinates</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <?php foreach ($cemeteries as $cem): ?>
                            <tr>
                                <td class="px-6 py-3 font-mono text-slate-600 dark:text-slate-400"><?= $cem['id'] ?></td>
                                <td class="px-6 py-3 font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($cem['name']) ?></td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400">
                                    <?= htmlspecialchars($cem['address'] ?? '') // FIX: null coalescing ?>
                                </td>
                                <td class="px-6 py-3 text-slate-500 dark:text-slate-400 text-xs">
                                    <?= htmlspecialchars($cem['description'] ?? '') // FIX: null coalescing ?>
                                </td>
                                <td class="px-6 py-3 text-slate-600 dark:text-slate-400 text-xs">
                                    <?php if ($cem['latitude'] && $cem['longitude']): ?>
                                        <?= $cem['latitude'] ?>, <?= $cem['longitude'] ?>
                                    <?php else: ?>
                                        <span class="text-slate-400">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-3">
                                    <button class="editBtn px-2 py-1 bg-brand-medium text-white text-xs rounded hover:bg-brand-dark transition" 
                                            data-id="<?= $cem['id'] ?>"
                                            data-name="<?= htmlspecialchars($cem['name']) ?>"
                                            data-address="<?= htmlspecialchars($cem['address'] ?? '') ?>"
                                            data-description="<?= htmlspecialchars($cem['description'] ?? '') ?>"
                                            data-lat="<?= $cem['latitude'] ?>"
                                            data-lng="<?= $cem['longitude'] ?>">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    <form method="post" onsubmit="return confirm('Delete this cemetery? This will also remove all associated lots.')" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="id" value="<?= $cem['id'] ?>">
                                        <button type="submit" name="delete" class="px-2 py-1 bg-rose-500 text-white text-xs rounded hover:bg-rose-600 transition">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($cemeteries)): ?>
                            <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400 dark:text-slate-500">No cemeteries found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // ---------- Leaflet Map ----------
    const map = L.map('cemeteryMap').setView([14.5995, 120.9842], 12); // Default: Manila

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const markers = {};
    <?php foreach ($cemeteries as $cem): ?>
        <?php if ($cem['latitude'] && $cem['longitude']): ?>
            (function() {
                const lat = <?= $cem['latitude'] ?>;
                const lng = <?= $cem['longitude'] ?>;
                const marker = L.marker([lat, lng]).addTo(map)
                    .bindPopup("<?= addslashes(htmlspecialchars($cem['name'])) ?>");
                markers[<?= $cem['id'] ?>] = marker;
            })();
        <?php endif; ?>
    <?php endforeach; ?>

    // ---------- Form Edit / Cancel ----------
    const form = document.getElementById('cemeteryForm');
    const editId = document.getElementById('editId');
    const nameInput = document.getElementById('name');
    const addressInput = document.getElementById('address');
    const descriptionInput = document.getElementById('description');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const submitAdd = document.getElementById('submitAdd');
    const submitUpdate = document.getElementById('submitUpdate');
    const cancelEdit = document.getElementById('cancelEdit');
    const formTitle = document.getElementById('formTitle');

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function() {
            editId.value = this.dataset.id;
            nameInput.value = this.dataset.name;
            addressInput.value = this.dataset.address || '';
            descriptionInput.value = this.dataset.description || '';
            latInput.value = this.dataset.lat || '';
            lngInput.value = this.dataset.lng || '';
            submitAdd.classList.add('hidden');
            submitUpdate.classList.remove('hidden');
            cancelEdit.classList.remove('hidden');
            formTitle.textContent = 'Edit Cemetery';
        });
    });

    cancelEdit.addEventListener('click', function() {
        editId.value = 0;
        nameInput.value = '';
        addressInput.value = '';
        descriptionInput.value = '';
        latInput.value = '';
        lngInput.value = '';
        submitAdd.classList.remove('hidden');
        submitUpdate.classList.add('hidden');
        cancelEdit.classList.add('hidden');
        formTitle.textContent = 'Add Cemetery';
    });
</script>

<?php include $basePath . 'includes/footer.php'; ?>