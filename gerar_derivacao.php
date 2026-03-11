<?php
require_once 'valida_sessao.php'; // Protege a geração
require_once 'conexao.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresa_id = $_POST['empresa_id'];
    $tours_selecionados = $_POST['tour_id']; 
    $quantidades = $_POST['quantidade_pessoas'];

    try {
        $pdo->beginTransaction();

        $stmt_empresa = $pdo->prepare("SELECT nome_empresa FROM empresas WHERE id = :empresa_id");
        $stmt_empresa->execute(['empresa_id' => $empresa_id]);
        $empresa = $stmt_empresa->fetch(PDO::FETCH_ASSOC);

        if (!$empresa) die("Erro: Empresa não encontrada.");

        $valor_total_geral = 0;
        $linhas_tabela_pdf = ""; 
        $dados_para_salvar = []; 

        $stmt_tour_info = $pdo->prepare("SELECT id, nome_tour, valor_base FROM tours WHERE id = :id");

        for ($i = 0; $i < count($tours_selecionados); $i++) {
            $tour_id_atual = $tours_selecionados[$i];
            $qtd_atual = (int) $quantidades[$i];

            if (empty($tour_id_atual) || $qtd_atual <= 0) continue;

            $stmt_tour_info->execute(['id' => $tour_id_atual]);
            $tour_info = $stmt_tour_info->fetch(PDO::FETCH_ASSOC);

            if ($tour_info) {
                $subtotal = $tour_info['valor_base'] * $qtd_atual;
                $valor_total_geral += $subtotal;

                $dados_para_salvar[] = [
                    'tour_id' => $tour_info['id'],
                    'qtd' => $qtd_atual,
                    'subtotal' => $subtotal
                ];

                $linhas_tabela_pdf .= "
                <tr>
                    <td>{$tour_info['nome_tour']}</td>
                    <td>$" . number_format($tour_info['valor_base'], 2, ',', '.') . "</td>
                    <td class='center'>{$qtd_atual}</td>
                    <td class='right'>$" . number_format($subtotal, 2, ',', '.') . "</td>
                </tr>";
            }
        }

        if (empty($dados_para_salvar)) die("Erro: Nenhum tour válido foi selecionado.");

        $stmt_pai = $pdo->prepare("INSERT INTO derivacoes (empresa_id, valor_total_geral) VALUES (:emp_id, :total) RETURNING id");
        $stmt_pai->execute([ 'emp_id' => $empresa_id, 'total' => $valor_total_geral ]);
        $derivacao_id = $stmt_pai->fetchColumn(); 

        $stmt_filho = $pdo->prepare("INSERT INTO derivacao_itens (derivacao_id, tour_id, quantidade_pessoas, valor_subtotal) VALUES (:dev_id, :tour_id, :qtd, :subtotal)");
        foreach ($dados_para_salvar as $item) {
            $stmt_filho->execute([
                'dev_id' => $derivacao_id, 'tour_id' => $item['tour_id'], 'qtd' => $item['qtd'], 'subtotal' => $item['subtotal']
            ]);
        }

        $pdo->commit();

        // --- PREPARAÇÃO DA LOGO EM BASE64 (MÁGICA PARA O DOMPDF) ---
        $caminho_logo = 'logo-pequeno.png';
        $logo_base64 = '';
        if (file_exists($caminho_logo)) {
            $tipo_img = pathinfo($caminho_logo, PATHINFO_EXTENSION);
            $dados_img = file_get_contents($caminho_logo);
            $logo_base64 = 'data:image/' . $tipo_img . ';base64,' . base64_encode($dados_img);
        }

        $data_atual = date('d/m/Y H:i');
        $numero_fatura = str_pad($derivacao_id, 4, '0', STR_PAD_LEFT);
        
        // --- HTML DO PDF COM DESIGN HARMONIZADO ---
        $html = "
        <html>
        <head>
            <style>
                /* Cores da Marca: Azul (#0B3A82) e Vermelho (#FF0000) */
                body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 14px; }
                
                /* Configuração das Margens para dar espaço ao Cabeçalho fixo */
                @page { margin: 130px 40px 80px 40px; }
                
                /* Cabeçalho que repete em todas as páginas */
                header { 
                    position: fixed; 
                    top: -100px; /* Sobe o cabeçalho para a margem da página */
                    left: 0px; 
                    right: 0px; 
                    height: 70px; 
                    border-bottom: 3px solid #0B3A82; /* Linha azul da marca */
                    padding-bottom: 15px;
                }
                
                .logo { max-height: 60px; float: left; }
                
                .header-info { float: right; text-align: right; }
                .header-info h1 { margin: 0; color: #0B3A82; font-size: 22px; text-transform: uppercase; }
                .header-info span { color: #FF0000; font-weight: bold; } /* Número em vermelho */
                .header-info p { margin: 5px 0 0 0; color: #666; font-size: 12px; }

                /* Rodapé que repete em todas as páginas */
                footer { position: fixed; bottom: -50px; left: 0px; right: 0px; height: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }

                /* Corpo do Documento */
                .detalhes-fatura { margin-top: 10px; margin-bottom: 30px; background: #f9f9f9; padding: 15px; border-left: 4px solid #FF0000; }
                .detalhes-fatura p { margin: 5px 0; }
                
                .tabela-itens { width: 100%; border-collapse: collapse; margin-top: 20px; }
                .tabela-itens th, .tabela-itens td { border: 1px solid #ddd; padding: 12px; }
                
                /* Cabeçalho da Tabela com o Azul da Marca */
                .tabela-itens th { background-color: #0B3A82; color: #ffffff; text-align: left; text-transform: uppercase; font-size: 12px; }
                
                .center { text-align: center; }
                .right { text-align: right; }
                
                /* Total Geral com destaque nas cores da logo */
                .box-total { margin-top: 30px; width: 100%; text-align: right; }
                .box-total p { font-size: 14px; color: #666; margin: 0 0 5px 0; text-transform: uppercase; }
                .box-total h2 { margin: 0; font-size: 28px; color: #FF0000; } /* Total em Vermelho Vivo */
            </style>
        </head>
        <body>
            
            <header>
                <img src='{$logo_base64}' class='logo'>
                <div class='header-info'>
                    <h1>Relatório <span>#{$numero_fatura}</span></h1>
                    <p>Documento de Derivação Oficial</p>
                </div>
            </header>

            <footer>
                Documento gerado automaticamente pelo Sistema de Operações.<br>
                As informações deste relatório são confidenciais.
            </footer>

            <main>
                <div class='detalhes-fatura'>
                    <p><strong>Para (Agência):</strong> {$empresa['nome_empresa']}</p>
                    <p><strong>Data de Emissão:</strong> {$data_atual}</p>
                    <p><strong>Operador(a):</strong> " . htmlspecialchars($_SESSION['usuario_nome'] ?? 'Sistema') . "</p>
                </div>

                <table class='tabela-itens'>
                    <thead>
                        <tr>
                            <th>Tour / Passeio Selecionado</th>
                            <th>Valor Base</th>
                            <th class='center'>Qtd Pax</th>
                            <th class='right'>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$linhas_tabela_pdf}
                    </tbody>
                </table>

                <div class='box-total'>
                    <p>Valor Total a Faturar:</p>
                    <h2>$" . number_format($valor_total_geral, 2, ',', '.') . "</h2>
                </div>
            </main>

        </body>
        </html>
        ";

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $nome_arquivo = "fatura_{$empresa['nome_empresa']}_" . date('Ymd_Hi') . ".pdf";
        $dompdf->stream($nome_arquivo, ["Attachment" => true]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Erro ao processar derivação: " . $e->getMessage());
    }
}
?>