<?php
declare(strict_types=1);
require_once __DIR__.'/bootstrap.php';

use AdvancedLG\LookingGlass;

// Configuration validation and session initialization
LookingGlass::validateConfig();
LookingGlass::startSession();

// Creating CSRF tokens
if (empty($_SESSION[LookingGlass::SESSION_CSRF])) {
    $_SESSION[LookingGlass::SESSION_CSRF] = bin2hex(random_bytes(12));
}
$csrfToken = $_SESSION[LookingGlass::SESSION_CSRF];

// Previous form data
$session_target = $_SESSION[LookingGlass::SESSION_TARGET_HOST] ?? '';
$session_method = $_SESSION[LookingGlass::SESSION_TARGET_METHOD] ?? 'ping';
$session_call_backend = $_SESSION[LookingGlass::SESSION_CALL_BACKEND] ?? false;
$session_tos_checked = isset($_SESSION[LookingGlass::SESSION_TOS_CHECKED]) ? 'checked' : '';

// Decoding the settings
$methods = json_decode(LG_METHODS, true);
$locations = json_decode(LG_LOCATIONS, true);
?>
<!doctype html>
<html lang="en" class="bg-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= LG_TITLE ?></title>
    <!-- Tailwind CSS CDN -->
    <link href="<?= LG_CSS_OVERRIDES ?>" rel="stylesheet">
</head>
<body class="antialiased">
    <div class="max-w-4xl mx-auto p-4">
        <!-- Title -->
        <header class="flex justify-between items-center py-4">
            <a href="<?= LG_LOGO_URL ?>" target="_blank">
                <div class="hidden dark:block">
                    <?= LG_LOGO_DARK ?>
                </div>
                <div class="block dark:hidden">
                    <?= LG_LOGO ?>
                </div>
            </a>
            <div>
                <select id="locationSelect" class="border rounded p-2" onchange="window.location=this.value" <?php if(count($locations)==0) echo 'disabled'; ?>>
                    <option selected><?= LG_LOCATION ?></option>
                    <?php foreach ($locations as $loc => $link): ?>
                        <?php if ($loc !== LG_LOCATION): ?>
                            <option value="<?= $link ?>"><?= $loc ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </header>

        <!-- Network Information -->
        <?php if(LG_BLOCK_NETWORK): ?>
        <div class="bg-white rounded shadow p-4 mb-6">
            <h2 class="text-xl font-semibold mb-4">Network</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-600">Location</label>
                    <input type="text" readonly class="w-full border rounded p-2" value="<?= LG_LOCATION ?>">
                </div>
                <div>
                    <label class="block text-gray-600">Facility</label>
                    <div class="flex">
                        <input type="text" readonly class="w-full border rounded-l p-2" value="<?= LG_FACILITY ?>">
                        <a href="<?= LG_FACILITY_URL ?>" target="_blank" class="bg-blue-500 text-white rounded-r px-3 py-2">PeeringDB</a>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-600">Your IP</label>
                    <input type="text" readonly class="w-full border rounded p-2" value="<?= AdvancedLG\LookingGlass::detectIpAddress() ?>">
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Looking Glass Form -->
        <?php if(LG_BLOCK_LOOKINGGLASS): ?>
        <div class="bg-white rounded shadow p-4 mb-6">
            <h2 class="text-xl font-semibold mb-4">Looking Glass</h2>
            <form method="POST" action="backend.php" id="lgForm" autocomplete="off">
                <input type="hidden" name="csrfToken" value="<?= $csrfToken ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-600">Target</label>
                        <input type="text" name="targetHost" required class="w-full border rounded p-2" placeholder="IP address or hostname" value="<?= htmlspecialchars($session_target) ?>">
                    </div>
                    <div>
                        <label class="block text-gray-600">Method</label>
                        <select name="backendMethod" class="w-full border rounded p-2">
                            <?php foreach ($methods as $method): ?>
                                <option value="<?= $method ?>" <?= ($session_method === $method) ? 'selected' : '' ?>><?= $method ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php if(LG_TERMS): ?>
                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="checkTerms" <?= $session_tos_checked ?> class="form-checkbox">
                        <span class="ml-2">I agree with the <a href="<?= LG_TERMS ?>" target="_blank" class="text-blue-500 underline">Terms of Use</a></span>
                    </label>
                </div>
                <?php endif; ?>
                <div class="flex justify-end">
                    <button type="submit" id="executeButton" name="submitForm" class="bg-blue-500 text-white rounded px-4 py-2">Execute</button>
                </div>
            </form>
            <!-- Output Area -->
            <div id="outputCard" class="bg-gray-900 text-green-300 rounded mt-4 p-4 hidden">
                <pre id="outputContent" class="whitespace-pre-wrap"></pre>
            </div>
        </div>
        <?php endif; ?>

        <!-- Additional blocks (speedtest, etc.) can be added -->

        <footer class="text-center text-gray-500 mt-8">
            Powered by <a href="https://github.com/yourusername/advanced-lookingglass" target="_blank" class="underline">Advanced Looking Glass</a>
        </footer>
    </div>

<!-- JavaScript: Live output streaming and form submission -->
    <script>
        document.getElementById('lgForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const executeButton = document.getElementById('executeButton');
            const outputCard = document.getElementById('outputCard');
            const outputContent = document.getElementById('outputContent');

            executeButton.innerText = 'Executing...';
            executeButton.disabled = true;
            outputCard.classList.remove('hidden');
            outputContent.innerHTML = '';

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            }).then(response => {
                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                function read() {
                    reader.read().then(({ done, value }) => {
                        if (done) {
                            executeButton.innerText = 'Execute';
                            executeButton.disabled = false;
                            return;
                        }
                        outputContent.innerHTML += decoder.decode(value);
                        read();
                    });
                }
                read();
            }).catch(() => {
                outputContent.innerHTML += "\nError occurred.";
                executeButton.innerText = 'Execute';
                executeButton.disabled = false;
            });
        });
    </script>
</body>
</html>
