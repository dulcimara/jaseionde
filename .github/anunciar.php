<?php
require 'db.php';
session_start();

// Verifica Login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Você precisa estar logado para anunciar!'); window.location.href='index.php';</script>";
    exit;
}

$msg = "";

// PROCESSAMENTO DO FORMULÁRIO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'publicar') {
    // Campos Comuns
    $titulo = $_POST['titulo'];
    $categoria = $_POST['categoria'];
    $valor = str_replace(['R$', '.', ','], ['', '', '.'], $_POST['valor']); 
    $descricao = $_POST['descricao'];
    $contato = $_POST['contato'];

    // Campos Específicos
    $quartos = $_POST['quartos'] ?? null;
    $banheiros = $_POST['banheiros'] ?? null;
    $area = $_POST['area'] ?? null;
    $vagas = $_POST['vagas'] ?? null;
    $modelo = $_POST['modelo'] ?? null;
    $ano = $_POST['ano'] ?? null;
    $km = $_POST['km'] ?? null;
    $cor = $_POST['cor'] ?? null;
    $tipo_negocio = $_POST['tipo_negocio'] ?? null;
    $tipo_imovel = $_POST['tipo_imovel'] ?? null;
    
    $sql = "INSERT INTO anuncios_detalhados (
        usuario_id, titulo, categoria, valor, descricao, contato_whatsapp, status,
        quartos, banheiros, area, vagas, modelo, ano, km, cor, tipo_negocio, tipo_imovel
    ) VALUES (?, ?, ?, ?, ?, ?, 'pendente', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([
        $_SESSION['user_id'], $titulo, $categoria, $valor, $descricao, $contato,
        $quartos, $banheiros, $area, $vagas, $modelo, $ano, $km, $cor, $tipo_negocio, $tipo_imovel
    ])) {
        $anuncio_id = $pdo->lastInsertId();
        
        // Upload de Fotos
        if (isset($_FILES['fotos'])) {
            $total = count($_FILES['fotos']['name']);
            // Limita no PHP também (segurança extra)
            if($total > 10) $total = 10; 

            for ($i = 0; $i < $total; $i++) {
                $tmp_name = $_FILES['fotos']['tmp_name'][$i];
                $name = $_FILES['fotos']['name'][$i];
                $size = $_FILES['fotos']['size'][$i];
                
                if ($size > 307200) continue; // 300KB

                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $novo_nome = uniqid() . '.' . $ext;
                    $destino = 'uploads/' . $novo_nome;
                    if (!is_dir('uploads')) mkdir('uploads'); 
                    if (move_uploaded_file($tmp_name, $destino)) {
                        $pdo->prepare("INSERT INTO anuncio_fotos (anuncio_id, caminho_arquivo) VALUES (?, ?)")->execute([$anuncio_id, $destino]);
                    }
                }
            }
        }
        $msg = "Anúncio enviado para aprovação!";
    } else {
        $msg = "Erro ao salvar anúncio.";
    }
}

include 'topo.php'; 
?>

