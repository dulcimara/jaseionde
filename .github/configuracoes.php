<?php
// 1. Inclui sua conexão existente (que já abre a sessão e cria o $pdo)
// Se o seu arquivo tiver outro nome (ex: conexao.php), altere a linha abaixo:
require_once 'db.php'; 

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";
$tipo_msg = "";

// --- LÓGICA DE PROCESSAMENTO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Atualizar Dados
    if (isset($_POST['acao']) && $_POST['acao'] == 'atualizar_dados') {
        $nome = $_POST['nome'];
        $telefone = $_POST['telefone'];
        $cpf = $_POST['cpf'];
        
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, telefone = ?, cpf = ? WHERE id = ?");
        if ($stmt->execute([$nome, $telefone, $cpf, $user_id])) {
            if(isset($_SESSION['user_nome'])) { $_SESSION['user_nome'] = $nome; }
            $msg = "Dados atualizados com sucesso!";
            $tipo_msg = "sucesso";
        } else {
            $msg = "Erro ao atualizar dados."; 
            $tipo_msg = "erro";
        }
    }
    // 2. Alterar Senha
    elseif (isset($_POST['acao']) && $_POST['acao'] == 'alterar_senha') {
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];
        $confirmar = $_POST['confirmar_senha'];
        
        $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id]);
        $dados_user = $stmt->fetch();
        
        if ($dados_user) {
            // Verifica senha (compatível com hash ou texto puro)
            $senha_banco = $dados_user['senha'];
            $senha_ok = (password_verify($senha_atual, $senha_banco) || $senha_atual == $senha_banco);

            if ($senha_ok) {
                if ($nova_senha === $confirmar) {
                    $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?")->execute([$hash, $user_id]);
                    $msg = "Senha alterada com sucesso!"; 
                    $tipo_msg = "sucesso";
                } else {
                    $msg = "As novas senhas não conferem."; 
                    $tipo_msg = "erro";
                }
            } else {
                $msg = "Senha atual incorreta."; 
                $tipo_msg = "erro";
            }
        }
    }
    // 3. Newsletter
    elseif (isset($_POST['acao']) && $_POST['acao'] == 'gerenciar_news') {
        $u = $pdo->prepare("SELECT email FROM usuarios WHERE id = ?");
        $u->execute([$user_id]);
        $email_user = $u->fetchColumn();

        if ($_POST['operacao'] == 'cancelar') {
            $pdo->prepare("DELETE FROM newsletter WHERE email = ?")->execute([$email_user]);
            $msg = "Você cancelou sua inscrição."; 
            $tipo_msg = "sucesso";
        } elseif ($_POST['operacao'] == 'ativar') {
            try { 
                $check = $pdo->prepare("SELECT id FROM newsletter WHERE email = ?");
                $check->execute([$email_user]);
                if($check->rowCount() == 0){
                    $pdo->prepare("INSERT INTO newsletter (email) VALUES (?)")->execute([$email_user]); 
                    $msg = "Newsletter ativada!"; 
                    $tipo_msg = "sucesso";
                } else {
                    $msg = "Você já está inscrito!";
                    $tipo_msg = "sucesso";
                }
            } catch(Exception $e){
                $msg = "Erro ao assinar."; $tipo_msg = "erro";
            }
        }
    }
}

// Carregar dados atuais
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch();

// Check Newsletter
$stmt_news = $pdo->prepare("SELECT id FROM newsletter WHERE email = ?");
$stmt_news->execute([$usuario['email']]);
$tem_newsletter = $stmt_news->rowCount() > 0;

// Inclui topo
if(file_exists('topo.php')) { include 'topo.php'; } 
?>

