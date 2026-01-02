<?php
require 'db.php';
session_start();

// Verifica se é Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

// PROCESSAR ALTERAÇÃO DE PERFIL
if (isset($_POST['acao']) && $_POST['acao'] == 'alterar_perfil') {
    $id_usuario = (int)$_POST['id'];
    $novo_status = (int)$_POST['novo_status']; // 1 para admin, 0 para user

    // Segurança: Não permite alterar o próprio perfil para evitar bloqueio acidental
    if ($id_usuario == $_SESSION['user_id']) {
        echo "<script>alert('Você não pode alterar seu próprio nível de acesso.');</script>";
    } else {
        $pdo->prepare("UPDATE usuarios SET is_admin = ? WHERE id = ?")->execute([$novo_status, $id_usuario]);
        header("Location: admin_usuarios.php"); // Recarrega para atualizar
        exit;
    }
}

// LISTAR USUÁRIOS (Admins primeiro)
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY is_admin DESC, nome ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin - Usuários</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* MESMO CSS DO ADMIN.PHP */
        :root { --azul-primario: #004AAD; --laranja-destaque: #ff8c00; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 20px; }
        
        .container { max-width: 1100px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        .admin-nav { display: flex; gap: 20px; border-bottom: 2px solid #eee; margin-bottom: 30px; padding-bottom: 10px; }
        .admin-nav a { text-decoration: none; color: #666; font-weight: 600; font-size: 16px; padding: 5px 10px; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
        .admin-nav a:hover { color: var(--azul-primario); }
        .admin-nav a.active { color: var(--azul-primario); border-bottom: 3px solid var(--azul-primario); margin-bottom: -12px; }
        .admin-nav a.site-link { margin-left: auto; color: var(--laranja-destaque); }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: var(--azul-primario); color: white; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
        tr:hover { background-color: #f8f9fa; }

        /* Estilos específicos de Usuário */
        .avatar-circle { width: 35px; height: 35px; background: #eef6ff; color: var(--azul-primario); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; margin-right: 10px; }
        .user-row { display: flex; align-items: center; }
        
        .badge-role { padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; display: inline-block; width: 80px; text-align: center; }
        .role-admin { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .role-user { background: #f8f9fa; color: #666; border: 1px solid #ddd; }

        .btn-action { background: none; border: 1px solid #ddd; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; color: #555; transition: 0.3s; display: flex; align-items: center; gap: 5px; }
        .btn-action:hover { background: #f1f1f1; border-color: #ccc; }
        .btn-promote { color: var(--azul-primario); border-color: var(--azul-primario); }
        .btn-promote:hover { background: var(--azul-primario); color: white; }
        .btn-demote { color: #dc3545; border-color: #dc3545; }
        .btn-demote:hover { background: #dc3545; color: white; }

    </style>
</head>
<body>
    <div class="container">
        
        <div class="admin-nav">
            <a href="admin.php"><i class="fas fa-bullhorn"></i> Gerenciar Anúncios</a>
            <a href="admin_usuarios.php" class="active"><i class="fas fa-users-cog"></i> Gerenciar Usuários</a>
            <a href="index.php" class="site-link"><i class="fas fa-external-link-alt"></i> Ver Site</a>
        </div>

        <h2 style="color: var(--azul-primario); margin-top: 0;">Base de Usuários</h2>

        <table>
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>E-mail</th>
                    <th>CPF / Telefone</th>
                    <th>Perfil Atual</th>
                    <th>Alterar Permissão</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $u): ?>
                <tr>
                    <td>
                        <div class="user-row">
                            <div class="avatar-circle"><?= strtoupper(substr($u['nome'], 0, 1)) ?></div>
                            <div>
                                <strong><?= htmlspecialchars($u['nome']) ?></strong><br>
                                <small style="color:#999;">ID: #<?= $u['id'] ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <small style="display:block;">CPF: <?= htmlspecialchars($u['cpf'] ?? '-') ?></small>
                        <small style="display:block;">Tel: <?= htmlspecialchars($u['telefone'] ?? '-') ?></small>
                    </td>
                    <td>
                        <?php if($u['is_admin'] == 1): ?>
                            <span class="badge-role role-admin">Admin</span>
                        <?php else: ?>
                            <span class="badge-role role-user">Usuário</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="acao" value="alterar_perfil">
                            
                            <?php if($u['id'] == $_SESSION['user_id']): ?>
                                <button type="button" class="btn-action" disabled style="opacity:0.5; cursor:not-allowed;">
                                    <i class="fas fa-lock"></i> Você
                                </button>
                            <?php elseif($u['is_admin'] == 1): ?>
                                <input type="hidden" name="novo_status" value="0">
                                <button type="submit" class="btn-action btn-demote" onclick="return confirm('Remover acesso de Administrador?')">
                                    <i class="fas fa-arrow-down"></i> Remover Admin
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="novo_status" value="1">
                                <button type="submit" class="btn-action btn-promote" onclick="return confirm('Tornar este usuário um Administrador?')">
                                    <i class="fas fa-shield-alt"></i> Virar Admin
                                </button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
