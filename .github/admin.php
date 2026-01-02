<?php
require 'db.php';
session_start();

// Verifica se é Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Acesso negado. Apenas administradores.");
}

// AÇÕES DO ADMIN (ANÚNCIOS)
if (isset($_POST['acao'])) {
    $id = $_POST['id'];
    
    if ($_POST['acao'] == 'aprovar') {
        $inicio = date('Y-m-d');
        $fim = date('Y-m-d', strtotime('+30 days'));
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        $pdo->prepare("UPDATE anuncios_detalhados SET status='aprovado', data_inicio=?, data_fim=?, destaque=? WHERE id=?")->execute([$inicio, $fim, $destaque, $id]);
    }
    if ($_POST['acao'] == 'rejeitar') {
        $pdo->prepare("UPDATE anuncios_detalhados SET status='rejeitado' WHERE id=?")->execute([$id]);
    }
    if ($_POST['acao'] == 'excluir') {
        $pdo->prepare("DELETE FROM anuncios_detalhados WHERE id=?")->execute([$id]);
    }
}

// LISTAR ANÚNCIOS
$lista = $pdo->query("SELECT * FROM anuncios_detalhados ORDER BY FIELD(status, 'pendente', 'aprovado', 'rejeitado'), id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin - Anúncios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --azul-primario: #004AAD; --laranja-destaque: #ff8c00; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; }
        
        .container { max-width: 1100px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        /* MENU DE NAVEGAÇÃO ADMIN */
        .admin-nav { display: flex; gap: 20px; border-bottom: 2px solid #eee; margin-bottom: 30px; padding-bottom: 10px; }
        .admin-nav a { text-decoration: none; color: #666; font-weight: 600; font-size: 16px; padding: 5px 10px; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
        .admin-nav a:hover { color: var(--azul-primario); }
        .admin-nav a.active { color: var(--azul-primario); border-bottom: 3px solid var(--azul-primario); margin-bottom: -12px; }
        .admin-nav a.site-link { margin-left: auto; color: var(--laranja-destaque); }

        /* TABELA */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: var(--azul-primario); color: white; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
        tr:hover { background-color: #f8f9fa; }

        /* BADGES E BOTÕES */
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 11px; color: white; font-weight: bold; text-transform: uppercase; }
        .bg-pendente { background: #f39c12; }
        .bg-aprovado { background: #27ae60; }
        .bg-rejeitado { background: #c0392b; }
        
        .btn { padding: 6px 12px; border: none; cursor: pointer; border-radius: 4px; color: white; font-size: 12px; font-weight: bold; transition: 0.3s; }
        .btn-green { background: #27ae60; } .btn-green:hover { background: #219150; }
        .btn-red { background: #c0392b; } .btn-red:hover { background: #a93226; }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="admin-nav">
            <a href="admin.php" class="active"><i class="fas fa-bullhorn"></i> Gerenciar Anúncios</a>
            <a href="admin_usuarios.php"><i class="fas fa-users-cog"></i> Gerenciar Usuários</a>
            <a href="index.php" class="site-link"><i class="fas fa-external-link-alt"></i> Ver Site</a>
        </div>

        <h2 style="color: var(--azul-primario); margin-top: 0;">Anúncios Recentes</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título / Detalhes</th>
                    <th>Categoria</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($lista as $item): ?>
                <tr>
                    <td>#<?= $item['id'] ?></td>
                    <td>
                        <strong style="color:#333; font-size:15px;"><?= htmlspecialchars($item['titulo']) ?></strong><br>
                        <small style="color:#666;">Valor: R$ <?= $item['valor'] ?></small>
                    </td>
                    <td><span style="color:var(--azul-primario); font-weight:bold; font-size:12px;"><?= ucfirst($item['categoria']) ?></span></td>
                    <td><span class="badge bg-<?= $item['status'] ?>"><?= $item['status'] ?></span></td>
                    <td>
                        <?php if($item['status'] == 'pendente'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="acao" value="aprovar">
                                <label style="font-size:12px; margin-right:5px; cursor:pointer;"><input type="checkbox" name="destaque"> Destaque</label>
                                <button class="btn btn-green" title="Aprovar"><i class="fas fa-check"></i></button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="acao" value="rejeitar">
                                <button class="btn btn-red" title="Rejeitar"><i class="fas fa-times"></i></button>
                            </form>
                        <?php else: ?>
                            <small style="color:#999;">Início: <?= date('d/m', strtotime($item['data_inicio'])) ?></small>
                            <?php if($item['destaque']): ?> <i class="fas fa-star" style="color:gold; margin-left:5px;"></i> <?php endif; ?>
                            <form method="POST" style="display:inline; float:right;">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="acao" value="excluir">
                                <button class="btn btn-red" onclick="return confirm('Tem certeza que deseja excluir?')" title="Excluir"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
