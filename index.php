<?php
require_once 'db.php';
$db = new Database();

$isAdmin = isset($_GET['admin']) && $_GET['admin'] === 'true';
$snippetId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    if ($snippetId && $isAdmin) {
        if ($db->updateCode($snippetId, $code)) {
            $message = 'Code updated successfully!';
        }
    } else {
        $newId = $db->saveCode($code);
        if ($newId) {
            $snippetId = $newId;
            $message = 'Code saved successfully!';
        }
    }
}

$code = '';
if ($snippetId) {
    $snippet = $db->getCode($snippetId);
    if ($snippet) {
        $code = $snippet['code'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Sharing Platform</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.js"></script>
</head>
<body>
    <div class="container">
        <h1>פלטפורמת שיתוף קוד</h1>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div id="editor"></div>

        <div class="actions">
            <?php if ($isAdmin || !$snippetId): ?>
                <form method="POST" id="codeForm">
                    <input type="hidden" name="code" id="codeInput">
                    <button type="submit" class="btn save-btn">שמור קוד</button>
                </form>
            <?php endif; ?>

            <?php if ($snippetId): ?>
                <button onclick="copyCode()" class="btn copy-btn">העתק קוד</button>
                <div class="share-url">
                    קישור לשיתוף: <input type="text" readonly value="<?php
                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                        echo htmlspecialchars($protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?id=' . $snippetId);
                    ?>" onclick="this.select()">
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs' }});
        require(['vs/editor/editor.main'], function() {
            window.editor = monaco.editor.create(document.getElementById('editor'), {
                value: <?php echo json_encode($code); ?>,
                language: 'javascript',
                theme: 'vs-dark',
                automaticLayout: true,
                readOnly: <?php echo (!$isAdmin && $snippetId) ? 'true' : 'false'; ?>
            });

            // Add keyboard shortcut for save
            if (<?php echo $isAdmin ? 'true' : 'false'; ?>) {
                editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, function() {
                    document.getElementById('codeInput').value = editor.getValue();
                    document.getElementById('codeForm').submit();
                });
            }
        });

        function copyCode() {
            const code = window.editor.getValue();
            navigator.clipboard.writeText(code).then(() => {
                alert('הקוד הועתק ללוח!');
            }).catch(err => {
                console.error('Failed to copy code:', err);
            });
        }

        document.getElementById('codeForm')?.addEventListener('submit', function(e) {
            document.getElementById('codeInput').value = window.editor.getValue();
        });
    </script>
</body>
</html>