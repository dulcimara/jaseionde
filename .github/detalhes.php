<?php
require 'db.php'; 
session_start(); 

// Pega o ID da URL (ex: detalhes.php?id=1)
$id_anuncio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$anuncio = null;
$fotos = [];

if($id_anuncio > 0) {
    // 1. Busca os dados do anúncio
    $stmt = $pdo->prepare("SELECT * FROM anuncios_detalhados WHERE id = ? AND status != 'rejeitado'");
    $stmt->execute([$id_anuncio]);
    $anuncio = $stmt->fetch();

    // 2. Busca as fotos do anúncio
    if($anuncio) {
        $stmt_fotos = $pdo->prepare("SELECT caminho_arquivo FROM anuncio_fotos WHERE anuncio_id = ?");
        $stmt_fotos->execute([$id_anuncio]);
        $fotos = $stmt_fotos->fetchAll(PDO::FETCH_COLUMN);
    }
}

// Configuração para o Topo (Menu)
$pagina = 'detalhes';
include 'topo.php'; 
?>

<style>
    /* Layout de Grade: Galeria na Esquerda, Info na Direita */
    .detalhes-container {
        display: grid;
        grid-template-columns: 3fr 2fr; /* 60% Galeria, 40% Info */
        gap: 30px;
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-top: 20px;
        margin-bottom: 50px;
    }

    /* Galeria */
    .main-photo-frame {
        width: 100%;
        height: 400px;
        background: #f4f4f4;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #eee;
    }
    .main-photo-frame img {
        width: 100%;
        height: 100%;
        object-fit: contain; /* Mostra a foto inteira sem cortar */
    }
    .thumbnails {
        display: flex;
        gap: 10px;
        overflow-x: auto;
    }
    .thumb {
        width: 80px;
        height: 80px;
        border-radius: 6px;
        cursor: pointer;
        object-fit: cover;
        opacity: 0.6;
        transition: 0.3s;
        border: 2px solid transparent;
    }
    .thumb:hover, .thumb.active {
        opacity: 1;
        border-color: #004AAD;
    }

    /* Informações */
    .info-area h1 {
        color: #004AAD;
        font-size: 28px;
        margin: 0 0 10px 0;
        line-height: 1.2;
    }
    .price-tag {
        font-size: 32px;
        color: #004AAD;
        font-weight: 800;
        margin-bottom: 20px;
        display: block;
    }
    .meta-info {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        font-size: 14px;
        color: #666;
        background: #f9f9f9;
        padding: 10px;
        border-radius: 6px;
    }
    .meta-item { display: flex; align-items: center; gap: 5px; }
    
    .desc-box {
        margin-bottom: 30px;
        line-height: 1.6;
        color: #444;
    }
    .desc-title {
        font-weight: bold;
        display: block;
        margin-bottom: 5px;
        color: #333;
    }

    /* Botão WhatsApp */
    .btn-wpp-large {
        background-color: #25D366;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 15px;
        border-radius: 8px;
        font-size: 18px;
        font-weight: bold;
        text-decoration: none;
        transition: 0.3s;
        box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);
    }
    .btn-wpp-large:hover {
        background-color: #128C7E;
        transform: translateY(-2px);
    }

    /* Botão Voltar */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        color: #666;
        font-weight: 600;
        margin-top: 30px;
    }
    .btn-back:hover { color: #004AAD; }

    /* Responsivo */
    @media (max-width: 768px) {
        .detalhes-container {
            grid-template-columns: 1fr; /* Vira uma coluna só no celular */
        }
        .main-photo-frame {
            height: 250px;
        }
    }
</style>

<div class="container">
    
    <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar para o início</a>

    <?php if ($anuncio): ?>
        
        <div class="detalhes-container">
            
            <div class="gallery-area">
                <div class="main-photo-frame">
                    <?php $foto_principal = !empty($fotos) ? $fotos[0] : 'https://via.placeholder.com/600x400?text=Sem+Foto'; ?>
                    <img src="<?= $foto_principal ?>" id="mainImage" alt="Foto Principal">
                </div>
                
                <?php if (count($fotos) > 1): ?>
                <div class="thumbnails">
                    <?php foreach($fotos as $foto): ?>
                        <img src="<?= $foto ?>" class="thumb" onclick="changeImage(this.src)">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="info-area">
                <span style="background: #004AAD; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; display: inline-block;">
                    <?= htmlspecialchars($anuncio['categoria']) ?>
                </span>

                <h1><?= htmlspecialchars($anuncio['titulo']) ?></h1>
                
                <span class="price-tag">R$ <?= number_format($anuncio['valor'], 2, ',', '.') ?></span>

                <?php if(!empty($anuncio['modelo']) || !empty($anuncio['ano'])): ?>
                <div class="meta-info">
                    <?php if(!empty($anuncio['modelo'])): ?>
                        <div class="meta-item"><i class="fas fa-tag"></i> <span><?= htmlspecialchars($anuncio['modelo']) ?></span></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($anuncio['ano'])): ?>
                        <div class="meta-item"><i class="fas fa-calendar-alt"></i> <span><?= htmlspecialchars($anuncio['ano']) ?></span></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="desc-box">
                    <span class="desc-title">Descrição:</span>
                    <?= nl2br(htmlspecialchars($anuncio['descricao'])) ?>
                </div>

                <?php 
                    $whats = preg_replace("/[^0-9]/", "", $anuncio['contato_whatsapp']); 
                ?>
                <a href="https://wa.me/55<?= $whats ?>?text=Olá, vi seu anúncio '<?= urlencode($anuncio['titulo']) ?>' no site Já Sei Onde." target="_blank" class="btn-wpp-large">
                    <i class="fab fa-whatsapp"></i> Conversar com Vendedor
                </a>
                
                <p style="text-align: center; margin-top: 15px; font-size: 12px; color: #999;">
                    <i class="fas fa-shield-alt"></i> Dica de segurança: Não faça pagamentos antecipados.
                </p>
            </div>

        </div>

    <?php else: ?>
        <div style="text-align: center; padding: 100px 0;">
            <i class="fas fa-exclamation-circle" style="font-size: 50px; color: #ddd; margin-bottom: 20px;"></i>
            <h2 style="color: #666;">Anúncio não encontrado ou indisponível.</h2>
            <a href="index.php" style="color: #004AAD; font-weight: bold;">Voltar para a Home</a>
        </div>
    <?php endif; ?>

</div>

<script>
    function changeImage(src) {
        document.getElementById('mainImage').src = src;
        // Remove active de todos
        document.querySelectorAll('.thumb').forEach(el => el.style.opacity = '0.6');
        document.querySelectorAll('.thumb').forEach(el => el.style.borderColor = 'transparent');
        // Adiciona efeito visual no clicado (opcional, simples aqui)
        event.target.style.opacity = '1';
        event.target.style.borderColor = '#004AAD';
    }
</script>

<?php include 'rodape.php'; ?>