<?php
require 'db.php';
session_start();

// Verifica se é Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Acesso negado. Apenas administradores.");
}

// AÇÕES DO ADMIN
if (isset($_POST['acao'])) {
    $id = $_POST['id'];
    
    if ($_POST['acao'] == 'aprovar') {
        // Define data inicio (hoje) e fim (hoje + 30 dias)
        $inicio = date('Y-m-d');
        $fim = date('Y-m-d', strtotime('+30 days'));
        $destaque = isset($_POST['destaque']) ? 1 : 0;
        
        $pdo->prepare("UPDATE anuncios_detalhados SET status='aprovado', data_inicio=?, data_fim=?, destaque=? WHERE id=?")
            ->execute([$inicio, $fim, $destaque, $id]);
    }
    
    if ($_POST['acao'] == 'rejeitar') {
        $pdo->prepare("UPDATE anuncios_detalhados SET status='rejeitado' WHERE id=?")->execute([$id]);
    }

    if ($_POST['acao'] == 'excluir') {
        $pdo->prepare("DELETE FROM anuncios_detalhados WHERE id=?")->execute([$id]);
    }
}

// LISTAR ANÚNCIOS (Pendentes primeiro)
$lista = $pdo->query("SELECT * FROM anuncios_detalhados ORDER BY FIELD(status, 'pendente', 'aprovado', 'rejeitado'), id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin - Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #004AAD; color: white; }
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; color: white; }
        .bg-pendente { background: #f39c12; }
        .bg-aprovado { background: #27ae60; }
        .bg-rejeitado { background: #c0392b; }
        .btn { padding: 5px 10px; border: none; cursor: pointer; border-radius: 4px; color: white; }
        .btn-green { background: #27ae60; }
        .btn-red { background: #c0392b; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h1>Gerenciar Anúncios</h1>
            <a href="index.php">Voltar ao Site</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título / Usuário</th>
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
                        <strong><?= $item['titulo'] ?></strong><br>
                        <small>Valor: R$ <?= $item['valor'] ?></small>
                    </td>
                    <td><?= ucfirst($item['categoria']) ?></td>
                    <td><span class="badge bg-<?= $item['status'] ?>"><?= $item['status'] ?></span></td>
                    <td>
                        <?php if($item['status'] == 'pendente'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="acao" value="aprovar">
                                <label><input type="checkbox" name="destaque"> Destaque?</label>
                                <button class="btn btn-green"><i class="fas fa-check"></i> Aprovar (30 dias)</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="acao" value="rejeitar">
                                <button class="btn btn-red"><i class="fas fa-times"></i></button>
                            </form>
                        <?php else: ?>
                            <small>Início: <?= date('d/m', strtotime($item['data_inicio'])) ?> | Fim: <?= date('d/m', strtotime($item['data_fim'])) ?></small>
                            <?php if($item['destaque']): ?> <span style="color:orange;">★</span> <?php endif; ?>
                            <form method="POST" style="display:inline; float:right;">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="acao" value="excluir">
                                <button class="btn btn-red" onclick="return confirm('Excluir?')"><i class="fas fa-trash"></i></button>
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