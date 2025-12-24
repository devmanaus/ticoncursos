<?php
session_start();

// ✅ SEMPRE inicia uma nova sessão de jogo
if (isset($_GET['nova']) || !isset($_SESSION['jogo_nova'])) {
    session_destroy();
    session_start();
    
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");

    define('TEMPO_POR_QUESTAO', 30);

    // Carregar banco.json
    $json = file_get_contents('bd/banco.json');
    $questoes = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($questoes) || count($questoes) < 10) {
        die('❌ Erro: arquivo banco.json inválido ou com menos de 10 questões.');
    }

    shuffle($questoes);
    $_SESSION['questoes'] = $questoes;
    $_SESSION['indice'] = 0;
    $_SESSION['pontos'] = 0;
    $_SESSION['inicio_questao'] = time();
    $_SESSION['jogo_nova'] = true; // Flag para controle
}

$indice = $_SESSION['indice'];
$questoes_sessao = $_SESSION['questoes'];

if ($indice >= count($questoes_sessao)) {
    header('Location: salvar.php');
    exit;
}

// Verifica timeout
$agora = time();
$tempo_decorrido = $agora - $_SESSION['inicio_questao'];
if ($tempo_decorrido > 30) {
    header('Location: salvar.php?timeout=1');
    exit;
}

// Processa resposta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resposta'])) {
    $resposta_enviada = trim($_POST['resposta']);
    
    if ($resposta_enviada === '###TIMEOUT###') {
        header('Location: salvar.php?timeout=1');
        exit;
    }

    $correta = $questoes_sessao[$indice]['resposta_correta'];
    if ($resposta_enviada === $correta) {
        $_SESSION['pontos'] += 10;
        $_SESSION['indice']++;
        if ($_SESSION['indice'] >= count($questoes_sessao)) {
            header('Location: salvar.php');
            exit;
        }
        $_SESSION['inicio_questao'] = time(); // Reinicia tempo
        header('Location: jogo.php');
        exit;
    } else {
        header('Location: salvar.php');
        exit;
    }
}

// Carrega dados
$questao = $questoes_sessao[$indice];
$pergunta = htmlspecialchars($questao['pergunta']);
$opcoes = [
    htmlspecialchars($questao['opcao_a']),
    htmlspecialchars($questao['opcao_b']),
    htmlspecialchars($questao['opcao_c']),
    htmlspecialchars($questao['opcao_d'])
];
$tempo_restante = max(0, 30 - ($agora - $_SESSION['inicio_questao']));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Concurso de Informática</title>
    <link rel="stylesheet" href="css/estilo.css" />
</head>
<body>
    <div class="app-container">
        <div class="jogo-header">
            <div class="pontos">🏆 <span id="pontos"><?= $_SESSION['pontos'] ?></span></div>
            <div class="tempo">⏱️ <span id="timer"><?= $tempo_restante ?></span>s</div>
        </div>

        <div class="pergunta-card">
            <h2><?= $pergunta ?></h2>
            <form method="POST" id="form-resposta">
                <button type="submit" name="resposta" value="<?= htmlspecialchars($questao['opcao_a']) ?>" class="opcao"><?= $opcoes[0] ?></button>
                <button type="submit" name="resposta" value="<?= htmlspecialchars($questao['opcao_b']) ?>" class="opcao"><?= $opcoes[1] ?></button>
                <button type="submit" name="resposta" value="<?= htmlspecialchars($questao['opcao_c']) ?>" class="opcao"><?= $opcoes[2] ?></button>
                <button type="submit" name="resposta" value="<?= htmlspecialchars($questao['opcao_d']) ?>" class="opcao"><?= $opcoes[3] ?></button>
            </form>
        </div>

        <footer class="rodape-jogo">
            Não use IA para responder cada questão, seja honesto com você, teste seus conhecimentos.
        </footer>
    </div>

    <script>
        let timeLeft = <?= $tempo_restante ?>;
        const timerEl = document.getElementById('timer');
        const form = document.getElementById('form-resposta');

        const interval = setInterval(() => {
            timeLeft--;
            timerEl.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(interval);
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'resposta';
                input.value = '###TIMEOUT###';
                form.appendChild(input);
                form.submit();
            }
        }, 1000);

        let confirmExit = true;
        window.addEventListener('beforeunload', (e) => {
            if (confirmExit) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        document.querySelectorAll('.opcao').forEach(btn => {
            btn.addEventListener('click', () => {
                confirmExit = false;
            });
        });
    </script>
</body>
</html>