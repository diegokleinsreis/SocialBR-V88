<?php
/**
 * ARQUIVO: api/usuarios/validar_recuperacao.php
 * PAPEL: Validar o código de 6 dígitos e atualizar a senha do utilizador.
 * VERSÃO: 2.0 - Saneamento e Segurança Atómica (socialbr.lol)
 */

// 1. --- [DEPENDÊNCIAS] ---
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../src/RecuperacaoLogic.php';

// Definimos o charset explicitamente para evitar erros de acentuação no JSON
header('Content-Type: application/json; charset=utf-8');

// 2. --- [CAPTURA E VALIDAÇÃO DE INPUT] ---
$input = json_decode(file_get_contents('php://input'), true);

$email       = $input['email'] ?? null;
$codigo      = $input['codigo'] ?? null;
$nova_senha  = $input['nova_senha'] ?? null;

if (!$email || !$codigo || !$nova_senha) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios.']);
    exit;
}

// Regra de Ouro: Senha mínima para evitar contas vulneráveis
if (strlen($nova_senha) < 6) {
    echo json_encode(['success' => false, 'message' => 'A nova senha deve ter pelo menos 6 caracteres.']);
    exit;
}

// 3. --- [PROCESSAMENTO TÉCNICO] ---
$recLogic = new RecuperacaoLogic($pdo);

// Busca o utilizador pelo e-mail para obter o ID real
$usuario = $recLogic->buscarUsuarioPorEmail($email);

if (!$usuario) {
    echo json_encode(['success' => false, 'message' => 'Utilizador não encontrado no sistema.']);
    exit;
}

// Verifica se o código é válido, pertence a este utilizador e não expirou
$registroCodigo = $recLogic->validarCodigo($usuario['id'], $codigo);

if (!$registroCodigo) {
    echo json_encode(['success' => false, 'message' => 'Código inválido, já utilizado ou expirado.']);
    exit;
}

// 4. --- [EXECUÇÃO DA TROCA ATÓMICA] ---
try {
    // Iniciamos a transação: Se a luz cair no meio, nada é alterado no banco
    $pdo->beginTransaction();

    // Atualiza a senha na tabela Usuarios (o hash é gerado dentro da Logic)
    $sucessoSenha = $recLogic->atualizarSenhaUsuario($usuario['id'], $nova_senha);

    if (!$sucessoSenha) {
        throw new Exception("Falha ao persistir a nova senha.");
    }

    // Invalida o código (usado = 1) para segurança total
    $recLogic->marcarComoUsado($registroCodigo['id']);

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Senha atualizada com sucesso! Já pode fazer o seu login.'
    ]);

} catch (Exception $e) {
    // Se algo der errado, desfazemos qualquer alteração pendente
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Logamos o erro técnico para o admin, mas enviamos mensagem amigável para o user
    error_log("[Recuperação] Erro no ID {$usuario['id']}: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Ocorreu um erro técnico ao salvar a sua senha. Tente novamente.'
    ]);
}