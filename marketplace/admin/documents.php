<?php
/**
 * Panel Admin del Marketplace - Gestión de Documentos
 */

require_once __DIR__ . '/auth-admin.php';
require_once __DIR__ . '/../config/marketplace-config.php';
require_once __DIR__ . '/../includes/marketplace-functions.php';

$db = getMarketplaceDB();
$projectCode = $_GET['project'] ?? null;

// Obtener proyecto si se especificó
$project = null;
if ($projectCode) {
    $stmt = $db->prepare("SELECT * FROM tbl_marketplace_projects WHERE project_code = ?");
    $stmt->execute([$projectCode]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Obtener todos los proyectos para el selector
$stmt = $db->query("SELECT id, project_code, name FROM tbl_marketplace_projects ORDER BY name ASC");
$allProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener documentos del proyecto seleccionado
$documents = [];
if ($project) {
    $stmt = $db->prepare("
        SELECT d.*, 
               COUNT(DISTINCT dal.id) as access_count,
               SUM(CASE WHEN dal.access_granted = 1 THEN 1 ELSE 0 END) as successful_access
        FROM tbl_marketplace_documents d
        LEFT JOIN tbl_marketplace_document_access_logs dal ON d.id = dal.document_id
        WHERE d.project_id = ?
        GROUP BY d.id
        ORDER BY d.display_order ASC, d.uploaded_at DESC
    ");
    $stmt->execute([$project['id']]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = 'Gestión de Documentos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Admin Marketplace</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/marketplace/admin/assets/css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/includes/admin-nav.php'; ?>

    <div class="container">
        <div class="page-header">
            <div>
                <h1><i class="bi bi-file-earmark-text"></i> Gestión de Documentos</h1>
                <p>Administra whitepapers, pitch decks y documentos del marketplace</p>
            </div>
            <?php if ($project): ?>
            <button onclick="openDocumentModal()" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Agregar Documento
            </button>
            <?php endif; ?>
        </div>

        <!-- Selector de Proyecto -->
        <div class="section">
            <div class="form-group">
                <label for="projectSelector">Seleccionar Proyecto</label>
                <select id="projectSelector" onchange="window.location.href='/marketplace/admin/documents.php?project=' + this.value" class="form-control">
                    <option value="">-- Seleccionar proyecto --</option>
                    <?php foreach ($allProjects as $p): ?>
                    <option value="<?php echo htmlspecialchars($p['project_code']); ?>" 
                            <?php echo ($project && $project['id'] === $p['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['name']); ?> (<?php echo htmlspecialchars($p['project_code']); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if ($project): ?>
        <!-- Lista de Documentos -->
        <div class="section">
            <div class="section-header">
                <h2>Documentos de <?php echo htmlspecialchars($project['name']); ?></h2>
                <span class="badge"><?php echo count($documents); ?> documentos</span>
            </div>

            <?php if (empty($documents)): ?>
            <div class="empty-state">
                <i class="bi bi-file-earmark-text" style="font-size: 4rem; color: #cbd5e1;"></i>
                <h3>No hay documentos</h3>
                <p>Agrega el primer documento para este proyecto</p>
                <button onclick="openDocumentModal()" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Agregar Documento
                </button>
            </div>
            <?php else: ?>
            <div class="documents-grid">
                <?php foreach ($documents as $doc): ?>
                <div class="document-card" data-id="<?php echo $doc['id']; ?>">
                    <div class="document-card-header">
                        <div class="document-icon">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </div>
                        <div class="document-badges">
                            <?php if ($doc['is_public']): ?>
                            <span class="badge badge-success">Público</span>
                            <?php else: ?>
                            <span class="badge badge-warning"><?php echo strtoupper($doc['required_access_level']); ?></span>
                            <?php endif; ?>
                            
                            <?php if (!empty($doc['coming_soon'])): ?>
                            <span class="badge badge-info">Próximamente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="document-card-body">
                        <h4><?php echo htmlspecialchars($doc['document_name']); ?></h4>
                        <p class="document-type">
                            <i class="bi bi-tag"></i> <?php echo ucfirst($doc['document_type']); ?>
                        </p>
                        <?php if ($doc['description']): ?>
                        <p class="document-description"><?php echo htmlspecialchars($doc['description']); ?></p>
                        <?php endif; ?>
                        
                        <div class="document-meta">
                            <span><i class="bi bi-download"></i> <?php echo $doc['download_count']; ?> descargas</span>
                            <span><i class="bi bi-eye"></i> <?php echo $doc['access_count']; ?> accesos</span>
                        </div>
                    </div>
                    
                    <div class="document-card-actions">
                        <button onclick="editDocument(<?php echo $doc['id']; ?>)" class="btn btn-sm btn-secondary">
                            <i class="bi bi-pencil"></i> Editar
                        </button>
                        <button onclick="deleteDocument(<?php echo $doc['id']; ?>)" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-folder2-open" style="font-size: 4rem; color: #cbd5e1;"></i>
            <h3>Selecciona un proyecto</h3>
            <p>Elige un proyecto del selector para gestionar sus documentos</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal para Agregar/Editar Documento -->
    <div id="documentModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3 id="documentModalTitle">Agregar Documento</h3>
                <button type="button" class="modal-close" onclick="closeDocumentModal()">&times;</button>
            </div>
            <form id="documentForm" onsubmit="saveDocument(event)" enctype="multipart/form-data">
                <input type="hidden" id="document_id" name="document_id">
                <input type="hidden" name="project_id" value="<?php echo $project['id'] ?? ''; ?>">
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="document_name">Nombre del Documento *</label>
                        <input type="text" id="document_name" name="document_name" required 
                               placeholder="Ej: Whitepaper Técnico">
                    </div>
                    
                    <div class="form-group">
                        <label for="document_type">Tipo de Documento *</label>
                        <select id="document_type" name="document_type" required>
                            <option value="whitepaper">Whitepaper</option>
                            <option value="pitch_deck">Pitch Deck</option>
                            <option value="report">Reporte</option>
                            <option value="legal">Legal</option>
                            <option value="financial">Financiero</option>
                            <option value="technical">Técnico</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="document_file">Archivo</label>
                        <input type="file" id="document_file" name="document_file" 
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                        <small>PDF, Word, PowerPoint, Excel. Máx 10MB</small>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="document_url">URL del Documento</label>
                        <input type="text" id="document_url" name="document_url" 
                               placeholder="/marketplace/uploads/projects/PROJECT/documents/file.pdf">
                        <small>Ruta relativa o URL completa. Se llena automáticamente al subir archivo.</small>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description" rows="3"
                                  placeholder="Describe el contenido del documento"></textarea>
                    </div>
                    
                    <!-- Control de Acceso -->
                    <div class="form-group full-width">
                        <h4 style="margin: 20px 0 10px 0; color: #1e293b;">Control de Acceso</h4>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_public" name="is_public" value="1">
                            <label for="is_public">Documento Público</label>
                        </div>
                        <small>Si está marcado, cualquiera puede acceder</small>
                    </div>
                    
                    <div class="form-group" id="access_level_group">
                        <label for="required_access_level">Nivel de Acceso Requerido</label>
                        <select id="required_access_level" name="required_access_level">
                            <option value="public">Público</option>
                            <option value="basic">Básico</option>
                            <option value="standard">Estándar</option>
                            <option value="premium">Premium</option>
                            <option value="vip">VIP</option>
                            <option value="founder">Fundador</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="requires_kyc" name="requires_kyc" value="1">
                            <label for="requires_kyc">Requiere KYC</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="min_investment_usd">Inversión Mínima (USD)</label>
                        <input type="number" id="min_investment_usd" name="min_investment_usd" 
                               step="0.01" min="0" placeholder="0.00">
                    </div>
                    
                    <!-- Próximamente -->
                    <div class="form-group full-width">
                        <h4 style="margin: 20px 0 10px 0; color: #1e293b;">Disponibilidad</h4>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="coming_soon" name="coming_soon" value="1">
                            <label for="coming_soon">Próximamente</label>
                        </div>
                        <small>El documento aparece pero no se puede descargar</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="available_date">Fecha Disponible</label>
                        <input type="date" id="available_date" name="available_date">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="coming_soon_message">Mensaje "Próximamente"</label>
                        <input type="text" id="coming_soon_message" name="coming_soon_message" 
                               placeholder="Ej: Disponible después del lanzamiento">
                    </div>
                    
                    <div class="form-group">
                        <label for="display_order">Orden de Visualización</label>
                        <input type="number" id="display_order" name="display_order" 
                               min="0" value="0">
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeDocumentModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle de acceso público/privado
        document.getElementById('is_public')?.addEventListener('change', function() {
            const accessLevelGroup = document.getElementById('access_level_group');
            if (this.checked) {
                accessLevelGroup.style.opacity = '0.5';
                accessLevelGroup.style.pointerEvents = 'none';
                document.getElementById('required_access_level').value = 'public';
            } else {
                accessLevelGroup.style.opacity = '1';
                accessLevelGroup.style.pointerEvents = 'auto';
            }
        });

        function openDocumentModal() {
            document.getElementById('documentForm').reset();
            document.getElementById('document_id').value = '';
            document.getElementById('documentModalTitle').textContent = 'Agregar Documento';
            document.getElementById('documentModal').style.display = 'flex';
        }

        function closeDocumentModal() {
            document.getElementById('documentModal').style.display = 'none';
        }

        function editDocument(id) {
            fetch('/marketplace/admin/api/manage-documents.php?action=get&id=' + id)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error al cargar documento');
                    return;
                }
                
                const doc = data.document;
                document.getElementById('document_id').value = doc.id;
                document.getElementById('document_name').value = doc.document_name || '';
                document.getElementById('document_type').value = doc.document_type || 'other';
                document.getElementById('document_url').value = doc.document_url || '';
                document.getElementById('description').value = doc.description || '';
                document.getElementById('is_public').checked = doc.is_public == 1;
                document.getElementById('required_access_level').value = doc.required_access_level || 'public';
                document.getElementById('requires_kyc').checked = doc.requires_kyc == 1;
                document.getElementById('min_investment_usd').value = doc.min_investment_usd || '';
                document.getElementById('coming_soon').checked = doc.coming_soon == 1;
                document.getElementById('available_date').value = doc.available_date || '';
                document.getElementById('coming_soon_message').value = doc.coming_soon_message || '';
                document.getElementById('display_order').value = doc.display_order || 0;
                
                document.getElementById('documentModalTitle').textContent = 'Editar Documento';
                document.getElementById('documentModal').style.display = 'flex';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar documento');
            });
        }

        function saveDocument(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            
            const documentId = document.getElementById('document_id').value;
            formData.append('action', documentId ? 'update' : 'add');
            if (documentId) {
                formData.append('id', documentId);
            }
            
            fetch('/marketplace/admin/api/manage-documents.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar documento');
            });
        }

        function deleteDocument(id) {
            if (!confirm('¿Eliminar este documento?\n\nEsta acción no se puede deshacer.')) return;
            
            const formData = new FormData();
            formData.append('csrf_token', '<?php echo $csrf_token; ?>');
            formData.append('action', 'delete');
            formData.append('id', id);
            
            fetch('/marketplace/admin/api/manage-documents.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar documento');
            });
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('documentModal');
            if (event.target === modal) {
                closeDocumentModal();
            }
        }
    </script>

    <style>
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .document-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .document-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .document-card-header {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .document-icon {
            font-size: 2rem;
        }

        .document-badges {
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: flex-end;
        }

        .document-card-body {
            padding: 20px;
        }

        .document-card-body h4 {
            margin: 0 0 8px 0;
            color: #1e293b;
            font-size: 1.1rem;
        }

        .document-type {
            color: #64748b;
            font-size: 0.875rem;
            margin: 0 0 12px 0;
        }

        .document-description {
            color: #475569;
            font-size: 0.875rem;
            line-height: 1.5;
            margin: 0 0 16px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .document-meta {
            display: flex;
            gap: 16px;
            font-size: 0.875rem;
            color: #64748b;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
        }

        .document-card-actions {
            padding: 12px 20px;
            background: #f8fafc;
            display: flex;
            gap: 8px;
            border-top: 1px solid #e2e8f0;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .empty-state h3 {
            margin: 16px 0 8px 0;
            color: #334155;
        }

        .empty-state p {
            margin: 0 0 24px 0;
        }

        .modal-large .modal-content {
            max-width: 800px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        .modal-header h3 {
            margin: 0;
            color: #1e293b;
            font-size: 1.25rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #64748b;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: #f1f5f9;
            color: #334155;
        }

        .modal-content form {
            padding: 24px;
        }

        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: normal;
        }
    </style>
</body>
</html>
