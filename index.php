<?php
require_once 'valida_sessao.php'; // Protege a página!
require_once 'conexao.php';

try {
    $stmtEmpresas = $pdo->query("SELECT id, nome_empresa FROM empresas ORDER BY nome_empresa ASC");
    $empresas = $stmtEmpresas->fetchAll(PDO::FETCH_ASSOC);

    $stmtTours = $pdo->query("SELECT id, nome_tour, valor_base FROM tours ORDER BY nome_tour ASC");
    $tours = $stmtTours->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Derivações</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen pb-12">

    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4 flex justify-between items-center mb-8">
        <div class="font-bold text-xl text-blue-900 tracking-tight">Operações <span class="text-blue-500">PRO</span></div>
        
        <div class="flex items-center gap-3">
            
            <?php if (isset($_SESSION['nivel_acesso']) && $_SESSION['nivel_acesso'] === 'admin'): ?>
                <a href="admin.php" class="text-sm bg-slate-800 hover:bg-slate-900 text-white font-semibold py-2 px-4 rounded-lg transition-all shadow-sm flex items-center gap-2 mr-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Painel Admin
                </a>
                <div class="h-6 w-px bg-slate-200"></div> <?php endif; ?>

            <span class="text-sm font-medium text-slate-600 ml-2">Olá, <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?></span>
            <a href="logout.php" class="text-sm text-red-500 hover:text-red-700 font-bold transition-colors ml-3">Sair</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4">
        
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-8 py-6">
                <h2 class="text-2xl font-bold text-white">Nova Derivação</h2>
                <p class="text-blue-100 text-sm mt-1">Gere relatórios em PDF com múltiplos passeios e cálculo automático.</p>
            </div>

            <form action="gerar_derivacao.php" method="POST" class="p-8">
                
                <div class="mb-8 bg-slate-50 p-6 rounded-xl border border-slate-200">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Agência / Empresa Parceira</label>
                    <select name="empresa_id" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">Selecione a empresa...</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>"><?= htmlspecialchars($empresa['nome_empresa']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="tours-container" class="space-y-4 mb-6">
                    <div class="tour-linha flex flex-col md:flex-row gap-4 items-end bg-white p-4 rounded-xl border border-slate-200 shadow-sm transition-all hover:shadow-md">
                        <div class="flex-1 w-full">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tour Selecionado</label>
                            <select name="tour_id[]" required class="tour-select w-full px-4 py-3 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" onchange="calcularTotal()">
                                <option value="" data-valor="0">Selecione o passeio...</option>
                                <?php foreach ($tours as $tour): ?>
                                    <option value="<?= $tour['id'] ?>" data-valor="<?= $tour['valor_base'] ?>">
                                        <?= htmlspecialchars($tour['nome_tour']) ?> ($<?= number_format($tour['valor_base'], 2, ',', '.') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-full md:w-32">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Qtd Pax</label>
                            <input type="number" name="quantidade_pessoas[]" min="1" value="1" required class="qtd-input w-full px-4 py-3 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-center" oninput="calcularTotal()">
                        </div>
                        <button type="button" class="w-full md:w-auto bg-red-50 text-red-600 hover:bg-red-500 hover:text-white font-bold py-3 px-4 rounded-lg border border-red-200 transition-colors" onclick="removerLinha(this)">
                            Remover
                        </button>
                    </div>
                </div>

                <button type="button" class="w-full border-2 border-dashed border-slate-300 text-slate-500 hover:border-blue-500 hover:text-blue-600 font-semibold py-4 rounded-xl transition-colors mb-8" onclick="adicionarLinha()">
                    + Adicionar outro Tour ao Relatório
                </button>

                <div class="flex flex-col md:flex-row items-center justify-between bg-slate-800 rounded-xl p-6 shadow-inner">
                    <div class="text-center md:text-left mb-4 md:mb-0">
                        <p class="text-slate-400 text-sm font-semibold uppercase tracking-wider mb-1">Valor Total Geral</p>
                        <h3 id="display_total" class="text-4xl font-bold text-emerald-400">$0,00</h3>
                    </div>
                    <button type="submit" class="w-full md:w-auto bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 px-8 rounded-lg shadow-lg hover:shadow-emerald-500/30 transition-all transform hover:-translate-y-1 text-lg">
                        Gerar PDF Completo
                    </button>
                </div>

            </form>
        </div>
    </div>

<script>
    function adicionarLinha() {
        const container = document.getElementById('tours-container');
        const linhaBase = document.querySelector('.tour-linha');
        const novaLinha = linhaBase.cloneNode(true);
        novaLinha.querySelector('.tour-select').value = '';
        novaLinha.querySelector('.qtd-input').value = '1';
        container.appendChild(novaLinha);
        calcularTotal();
    }

    function removerLinha(botao) {
        const container = document.getElementById('tours-container');
        if (container.querySelectorAll('.tour-linha').length > 1) {
            botao.closest('.tour-linha').remove();
            calcularTotal();
        } else {
            alert("O relatório precisa ter pelo menos um tour.");
        }
    }

    function calcularTotal() {
        let totalGeral = 0;
        const selects = document.querySelectorAll('.tour-select');
        const inputs = document.querySelectorAll('.qtd-input');

        for (let i = 0; i < selects.length; i++) {
            const select = selects[i];
            const quantidade = parseInt(inputs[i].value) || 0;
            const valorBase = parseFloat(select.options[select.selectedIndex].getAttribute('data-valor')) || 0;
            totalGeral += (valorBase * quantidade);
        }
        
        document.getElementById('display_total').innerText = '$' + totalGeral.toFixed(2).replace('.', ',');
    }
</script>

</body>
</html>