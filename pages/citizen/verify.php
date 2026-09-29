<?php
require_once __DIR__ . '/../../src/bootstrap.php';

$pageTitle = 'Citizen Verification';
include $basePath . 'includes/header.php';
include $basePath . 'includes/sidebar.php';
?>

<div class="flex-1 p-6 lg:p-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
            <h1 class="text-2xl font-black text-slate-800 tracking-tight mb-3">
                <i class="fa-solid fa-user-check text-brand-medium mr-2"></i>Citizen Verification
            </h1>
            <p class="text-sm text-slate-600 leading-6">
                This verification page is available as a placeholder for citizen validation flows.
                Connect it to the appropriate verification logic or redirect it to the real citizen account screen as needed.
            </p>
        </div>
    </div>
</div>

<?php include $basePath . 'includes/footer.php'; ?>
