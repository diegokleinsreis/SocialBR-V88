<?php
/**
 * templates/footer.php
 * VERSÃO INTEGRAL (V122.1) - CENTRALIZAÇÃO DO LIGHTBOX & MOTOR DE EVENTOS
 * PAPEL: Incluir componentes de UI, Scripts Globais e Motores JavaScript.
 * AJUSTE: Inclusão do admin_menus.js para suporte a Contagem Regressiva e Bloqueio Sutil.
 * VERSÃO: V122.1 (socialbr.lol)
 */

// Garante que a configuração e a versão dos assets estejam carregadas
if (!isset($config) || !isset($asset_version)) {
    $paths = [
        __DIR__ . '/../config/database.php',
        $_SERVER['DOCUMENT_ROOT'] . '/config/database.php'
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}
?>

    <div id="container-notificacoes-toast"></div>

    <?php include __DIR__ . '/modais/modal_lightbox.php'; ?>
    <?php include __DIR__ . '/modal_interacao.php'; ?>
    <?php include __DIR__ . '/modais/modal_editar_postagem.php'; ?>
    <?php include __DIR__ . '/modais/modal_denuncia.php'; ?>
    <?php include __DIR__ . '/modais/modal_compartilhamento.php'; ?>

    <?php include __DIR__ . '/modais/modal_colecao.php'; ?>
    <?php include __DIR__ . '/modais/modal_selecionar_colecao.php'; ?>

    <script>
        /**
         * Variáveis Globais de Comunicação PHP -> JS
         */
        const base_path = '<?php echo $config['base_path']; ?>';
        const CSRF_TOKEN = '<?php echo get_csrf_token(); ?>';
        window.csrf_token = CSRF_TOKEN; // Ponte de segurança para o motor de salvos
        window.LOGGED_USER_ID = <?php echo $_SESSION['user_id'] ?? 0; ?>;
    </script>

    <script src="<?php echo $config['base_path']; ?>assets/js/barra_carregamento.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/main.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/theme_toggle.js?v=<?php echo $asset_version; ?>" defer></script>
    
    <script src="<?php echo $config['base_path']; ?>assets/js/controle_menu.js?v=<?php echo $asset_version; ?>" defer></script>
    
    <script src="<?php echo $config['base_path']; ?>admin/assets/js/admin_menus.js?v=<?php echo $asset_version; ?>" defer></script>
    
    <script src="<?php echo $config['base_path']; ?>assets/js/lightbox.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/chat_lightbox.js?v=<?php echo $asset_version; ?>" defer></script>
    
    <script src="<?php echo $config['base_path']; ?>assets/js/componentes/ui/MotorToast.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/componentes/ui/MotorDeAlertas.js?v=<?php echo $asset_version; ?>" defer></script>
    
    <script src="<?php echo $config['base_path']; ?>assets/js/MotorBusca.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/notificacoes.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/curtidas.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/curtidas_comentarios.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/compartilhar.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/editar_excluir.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/post_text_expander.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/denuncia.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/comentarios.js?v=<?php echo $asset_version; ?>" defer></script>
    
    <script src="<?php echo $config['base_path']; ?>assets/js/salvos_acoes.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/salvar_post.js?v=<?php echo $asset_version; ?>" defer></script>
    
    <script src="<?php echo $config['base_path']; ?>assets/js/configuracoes.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/amizade.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/post_form.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/form_postagem.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/enquetes.js?v=<?php echo $asset_version; ?>" defer></script>
    <script src="<?php echo $config['base_path']; ?>assets/js/gerenciador_videos.js?v=<?php echo $asset_version; ?>" defer></script>

    <?php if (strpos($_SERVER['REQUEST_URI'], 'feed') !== false || strpos($_SERVER['REQUEST_URI'], 'perfil') !== false): ?>
        <script src="<?php echo $config['base_path']; ?>assets/js/infinite_scroll.js?v=<?php echo $asset_version; ?>" defer></script>
    <?php endif; ?>

    <?php if (strpos($_SERVER['REQUEST_URI'], 'marketplace/vender') !== false): ?>
        <script src="<?php echo $config['base_path']; ?>assets/js/marketplace_create.js?v=<?php echo $asset_version; ?>" defer></script>
    <?php endif; ?>

    <?php if (strpos($_SERVER['REQUEST_URI'], 'chat') !== false): ?>
        <script src="<?php echo $config['base_path']; ?>assets/js/chat_visual.js?v=<?php echo $asset_version; ?>" defer></script>
        <script src="<?php echo $config['base_path']; ?>assets/js/chat_midia.js?v=<?php echo $asset_version; ?>" defer></script>
        <script src="<?php echo $config['base_path']; ?>assets/js/chat_acoes.js?v=<?php echo $asset_version; ?>" defer></script>
        <script src="<?php echo $config['base_path']; ?>assets/js/chat_motor.js?v=<?php echo $asset_version; ?>" defer></script>
    <?php endif; ?>

</body>
</html>