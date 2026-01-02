<?php
// Tenta incluir o arquivo de conexão que você já usa no site
// SE O SEU ARQUIVO TIVER OUTRO NOME (ex: config.php, db.php), MUDE AQUI EMBAIXO:
require_once 'conexao.php'; 

// Se a variável de conexão no seu arquivo não se chamar '$conn' ou '$mysqli',
// ajuste esta linha. Geralmente é $conn, $mysqli ou $pdo.
if (!isset($conn)) {
    // Tenta conectar manualmente apenas se o require falhar (Preencha com dados REAIS da hospedagem se necessário)
    $host = 'localhost'; 
    $usuario = 'SEU_USUARIO_DA_HOSPEDAGEM'; 
    $senha = 'SUA_SENHA_DA_HOSPEDAGEM'; 
    $banco = 'jaseionde';
    $conn = new mysqli($host, $usuario, $senha, $banco);
}

// Verifica conexão
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Busca os artigos
$sql = "SELECT id, titulo, categoria, imagem, resumo, data_postagem FROM artigos ORDER BY data_postagem DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artigos - Já Sei Onde</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { text-align: center; color: #333; }
        .grid-artigos { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 30px; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow: hidden; }
        .card img { width: 100%; height: 200px; object-fit: cover; }
        .card-body { padding: 15px; }
        .categoria { background-color: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; text-transform: uppercase; }
        .data { font-size: 12px; color: #888; float: right; }
        .card h3 { margin: 10px 0; font-size: 18px; color: #333; }
        .card p { color: #666; font-size: 14px; }
        .btn-ler { display: inline-block; margin-top: 10px; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Últimas Notícias</h1>
    <div class="grid-artigos">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($row['imagem'] ? $row['imagem'] : 'https://via.placeholder.com/600x400'); ?>" alt="Imagem">
                    <div class="card-body">
                        <div>
                            <span class="categoria"><?php echo htmlspecialchars($row['categoria']); ?></span>
                            <span class="data"><?php echo date('d/m/Y', strtotime($row['data_postagem'])); ?></span>
                        </div>
                        <h3><?php echo htmlspecialchars($row['titulo']); ?></h3>
                        <p><?php echo htmlspecialchars($row['resumo']); ?></p>
                        <a href="ver_artigo.php?id=<?php echo $row['id']; ?>" class="btn-ler">Ler Completo</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nenhum artigo encontrado ou erro no SQL: <?php echo $conn->error; ?></p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>