<?php
/**
 * ARQUIVO: views/teste_erro.php
 * VERSÃO: 2.0 (Laboratório de Testes Sentinela JS)
 * PAPEL: Forçar erros de JavaScript para validar o monitoramento.
 */

// 1. Inclusão do Header (Garante o carregamento do sentinela_global.js)
require_once __DIR__ . '/../templates/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-dark text-white p-4">
                    <h4 class="mb-0"><i class="fas fa-flask me-2"></i>Laboratório de Caos JS</h4>
                    <p class="small mb-0 opacity-75">Use os botões abaixo para disparar erros e verificar o Painel Admin.</p>
                </div>
                
                <div class="card-body p-4">
                    <div class="alert alert-info border-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Após clicar em um botão, vá ao seu <strong>Monitor de Erros</strong> para ver o log.
                    </div>

                    <div class="d-grid gap-3">
                        
                        <div class="test-item border rounded p-3">
                            <h6 class="fw-bold text-danger">1. ReferenceError (Função Inexistente)</h6>
                            <p class="small text-muted">Tenta chamar uma função que nunca foi declarada.</p>
                            <button onclick="chamarFuncaoQueNaoExiste()" class="btn btn-outline-danger btn-sm">
                                Disparar ReferenceError
                            </button>
                        </div>

                        <div class="test-item border rounded p-3">
                            <h6 class="fw-bold text-danger">2. TypeError (Acesso Inválido)</h6>
                            <p class="small text-muted">Tenta ler propriedades de um elemento que não existe (null).</p>
                            <button id="btn-trigger-type-error" class="btn btn-outline-danger btn-sm">
                                Disparar TypeError
                            </button>
                        </div>

                        <div class="test-item border rounded p-3">
                            <h6 class="fw-bold text-warning">3. JS Promise Rejection (Assíncrono)</h6>
                            <p class="small text-muted">Simula uma falha de API/Banco que não foi tratada pelo código.</p>
                            <button id="btn-trigger-promise-error" class="btn btn-outline-warning btn-sm text-dark">
                                Disparar Falha de Promise
                            </button>
                        </div>

                    </div>
                </div>
                
                <div class="card-footer bg-light p-3 text-center">
                    <a href="<?php echo $config['base_path']; ?>admin/erros-sistema" class="btn btn-primary btn-sm px-4">
                        <i class="fas fa-shield-virus me-2"></i>Ver Monitor agora
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * LÓGICA DE CAOS CONTROLADO
 */

// Resposta ao Botão 2 (TypeError)
document.getElementById('btn-trigger-type-error').addEventListener('click', function() {
    const elementoFantasma = document.getElementById('id-que-nao-existe-no-html');
    // Isso vai quebrar porque 'elementoFantasma' é null
    elementoFantasma.classList.add('erro-proposital');
});

// Resposta ao Botão 3 (Promise Rejection)
document.getElementById('btn-trigger-promise-error').addEventListener('click', function() {
    new Promise((resolve, reject) => {
        setTimeout(() => {
            reject(new Error("Falha Crítica: Conexão com o servidor de testes perdida!"));
        }, 500);
    });
});
</script>

<?php 
// Inclusão do Footer padrão
require_once __DIR__ . '/../templates/footer.php'; 
?>