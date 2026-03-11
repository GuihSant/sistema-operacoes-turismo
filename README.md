# ✈️ Sistema de Derivações e Operações (SaaS)

Um sistema completo em PHP para gestão de faturamento, controle de acesso e geração automatizada de relatórios em PDF para agências de turismo.

## 🚀 Funcionalidades Principais

* **Arquitetura Master-Detail:** Inserção de múltiplos passeios em um único relatório com recálculo financeiro em tempo real via JavaScript.
* **Geração Automática de PDFs:** Transforma o HTML dinâmico em documentos PDF profissionais (via Dompdf) com injeção de imagens em Base64 e tipografia corporativa.
* **Controle de Acesso (RBAC):** Sistema de autenticação com níveis de privilégio (Administrador vs. Operador). A interface se adapta ocultando rotas críticas de usuários sem permissão.
* **Cibersegurança Aplicada:** * Senhas criptografadas via `password_hash()` (Bcrypt).
  * Proteção contra SQL Injection utilizando `PDO Prepare Statements`.
  * Controle de sessão rigoroso para evitar acessos diretos via URL.

## 🛠️ Tecnologias Utilizadas

* **Backend:** PHP 8+
* **Banco de Dados:** PostgreSQL (Hospedado via Supabase)
* **Frontend:** HTML5, JS (Vanilla) e Tailwind CSS (via CDN)
* **Bibliotecas:** Dompdf (Geração de relatórios via Composer)

## ⚙️ Como rodar o projeto localmente

1. Clone o repositório:
   ```bash
   git clone [https://github.com/GuihSant/sistema-turismo.git](https://github.com/GuihSant/sistema-turismo.git)
2 . Instale as dependências do Composer:
       composer install
3 . Configure o banco de dados PostgreSQL executando o script SQL fornecido na documentação e atualize o arquivo conexao.php com suas credenciais.
4. Inicie o servidor PHP embutido:
      php -S localhost:8000


      *Desenvolvido por Guilherme Gomes Santana*
   
