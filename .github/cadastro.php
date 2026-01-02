<?php
require 'db.php';
session_start();

// Inicializa variáveis para manter o que foi digitado
$nome = isset($_POST['nome']) ? $_POST['nome'] : "";
$email = isset($_POST['email']) ? $_POST['email'] : "";
$telefone = isset($_POST['telefone']) ? $_POST['telefone'] : "";
$cpf = isset($_POST['cpf']) ? $_POST['cpf'] : "";
$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $cpf = $_POST['cpf'];
    $senha = $_POST['senha'];
    $termos = isset($_POST['termos']);

    // Validação PHP (Back-up)
    if (!$termos) {
        $erro = "Você precisa aceitar os termos de uso.";
    } else {
        // Verifica se e-mail já existe
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->execute([$email]);
        
        if ($check->rowCount() > 0) {
            $erro = "Este e-mail já possui cadastro.";
        } else {
            // Hash da senha seguro
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO usuarios (nome, email, telefone, cpf, senha) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$nome, $email, $telefone, $cpf, $senhaHash])) {
                echo "<script>alert('Cadastro realizado com sucesso! Faça login.'); window.location.href='index.php';</script>";
                exit;
            } else {
                $erro = "Erro ao salvar no banco de dados.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Já Sei Onde</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* MESMO CSS DO INDEX.PHP PARA MANTER O PADRÃO */
        :root {
            --azul-primario: #004AAD;
            --azul-claro: #eef6ff;
            --laranja-destaque: #ff8c00;
            --branco: #ffffff;
        }
        body { 
            font-family: 'Segoe UI', sans-serif; 
            margin: 0; 
            padding-top: 100px; /* Espaço para o header fixo */
            background: linear-gradient(to bottom, var(--azul-primario) 0%, #0a4da2 35%, #ffffff 520px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* HEADER SIMPLIFICADO (Mesmo visual, sem busca) */
        .fixed-header-wrapper { 
            position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; 
            background-color: var(--branco); box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
        }
        header { padding: 15px 0; border-bottom: 1px solid #eee; }
        .header-flex { 
            display: flex; align-items: center; justify-content: space-between; 
            max-width: 1200px; margin: 0 auto; padding: 0 15px; 
        }
        .logo img { height: 50px; }
        .link-voltar { color: var(--azul-primario); font-weight: bold; text-decoration: none; display: flex; align-items: center; gap: 5px; }

        /* CONTAINER DO FORMULÁRIO */
        .card-cadastro {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            margin-top: 40px;
        }

        h2 { text-align: center; color: var(--azul-primario); margin-bottom: 30px; }

        label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; font-size: 14px; }
        
        input[type="text"], 
        input[type="email"], 
        input[type="password"] { 
            width: 100%; padding: 12px; margin-bottom: 15px; 
            border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; 
            font-size: 15px;
        }
        
        input:focus { border-color: var(--azul-primario); outline: none; }

        .btn-full { 
            width: 100%; padding: 12px; 
            background: var(--laranja-destaque); color: white; 
            border: none; border-radius: 25px; 
            font-weight: bold; font-size: 16px; cursor: pointer; 
            transition: 0.3s; margin-top: 10px;
        }
        .btn-full:hover { background: #e07b00; }

        .error-msg { 
            background: #f8d7da; color: #721c24; 
            padding: 10px; border-radius: 4px; 
            margin-bottom: 20px; font-size: 14px; text-align: center; 
            border: 1px solid #f5c6cb;
        }

        /* OLHO NA SENHA */
        .password-wrapper { position: relative; }
        .toggle-password { 
            position: absolute; right: 15px; top: 38px; /* Ajuste conforme altura do label */
            cursor: pointer; color: #777; 
        }

        .termos-wrapper { display: flex; align-items: center; gap: 8px; margin-bottom: 20px; }
        .termos-wrapper input { margin: 0; width: auto; }
        .termos-wrapper label { margin: 0; font-weight: normal; font-size: 13px; }

    </style>
</head>
<body>

    <div class="fixed-header-wrapper">
        <header>
            <div class="header-flex">
                <a href="index.php" class="logo">
                    <img src="http://jaseionde.com.br/wp-content/uploads/2025/12/LogoJaseiondeP.png" alt="Logo">
                </a>
                <a href="index.php" class="link-voltar"><i class="fas fa-arrow-left"></i> Voltar para Home</a>
            </div>
        </header>
        <div style="background: var(--azul-primario); height: 50px;"></div>
    </div>

    <div class="card-cadastro">
        <h2>Crie sua Conta</h2>
        
        <?php if($erro): ?>
            <div class="error-msg"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST" onsubmit="return validarFrontEnd()">
            
            <label>Nome Completo</label>
            <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($nome) ?>" placeholder="Seu nome" required>

            <label>E-mail</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" placeholder="seu@email.com" required>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label>Telefone</label>
                    <input type="text" name="telefone" id="telefone" value="<?= htmlspecialchars($telefone) ?>" placeholder="(61) 99999-9999" required>
                </div>
                <div>
                    <label>CPF</label>
                    <input type="text" name="cpf" id="cpf" value="<?= htmlspecialchars($cpf) ?>" placeholder="000.000.000-00" required>
                </div>
            </div>
            
            <div class="password-wrapper">
                <label>Senha</label>
                <input type="password" name="senha" id="senha" placeholder="Crie uma senha forte" required>
                <i class="fas fa-eye toggle-password" onclick="toggleSenha()"></i>
            </div>

            <div class="termos-wrapper">
                <input type="checkbox" name="termos" id="termos" <?= isset($_POST['termos']) ? 'checked' : '' ?>>
                <label for="termos">Li e concordo com os <a href="#" style="color:var(--azul-primario)">Termos de Uso</a></label>
            </div>

            <button type="submit" class="btn-full">Cadastrar</button>
        </form>

        <p style="text-align:center; font-size:14px; margin-top:20px; color:#666;">
            Já tem uma conta? <a href="index.php" style="color:var(--azul-primario); font-weight:bold;">Faça Login</a>
        </p>
    </div>

    <script>
        // Funcionalidade do Olhinho
        function toggleSenha() {
            const campo = document.getElementById('senha');
            const icone = document.querySelector('.toggle-password');
            
            if (campo.type === "password") {
                campo.type = "text";
                icone.classList.remove("fa-eye");
                icone.classList.add("fa-eye-slash");
            } else {
                campo.type = "password";
                icone.classList.remove("fa-eye-slash");
                icone.classList.add("fa-eye");
            }
        }

        // Validação no Navegador (Evita recarregar a página se faltar algo óbvio)
        function validarFrontEnd() {
            const termos = document.getElementById('termos');
            const senha = document.getElementById('senha');
            
            // Valida Termos
            if (!termos.checked) {
                alert("Por favor, aceite os Termos de Uso para continuar.");
                return false; // Impede o envio do formulário
            }

            // Valida Tamanho da Senha
            if (senha.value.length < 4) {
                alert("A senha deve ter pelo menos 4 caracteres.");
                return false;
            }

            return true; // Pode enviar
        }

        // Máscaras simples (Opcional, para melhorar UX)
        document.getElementById('telefone').addEventListener('input', function (e) {
            var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });
    </script>

</body>
</html>