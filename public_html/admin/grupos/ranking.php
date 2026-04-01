<?php
/**
 * admin/grupos/ranking.php
 * PAPEL: Exibir o Top 5 de grupos com maior número de membros.
 * LOCALIZAÇÃO: Deve estar dentro da pasta 'admin/grupos/'
 */

// 1. BUSCA DOS DADOS DE RANKING (Apenas grupos ativos)
$sql_ranking = "SELECT g.id, g.nome, g.foto_capa_url, 
                (SELECT COUNT(*) FROM Grupos_Membros WHERE id_grupo = g.id) as total_membros
                FROM Grupos g
                WHERE g.status = 'ativo'
                ORDER BY total_membros DESC
                LIMIT 5";

$res_ranking = $conn->query($sql_ranking);
$ranking_grupos = $res_ranking ? $res_ranking->fetch_all(MYSQLI_ASSOC) : [];
?>

<style>
    /* Contentor do Ranking */
    .ranking-card { 
        background: #fff; 
        padding: 20px; 
        border-radius: 10px; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
    }

    .ranking-card h4 { 
        margin-top: 0; 
        color: #0C2D54; 
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
        margin-bottom: 15px;
    }

    /* Lista de Itens */
    .rank-list { list-style: none; padding: 0; margin: 0; }
    
    .rank-item { 
        display: flex; 
        align-items: center; 
        gap: 12px; 
        padding: 12px 0; 
        border-bottom: 1px solid #f9f9f9; 
    }
    
    .rank-item:last-child { border-bottom: none; }

    /* Posição (1º, 2º...) */
    .rank-pos {
        font-weight: 800;
        font-size: 0.9rem;
        color: #ced4da;
        min-width: 25px;
    }
    .rank-item:nth-child(1) .rank-pos { color: #ffd700; } /* Ouro */
    .rank-item:nth-child(2) .rank-pos { color: #c0c0c0; } /* Prata */
    .rank-item:nth-child(3) .rank-pos { color: #cd7f32; } /* Bronze */

    /* Avatar do Grupo */
    .rank-img {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        object-fit: cover;
        background: #eee;
    }

    /* Informações */
    .rank-info { flex: 1; overflow: hidden; }
    .rank-name { 
        display: block; 
        font-size: 0.85rem; 
        font-weight: 700; 
        color: #333;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }
    .rank-count { 
        font-size: 0.75rem; 
        color: #888; 
    }
</style>

<div class="ranking-card">
    <h4><i class="fas fa-trophy"></i> Grupos Populares</h4>
    
    <div class="rank-list">
        <?php if (empty($ranking_grupos)): ?>
            <p style="font-size:0.8rem; color:#999; text-align:center; padding:10px;">
                Nenhum grupo ativo para rankear.
            </p>
        <?php else: ?>
            <?php foreach($ranking_grupos as $index => $group): ?>
            <div class="rank-item">
                <span class="rank-pos"><?php echo ($index + 1); ?>º</span>
                
                <img src="<?php echo $config['base_path'] . $group['foto_capa_url']; ?>" 
                     class="rank-img" 
                     onerror="this.src='assets/img/default_group.png'">
                
                <div class="rank-info">
                    <span class="rank-name"><?php echo htmlspecialchars($group['nome']); ?></span>
                    <span class="rank-count">
                        <i class="fas fa-users"></i> <?php echo number_format($group['total_membros']); ?> membros
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>