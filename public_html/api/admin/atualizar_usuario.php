<?php
/**
 * api/admin/atualizar_usuario.php
 * PAPEL: Atualizar dados de um usuário com log de auditoria detalhado.
 * VERSÃO: 4.0 (Comparação de Dados - socialbr.lol)
 */

// Inclui a "guarita de segurança" do administrador
require_once __DIR__ . '/../../admin/admin_auth.php'; // Já provê admin_log() e LogsLogic
// $conn e $config['base_path'] já estão disponíveis aqui

// Verifica se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- BLOCO DE SEGURANÇA: VERIFICAÇÃO CSRF ---
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        http_response_code(403); 
        die("Erro de segurança: Token inválido. Recarregue a página e tente novamente.");
    }

    // --- CAPTURA DE DADOS ---
    $id = (int)($_POST['id_usuario_a_editar'] ?? 0);
    $nome = trim($_POST['nome']);
    $sobrenome = trim($_POST['sobrenome']);
    $nome_de_usuario = trim($_POST['nome_de_usuario']);
    $email = trim($_POST['email']);
    $role = $_POST['role'] ?? 'membro'; 
    $status = $_POST['status'] ?? 'ativo';
    $nova_senha = $_POST['nova_senha'];
    $id_bairro = isset($_POST['id_bairro']) ? (int)$_POST['id_bairro'] : 0;

    // --- BUSCA DADOS ATUAIS PARA COMPARAÇÃO (LOG DETALHADO) ---
    $stmt_old = $conn->prepare("SELECT nome, sobrenome, nome_de_usuario, email, role, status FROM Usuarios WHERE id = ?");
    $stmt_old->bind_param("i", $id);
    $stmt_old->execute();
    $old_data = $stmt_old->get_result()->fetch_assoc();
    $stmt_old->close();

    if (!$old_data) {
        die("Erro: Usuário não encontrado.");
    }

    // ===== Verificação de Segurança =====
    if ($id === $_SESSION['user_id'] && $role !== 'admin') {
        $count_sql = "SELECT COUNT(id) AS admin_count FROM Usuarios WHERE role = 'admin'";
        $count_result = $conn->query($count_sql);
        $admin_count = $count_result->fetch_assoc()['admin_count'];

        if ($admin_count <= 1) {
            die("Erro de segurança: Você não pode rebaixar o único administrador do sistema.");
        }
    }
    
    // Validações básicas
    if ($id <= 0 || empty($nome) || empty($sobrenome) || empty($nome_de_usuario) || empty($email) || $id_bairro <= 0) {
        die("Erro: Dados inválidos ou faltando. Todos os campos, incluindo o bairro, são obrigatórios.");
    }
    if (!in_array($role, ['membro', 'admin'])) {
        die("Erro: Função (role) inválida.");
    }
    if (!in_array($status, ['ativo', 'suspenso'])) {
        die("Erro: Status inválido.");
    }

    // --- Lógica de Atualização Dinâmica ---
    $sql_parts = [];
    $params = [];
    $types = '';
    $mudancas = []; // Para o log

    // Comparação de campos para o log
    if ($old_data['nome'] !== $nome) { $mudancas[] = "Nome: '{$old_data['nome']}' -> '$nome'"; }
    if ($old_data['sobrenome'] !== $sobrenome) { $mudancas[] = "Sobrenome: '{$old_data['sobrenome']}' -> '$sobrenome'"; }
    
    // Verifica duplicidade de usuário/e-mail
    $sql_check = "SELECT id FROM Usuarios WHERE (nome_de_usuario = ? OR email = ?) AND id != ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ssi", $nome_de_usuario, $email, $id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        die("Erro: O nome de usuário ou e-mail já está em uso por outra conta.");
    }
    $stmt_check->close();

    // Adiciona campos base
    $sql_parts[] = "nome = ?"; array_push($params, $nome); $types .= 's';
    $sql_parts[] = "sobrenome = ?"; array_push($params, $sobrenome); $types .= 's';
    
    if ($old_data['nome_de_usuario'] !== $nome_de_usuario) { $mudancas[] = "Username: '{$old_data['nome_de_usuario']}' -> '$nome_de_usuario'"; }
    $sql_parts[] = "nome_de_usuario = ?"; array_push($params, $nome_de_usuario); $types .= 's';
    
    if ($old_data['email'] !== $email) { $mudancas[] = "E-mail: '{$old_data['email']}' -> '$email'"; }
    $sql_parts[] = "email = ?"; array_push($params, $email); $types .= 's';
    
    $sql_parts[] = "id_bairro = ?"; array_push($params, $id_bairro); $types .= 'i';

    // Apenas atualiza role e status se o admin não estiver a editar-se a si mesmo
    if ($id !== $_SESSION['user_id']) {
        if ($old_data['role'] !== $role) { $mudancas[] = "Cargo: '{$old_data['role']}' -> '$role'"; }
        if ($old_data['status'] !== $status) { $mudancas[] = "Status: '{$old_data['status']}' -> '$status'"; }
        
        $sql_parts[] = "role = ?"; array_push($params, $role); $types .= 's';
        $sql_parts[] = "status = ?"; array_push($params, $status); $types .= 's';
    }

    // Se uma nova senha foi fornecida
    if (!empty($nova_senha)) {
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $sql_parts[] = "senha_hash = ?";
        array_push($params, $senha_hash);
        $types .= 's';
        $mudancas[] = "SENHA REDEFINIDA";
    }

    // Monta a query UPDATE final
    $sql = "UPDATE Usuarios SET " . implode(", ", $sql_parts) . " WHERE id = ?";
    array_push($params, $id); 
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        // --- REGISTRO DE AUDITORIA CLARO E INTEGRAL ---
        if (!empty($mudancas)) {
            $detalhe_final = "Alterações no perfil #$id (" . $old_data['nome_de_usuario'] . "): " . implode(" | ", $mudancas);
            admin_log('editar_usuario', 'usuario', $id, $detalhe_final);
        }

        header("Location: " . $config['base_path'] . "admin/admin_usuarios.php?success=1"); 
        exit();
    } else {
        die("Erro ao atualizar o usuário: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: " . $config['base_path'] . "admin/index.php"); 
    exit();
}