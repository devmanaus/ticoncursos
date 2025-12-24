<?php
session_start();

// Registra acesso
$acessos_file = 'acessos.json';
$acessos = ['total' => 0];
if (file_exists($acessos_file)) {
    $acessos = json_decode(file_get_contents($acessos_file), true);
}
$acessos['total']++;
file_put_contents($acessos_file, json_encode($acessos, JSON_PRETTY_PRINT));

// Reinicia jogo se necessário
if (isset($_GET['nova'])) {
    session_destroy();
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TIConcursos</title>
    <link rel="stylesheet" href="css/estilo.css" />
</head>
<body>
    <div class="app-container">
        <div class="logo">💻 TI Concursos</div>
        <center><h3>Resolva questões de Informática e acelere sua aprovação!</h3></center>
        <!-- ✅ Força nova sessão ao clicar em INICIAR -->
        <button class="btn primary" onclick="location.href='jogo.php?nova=1'">INICIAR</button>
        <button class="btn outline" onclick="abrirModal()">INFORMAÇÕES</button>
        <button class="btn outline" onclick="location.href='ranking.php'">RANKING</button>
    </div>

    <!-- Modal de Informações -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal()">&times;</span>
            <h2>ℹ️ Informações</h2>
            <ul>
                <li>Banco de dados contendo <strong>1.000 questões </strong> de concursos de informática produzidas por IA</li>
                <li>Assuntos:Arquitetura de computadores, programação, engenharia de software, Banco de dados, 
                governança em TI, segurança da informação, informática básica, rede de computadores</li>
                <li>As questões são <strong>sorteadas aleatoriamente</strong> para cada participante</li>
                <li>Cada questão tem <strong>4 alternativas</strong> e <strong>30 segundos</strong> para responder</li>
                <li>Ganha <strong>10 pontos </strong> por cada resposta correta, e as 10 maiores pontuações aparecem no ranking</li>
                <li>Sugestões enviar email para <strong>tucumadev@gmail.com</strong> </li>
            </ul>
            <button class="btn primary" onclick="fecharModal()">FECHAR</button>
        </div>
    </div>
    <!-- Rodapé com copyright -->
    <footer class="rodape">
        <center>© <?= date('Y') ?> - Desenvolvido por <strong>tucumadev</strong></center>
    </footer>

    <script>
        function abrirModal() {
            document.getElementById('modal').style.display = 'block';
        }
        function fecharModal() {
            document.getElementById('modal').style.display = 'none';
        }
        window.onclick = function(event) {
            const modal = document.getElementById('modal');
            if (event.target === modal) fecharModal();
        }
    </script>
</body>
</html>