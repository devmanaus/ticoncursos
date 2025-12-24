<?php
session_start();

// Se veio por timeout, redireciona sem salvar
if (isset($_GET['timeout'])) {
    session_destroy();
    header('Location: index.php?msg=timeout');
    exit;
}

function getDeviceType() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match('/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini)/i', $ua) ? 'mobile' : 'desktop';
}

function getUserIP() {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

$erro = '';

// Verifica se está no TOP 10
$ranking = [];
if (file_exists('ranking.csv')) {
    $ranking = array_map('str_getcsv', file('ranking.csv', FILE_IGNORE_NEW_LINES));
    if (!empty($ranking) && $ranking[0][0] === 'nome') array_shift($ranking);
}
usort($ranking, function($a, $b) { return ($b[2] ?? 0) - ($a[2] ?? 0); });
$pontuacao_minima = count($ranking) >= 10 ? ($ranking[9][2] ?? 0) : 0;
$esta_no_top10 = $_SESSION['pontos'] > $pontuacao_minima || count($ranking) < 10;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sair']) || isset($_POST['nao_registrar'])) {
        // ✅ Destrói sessão ao sair
        session_destroy();
        header('Location: index.php');
        exit;
    }

    if ($esta_no_top10) {
        $nome = trim($_POST['nome']);
        $estado = $_POST['estado'];

        if (strlen($nome) < 2 || strlen($nome) > 15) {
            $erro = "Nome deve ter entre 2 e 15 caracteres.";
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $nome)) {
            $erro = "Nome inválido.";
        } elseif (!in_array($estado, ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'])) {
            $erro = "Selecione um estado válido.";
        } else {
            // Salva no histórico SEMPRE
            $historico = [
                $nome,
                $estado,
                $_SESSION['pontos'],
                date('d/m/Y'),
                date('H:i'),
                getUserIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido',
                getDeviceType()
            ];
            $h = fopen('historico.csv', 'a');
            if ($h) {
                fputcsv($h, $historico);
                fclose($h);
            }

            // Salva no ranking se TOP 10
            if ($esta_no_top10) {
                $ranking_line = array_merge($historico, [getDeviceType()]);
                array_pop($ranking_line); // remove duplicado
                $r = fopen('ranking.csv', 'a');
                if ($r) {
                    fputcsv($r, $ranking_line);
                    fclose($r);
                }
            }

            session_destroy();
            header('Location: ranking.php?salvo=1');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final do Jogo</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <h2>Jogo Finalizado!</h2>
        <p>Pontuação: <strong><?= $_SESSION['pontos'] ?></strong></p>

        <?php if (!$esta_no_top10): ?>
            <div class="aviso">
                Sua pontuação foi inferior ao TOP 10.
            </div>
            <form method="POST">
                <button type="submit" name="sair" class="btn primary">SAIR</button>
            </form>
        <?php else: ?>
            <?php if (isset($erro)): ?>
                <div class="erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" class="form-salvar">
                <label>Nome (até 15 caracteres):</label>
                <input type="text" name="nome" maxlength="15" required autofocus>

                <label>Estado:</label>
                <select name="estado" required>
                    <option value="">Selecione</option>
                    <?php
                    $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                    foreach($ufs as $uf) echo "<option value='$uf'>$uf</option>";
                    ?>
                </select>

                <div class="botoes-salvar">
                    <button type="submit" class="btn primary">SALVAR NO RANKING</button>
                    <button type="submit" name="nao_registrar" value="1" class="btn outline">Não quero registrar no ranking</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>