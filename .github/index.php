<?php
require 'db.php'; 

// --- 1. LÓGICA DE LOGIN ---
if (isset($_POST['acao']) && $_POST['acao'] == 'login') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];
    $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : 'index.php';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($senha, $user['senha'])) { 
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
        header("Location: " . $redirect); 
        exit;
    }
}

// --- 2. CONFIGURAÇÃO DE PÁGINAS ---
$pagina = isset($_GET['page']) ? $_GET['page'] : 'home';
$filtro_categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
$termo_busca = isset($_GET['busca']) ? $_GET['busca'] : null;

// Roteamento
if ($pagina == 'home' && isset($_GET['page']) && $_GET['page'] == 'todos') { $pagina = 'todos'; }
if (($filtro_categoria || $termo_busca) && $pagina != 'artigos') { $pagina = 'busca'; }

// --- 3. BUSCAS DO BANCO ---
$anuncios_home = [];
$resultados_busca = [];
$anuncios_por_cat = [];
$artigos_lista = [];
$artigo_leitura = null;
$ultimos_artigos = [];

// Home: Anúncios
try {
    $anuncios_home = $pdo->query("SELECT a.*, (SELECT caminho_arquivo FROM anuncio_fotos WHERE anuncio_id = a.id LIMIT 1) as capa FROM anuncios_detalhados a WHERE a.status != 'rejeitado' ORDER BY a.id DESC LIMIT 12")->fetchAll();
} catch (Exception $e) { $anuncios_home = []; }

// Home: Últimos Artigos
try {
    $ultimos_artigos = $pdo->query("SELECT * FROM artigos ORDER BY data_postagem DESC LIMIT 5")->fetchAll();
} catch (Exception $e) { $ultimos_artigos = []; }


// PÁGINA: LISTA DE ARTIGOS
if ($pagina == 'artigos') {
    $cat_artigo = isset($_GET['cat']) ? $_GET['cat'] : null;
    if($cat_artigo) {
        $stmt = $pdo->prepare("SELECT * FROM artigos WHERE categoria = ? ORDER BY data_postagem DESC");
        $stmt->execute([$cat_artigo]);
        $artigos_lista = $stmt->fetchAll();
    } else {
        $artigos_lista = $pdo->query("SELECT * FROM artigos ORDER BY data_postagem DESC")->fetchAll();
    }
}

// PÁGINA: LER ARTIGO
if ($pagina == 'ler_artigo' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM artigos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $artigo_leitura = $stmt->fetch();
}

// PÁGINA: VER TODOS ANÚNCIOS
if ($pagina == 'todos') {
    try {
        $sql_todos = "SELECT a.*, (SELECT caminho_arquivo FROM anuncio_fotos WHERE anuncio_id = a.id LIMIT 1) as capa FROM anuncios_detalhados a WHERE a.status != 'rejeitado' ORDER BY categoria ASC, id DESC";
        $todos_raw = $pdo->query($sql_todos)->fetchAll();
        foreach($todos_raw as $ad) {
            $cat_name = ucfirst($ad['categoria']);
            $anuncios_por_cat[$cat_name][] = $ad;
        }
    } catch (Exception $e) { $anuncios_por_cat = []; }
}

