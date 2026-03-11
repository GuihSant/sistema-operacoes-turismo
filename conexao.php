    <?php


$host = 'aws-0-us-west-2.pooler.supabase.com'; 
$port = '6543';                                
$db   = 'postgres';                            
$user = 'postgres.aujzzrajcjtrwhidcxys';       
$pass = 'COLOQUE_SUA_SENHA_AQUI'; 

// 2. Montando a string de conexão
$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

try {
    // 3. Tenta conectar passando parâmetros adicionais para resolver o erro de "PREPARE"
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => true // <-- ISSO RESOLVE O AVISO DA SUA IMAGEM!
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Descomente a linha abaixo para testar no navegador se a conexão deu certo
    // echo "Conectado ao Supabase com sucesso!";
    
} catch (PDOException $e) {
    die("Erro de conexão com o Supabase: " . $e->getMessage());
}
?>