<style>
    /* CSS DO LAYOUT */
    .config-layout { display: grid; grid-template-columns: 250px 1fr; gap: 30px; margin-top: 40px; margin-bottom: 50px; }
    .config-sidebar { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); height: fit-content; }
    .config-menu-item { display: block; padding: 15px 20px; border-bottom: 1px solid #eee; color: #007bff; font-weight: 600; cursor: pointer; transition: 0.3s; border-left: 4px solid transparent; }
    .config-menu-item:hover { background: #fff8f0; color: #ff8c00; border-left-color: #ff8c00; }
    .config-menu-item.active { background: #e7f1ff; color: #007bff; border-left-color: #007bff; }
    .config-content { background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    .section-title { color: #007bff; margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
    input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    .btn-save { background: #007bff; color: white; border: none; padding: 12px 25px; border-radius: 4px; font-weight: bold; cursor: pointer; transition:0.3s; }
    .btn-save:hover { background: #0056b3; }
    .btn-news { background: #ff8c00; color: white; border: none; padding: 12px 25px; border-radius: 4px; font-weight: bold; cursor: pointer; transition:0.3s; }
    .btn-news:hover { background: #e07b00; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    @media (max-width: 768px) { .config-layout { grid-template-columns: 1fr; } }
</style>

<div class="container">
    <div class="config-layout">
        <div class="config-sidebar">
            <div class="config-menu-item active" onclick="abrirTab(event, 'dados')"> Meus Dados </div>
            <div class="config-menu-item" onclick="abrirTab(event, 'senha')"> Alterar Senha </div>
            <div class="config-menu-item" onclick="abrirTab(event, 'newsletter')"> Newsletter </div>
            <a href="logout.php" class="config-menu-item" style="color:red !important;"> Sair </a>
        </div>

        <div class="config-content">
            <?php if($msg): ?> 
                <div class="alert" style="padding:15px; margin-bottom:20px; background:<?= $tipo_msg=='sucesso'?'#d4edda':'#f8d7da' ?>; color:<?= $tipo_msg=='sucesso'?'#155724':'#721c24' ?>; border-radius:4px;">
                    <?= $msg ?>
                </div> 
            <?php endif; ?>

            <div id="dados" class="tab-content active">
                <h2 class="section-title">Dados Cadastrais</h2>
                <form method="POST">
                    <input type="hidden" name="acao" value="atualizar_dados">
                    <div class="form-group"><label>Nome</label><input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required></div>
                    <div class="form-group"><label>Email</label><input type="email" value="<?= htmlspecialchars($usuario['email']) ?>" disabled style="background:#eee;"></div>
                    <div class="form-group"><label>Telefone</label><input type="text" name="telefone" value="<?= htmlspecialchars($usuario['telefone']) ?>"></div>
                    <div class="form-group"><label>CPF</label><input type="text" name="cpf" value="<?= htmlspecialchars($usuario['cpf']) ?>"></div>
                    <button type="submit" class="btn-save">Salvar Alterações</button>
                </form>
            </div>

            <div id="senha" class="tab-content">
                <h2 class="section-title">Alterar Senha</h2>
                <form method="POST">
                    <input type="hidden" name="acao" value="alterar_senha">
                    <div class="form-group"><label>Senha Atual</label><input type="password" name="senha_atual" required></div>
                    <div class="form-group"><label>Nova Senha</label><input type="password" name="nova_senha" required></div>
                    <div class="form-group"><label>Confirmar Nova Senha</label><input type="password" name="confirmar_senha" required></div>
                    <button type="submit" class="btn-save">Atualizar Senha</button>
                </form>
            </div>

            <div id="newsletter" class="tab-content">
                <h2 class="section-title">Gerenciar Newsletter</h2>
                <div style="padding: 30px; background: #f9f9f9; border-radius: 8px; text-align: center; border: 1px solid #eee;">
                    <?php if($tem_newsletter): ?>
                        <div style="margin-bottom: 20px;">
                            <h3 style="margin: 0; color: green;">Você está inscrito!</h3>
                            <p style="color: #666;">Você recebe nossas novidades no e-mail: <strong><?= $usuario['email'] ?></strong></p>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="acao" value="gerenciar_news">
                            <input type="hidden" name="operacao" value="cancelar">
                            <button type="submit" class="btn-news">Quero Cancelar Minha Inscrição</button>
                        </form>
                    <?php else: ?>
                        <div style="margin-bottom: 20px;">
                            <h3 style="margin: 0; color: #333;">Você não está inscrito</h3>
                            <p style="color: #666;">Assine para receber novidades e eventos do condomínio.</p>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="acao" value="gerenciar_news">
                            <input type="hidden" name="operacao" value="ativar">
                            <button type="submit" class="btn-news">Quero me Inscrever</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = "none"; }
        tablinks = document.getElementsByClassName("config-menu-item");
        for (i = 0; i < tablinks.length; i++) { tablinks[i].classList.remove("active"); }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.classList.add("active");
    }
</script>

<?php if(file_exists('rodape.php')) { include 'rodape.php'; } ?>