// PÁGINA: BUSCA
if ($pagina == 'busca') {
    try {
        $cats_anuncios = ['imoveis', 'veiculos', 'servicos', 'desapegos'];
        if ($filtro_categoria && in_array($filtro_categoria, $cats_anuncios)) {
            $tipo_resultado = 'anuncio';
            $termo = $filtro_categoria;
            if($filtro_categoria=='imoveis') $termo='Imóvel';
            if($filtro_categoria=='veiculos') $termo='Veículo';
            if($filtro_categoria=='servicos') $termo='Serviço';
            $sql = "SELECT a.*, (SELECT caminho_arquivo FROM anuncio_fotos WHERE anuncio_id = a.id LIMIT 1) as capa FROM anuncios_detalhados a WHERE a.status != 'rejeitado' AND (categoria LIKE ? OR titulo LIKE ?) ORDER BY id DESC";
            $stmt = $pdo->prepare($sql); $stmt->execute(["%$filtro_categoria%", "%$termo%"]);
            $resultados_busca = $stmt->fetchAll();
        }
        elseif ($filtro_categoria) {
            $tipo_resultado = 'comercio'; 
            $mapa = ['alimentacao'=>['Restaurante','Lanchonete','Hamburgueria','Padaria','Cafeteria','Açaí','Pizzaria'],'esporte'=>['Academia','Esportes','Natação','Crossfit','Luta'],'saude'=>['Farmácia','Clínica','Laboratório','Dentista','Saúde'],'educacao'=>['Escola','Curso','Idioma','Creche'],'mercado'=>['Supermercado','Mercado','Hortifruti','Açougue']];
            $termos = isset($mapa[$filtro_categoria]) ? $mapa[$filtro_categoria] : [$filtro_categoria];
            $placeholders = implode(',', array_fill(0, count($termos), '?')); $termos[] = $filtro_categoria;
            $stmt = $pdo->prepare("SELECT * FROM comercios WHERE categoria IN ($placeholders) OR categoria_slug = ? ORDER BY nome ASC");
            $stmt->execute($termos); $resultados_busca = $stmt->fetchAll();
        }
        elseif ($termo_busca) {
            $sql = "SELECT a.*, (SELECT caminho_arquivo FROM anuncio_fotos WHERE anuncio_id = a.id LIMIT 1) as capa FROM anuncios_detalhados a WHERE a.status != 'rejeitado' AND (titulo LIKE ? OR descricao LIKE ?) ORDER BY id DESC";
            $stmt = $pdo->prepare($sql); $stmt->execute(["%$termo_busca%", "%$termo_busca%"]);
            $resultados_busca = $stmt->fetchAll();
        }
    } catch (Exception $e) { $resultados_busca = []; }
}

include 'topo.php'; 
?>

