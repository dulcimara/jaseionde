<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Já Sei Onde - Portal do Condomínio</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    
    <style>
        /* --- CSS BASE (Mantido para segurança, mas o style.css deve assumir) --- */
        :root {
            --azul-primario: #004AAD; --azul-claro: #eef6ff; --laranja-destaque: #ff8c00; --branco: #ffffff;
            --verde-jardim: #2ecc71; --roxo-artesanato: #9b59b6; --azul-diy: #3498db; --amarelo-econ: #f1c40f;
        }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; padding-top: 140px; background: linear-gradient(to bottom, var(--azul-primario) 0%, #0a4da2 35%, #ffffff 520px); background-repeat: no-repeat; background-attachment: fixed; min-height: 100vh; }
        a { text-decoration: none; color: inherit; transition: 0.3s; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }

        /* HEADER & NAV FIXO */
        .fixed-header-wrapper { position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; background-color: var(--branco); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        header { padding: 15px 0; border-bottom: 1px solid #eee; }
        .header-flex { display: flex; align-items: center; justify-content: space-between; }
        .logo img { height: 50px; }
        
        /* SEARCH BOX (Padrão) */
        .search-box { display: flex; flex-grow: 1; max-width: 500px; margin: 0 20px; }
        .search-box input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px 0 0 4px; outline:none; }
        .search-box button { background: var(--laranja-destaque); color: white; border: none; padding: 10px 20px; border-radius: 0 4px 4px 0; cursor: pointer; }
        
        .user-nav { color: var(--azul-primario); font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 10px; }
        
        .main-nav { background: var(--azul-primario); height: 50px; display: flex; align-items: center; }
        .nav-link { color: white; font-weight: 500; font-size: 15px; margin-right: 20px; cursor: pointer; display: flex; align-items: center; gap: 5px; }
        .nav-right { margin-left: auto; display: flex; align-items: center; gap: 15px; }
        
        /* --- NEWSLETTER (CORRIGIDO PARA IGUALAR À BUSCA) --- */
        .form-newsletter { display: flex; align-items: center; }
        .form-newsletter input { 
            width: 220px; 
            padding: 10px; /* Mesmo padding da busca */
            border: 1px solid #ccc; 
            border-radius: 4px 0 0 4px; 
            outline: none; 
            font-size: 13px;
            background-color: white; /* Fundo branco */
            color: #333;
            height: 38px; /* Altura forçada para alinhar */
            box-sizing: border-box;
        }
        .form-newsletter button { 
            background: var(--laranja-destaque); /* Laranja igual busca */
            color: white; 
            border: none; 
            border-radius: 0 4px 4px 0; 
            padding: 0 15px; 
            font-weight: bold; 
            font-size: 12px; 
            cursor: pointer;
            height: 38px; /* Altura forçada para alinhar */
        }

        .btn-anunciar-nav { background: var(--laranja-destaque); color: white; padding: 8px 20px; border-radius: 20px; font-weight: bold; font-size: 14px; cursor: pointer; border:none; }

        /* --- OUTROS ESTILOS (Mantidos) --- */
        .sidenav { height: 100%; width: 0; position: fixed; z-index: 2001; top: 0; left: 0; background-color: #fff; overflow-x: hidden; transition: 0.4s; padding-top: 60px; box-shadow: 2px 0 10px rgba(0,0,0,0.2); }
        .sidenav a { padding: 10px 30px; text-decoration: none; font-size: 16px; color: #333; display: block; border-bottom: 1px solid #f0f0f0; }
        .sidenav .closebtn { position: absolute; top: 10px; right: 20px; font-size: 36px; border: none; color: #999; }
        .hamburger-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; display: none; margin-right: 15px; }

        .modal { display: none; position: fixed; z-index: 3000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 30px; width: 400px; border-radius: 8px; position: relative; }
        .close { position: absolute; right: 15px; top: 10px; cursor: pointer; font-size: 20px; }
        .btn-full { width: 100%; padding: 10px; background: var(--azul-primario); color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .modal input, .modal textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .password-wrapper { position: relative; width: 100%; }
        .password-wrapper input { width: 100%; padding-right: 40px; }
        .toggle-password { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #777; z-index: 10; }

        @media (max-width: 900px) { 
            .form-newsletter { display: none; } 
            .hamburger-btn { display: block; }
        }
        @media (max-width: 768px) { .header-flex { flex-direction: column; } .search-box { width: 100%; margin: 0; } }
    </style>
</head>
<body>

    <div id="mySidenav" class="sidenav">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
        <a href="index.php?page=home"><i class="fas fa-home"></i> Home</a>
        <a href="index.php?page=utilidade"><i class="fas fa-info-circle"></i> Utilidade Pública</a>
        <a href="#" onclick="document.getElementById('modal-contato').style.display='flex'">Fale Conosco</a>
    </div>

    <div class="fixed-header-wrapper">
        <header>
            <div class="container header-flex">
                <a href="index.php" class="logo"><img src="http://jaseionde.com.br/wp-content/uploads/2025/12/LogoJaseiondeP.png" alt="Logo"></a>
                <form action="index.php" method="GET" class="search-box">
                    <input type="text" name="busca" placeholder="O que você procura?" value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                
                <div class="user-nav">
                    <?php if(isset($_SESSION['user_nome'])): ?>
                        <a href="configuracoes.php" style="display:flex; align-items:center; gap:5px; color:var(--azul-primario);">
                            <i class="far fa-user-circle" style="font-size: 22px;"></i>
                            <?= substr($_SESSION['user_nome'], 0, 10) ?>
                            <i class="fas fa-cog" style="color:#ffcc00;"></i>
                        </a>
                    <?php else: ?>
                        <div onclick="abrirLogin()" style="cursor:pointer; display:flex; align-items:center; gap:5px;">
                            <i class="far fa-user-circle" style="font-size: 22px;"></i> Entrar / Cadastrar
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <nav class="main-nav">
            <div class="container" style="display:flex; width:100%; align-items:center;">
                <div class="nav-left" style="display:flex; align-items:center;">
                    
                    <button class="hamburger-btn" onclick="openNav()"><i class="fas fa-bars"></i></button>

                    <a href="index.php?page=home" class="nav-link"><i class="fas fa-home"></i> Home</a>
                    <a href="index.php?page=utilidade" class="nav-link">Utilidade Pública</a>
                    <a href="#" class="nav-link" onclick="document.getElementById('modal-contato').style.display='flex'">Fale Conosco</a>
                    
                    <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <a href="admin.php" class="nav-link" style="color:#ffcc00;"><i class="fas fa-cog"></i> Admin</a>
                    <?php endif; ?>
                </div>
                
                <div class="nav-right">
                    <form method="POST" class="form-newsletter">
                        <input type="hidden" name="acao" value="newsletter">
                        <input type="email" name="email_newsletter" placeholder="Cadastre seu e-mail" required>
                        <button type="submit">OK</button>
                    </form>
                    <button onclick="clicarAnunciar()" class="btn-anunciar-nav" style="margin-left: 15px;">Anunciar</button>
                </div>
            </div>
        </nav>
    </div>