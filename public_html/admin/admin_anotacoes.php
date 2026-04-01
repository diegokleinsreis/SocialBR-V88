<?php
/**
 * admin/admin_anotacoes.php
 * PAPEL: Bloco de notas administrativo com correção de Headers e DOM.
 * VERSÃO: 1.4 (Final Fix - socialbr.lol)
 */

require_once 'admin_auth.php'; 

// BUSCAR ANOTAÇÃO ATUAL
$query = "SELECT conteudo_texto FROM Anotacoes_Admin WHERE id = 1 LIMIT 1";
$res = $conn->query($query);
$anotacao = $res ? $res->fetch_assoc() : null;
$texto_inicial = $anotacao['conteudo_texto'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anotações - Painel Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo $config['versao_assets']; ?>"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        .notes-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #e4e6eb;
            margin-top: 20px;
        }

        .notes-header {
            background: #0C2D54; 
            color: #fff;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notes-textarea {
            width: 100%;
            height: 65vh;
            padding: 20px;
            border: none;
            border-top: 1px solid #f0f2f5;
            resize: none;
            font-size: 1.05rem;
            line-height: 1.6;
            color: #1c1e21;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #ffffff;
            box-sizing: border-box;
            outline: none;
            display: block;
        }

        .btn-save-notes {
            background: #fff;
            color: #0C2D54;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .save-status { font-size: 0.85rem; display: flex; align-items: center; gap: 8px; }
        .saving-mode { opacity: 0.5; pointer-events: none; }
    </style>
</head>
<body>

    <?php include 'templates/admin_header.php'; ?>
    <?php include 'templates/admin_mobile_nav.php'; ?>

    <main class="admin-main-content">
        <a href="index.php" class="admin-back-button"><i class="fas fa-arrow-left"></i> Voltar</a>
        
        <div class="admin-card">
            <h1><i class="fas fa-sticky-note"></i> Anotações</h1>
            <p>Espaço interno para planeamento da rede social.</p>
        </div>

        <div class="notes-container">
            <div class="notes-header">
                <div class="save-status" id="js-save-status">
                    <i class="fas fa-check-circle"></i> Sincronizado
                </div>
                <button type="button" class="btn-save-notes" id="js-btn-save">
                    <i class="fas fa-save"></i> SALVAR NOTAS
                </button>
            </div>
            
            <textarea id="js-admin-notes" class="notes-textarea" placeholder="Escreva aqui..."><?php echo htmlspecialchars($texto_inicial); ?></textarea>
        </div>
    </main>

    <script>
        const btnSave = document.getElementById('js-btn-save');
        const textarea = document.getElementById('js-admin-notes');
        const statusLabel = document.getElementById('js-save-status');

        function updateUI(msg, icon, isSaving = false) {
            statusLabel.innerHTML = `<i class="fas ${icon}"></i> ${msg}`;
            if(isSaving) btnSave.classList.add('saving-mode');
            else btnSave.classList.remove('saving-mode');
        }

        async function performSave() {
            const textValue = textarea.value;
            updateUI('A guardar...', 'fa-spinner fa-spin', true);

            try {
                const fullUrl = window.location.origin + '/api/admin/salvar_anotacoes.php';
                
                const response = await fetch(fullUrl, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/x-www-form-urlencoded' 
                    },
                    body: new URLSearchParams({
                        'conteudo': textValue,
                        'csrf_token': '<?php echo get_csrf_token(); ?>'
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    updateUI('Guardado com sucesso!', 'fa-check-circle');
                    setTimeout(() => updateUI('Sincronizado', 'fa-check-circle'), 2000);
                } else {
                    updateUI('Erro: ' + result.error, 'fa-exclamation-triangle');
                }
            } catch (err) {
                console.error("Falha no Fetch:", err);
                updateUI('Erro de conexão!', 'fa-wifi');
            }
        }

        btnSave.onclick = performSave;

        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                performSave();
            }
        });
    </script>
</body>
</html>
<?php if(isset($conn)) $conn->close(); ?>