<style>
    /* =========================================
       ESTILOS GERAIS E SLIDER
       ========================================= */
    .slider-container { width: 100%; max-width: 1200px; height: 250px; margin: 20px auto; position: relative; overflow: hidden; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
    .slider-wrapper { display: flex; width: 100%; height: 100%; transition: transform 0.5s ease-in-out; }
    .slider-slide { min-width: 100%; height: 100%; }
    .slider-slide img { width: 100%; height: 100%; object-fit: cover; object-position: center; }
    .slider-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0,0,0,0.3); color: white; border: none; padding: 15px; cursor: pointer; font-size: 18px; border-radius: 50%; z-index:10; }
    .prev-btn { left: 15px; } .next-btn { right: 15px; }
    .slider-dots { position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); display: flex; gap: 10px; z-index: 10; }
    .dot { width: 12px; height: 12px; background-color: rgba(255, 255, 255, 0.5); border-radius: 50%; cursor: pointer; transition: 0.3s; border: 2px solid transparent; }
    .dot:hover, .dot.active { background-color: white; border-color: var(--azul-primario); transform: scale(1.2); }
    
    /* CATEGORIAS (Banners Pequenos) */
    .banner-cat-link { flex: 1; min-width: 200px; height: 110px; position: relative; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-decoration: none; transition: transform 0.2s; }
    .banner-cat-link:hover { transform: translateY(-3px); }
    .banner-cat-img { width: 100%; height: 100%; object-fit: cover; filter: brightness(0.7); }
    .banner-cat-text { position: absolute; bottom: 10px; left: 15px; color: white; font-size: 18px; font-weight: 800; text-shadow: 2px 2px 4px rgba(0,0,0,0.8); }

    /* CARROSSEL DE ANÚNCIOS */
    .ads-carousel-wrapper { position: relative; padding: 0 10px; }
    .ads-track-container { overflow-x: hidden; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; }
    .ads-track { display: flex; gap: 15px; padding: 10px 2px 20px 2px; }
    .ads-card-slide {
        flex: 0 0 calc((100% - 75px) / 6); 
        min-width: 160px;
        text-decoration: none; color: inherit; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s;
        display: flex; flex-direction: column;
    }
    .ads-card-slide:hover { transform: translateY(-5px); }
    .ads-card-img { width: 100%; height: 140px; object-fit: cover; border-top-left-radius: 8px; border-top-right-radius: 8px; }
    .ads-card-body { padding: 10px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
    .ads-nav-btn { position: absolute; top: 45%; transform: translateY(-50%); width: 40px; height: 40px; background: white; border-radius: 50%; box-shadow: 0 2px 10px rgba(0,0,0,0.2); border: none; cursor: pointer; z-index: 5; display: flex; align-items: center; justify-content: center; color: var(--azul-primario); font-size: 18px; transition: 0.3s; }
    .ads-nav-btn:hover { background: var(--azul-primario); color: white; }
    .ads-prev { left: -15px; } .ads-next { right: -15px; }

    /* =========================================
       LAYOUT DO PORTAL DE NOTÍCIAS (CORRIGIDO)
       ========================================= */
    .portal-container { height: 440px; }
    .portal-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; height: 100%; }

    /* --- ARTIGO DESTAQUE (ESQUERDA) --- */
    .destaque-principal {
        position: relative; height: 100%; border-radius: 12px; overflow: hidden;
        text-decoration: none; display: block; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .destaque-principal img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
    .destaque-principal:hover img { transform: scale(1.03); }
    .destaque-overlay {
        position: absolute; bottom: 0; left: 0; width: 100%; 
        background: linear-gradient(transparent, rgba(0,0,0,0.9)); 
        padding: 30px; box-sizing: border-box;
    }
    .destaque-tag { background: var(--azul-primario); color: white; padding: 4px 12px; font-size: 12px; font-weight: bold; border-radius: 4px; text-transform: uppercase; margin-bottom: 8px; display: inline-block; }
    
    /* COR BRANCA FORÇADA PARA O TÍTULO DO DESTAQUE */
    .destaque-titulo { 
        color: white !important; 
        font-size: 26px; margin: 0; font-weight: 800; line-height: 1.2; text-shadow: 2px 2px 4px rgba(0,0,0,0.6); 
    }
    
    /* RESUMO DO DESTAQUE */
    .destaque-resumo {
        color: #f0f0f0; font-size: 14px; margin: 8px 0 0 0; line-height: 1.4; opacity: 0.9;
    }

    /* --- GRID LATERAL (DIREITA) --- */
    .lista-lateral-grid { display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; gap: 20px; height: 100%; }
    .item-lateral-card { display: flex; flex-direction: column; text-decoration: none; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: 0.2s; overflow: hidden; height: 100%; }
    .item-lateral-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .lateral-img-card { width: 100%; height: 110px; object-fit: cover; }
    .lateral-info-card { padding: 12px; display: flex; flex-direction: column; justify-content: center; flex-grow: 1; }
    .lateral-tag { color: var(--azul-primario); font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; }
    .lateral-titulo { color: #333; font-size: 14px; font-weight: bold; line-height: 1.4; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    
    /* RESUMO LATERAL */
    .lateral-resumo {
        color: #666; font-size: 12px; margin: 5px 0 0 0; line-height: 1.3;
    }

    /* PÁGINA DO ARTIGO */
    .article-header-img { width: 100%; max-height: 400px; object-fit: cover; border-radius: 12px; margin-bottom: 25px; }
    .article-content { font-size: 18px; line-height: 1.8; color: #444; }
    .article-content p { margin-bottom: 20px; text-align: justify; }
    .article-content h2, .article-content h3 { color: var(--azul-primario); margin-top: 35px; margin-bottom: 15px; }
    .article-content ul, .article-content ol { margin-bottom: 20px; padding-left: 20px; }
    .article-content li { margin-bottom: 10px; }
    .article-content img, .artigo-img-container img { max-width: 100%; height: auto; display: block; margin: 30px auto; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .article-content figcaption, .artigo-img-container figcaption { text-align: center; font-size: 14px; color: #777; font-style: italic; margin-top: -20px; margin-bottom: 30px; }
    
    /* Responsividade */
    @media (max-width: 1024px) { .ads-card-slide { flex: 0 0 calc((100% - 45px) / 4); } }
    @media (max-width: 900px) {
        .portal-container { height: auto; }
        .portal-grid { grid-template-columns: 1fr; height: auto; } 
        .destaque-principal { height: 300px; margin-bottom: 20px; }
        .lista-lateral-grid { height: auto; }
    }
    @media (max-width: 768px) { 
        .slider-container { height: 180px; } 
        .ads-card-slide { flex: 0 0 calc((100% - 15px) / 2); } 
        .article-content { font-size: 16px; } 
    }
</style>

<?php if ($pagina == 'home'): ?>
    
    <div class="slider-container" id="mainSlider">
        <div class="slider-wrapper" id="sliderWrapper">
            <div class="slider-slide"><img src="img/Banner01.jpg"></div>
            <div class="slider-slide"><img src="img/Banner02.jpg"></div>
            <div class="slider-slide"><img src="img/Banner03.jpg"></div>
        </div>
        <button class="slider-btn prev-btn" onclick="moveSlide(-1)">❮</button>
        <button class="slider-btn next-btn" onclick="moveSlide(1)">❯</button>
        <div class="slider-dots" id="dotsContainer"></div>
    </div>
    
    <section class="container">
        <div class="cat-grid" style="display:flex; justify-content:center; gap:20px; flex-wrap:wrap; padding:40px 0;">
            <a href="index.php?categoria=saude" class="cat-item"><div class="icon-circle"><i class="fas fa-heartbeat"></i></div><span class="cat-label">Saúde</span></a>
            <a href="index.php?categoria=esporte" class="cat-item"><div class="icon-circle"><i class="fas fa-running"></i></div><span class="cat-label">Esporte</span></a>
            <a href="index.php?categoria=educacao" class="cat-item"><div class="icon-circle"><i class="fas fa-graduation-cap"></i></div><span class="cat-label">Educação</span></a>
            <a href="index.php?categoria=alimentacao" class="cat-item"><div class="icon-circle"><i class="fas fa-utensils"></i></div><span class="cat-label">Alimentação</span></a>
            <a href="index.php?categoria=mercado" class="cat-item"><div class="icon-circle"><i class="fas fa-shopping-cart"></i></div><span class="cat-label">Mercado</span></a>
            <div class="cat-item cat-anunciar" onclick="clicarAnunciar()"><div class="icon-circle" style="background:#ff8c00;"><i class="fas fa-bullhorn"></i></div><span class="cat-label">Anunciar</span></div>
        </div>
    </section>
 
    <section class="container" style="margin-bottom: 50px;">
        <h2 style="color: #004AAD; margin:0 0 15px 0;">Anúncios</h2>
        <div style="display: flex; justify-content: space-between; gap: 15px; flex-wrap: wrap;">
            <a href="index.php?categoria=imoveis" class="banner-cat-link"><img src="img/predios.jpg" class="banner-cat-img"><span class="banner-cat-text">Imóveis</span></a>
            <a href="index.php?categoria=veiculos" class="banner-cat-link"><img src="img/carros.jpg" class="banner-cat-img"><span class="banner-cat-text">Veículos</span></a>
            <a href="index.php?categoria=servicos" class="banner-cat-link"><img src="img/servicos.jpg" class="banner-cat-img"><span class="banner-cat-text">Serviços</span></a>
            <a href="index.php?categoria=desapegos" class="banner-cat-link"><img src="img/desapegos.jpg" class="banner-cat-img"><span class="banner-cat-text">Desapegos</span></a>
        </div>
    </section>

    <section class="container" style="margin-bottom: 50px;">
        <div style="background-color: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px); border-radius: 15px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 15px; margin-bottom: 20px;">
                <h2 style="color: #004AAD; margin:0;">Últimos Classificados</h2>
                <a href="index.php?page=todos" style="color: #ff8c00; font-weight:bold; font-size:14px; text-decoration:none; transition:0.3s;">Ver classificados <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="ads-carousel-wrapper">
                <button class="ads-nav-btn ads-prev" onclick="scrollAds(-1)"><i class="fas fa-chevron-left"></i></button>
                <div class="ads-track-container" id="adsTrackContainer">
                    <div class="ads-track">
                        <?php if(count($anuncios_home) > 0): foreach($anuncios_home as $anuncio): ?>
                            <a href="detalhes.php?id=<?= $anuncio['id'] ?>" class="ads-card-slide">
                                <img src="<?= $anuncio['capa'] ?: 'https://via.placeholder.com/300?text=Sem+Foto' ?>" class="ads-card-img">
                                <div class="ads-card-body">
                                    <h3 class="card-title" style="font-size:14px; margin:0 0 5px 0;"><?= htmlspecialchars($anuncio['titulo']) ?></h3>
                                    <?php if(!empty($anuncio['modelo']) || !empty($anuncio['ano'])): ?>
                                        <p class="card-model" style="font-size:12px; color:#999; margin:0;"><?= htmlspecialchars($anuncio['modelo']) ?></p>
                                    <?php endif; ?>
                                    <span class="price" style="font-size:16px; color:#28a745; margin-top:5px;">R$ <?= number_format($anuncio['valor'], 2, ',', '.') ?></span>
                                </div>
                            </a>
                        <?php endforeach; else: ?><p style="color:#666; width:100%; text-align:center;">Ainda não há anúncios.</p><?php endif; ?>
                    </div>
                </div>
                <button class="ads-nav-btn ads-next" onclick="scrollAds(1)"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <section class="container" style="margin-bottom: 60px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="color: #004AAD; margin:0;">Dicas & Notícias</h2>
            <a href="index.php?page=artigos" style="color: #ff8c00; font-weight:bold; font-size:14px;">Ver todos <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <?php if(count($ultimos_artigos) > 0): ?>
            <div class="portal-container">
                <div class="portal-grid">
                    <?php 
                        // Destaque Principal
                        $destaque = $ultimos_artigos[0];
                        // Resumo de 20 chars
                        $txt_destaque = !empty($destaque['resumo']) ? $destaque['resumo'] : strip_tags($destaque['conteudo']);
                        $resumo_destaque = mb_substr($txt_destaque, 0, 20) . '...';
                    ?>
                    <a href="index.php?page=ler_artigo&id=<?= $destaque['id'] ?>" class="destaque-principal">
                        <img src="<?= $destaque['imagem'] ?: 'https://via.placeholder.com/800x600?text=Novidade' ?>">
                        <div class="destaque-overlay">
                            <span class="destaque-tag"><?= htmlspecialchars($destaque['categoria']) ?></span>
                            <h3 class="destaque-titulo"><?= htmlspecialchars($destaque['titulo']) ?></h3>
                            <p class="destaque-resumo"><?= htmlspecialchars($resumo_destaque) ?></p>
                        </div>
                    </a>

                    <div class="lista-lateral-grid">
                        <?php for($i = 1; $i < count($ultimos_artigos); $i++): 
                            $art = $ultimos_artigos[$i];
                            // Resumo de 20 chars
                            $txt_art = !empty($art['resumo']) ? $art['resumo'] : strip_tags($art['conteudo']);
                            $resumo_art = mb_substr($txt_art, 0, 20) . '...';
                        ?>
                            <a href="index.php?page=ler_artigo&id=<?= $art['id'] ?>" class="item-lateral-card">
                                <img src="<?= $art['imagem'] ?: 'https://via.placeholder.com/300x200' ?>" class="lateral-img-card">
                                <div class="lateral-info-card">
                                    <span class="lateral-tag"><?= htmlspecialchars($art['categoria']) ?></span>
                                    <h4 class="lateral-titulo"><?= htmlspecialchars($art['titulo']) ?></h4>
                                    <p class="lateral-resumo"><?= htmlspecialchars($resumo_art) ?></p>
                                </div>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p>Nenhuma notícia encontrada.</p>
        <?php endif; ?>
    </section>

<?php elseif ($pagina == 'artigos'): ?>
    <div class="container" style="margin-top: 30px; margin-bottom:50px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h2 style="color:var(--azul-primario); margin:0;">Artigos e Dicas</h2>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="gerenciar_artigos.php" style="background:#28a745; color:white; padding:10px 20px; border-radius:20px; text-decoration:none; font-weight:bold;">+ Escrever Artigo</a>
            <?php endif; ?>
        </div>
        <div style="display:flex; gap:10px; margin-bottom:30px; flex-wrap:wrap;">
            <a href="index.php?page=artigos" style="padding:8px 15px; background:<?= !isset($_GET['cat']) ? 'var(--azul-primario)' : '#ddd' ?>; color:<?= !isset($_GET['cat']) ? 'white' : '#333' ?>; border-radius:20px;">Todos</a>
            <a href="index.php?page=artigos&cat=reciclagem" style="padding:8px 15px; background:<?= ($_GET['cat']??'')=='reciclagem' ? 'var(--azul-primario)' : '#ddd' ?>; color:<?= ($_GET['cat']??'')=='reciclagem' ? 'white' : '#333' ?>; border-radius:20px;">Reciclagem</a>
            <a href="index.php?page=artigos&cat=faca-voce-mesmo" style="padding:8px 15px; background:<?= ($_GET['cat']??'')=='faca-voce-mesmo' ? 'var(--azul-primario)' : '#ddd' ?>; color:<?= ($_GET['cat']??'')=='faca-voce-mesmo' ? 'white' : '#333' ?>; border-radius:20px;">Faça Você Mesmo</a>
            <a href="index.php?page=artigos&cat=plantas" style="padding:8px 15px; background:<?= ($_GET['cat']??'')=='plantas' ? 'var(--azul-primario)' : '#ddd' ?>; color:<?= ($_GET['cat']??'')=='plantas' ? 'white' : '#333' ?>; border-radius:20px;">Plantas</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px;">
            <?php foreach($artigos_lista as $artigo): ?>
                <a href="index.php?page=ler_artigo&id=<?= $artigo['id'] ?>" style="background:white; border-radius:12px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.05); text-decoration:none; color:inherit; display:flex; flex-direction:column;">
                    <img src="<?= $artigo['imagem'] ?: 'https://via.placeholder.com/600x400' ?>" style="width:100%; height:200px; object-fit:cover;">
                    <div style="padding:20px; flex-grow:1; display:flex; flex-direction:column;">
                        <span style="font-size:12px; color:#999; margin-bottom:5px;"><?= date('d/m/Y', strtotime($artigo['data_postagem'])) ?> • <?= ucfirst($artigo['categoria']) ?></span>
                        <h3 style="margin: 0 0 10px 0; font-size:20px; color:#333;"><?= htmlspecialchars($artigo['titulo']) ?></h3>
                        <span style="color:var(--azul-primario); font-weight:bold; margin-top:auto;">Ler matéria completa -></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

<?php elseif ($pagina == 'ler_artigo' && $artigo_leitura): ?>
    <div class="container" style="margin-top: 30px; margin-bottom:80px;">
        <a href="index.php?page=artigos" style="display:inline-block; margin-bottom:20px; font-weight:bold; color:#666;">← Voltar para lista</a>
        <div style="background:white; padding:40px; border-radius:15px; box-shadow:0 5px 20px rgba(0,0,0,0.05); max-width:900px; margin:0 auto;">
            <span style="background:var(--laranja-destaque); color:white; padding:5px 15px; border-radius:20px; font-size:12px; font-weight:bold; text-transform:uppercase;"><?= $artigo_leitura['categoria'] ?></span>
            <h1 style="font-size:42px; color:var(--azul-primario); margin:20px 0; line-height:1.2;"><?= htmlspecialchars($artigo_leitura['titulo']) ?></h1>
            <p style="color:#888; border-bottom:1px solid #eee; padding-bottom:20px; margin-bottom:30px;">
                Publicado em <?= date('d/m/Y', strtotime($artigo_leitura['data_postagem'])) ?>
            </p>
            <?php if($artigo_leitura['imagem']): ?>
                <img src="<?= $artigo_leitura['imagem'] ?>" class="article-header-img">
            <?php endif; ?>
            <div class="article-content">
                <?= $artigo_leitura['conteudo'] ?>
            </div>
        </div>
    </div>

<?php elseif ($pagina == 'todos'): ?>
    <div class="container" style="margin-top: 30px; margin-bottom:50px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="color: white; text-shadow: 1px 1px 2px #000; margin:0;">Todos os Anúncios</h2>
            <a href="index.php?page=home" style="color: white; font-weight: bold; text-shadow: 1px 1px 2px #000;">← Voltar</a>
        </div>
        <?php if(!empty($anuncios_por_cat)): ?>
            <?php foreach($anuncios_por_cat as $categoria_nome => $lista_anuncios): ?>
                <div style="background-color: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-radius: 15px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 40px;">
                    <h3 style="color: #004AAD; margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;"><?= htmlspecialchars($categoria_nome) ?></h3>
                    <div class="cards-grid" style="grid-template-columns: repeat(5, 1fr);">
                        <?php foreach($lista_anuncios as $anuncio): ?>
                            <a href="detalhes.php?id=<?= $anuncio['id'] ?>" class="card-anuncio" style="text-decoration: none; color: inherit;">
                                <img src="<?= $anuncio['capa'] ?: 'https://via.placeholder.com/300?text=Sem+Foto' ?>">
                                <div class="card-body-anuncio">
                                    <h3 class="card-title"><?= htmlspecialchars($anuncio['titulo']) ?></h3>
                                    <?php if(!empty($anuncio['modelo']) || !empty($anuncio['ano'])): ?><p class="card-model"><?= htmlspecialchars($anuncio['modelo'] . ' - ' . $anuncio['ano']) ?></p><?php endif; ?>
                                    <span class="price">R$ <?= number_format($anuncio['valor'], 2, ',', '.') ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php elseif ($pagina == 'busca'): ?>
    <div class="container" style="margin-top: 30px;">
        <div class="results-header"><div><a href="index.php" style="color:#999; font-size:14px;"><i class="fas fa-arrow-left"></i> Voltar</a><h2 class="results-title"><?= $filtro_categoria ? ucfirst($filtro_categoria) : "Busca: $termo_busca" ?></h2></div><span style="color:#666;"><?= count($resultados_busca) ?> resultados</span></div>
        <?php if(count($resultados_busca) > 0): ?>
            <?php if($tipo_resultado == 'comercio'): ?>
                <div class="clean-grid">
                    <?php foreach($resultados_busca as $item): $icone='fa-store'; $c=strtolower($item['categoria']); if(strpos($c,'academia')!==false)$icone='fa-dumbbell'; elseif(strpos($c,'farmacia')!==false)$icone='fa-pills'; elseif(strpos($c,'lanche')!==false)$icone='fa-utensils'; ?>
                    <div class="card-clean"><div class="clean-header"><div class="clean-info"><h3><?= htmlspecialchars($item['nome']) ?></h3><div class="clean-cat"><i class="fas <?= $icone ?>"></i> <?= htmlspecialchars($item['categoria']) ?></div></div><div class="clean-actions"><?php if($item['telefone']): $w=preg_replace("/[^0-9]/","",$item['telefone']); ?><a href="tel:<?= $w ?>" class="btn-circle-action phone"><i class="fas fa-phone-alt"></i></a><?php if(strlen($w)>=10): ?><a href="https://wa.me/55<?= $w ?>" target="_blank" class="btn-circle-action wpp"><i class="fab fa-whatsapp"></i></a><?php endif; endif; ?><?php if($item['geolocalizacao']): ?><a href="https://www.google.com/maps/search/?api=1&query=<?= $item['geolocalizacao'] ?>" target="_blank" class="btn-pill-map"><i class="fas fa-map-marker-alt"></i><span>Mapa</span></a><?php endif; ?></div></div><div class="clean-body"><div class="clean-row"><i class="fas fa-map-marker-alt"></i> <span><?= $item['endereco'] ?></span></div><?php if($item['horario']): ?><div class="clean-row time-row"><i class="fas fa-clock"></i> <span><?= $item['horario'] ?></span></div><?php endif; ?></div></div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="cards-grid">
                    <?php foreach($resultados_busca as $item): ?>
                    <a href="detalhes.php?id=<?= $item['id'] ?>" class="card-anuncio" style="text-decoration:none; color:inherit;"><div class="card-img-top"><span class="card-tag"><?= strtoupper($item['categoria']) ?></span><img src="<?= $item['capa'] ?: 'https://via.placeholder.com/300' ?>"></div><div class="card-body-anuncio"><h3 class="card-title"><?= htmlspecialchars($item['titulo']) ?></h3><?php if(!empty($item['modelo']) || !empty($item['ano'])): ?><p class="card-model"><?= $item['modelo'] ?></p><?php endif; ?><span class="price">R$ <?= number_format($item['valor'], 2, ',', '.') ?></span></div></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?><div style="text-align:center; padding:50px;"><h3>Nenhum resultado encontrado.</h3></div><?php endif; ?>
    </div>

<?php elseif ($pagina == 'utilidade'): ?>
    <div class="container" style="margin-top: 30px; margin-bottom: 50px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="color: white; text-shadow: 1px 1px 2px #000; margin:0;">Utilidade Pública</h2>
            <a href="index.php?page=home" style="color: white; font-weight: bold; text-shadow: 1px 1px 2px #000;">← Voltar</a>
        </div>
        <div class="util-grid">
            <div class="util-card" onclick="document.getElementById('modal-coleta').style.display='flex'"><i class="fas fa-recycle util-icon"></i><h3 class="util-title">Coleta Seletiva</h3><p>Guia de separação.</p></div>
            <div class="util-card" onclick="document.getElementById('modal-horta').style.display='flex'"><i class="fas fa-carrot util-icon"></i><h3 class="util-title">Horta</h3><p>Sustentabilidade.</p></div>
            <div class="util-card" onclick="document.getElementById('modal-telefones').style.display='flex'"><i class="fas fa-phone-alt util-icon"></i><h3 class="util-title">Telefones</h3><p>Úteis e Emergência.</p></div>
            <div class="util-card" onclick="document.getElementById('modal-biblioteca').style.display='flex'"><i class="fas fa-book-reader util-icon"></i><h3 class="util-title">Biblioteca</h3><p>Acervo.</p></div>
        </div>
    </div>
<?php endif; ?>

<script>
    <?php if ($pagina == 'home'): ?>
        // --- SLIDER PRINCIPAL (BANNER) ---
        let currentIndex = 0;
        const wrapper = document.getElementById('sliderWrapper');
        const slides = document.querySelectorAll('.slider-slide');
        const dotsContainer = document.getElementById('dotsContainer');
        const totalSlides = slides.length;
        let slideInterval;

        if(dotsContainer && totalSlides > 0) {
            dotsContainer.innerHTML = '';
            for(let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('span');
                dot.classList.add('dot');
                if(i === 0) dot.classList.add('active');
                dot.setAttribute('onclick', `currentSlide(${i})`);
                dotsContainer.appendChild(dot);
            }
        }

        function updateSliderPosition() {
            if(!wrapper) return;
            const offset = -currentIndex * 100;
            wrapper.style.transform = `translateX(${offset}%)`;
            const dots = document.querySelectorAll('.dot');
            dots.forEach(dot => dot.classList.remove('active'));
            if(dots[currentIndex]) dots[currentIndex].classList.add('active');
        }

        function moveSlide(direction) {
            currentIndex += direction;
            if (currentIndex >= totalSlides) currentIndex = 0;
            if (currentIndex < 0) currentIndex = totalSlides - 1;
            updateSliderPosition();
        }

        function currentSlide(n) { currentIndex = n; updateSliderPosition(); }
        function startSlide() { slideInterval = setInterval(() => { moveSlide(1); }, 4000); }
        function stopSlide() { clearInterval(slideInterval); }

        const sliderContainer = document.getElementById('mainSlider');
        if(sliderContainer) {
            startSlide();
            sliderContainer.addEventListener('mouseenter', stopSlide);
            sliderContainer.addEventListener('mouseleave', startSlide);
        }

        // --- CARROSSEL DE ANÚNCIOS (SETINHAS) ---
        function scrollAds(direction) {
            const container = document.getElementById('adsTrackContainer');
            // Rola a largura do container (mostra os próximos X itens)
            const scrollAmount = container.offsetWidth; 
            container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
        }
    <?php endif; ?>
</script>

<?php include 'rodape.php'; ?>