<?php
$linhas = [];
if (file_exists('ranking.csv')) {
    $linhas = array_map('str_getcsv', file('ranking.csv'));
    // Ordenar: pontos DESC, data DESC
    usort($linhas, function($a, $b) {
        if ($a[2] != $b[2]) return $b[2] - $a[2]; // pontos
        return strtotime(str_replace('/', '-', $b[3] . ' ' . $b[4])) - strtotime(str_replace('/', '-', $a[3] . ' ' . $a[4])); // data+hora
    });
    $top10 = array_slice($linhas, 0, 10);
} else {
    $top10 = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - Concurso Info</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <h2>🏆 TOP 10 - CONCURSEIROS</h2>
        <table class="ranking-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>UF</th>
                    <th>Pts</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($top10)): ?>
                    <tr><td colspan="6">Nenhum resultado ainda.</td></tr>
                <?php else: ?>
                    <?php foreach ($top10 as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?>º</td>
                            <td><?= htmlspecialchars($row[0]) ?></td>
                            <td><?= htmlspecialchars($row[1]) ?></td>
                            <td><?= (int)$row[2] ?></td>
                            <td><?= htmlspecialchars($row[3]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
		<div class="botoes-salvar">
			<button type="button" class="btn primary" onclick="window.location.href='index.php'">
				SAIR
			</button>
        </div>
    </div>
</body>
</html>