<?php
// rodape.php
?>
    <div id="modal-login" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('modal-login').style.display='none'">×</span>
            <h2 style="text-align: center;">Entrar</h2>
            
            <div style="background:#fff3cd; color:#856404; padding:10px; margin-bottom:15px; border-radius:4px; font-size:12px; border:1px solid #ffeeba;">
                <strong>Nota:</strong> Se o navegador alertar sobre "vazamento de dados", ignore. É um falso positivo do navegador. Você pode alterar sua senha em "Configurações".
            </div>

            <form method="POST" action="index.php">
                <input type="hidden" name="acao" value="login">
                <input type="hidden" name="redirect_to" id="redirect_input" value="index.php">
                
                <label style="display:block; margin-bottom:5px;">E-mail</label>
                <input type="email" name="email" required style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ddd; border-radius:4px;">
                
                <label style="display:block; margin-bottom:5px;">Senha</label>
                <div style="position:relative;">
                    <input type="password" name="senha" id="login_senha_modal" required style="width:100%; padding:10px; padding-right:40px; margin-bottom:10px; border:1px solid #ddd; border-radius:4px;">
                    <i class="fas fa-eye" onclick="toggleSenhaRodape('login_senha_modal', this)" style="position:absolute; right:10px; top:12px; cursor:pointer; color:#777;"></i>
                </div>

                <button type="submit" class="btn-full">Entrar</button>
            </form>
            <p style="text-align:center; font-size:13px; margin-top:10px;">
                Ainda não tem conta? <a href="cadastro.php" style="color:var(--azul-primario); font-weight:bold;">Cadastre-se</a>
            </p>
        </div>
    </div>
    
    <div id="modal-contato" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('modal-contato').style.display='none'">×</span>
            <h2 style="text-align:center;">Fale Conosco</h2>
            <form method="POST" action="index.php">
                <input type="hidden" name="acao" value="contato">
                <input type="text" name="nome" placeholder="Seu Nome" required style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ddd;">
                <input type="email" name="email" placeholder="Seu E-mail" required style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ddd;">
                <textarea name="mensagem" rows="4" placeholder="Mensagem" required style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ddd;"></textarea>
                <button type="submit" class="btn-full">Enviar</button>
            </form>
        </div>
    </div>

    <script>
      
      function openNav() {
    document.getElementById("mySidenav").style.width = "250px";
}

function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
}


        // VARIAVEL DE ESTADO
        const usuarioLogadoRodape = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        function openNav() { document.getElementById("mySidenav").style.width = "250px"; }
        function closeNav() { document.getElementById("mySidenav").style.width = "0"; }
        
        function abrirLogin(destino = 'index.php') {
            document.getElementById('redirect_input').value = destino;
            document.getElementById('modal-login').style.display = 'flex';
        }

        function clicarAnunciar() {
            if (usuarioLogadoRodape) {
                window.location.href = 'anunciar.php';
            } else {
                abrirLogin('anunciar.php');
            }
        }

        function toggleSenhaRodape(id, icon) {
            var campo = document.getElementById(id);
            if (campo.type === "password") {
                campo.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                campo.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>