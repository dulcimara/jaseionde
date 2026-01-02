<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso e Privacidade - Já Sei Onde</title>
    <style>
        :root { --azul-primario: #0056b3; --branco: #ffffff; }
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f4f4; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: var(--branco); padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { color: var(--azul-primario); text-align: center; }
        h2 { color: var(--azul-primario); font-size: 18px; margin-top: 20px; }
        p, li { line-height: 1.6; font-size: 15px; }
        .btn-voltar { display: inline-block; margin-top: 20px; text-decoration: none; background: var(--azul-primario); color: white; padding: 10px 20px; border-radius: 5px; font-weight: bold; }
        .btn-voltar:hover { background: #004494; }
    </style>
</head>
<body>

<div class="container">
    <h1>Termos de Uso e Política de Privacidade</h1>
    <p><strong>Última atualização:</strong> <?= date('d/m/Y') ?></p>

    <p>Bem-vindo ao <strong>Já Sei Onde</strong>. Ao se cadastrar em nossa plataforma, você concorda com os termos descritos abaixo, em conformidade com a Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018).</p>

    <h2>1. Coleta de Dados</h2>
    <p>Para a prestação de nossos serviços, coletamos dados pessoais como: Nome Completo, CPF, E-mail e Telefone. A veracidade das informações fornecidas é de responsabilidade do usuário.</p>

    <h2>2. Finalidade do Tratamento dos Dados</h2>
    <p>Os dados coletados têm como finalidade principal:</p>
    <ul>
        <li>Melhorar a navegação e a experiência do usuário no site;</li>
        <li>Gerenciar o acesso à área de anúncios e funcionalidades restritas;</li>
        <li>Garantir a segurança e autenticidade das transações de compra e venda.</li>
    </ul>

    <h2>3. Compartilhamento de Dados</h2>
    <p>O usuário declara estar ciente e concorda que seus dados poderão ser compartilhados com <strong>parceiros comerciais</strong> do Já Sei Onde para fins de:</p>
    <ul>
        <li>Envio de promoções, ofertas e novidades relevantes;</li>
        <li>Geração de dados estatísticos e estudos de mercado (de forma anonimizada sempre que possível);</li>
        <li>Processamento de pagamentos e validações de segurança.</li>
    </ul>

    <h2>4. Consentimento</h2>
    <p>Ao clicar no botão "Cadastrar", o usuário manifesta seu consentimento livre, expresso e informado para o tratamento de seus dados conforme descrito neste documento.</p>

    <h2>5. Seus Direitos</h2>
    <p>Você pode, a qualquer momento, solicitar a visualização, correção ou exclusão de seus dados de nossa base, entrando em contato através de nossos canais de atendimento.</p>

    <br>
    <a href="cadastro.php" class="btn-voltar">Voltar para o Cadastro</a>
</div>

</body>
</html>