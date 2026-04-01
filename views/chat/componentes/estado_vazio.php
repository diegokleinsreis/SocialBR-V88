<?php
/**
 * views/chat/componentes/estado_vazio.php
 * Componente: Estado Vazio (Placeholder).
 * PAPEL: Exibir uma interface de boas-vindas quando nenhuma conversa está ativa.
 * VERSÃO: V63.4 (Correção de Escala e Visibilidade - socialbr.lol)
 */
?>

<div class="chat-empty-window">
    <div class="chat-empty-hero">
        <div class="chat-empty-icon-box">
            <i class="fas fa-paper-plane"></i>
        </div>
        
        <h2 style="color: var(--chat-primary);">Suas Mensagens</h2>
        <p>Selecione um dos seus contatos na lista ao lado para começar a conversar em tempo real.</p>
        
        <button class="chat-empty-cta-btn" id="btn-empty-iniciador" onclick="document.getElementById('btn-open-new-chat').click()">
            <i class="fas fa-plus"></i> Iniciar Nova Conversa
        </button>
        
        <div class="chat-empty-features">
            <div class="feature-item">
                <i class="fas fa-shield-alt"></i>
                <span>Segurança</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-bolt"></i>
                <span>Tempo Real</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-photo-video"></i>
                <span>Multimídia</span>
            </div>
        </div>
    </div>
</div>

<style>
/**
 * ESTILOS ESPECÍFICOS DO ESTADO VAZIO V63.4
 * Ajustes críticos para evitar que informações fiquem escondidas.
 */
.chat-empty-window {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: 100%;
    background-color: var(--chat-bg-card);
    /* [V63.4] Padding reduzido para evitar overflow vertical */
    padding: 30px 20px; 
    text-align: center;
    overflow-y: auto; /* Garante scroll interno se a tela for muito pequena */
}

.chat-empty-hero {
    max-width: 450px;
    animation: fadeInScale 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0; /* Impede que o conteúdo seja esmagado */
}

.chat-empty-icon-box {
    /* [V63.4] Tamanho ligeiramente reduzido para ganhar espaço vertical */
    width: 90px;
    height: 90px;
    background: rgba(12, 45, 84, 0.06);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    border: 1px solid rgba(12, 45, 84, 0.1);
}

.chat-empty-icon-box i {
    font-size: 36px;
    color: var(--chat-primary);
    transform: rotate(15deg);
    filter: drop-shadow(0 4px 8px rgba(12, 45, 84, 0.2));
}

.chat-empty-hero h2 {
    /* [V63.4] Tamanho ajustado para equilíbrio visual */
    font-size: 1.7rem;
    font-weight: 800;
    margin-bottom: 10px;
    letter-spacing: -0.5px;
}

.chat-empty-hero p {
    color: var(--chat-text-sub);
    font-size: 1rem;
    line-height: 1.5;
    margin-bottom: 25px;
    max-width: 90%;
}

/* Botão CTA Premium */
.chat-empty-cta-btn {
    background: var(--chat-primary);
    color: white;
    border: none;
    padding: 12px 26px;
    border-radius: 30px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    /* [V63.4] Margem reduzida para aproximar das features */
    margin-bottom: 35px;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(12, 45, 84, 0.25);
}

.chat-empty-cta-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 25px rgba(12, 45, 84, 0.35);
}

.chat-empty-features {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    border-top: 1px solid var(--chat-border);
    padding-top: 25px;
    width: 100%;
}

.feature-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    flex: 1;
}

.feature-item i {
    color: var(--chat-text-sub);
    font-size: 1.1rem;
    opacity: 0.6;
}

.feature-item span {
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--chat-text-sub);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

@keyframes fadeInScale {
    from { opacity: 0; transform: translateY(15px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@media (max-width: 768px) {
    .chat-empty-window { display: none !important; }
}

/* Ajuste fino para telas curtas (Laptops) */
@media (max-height: 700px) {
    .chat-empty-icon-box { width: 70px; height: 70px; margin-bottom: 15px; }
    .chat-empty-hero h2 { font-size: 1.4rem; }
    .chat-empty-cta-btn { margin-bottom: 20px; }
    .chat-empty-features { padding-top: 15px; }
}
</style>