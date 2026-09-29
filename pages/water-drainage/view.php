<?php
declare(strict_types=1);

$basePath = '../../';
require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/WaterDrainageService.php';

use App\Service\WaterDrainageService;

$wdService = new WaterDrainageService($pdo);

if (!$isSuperAdmin && !$hasResourceAccess(['water_drainage', 'water drainage', 'water supply', 'drainage'])) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$request = $wdService->getRequest($id);
if (!$request) {
    header('Location: ' . $basePath . 'pages/water-drainage/requests.php?error=not_found');
    exit;
}

// Permission: if not admin, user can only view their own
if (!$isSuperAdmin && !$hasResourceAccess(['water_drainage_manage']) && $request['user_id'] != ($headerUser['id'] ?? ($_SESSION['user_id'] ?? null))) {
    header('Location: ' . $basePath . 'pages/dashboard.php?error=access_denied');
    exit;
}

$message = $error = '';
if (empty($_SESSION['water_drainage_csrf_token'])) {
    $_SESSION['water_drainage_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['water_drainage_csrf_token'];
$staff = $wdService->getStaffUsers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $error = 'Invalid request.';
    } else {
        if (isset($_POST['update_status'])) {
            $status = $_POST['status'] ?? '';
            if (!in_array($status, ['pending','assigned','in_progress','resolved','rejected'], true)) {
                $error = 'Invalid status.';
            } else {
                try {
                    if ($wdService->updateRequestStatus($id, $status)) {
                        $message = 'Status updated to ' . ucfirst(str_replace('_',' ', $status));
                    } else {
                        $error = 'Failed to update status.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['assign'])) {
            $assignedTo = isset($_POST['assigned_to']) && $_POST['assigned_to'] !== '' ? (int)$_POST['assigned_to'] : null;
            try {
                if ($wdService->assignRequest($id, $assignedTo)) {
                    $message = $assignedTo ? 'Request assigned successfully.' : 'Request unassigned.';
                } else {
                    $error = 'Assignment failed.';
                }
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        } elseif (isset($_POST['add_note'])) {
            $note = trim($_POST['admin_note'] ?? '');
            if ($note === '') {
                $error = 'Note cannot be empty.';
            } else {
                try {
                    if ($wdService->addAdminNote($id, $note)) {
                        $message = 'Note added.';
                    } else {
                        $error = 'Failed to add note.';
                    }
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif (isset($_POST['delete'])) {
            try {
                if ($wdService->deleteRequest($id)) {
                    header('Location: ' . $basePath . 'pages/water-drainage/requests.php?message=deleted');
                    exit;
                } else {
                    $error = 'Delete failed.';
                }
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        } elseif (isset($_POST['delete_attachment'])) {
            // ----- NEW: Delete attachment -----
            $attachmentId = (int)$_POST['delete_attachment'];
            try {
                if ($wdService->deleteAttachment($attachmentId)) {
                    $message = 'Attachment deleted.';
                } else {
                    $error = 'Failed to delete attachment.';
                }
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
        // Refresh request data
        $request = $wdService->getRequest($id);
    }
}

$pageTitle = 'Request #' . $id;
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<!-- Leaflet for map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    #map { height: 250px; border-radius: 0.5rem; border: 1px solid #cbd5e1; }
    .dark #map { border-color: #334155; }
</style>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">
                <i class="fa-solid fa-file-lines text-brand-medium mr-3"></i>Request #<?= $id ?>
            </h1>
            <a href="<?= $basePath ?>pages/water-drainage/requests.php" class="text-sm font-bold text-brand-medium hover:text-brand-dark dark:hover:text-brand-light transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>

        <?php if ($message): ?>
            <div class="mb-4 p-3 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-sm font-bold"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm space-y-4">
            <!-- Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Title</p>
                    <p class="font-medium text-slate-800 dark:text-white"><?= htmlspecialchars($request['title']) ?></p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Category</p>
                    <p class="font-medium text-slate-800 dark:text-white"><?= htmlspecialchars($request['category_name']) ?></p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Status</p>
                    <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-full
                        <?php
                        $statusClass = match ($request['status']) {
                            'pending'     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                            'assigned'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                            'in_progress' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                            'resolved'    => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                            'rejected'    => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                            default       => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                        };
                        echo $statusClass;
                        ?>">
                        <?= ucfirst(str_replace('_',' ', $request['status'])) ?>
                    </span>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Submitted By</p>
                    <p class="font-medium text-slate-800 dark:text-white"><?= htmlspecialchars((string)$request['submitter_name']) ?></p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Assigned To</p>
                    <p class="font-medium text-slate-800 dark:text-white"><?= htmlspecialchars((string)($request['assigned_name'] ?? 'Unassigned')) ?></p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Submitted On</p>
                    <p class="font-medium text-slate-800 dark:text-white"><?= date('F d, Y H:i', strtotime($request['created_at'])) ?></p>
                </div>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Description</p>
                <p class="text-slate-700 dark:text-slate-300"><?= nl2br(htmlspecialchars($request['description'])) ?></p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Location</p>
                <p class="text-slate-700 dark:text-slate-300"><?= htmlspecialchars($request['location_text']) ?></p>
                <?php if ($request['latitude'] && $request['longitude']): ?>
                    <div id="map" class="mt-2"></div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var lat = <?= $request['latitude'] ?>;
                            var lng = <?= $request['longitude'] ?>;
                            var map = L.map('map').setView([lat, lng], 15);
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '© OpenStreetMap'
                            }).addTo(map);
                            L.marker([lat, lng]).addTo(map);
                        });
                    </script>
                <?php endif; ?>
            </div>

            <?php if ($isSuperAdmin || $hasResourceAccess(['water_drainage_manage'])): ?>
                <!-- Admin Actions -->
                <hr class="border-slate-200 dark:border-slate-800">
                <div class="space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white">Admin Actions</h3>
                    <div class="flex flex-wrap gap-4">
                        <!-- Update Status -->
                        <form method="post" class="flex items-center gap-2">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <select name="status" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                                <option value="pending" <?= $request['status']=='pending'?'selected':'' ?>>Pending</option>
                                <option value="assigned" <?= $request['status']=='assigned'?'selected':'' ?>>Assigned</option>
                                <option value="in_progress" <?= $request['status']=='in_progress'?'selected':'' ?>>In Progress</option>
                                <option value="resolved" <?= $request['status']=='resolved'?'selected':'' ?>>Resolved</option>
                                <option value="rejected" <?= $request['status']=='rejected'?'selected':'' ?>>Rejected</option>
                            </select>
                            <button type="submit" name="update_status" class="px-4 py-1.5 bg-brand-medium text-white text-sm font-bold rounded-lg hover:bg-brand-dark transition">Update Status</button>
                        </form>

                        <!-- Assign -->
                        <form method="post" class="flex items-center gap-2">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <select name="assigned_to" class="px-3 py-1.5 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                                <option value="">Unassign</option>
                                <?php foreach ($staff as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ($request['assigned_to']==$s['id'])?'selected':'' ?>><?= htmlspecialchars($s['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="assign" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition">Assign</button>
                        </form>

                        <!-- Delete -->
                        <form method="post" onsubmit="return confirm('Delete this request?')">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button type="submit" name="delete" class="px-4 py-1.5 bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold rounded-lg transition">Delete</button>
                        </form>
                    </div>

                    <!-- Admin Notes -->
                    <div>
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Admin Notes</p>
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap max-h-40 overflow-y-auto">
                            <?= nl2br(htmlspecialchars($request['admin_notes'] ?? 'No notes yet.')) ?>
                        </div>
                        <form method="post" class="mt-2 flex gap-2">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="text" name="admin_note" placeholder="Add a note..." class="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm focus:ring-2 focus:ring-brand-medium focus:outline-none">
                            <button type="submit" name="add_note" class="px-4 py-2 bg-brand-dark hover:bg-[#0e4f62] text-white text-sm font-bold rounded-lg transition">Add Note</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ===== NEW: Attachments Section ===== -->
            <hr class="border-slate-200 dark:border-slate-800">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white mb-2">Attachments</h3>
                <?php
                $attachments = $wdService->getAttachments($id);
                if ($attachments): ?>
                    <ul class="space-y-2">
                        <?php foreach ($attachments as $att): ?>
                            <li class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-paperclip text-slate-400"></i>
                                    <a href="<?= $basePath ?>pages/water-drainage/download_attachment.php?id=<?= $att['id'] ?>" 
                                        class="text-brand-medium hover:underline font-medium text-sm">
                                        <?= htmlspecialchars($att['original_name']) ?>
                                    </a>
                                    <span class="text-xs text-slate-400">(<?= number_format($att['size'] / 1024, 1) ?> KB)</span>
                                </div>
                                <?php if ($isSuperAdmin || $hasResourceAccess(['water_drainage_manage'])): ?>
                                    <form method="post" onsubmit="return confirm('Delete this attachment?')" class="inline">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <input type="hidden" name="delete_attachment" value="<?= $att['id'] ?>">
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 text-sm font-bold">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-sm text-slate-500 dark:text-slate-400">No attachments uploaded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>