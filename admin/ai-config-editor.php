<?php
/**
 * Editor de Configuración de IA - Mizton
 * Panel simple para editar knowledge-base.md y system-prompt.txt
 */

// Verificación de acceso de administrador
session_start();

// Verificar si el usuario tiene sesión de admin activa
if (!isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    // Redirigir a news si no tiene acceso de admin
    header('Location: https://mizton.cat/news/');
    exit;
}

// Rutas de archivos
$knowledgeFile = __DIR__ . '/../config/knowledge-base.md';
$promptFile = __DIR__ . '/../config/system-prompt.txt';

// Procesar guardado
if ($_POST['action'] ?? '' === 'save') {
    $file = $_POST['file'] === 'knowledge' ? $knowledgeFile : $promptFile;
    $content = $_POST['content'] ?? '';
    
    if (file_put_contents($file, $content)) {
        $message = "✅ Archivo guardado correctamente";
    } else {
        $message = "❌ Error al guardar archivo";
    }
}

// Leer contenidos actuales
$knowledgeContent = file_exists($knowledgeFile) ? file_get_contents($knowledgeFile) : '';
$promptContent = file_exists($promptFile) ? file_get_contents($promptFile) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor IA - Mizton</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        .tab {
            padding: 10px 20px;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            border-radius: 5px 5px 0 0;
            margin-right: 5px;
        }
        .tab.active {
            background: #40916C;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        textarea {
            width: 100%;
            height: 500px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            resize: vertical;
        }
        .save-btn {
            background: #40916C;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .save-btn:hover {
            background: #52B788;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .info {
            background: #e7f3ff;
            color: #0066cc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .logout {
            float: right;
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 3px;
        }
        .regenerate-btn {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .regenerate-btn:hover {
            background: #0056b3;
        }
        .regenerate-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>🤖 Editor de IA - Mizton</h1>
            <a href="?logout=1" class="logout">Cerrar Sesión</a>
        </div>

        <?php if (isset($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <div class="info">
            <strong>💡 Instrucciones:</strong><br>
            • <strong>Base de Conocimiento:</strong> Información que la IA usará para responder preguntas<br>
            • <strong>System Prompt:</strong> Instrucciones de comportamiento para la IA<br>
            • Los cambios se aplican inmediatamente después de guardar<br>
            • Usa Markdown en la base de conocimiento para mejor formato
        </div>

        <div class="tabs">
            <button class="tab active" onclick="showTab('knowledge')">📚 Base de Conocimiento</button>
            <button class="tab" onclick="showTab('prompt')">⚙️ System Prompt</button>
        </div>

        <!-- Tab Base de Conocimiento -->
        <div id="knowledge-tab" class="tab-content active">
            <h3>📚 Base de Conocimiento (knowledge-base.md)</h3>
            <form method="post">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="file" value="knowledge">
                <textarea name="content" placeholder="Escribe aquí la base de conocimiento en formato Markdown..."><?= htmlspecialchars($knowledgeContent) ?></textarea>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    <button type="submit" class="save-btn">💾 Guardar Base de Conocimiento</button>
                    <button type="button" class="regenerate-btn" onclick="regenerateEmbeddings()">🔄 Regenerar Embeddings</button>
                </div>
            </form>
        </div>

        <!-- Tab System Prompt -->
        <div id="prompt-tab" class="tab-content">
            <h3>⚙️ System Prompt (system-prompt.txt)</h3>
            <form method="post">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="file" value="prompt">
                <textarea name="content" placeholder="Escribe aquí las instrucciones para la IA..."><?= htmlspecialchars($promptContent) ?></textarea>
                <button type="submit" class="save-btn">💾 Guardar System Prompt</button>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Ocultar todas las tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Mostrar tab seleccionada
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }
        
        function regenerateEmbeddings() {
            const btn = document.querySelector('.regenerate-btn');
            btn.disabled = true;
            btn.textContent = '🔄 Regenerando...';
            
            fetch('../api/chat-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'generate_embeddings'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Embeddings regenerados exitosamente!\n\nLa IA ahora tiene acceso a la información actualizada.');
                } else {
                    alert('❌ Error al regenerar embeddings:\n' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                alert('❌ Error de conexión:\n' + error.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = '🔄 Regenerar Embeddings';
            });
        }
    </script>
</body>
</html>

<?php
// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: https://mizton.cat/news/');
    exit;
}
?>