<style>
    /* Estilos de Animação e Layout */
    .step-container { display: none; }
    .step-container.active { display: block; animation: fadeIn 0.5s; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* Seletor de Categoria */
    .cat-selector { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 30px; }
    
    .cat-option { 
        background: white; border: 2px solid #eee; border-radius: 12px; padding: 40px 20px; 
        text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }
    .cat-option i { font-size: 45px; color: var(--azul-primario); margin-bottom: 15px; transition: 0.3s; }
    .cat-option h3 { margin: 0; color: #333; font-size: 18px; font-weight: 600; transition: 0.3s; }

    /* Hover Laranja */
    .cat-option:hover { 
        background-color: var(--laranja-destaque); border-color: var(--laranja-destaque); 
        transform: translateY(-5px); box-shadow: 0 8px 15px rgba(255, 140, 0, 0.3);
    }
    .cat-option:hover i, .cat-option:hover h3 { color: white !important; }

    /* Formulário Estilo "Dados Cadastrais" */
    .form-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto; border: 1px solid #eee; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 700; color: #444; font-size: 14px; }
    
    .form-group input[type="text"], .form-group input[type="number"], .form-group select, .form-group textarea { 
        width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 4px; 
        font-size: 15px; color: #333; box-sizing: border-box; background-color: #fff; transition: border-color 0.3s;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--azul-primario); outline: none; }

    /* Campos Específicos */
    .specific-fields { display: none; background: #f8f9fa; padding: 25px; border-radius: 6px; margin-bottom: 25px; border: 1px solid #e9ecef; }

    /* Upload e Preview */
    .upload-area { border: 2px dashed #ccc; padding: 25px; text-align: center; cursor: pointer; border-radius: 6px; background: #fafafa; margin-bottom: 15px; transition: 0.3s; }
    .upload-area:hover { border-color: var(--azul-primario); background: #eef6ff; }
    
    .preview-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-top: 10px; }
    .preview-item { height: 80px; border-radius: 4px; overflow: hidden; position: relative; border: 1px solid #ddd; }
    .preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .preview-remove { position: absolute; top: 0; right: 0; background: rgba(255,0,0,0.7); color: white; border: none; cursor: pointer; font-size: 12px; padding: 2px 6px; }

    /* Modal */
    #modal-preview { background: rgba(0,0,0,0.8); }
    .preview-content { max-width: 600px; width: 90%; background: white; padding: 0; border-radius: 8px; overflow: hidden; }
    .preview-header { background: var(--azul-primario); color: white; padding: 15px; font-weight: bold; display: flex; justify-content: space-between; }
    .preview-body { padding: 20px; max-height: 70vh; overflow-y: auto; }
    .preview-footer { padding: 15px; background: #eee; text-align: right; }

    @media (max-width: 768px) { .cat-selector { grid-template-columns: 1fr 1fr; } }
</style>

<div class="container" style="margin-top: 40px; margin-bottom: 50px;">
    
    <?php if($msg): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
            <?= $msg ?> <br> <a href="index.php" style="font-weight:bold;">Voltar para Home</a>
        </div>
    <?php else: ?>

    <div id="step-1" class="step-container active">
        <h2 style="text-align:center; color:var(--azul-primario); font-weight:700;">O que você deseja anunciar?</h2>
        <div class="cat-selector">
            <div class="cat-option" onclick="selectCategory('imoveis')">
                <i class="fas fa-building"></i>
                <h3>Imóveis</h3>
            </div>
            <div class="cat-option" onclick="selectCategory('veiculos')">
                <i class="fas fa-car"></i>
                <h3>Veículos</h3>
            </div>
            <div class="cat-option" onclick="selectCategory('servicos')">
                <i class="fas fa-tools"></i>
                <h3>Serviços</h3>
            </div>
            <div class="cat-option" onclick="selectCategory('desapegos')">
                <i class="fas fa-box-open"></i>
                <h3>Desapegos</h3>
            </div>
        </div>
    </div>

    <div id="step-2" class="step-container">
        <div class="form-box">
            
            <button type="button" onclick="backToStep1()" style="background:none; border:none; color:#666; cursor:pointer; margin-bottom:20px; font-size:14px; display:flex; align-items:center; gap:5px;">
                <i class="fas fa-arrow-left"></i> Voltar e trocar categoria
            </button>

            <h2 id="form-title" style="color:var(--azul-primario); margin-top:0; margin-bottom:25px; padding-bottom:15px; border-bottom:1px solid #eee;">Criar Anúncio</h2>

            <form method="POST" enctype="multipart/form-data" id="mainForm">
                <input type="hidden" name="acao" value="publicar">
                <input type="hidden" name="categoria" id="input-categoria">

                <div class="form-group">
                    <label>Título do Anúncio</label>
                    <input type="text" name="titulo" id="p_titulo" required placeholder="Ex: Vendo Apartamento no Centro">
                </div>

                <div id="fields-imoveis" class="specific-fields">
                    <h4 style="margin-top:0; color:var(--azul-primario); margin-bottom:15px;">Detalhes do Imóvel</h4>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:15px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Finalidade</label>
                            <select name="tipo_negocio" id="p_tipo_negocio"><option value="venda">Venda</option><option value="aluguel">Aluguel</option></select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label>Tipo de Imóvel</label>
                            <select name="tipo_imovel" id="p_tipo_imovel"><option value="apartamento">Apartamento</option><option value="casa">Casa</option><option value="chacara">Chácara</option><option value="loja">Loja Comercial</option><option value="terreno">Terreno</option></select>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div class="form-group"><label>Quartos</label><input type="number" name="quartos" id="p_quartos"></div>
                        <div class="form-group"><label>Banheiros</label><input type="number" name="banheiros" id="p_banheiros"></div>
                        <div class="form-group"><label>Área (m²)</label><input type="number" name="area" id="p_area"></div>
                        <div class="form-group"><label>Vagas</label><input type="number" name="vagas" id="p_vagas"></div>
                    </div>
                </div>

                <div id="fields-veiculos" class="specific-fields">
                    <h4 style="margin-top:0; color:var(--azul-primario); margin-bottom:15px;">Detalhes do Veículo</h4>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div class="form-group"><label>Modelo</label><input type="text" name="modelo" id="p_modelo"></div>
                        <div class="form-group"><label>Ano</label><input type="number" name="ano" id="p_ano"></div>
                        <div class="form-group"><label>Quilometragem (KM)</label><input type="number" name="km" id="p_km"></div>
                        <div class="form-group"><label>Cor</label><input type="text" name="cor" id="p_cor"></div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <div class="form-group">
                        <label>Valor (R$)</label>
                        <input type="text" name="valor" id="valor" required placeholder="0,00">
                    </div>
                    <div class="form-group">
                        <label>WhatsApp</label>
                        <input type="text" name="contato" id="p_contato" required placeholder="(00) 00000-0000">
                    </div>
                </div>

                <div class="form-group">
                    <label>Descrição Detalhada</label>
                    <textarea name="descricao" id="p_descricao" rows="5" required placeholder="Descreva os detalhes..."></textarea>
                </div>

                <div class="form-group">
                    <label>Fotos (Máx 10)</label>
                    <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-camera" style="font-size:30px; color:#ccc;"></i>
                        <p style="margin:5px 0; color:#666;">Clique para adicionar fotos</p>
                    </div>
                    <input type="file" name="fotos[]" id="fileInput" multiple accept="image/*" style="display:none">
                    <div class="preview-grid" id="previewGrid"></div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 2fr; gap:15px; margin-top:30px;">
                    <button type="button" onclick="openPreview()" style="padding:15px; background:#6c757d; color:white; border:none; border-radius:4px; font-weight:bold; cursor:pointer;">Pré-visualizar</button>
                    <button type="submit" style="padding:15px; background:var(--laranja-destaque); color:white; border:none; border-radius:4px; font-weight:bold; cursor:pointer; font-size:16px;">Publicar Agora</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

</div>

<div id="modal-preview" class="modal">
    <div class="modal-content preview-content">
        <div class="preview-header">
            <span>Pré-visualização</span>
            <span onclick="document.getElementById('modal-preview').style.display='none'" style="cursor:pointer;">×</span>
        </div>
        <div class="preview-body">
            <span class="badge-cat" style="background:var(--azul-primario); color:white; padding:4px 8px; border-radius:4px; font-size:12px;" id="prev-cat"></span>
            <h2 id="prev-titulo" style="margin:10px 0; color:#333;"></h2>
            <h3 id="prev-valor" style="color:var(--laranja-destaque); font-weight:bold; font-size:24px;"></h3>
            <div id="prev-detalhes" style="background:#f1f3f5; padding:15px; margin:15px 0; font-size:14px; color:#555; border-radius:4px; line-height:1.8;"></div>
            <p id="prev-descricao" style="line-height:1.6; color:#444; white-space: pre-wrap;"></p>
            <hr style="border:0; border-top:1px solid #eee; margin:20px 0;">
            <p><strong>Contato:</strong> <span id="prev-contato" style="color:green; font-weight:bold;"></span></p>
        </div>
        <div class="preview-footer">
            <button onclick="document.getElementById('modal-preview').style.display='none'" style="padding:10px 20px; border:1px solid #ccc; background:white; cursor:pointer; border-radius:4px;">Voltar</button>
        </div>
    </div>
</div>

<script>
    // Gerenciador de Arquivos (DataTransfer) para permitir adição cumulativa
    const dt = new DataTransfer(); 
    const fileInput = document.getElementById('fileInput');
    const previewGrid = document.getElementById('previewGrid');

    // 1. Lógica de Categorias
    function selectCategory(cat) {
        document.getElementById('input-categoria').value = cat;
        document.getElementById('step-1').classList.remove('active');
        document.getElementById('step-2').classList.add('active');
        
        // Reset campos
        document.querySelectorAll('.specific-fields').forEach(el => el.style.display = 'none');
        let title = document.getElementById('form-title');

        // Configura Título e Campos
        if (cat === 'imoveis') {
            document.getElementById('fields-imoveis').style.display = 'block';
            title.innerText = 'Anunciar Imóvel';
        } else if (cat === 'veiculos') {
            document.getElementById('fields-veiculos').style.display = 'block';
            title.innerText = 'Anunciar Veículo';
        } else if (cat === 'servicos') {
            title.innerText = 'Anunciar Serviço';
        } else if (cat === 'desapegos') {
            title.innerText = 'Anunciar Desapego';
        }
        window.scrollTo(0, 0);
    }

    function backToStep1() {
        document.getElementById('step-2').classList.remove('active');
        document.getElementById('step-1').classList.add('active');
    }

    // 2. Lógica de Upload (Acumulativa)
    fileInput.addEventListener('change', function(e) {
        for(let file of this.files){
            if(file.size < 307200 && dt.items.length < 10){ // < 300KB e Max 10
                dt.items.add(file);
            } else if (file.size >= 307200) {
                alert("Arquivo " + file.name + " ignorado (maior que 300KB).");
            }
        }
        this.files = dt.files; // Atualiza o input com a lista acumulada
        renderPreviews();
    });

    function renderPreviews(){
        previewGrid.innerHTML = '';
        for(let i = 0; i < dt.files.length; i++){
            let file = dt.files[i];
            let div = document.createElement('div');
            div.className = 'preview-item';
            
            let img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            
            let btn = document.createElement('button');
            btn.className = 'preview-remove';
            btn.innerHTML = '×';
            btn.onclick = function(e){ 
                e.preventDefault(); 
                dt.items.remove(i); 
                fileInput.files = dt.files; 
                renderPreviews(); 
            };

            div.appendChild(img);
            div.appendChild(btn);
            previewGrid.appendChild(div);
        }
    }

    // 3. Máscara Moeda
    const valorInput = document.getElementById('valor');
    valorInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g,"");
        value = (value/100).toFixed(2) + "";
        value = value.replace(".", ",");
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        e.target.value = "R$ " + value;
    });

    // 4. Pré-visualização
    function openPreview() {
        let cat = document.getElementById('input-categoria').value;
        document.getElementById('prev-cat').innerText = cat.toUpperCase();
        document.getElementById('prev-titulo').innerText = document.getElementById('p_titulo').value;
        document.getElementById('prev-valor').innerText = document.getElementById('valor').value;
        document.getElementById('prev-descricao').innerText = document.getElementById('p_descricao').value;
        document.getElementById('prev-contato').innerText = document.getElementById('p_contato').value;

        let detailsHtml = "";
        if (cat === 'imoveis') {
            detailsHtml += "<strong>" + document.getElementById('p_tipo_negocio').value.toUpperCase() + "</strong> - " + document.getElementById('p_tipo_imovel').value.toUpperCase() + "<br>";
            detailsHtml += "Quartos: " + document.getElementById('p_quartos').value + " | ";
            detailsHtml += "Área: " + document.getElementById('p_area').value + "m² | ";
            detailsHtml += "Vagas: " + document.getElementById('p_vagas').value;
        } else if (cat === 'veiculos') {
            detailsHtml += "Modelo: " + document.getElementById('p_modelo').value + " | ";
            detailsHtml += "Ano: " + document.getElementById('p_ano').value + " | ";
            detailsHtml += "KM: " + document.getElementById('p_km').value;
        }
        document.getElementById('prev-detalhes').innerHTML = detailsHtml;
        document.getElementById('modal-preview').style.display = 'flex';
    }
</script>

<?php include 'rodape.php'; ?>