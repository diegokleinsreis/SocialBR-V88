<?php
/**
 * FICHEIRO: templates/head_common.php
 * PAPEL: O <head> HTML comum para todas as páginas.
 * VERSÃO: 2.7 - Integração Google Analytics - socialbr.lol
 * RESPONSABILIDADE: Carregamento de assets, SEO básico e monitoramento de integridade.
 */

if (!isset($page_title)) {
    $page_title = $config['site_nome'] ?? 'Bem-vindo(a)';
}

if (!isset($asset_version)) {
    $asset_version = time(); 
}
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script async src="https://www.googletagmanager.com/gtag/js?id=G-1QV840YZG8"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-1QV840YZG8');
</script>

<meta name="csrf-token" content="<?php echo get_csrf_token(); ?>">

<title><?php echo htmlspecialchars($page_title); ?> - <?php echo htmlspecialchars($config['site_nome']); ?></title>

<script shadow-sm>
    /**
     * Variável Global base_path (Sentinela & Admin)
     */
    window.base_path = '<?php echo $config['base_path']; ?>';
    const BASE_PATH = window.base_path;
</script>

<script src="<?php echo $config['base_path']; ?>assets/js/sentinela_global.js?v=<?php echo $asset_version; ?>"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css" crossorigin="anonymous">

<style>
    /**
     * Customização da barra para o azul do Social BR
     */
    #nprogress .bar { 
        background: #0C2D54 !important; 
        height: 4px !important; 
        z-index: 20000 !important; 
    }
    #nprogress .peg { box-shadow: 0 0 10px #0C2D54, 0 0 8px #0C2D54 !important; }

    /* Customização Global SweetAlert2 */
    .swal2-confirm { background-color: #0C2D54 !important; }
    .swal2-styled:focus { box-shadow: 0 0 0 3px rgba(12, 45, 84, 0.5) !important; }
</style>

<script>
    /**
     * MOTOR DE TEMA (Anti-Flicker)
     */
    (function() {
        const theme = localStorage.getItem('theme');
        if (theme === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    })();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" crossorigin="anonymous"></script>
<script src="https://unpkg.com/nprogress@0.2.0/nprogress.js" crossorigin="anonymous"></script>
<script src="<?php echo $config['base_path']; ?>assets/js/barra_carregamento.js"></script>

<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/base/_base.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/layout/_layout.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/layout/_header.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/layout/_sidebar.css?v=<?php echo $asset_version; ?>">

<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_forms.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_post.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_feed_midia.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_profile.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_comments.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_notifications.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_notificacao_toast.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_modal.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_settings.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_public.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_lightbox.css?v=<?php echo $asset_version; ?>"> 
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_dark_mode.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_post_interactive.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_feed.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_salvos.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_pesquisa_premium.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_marketplace.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_marketplace_detalhes.css?v=<?php echo $asset_version; ?>">
<link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/_marketplace_create.css?v=<?php echo $asset_version; ?>">

<?php if (strpos($_SERVER['REQUEST_URI'], 'chat') !== false): ?>
    <link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/chat/_chat_estrutura.css?v=<?php echo $asset_version; ?>">
    <link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/chat/_chat_lista.css?v=<?php echo $asset_version; ?>">
    <link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/chat/_chat_conversa.css?v=<?php echo $asset_version; ?>">
    <link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/chat/_chat_midia_galeria.css?v=<?php echo $asset_version; ?>">
    <link rel="stylesheet" href="<?php echo $config['base_path']; ?>assets/css/components/chat/_chat_lightbox.css?v=<?php echo $asset_version; ?>">
<?php endif; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" crossorigin="anonymous">