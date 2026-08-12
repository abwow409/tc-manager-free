Sistema de Gestão de Contas Seguro para TrinityCore – Documentação do Sistema

1. Visão Geral do Sistema
Este sistema é uma plataforma web de gestão e segurança de contas projetada para o TrinityCore (emulador de World of Warcraft). Suporta o sistema de contas Battle.net e oferece funcionalidades completas: registo, ativação, início de sessão, recuperação de palavra‑passe, definições de segurança, carregamento de pontos e loja de pontos. O sistema integra‑se profundamente com as bases de dados auth, characters e world do TrinityCore e comunica com o servidor do jogo através da interface SOAP, permitindo a gestão online de contas, personagens, itens, etc.

Desenvolvido em PHP 8+, segue uma arquitetura MVC e incorpora mecanismos de segurança robustos (proteção CSRF, prevenção de fixação de sessão, lista negra de IP, limitação de taxa, registo de auditoria, exigência de palavras‑passe fortes, etc.). Suporta também vários idiomas (chinês, inglês, francês, russo, etc.) e várias gateways de pagamento (Stripe, YiPay, etc.), sendo adequado para servidores privados de WoW de pequeno e médio porte.

2. Arquitetura Técnica e Estrutura de Diretórios

2.1 Pilha Tecnológica

Componente	Escolha Tecnológica
Linguagem Backend	PHP 8.0+
Base de Dados	MySQL / MariaDB (partilhada com auth/characters/world do TrinityCore)
Comunicação	SOAP (interação com o worldserver)
Frontend	HTML5 + CSS3 + JavaScript nativo (responsivo, sem frameworks)
Serviço de Email	PHPMailer (SMTP)
Gateways de Pagamento	Stripe (cartão de crédito), YiPay (pagamentos agregados), com interfaces reservadas para PayPal/WeChat/Alipay
Extensões Cripto	OpenSSL, GMP (ou BC Math)
Gestão de Sessões	Sessões PHP + persistência em BD (suporta expulsão de múltiplos dispositivos)
2.2 Estrutura de Diretórios (ficheiros principais)

text
/
├── config/
│   └── config.php                  # Configuração unificada (BD, SOAP, pagamentos, email, parâmetros de segurança, etc.)
├── includes/
│   ├── Database.php                # Classe singleton da BD, cria tabelas adicionais automaticamente
│   ├── Security.php                # Núcleo de segurança: CSRF, hashing de palavras‑passe, lista negra de IP, bloqueio de login, etc.
│   ├── Session.php                 # Gestão de sessões: login, logout, lembrar‑me, controlo de múltiplas sessões
│   ├── AuditLogger.php             # Sistema de registo de auditoria (escreve na tabela audit_logs)
│   ├── RateLimiter.php             # Limitação de taxa de pedidos (por IP/operação)
│   ├── Recaptcha.php               # Integração com Google reCAPTCHA
│   ├── EmailService.php            # Serviço de email (baseado no PHPMailer)
│   ├── SOAPClient.php              # Cliente SOAP para TrinityCore (executa comandos GM)
│   ├── SRP6.php / TrinitySRP6.php  # Validador SRP6 (compatível com o sistema de palavras‑passe do TrinityCore)
│   ├── languages.php               # Classe de suporte multilíngue
│   ├── functions.php               # Funções auxiliares globais (auto‑carregamento, carregamento de configuração, etc.)
│   └── footer.php                  # Rodapé comum (inclui estatísticas da página)
├── vendor/                         # Dependências do Composer (PHPMailer, Stripe SDK, etc.)
├── languages/                      # Ficheiros de idioma (subdiretórios cn/en/fr/...)
├── auth.sql                        # Estruturas de tabelas adicionais (pontos, itens da loja, tokens de ativação, sessões, etc.)
├── login.php                       # Página de início de sessão
├── register.php                    # Página de registo (chama SOAP para criar conta Battle.net)
├── activate.php                    # Ativação da conta (via token de email)
├── resend_activation.php           # Reenvio do email de ativação
├── forgot_password.php             # Recuperação de palavra‑passe (por email ou perguntas de segurança)
├── reset_password.php              # Redefinição de palavra‑passe via token
├── profile.php                     # Perfil do utilizador (mostra personagens, tempo online, troca de pontos)
├── security_settings.php           # Definições de segurança (alterar palavra‑passe, gerir sessões, definir perguntas de segurança)
├── points_shop.php                 # Loja de pontos (itens, subida de nível, ouro, permissões GM)
├── topup.php                       # Carregamento de pontos (múltiplas gateways de pagamento)
└── logout.php                      # Terminar sessão
3. Módulos Funcionais Principais

3.1 Registo e Ativação de Contas

Fluxo de registo: O utilizador introduz email e palavra‑passe → o sistema chama SOAP bnetaccount create para criar uma conta Battle.net → associa automaticamente a conta de jogo (tabela account) e guarda o campo email → gera um token de ativação (válido 24 h) → envia um email de ativação via SMTP.

Mecanismo de ativação: O utilizador clica no link do email → a validade do token é verificada → o hash da palavra‑passe temporária é escrito em account.passwd → o token é marcado como usado e a conta fica ativada.

3.2 Início de Sessão e Gestão de Sessões

Início de sessão: Suporta email + palavra‑passe (verificação SHA1, compatível com sha_pass_hash ou passwd do TrinityCore).

Lembrar‑me: Baseado na tabela remember_me_tokens, renovação automática por 30 dias.

Segurança de sessão: Cada início de sessão gera um ID de sessão único, registando IP, User‑Agent e última atividade; permite visualizar e revogar sessões noutros dispositivos.

Bloqueio de conta: Após um número configurável de falhas consecutivas (por padrão 5), a conta é bloqueada durante 30 minutos.

3.3 Recuperação e Redefinição de Palavra‑Passe

Método 1: Receber um link de redefinição por email (válido 60 minutos, uso único).

Método 2: Autenticar‑se através de perguntas de segurança predefinidas (pelo menos 3) e definir diretamente uma nova palavra‑passe.

Ambos os métodos chamam SOAP bnetaccount set password para atualizar a palavra‑passe e sincronizar account.passwd.

3.4 Página de Definições de Segurança

Alterar palavra‑passe: Requer verificação da palavra‑passe atual, atualização via SOAP.

Gerir sessões ativas: Lista todos os dispositivos conectados; permite encerrar uma sessão individual ou todas exceto a atual.

Definir perguntas de segurança: O utilizador pode personalizar de 3 a 5 perguntas e respostas (armazenadas com hash) para recuperação auxiliar.

3.5 Perfil do Utilizador e Informações dos Personagens

Mostra informações da conta Battle.net, nível GM e lista de contas de jogo associadas.

Liga‑se à base de dados characters para exibir todos os personagens (nome, raça, classe, nível, dinheiro, mapa, estado online, tempo total online, etc.).

Fornece função “Unstuck”: teletransporta o personagem para o ponto de partida da raça/classe (atualiza coordenadas diretamente na BD, sem SOAP).

3.6 Sistema de Pontos (Créditos)

Obtenção de pontos:

Troca de tempo online (campo totaltime) – pontos por hora configuráveis, com horas mínimas exigidas.

Compra via carregamento (ver secção seguinte).

Gasto de pontos:

Troca de itens: Lê itens de points_shop_items (ID, quantidade, preço) e envia por correio para o personagem selecionado.

Subida de nível: Eleva o personagem ao nível alvo configurado (ex. 90), requer que o personagem esteja offline.

Compra de ouro: Adiciona uma quantidade especificada de ouro (em cobre, com proteção contra estouro) ao personagem.

Compra de permissões GM: Concede nível GM 1 à conta de jogo via account_access (RealmID = -1 para todos os reinos).

Todas as transações são registadas em points_transactions com acompanhamento de estado (pendente/sucesso/falha).

3.7 Carregamento de Pontos (Integração de Pagamentos)

Configuração: Ativar e configurar cada gateway em config.php.

Gateways suportadas:

Stripe: Utiliza fluxo PaymentIntent, o frontend renderiza Stripe Elements, o backend confirma o pagamento e adiciona pontos automaticamente.

YiPay (pagamento agregado): Gera assinatura, redireciona para a plataforma de pagamento e trata notificações assíncronas (notify) e retornos síncronos (return).

Controlo de taxa: Cada gateway pode ter a sua própria taxa de câmbio (1 CNY = X pontos), com valor global predefinido de 100.

Segurança: Todos os retornos de pagamento verificam assinaturas e validam que a encomenda corresponde ao utilizador, evitando falsificações.

3.8 Auditoria e Registos

Registo de auditoria: Regista operações críticas (início de sessão, registo, alteração de palavra‑passe, troca de itens, revogação de sessões, etc.) na tabela audit_logs, com IP, User‑Agent e detalhes em JSON.

Registos de início de sessão: Regista separadamente cada tentativa (sucesso/fracasso) para análise de segurança.

Limitação de taxa: Baseada no IP e tipo de operação (ex. registo, redefinição de palavra‑passe) para prevenir ataques de força bruta.

4. Mecanismos de Segurança Detalhados

Camada de Proteção	Medidas Específicas
Camada de Transporte	Força HTTPS (configurável) para evitar ataques MITM.
Autenticação	Palavras‑passe com hash SHA1 (compatível com TrinityCore nativo) ou SRP6; interface reservada para 2FA.
Segurança de Sessão	ID de sessão regenerado periodicamente; vinculado a IP e User‑Agent; cookies HttpOnly, SameSite=Strict; sessões persistidas em BD, com expiração e expulsão forçada.
Proteção CSRF	Cada formulário inclui um token aleatório (Security::generateCSRFToken) verificado no envio.
Filtragem de Entradas	Saída escapada com htmlspecialchars; consultas SQL com instruções preparadas (mysqli).
Fortaleza de Palavra‑Passe	Exige pelo menos 8 caracteres, com maiúsculas, minúsculas, dígitos e caracteres especiais; lista negra de palavras‑passe fracas incorporada.
Limitação de Taxa	RateLimiter usa Redis ou registos na BD para limitar registos, redefinições, etc. (por padrão 5 por hora).
Lista Negra de IP	Adiciona automaticamente IPs que violam repetidamente (ex. >10 erros de palavra‑passe), com expiração configurável.
Proteção de Início	Bloqueio da conta após demasiadas falhas (30 minutos) para evitar força bruta.
Mecanismo de Ativação	As contas devem ser ativadas por email antes de aceder ao painel web; o token é de uso único e válido 24 h.
Controlo de Acesso	Todas as páginas restritas (perfil, loja, etc.) verificam a sessão e redirecionam utilizadores não autenticados.
Registo de Auditoria	Todas as operações sensíveis são registadas em audit_logs para investigação posterior.
Comunicação SOAP	Utiliza credenciais independentes (utilizador/palavra‑passe) para comunicar com o worldserver; recomenda‑se TLS.
5. Design da Base de Dados (Tabelas Adicionais)
Além das tabelas nativas do TrinityCore, o sistema adiciona as seguintes tabelas (ver auth.sql):

Nome da Tabela	Propósito
account_activation_tokens	Armazena tokens de ativação de registo (com hash da palavra‑passe temporária)
password_reset_tokens	Armazena tokens de redefinição de palavra‑passe (uso único, 60 min)
password_reset_limits	Regista contagens de pedidos de redefinição por IP/utilizador (para limitação de taxa)
user_security_questions	Armazena perguntas de segurança do utilizador (ID da pergunta e hash da resposta)
user_2fa	Armazena chaves secretas TOTP (reservado)
remember_me_tokens	Tokens “lembrar‑me” (início de sessão persistente)
account_sessions	Registos de sessões ativas (para gestão de múltiplos dispositivos)
login_logs	Registos de tentativas de início de sessão
audit_logs	Registos de auditoria (detalhes em JSON)
rate_limits	Registos genéricos de limitação de taxa
ip_blacklist	Lista negra de IP (com expiração)
user_points	Saldo e estatísticas de pontos do utilizador
points_shop_items	Configuração dos itens da loja (ID, preço, stock, categoria, etc.)
points_transactions	Registo de transações de pontos (trocas, carregamentos, troca de tempo)
6. Instruções de Configuração (config.php)
O ficheiro de configuração contém as seguintes secções principais:

6.1 Ligações à Base de Dados (database / characters_database / world_database)

Liga respetivamente às bases auth, characters e world, com suporte para hosts e portos independentes.

6.2 Configuração SOAP

php
$config['soap'] = [
    'host' => '127.0.0.1',      // Endereço SOAP do worldserver
    'port' => 7878,             // Porta padrão
    'username' => '3#1',        // Formato `account_id#realm_id`
    'password' => '...',        // Palavra‑passe SOAP (deve coincidir com worldserver.conf)
    'timeout' => 30,
    'debug' => false,
];
6.3 Gateways de Pagamento (stripe / yipay / paypal / wechat / alipay)

Cada gateway tem o seu próprio interruptor de ativação, chaves, taxa de câmbio e ambiente (sandbox).

YiPay suporta assinatura MD5.

6.4 Serviço de Email

Utiliza SMTP para enviar emails de ativação, redefinição de palavra‑passe, alertas de segurança, etc.

Suporta Gmail, QQ Mail, etc. (requer palavras‑passe específicas da aplicação).

6.5 Parâmetros de Segurança

min_password_length, max_login_attempts, lockout_duration_minutes, session_lifetime, remember_me_lifetime, etc.

Ativar/desativar reCAPTCHA, 2FA (reservado).

6.6 Pontos e Loja

points_per_hour (taxa de troca de tempo online), min_exchange_hours.

level_boost_target (nível alvo para subidas de nível).

Categorias de itens: level_boost, gold, gm_level, itens normais.

7. Requisitos do Ambiente de Implantação

7.1 Ambiente do Servidor

PHP: Versão 8.2 (a versão gratuita requer exatamente 8.2)

MySQL: 8.0+ / MariaDB 12+

Servidor Web: Apache / Nginx

Extensões PHP necessárias: mysqli, session, curl, soap (obrigatório), gd, json, mbstring, gmp, sg11, Imagick

Composer: 2.0+

Instalação de dependências:

bash
composer require phpmailer/phpmailer
composer require stripe/stripe-php   # se o Stripe estiver ativado
7.2 Configuração do TrinityCore

worldserver.conf deve ativar o SOAP:

text
SOAP.Enabled = 1
SOAP.Port = 7878
SOAP.Redirect = 0
A base auth deve conter a tabela battlenet_accounts (fornecida pelo TrinityCore).

A tabela account deve incluir um campo email (o sistema adicioná‑lo‑á automaticamente se faltar).

7.3 Instalação de Dependências
Utilizar o Composer conforme indicado acima.

7.4 Permissões de Ficheiros

config/config.php deve ter permissões 600 ou 640 (apenas leitura).

Os diretórios de registos (se não for utilizada auditoria em BD) devem ter permissões de escrita.

Os diretórios de upload (se existirem) requerem controlos de acesso adequados.

7.5 Rede e Segurança

É fortemente recomendado ativar HTTPS (definir require_https = true na configuração).

Configurar a firewall para abrir apenas as portas 80/443; restringir a porta SOAP (7878) a localhost.

Atualizar regularmente o PHP e as extensões.

8. Exemplos de Fluxo de Utilização

8.1 Registo de Novo Utilizador

Visitar /register.php, preencher email e palavra‑passe.

O sistema chama SOAP para criar uma conta Battle.net, gera uma conta de jogo e envia um email de ativação.

O utilizador clica no link de ativação → a conta é ativada e pode iniciar sessão no painel web.

8.2 Início de Sessão e Obtenção de Pontos

Visitar /login.php, introduzir email e palavra‑passe, opcionalmente marcar “Lembrar‑me”.

Após o início, ir a /profile.php para ver a lista de personagens e o saldo de pontos.

Na secção “Centro de Pontos”, introduzir o número de horas online para trocar por pontos (consome o totaltime do personagem).

8.3 Gasto de Pontos

Clicar em “Loja de Pontos” para ir a /points_shop.php.

Navegar pelos itens (objetos, subida de nível, ouro, permissões GM).

Selecionar um item e um personagem destino, clicar em “Trocar”.

O sistema deduz os pontos, executa a ação correspondente (envia correio com o item, atualiza nível, adiciona ouro, etc.) e regista a transação.

8.4 Carregamento de Pontos

Visitar /topup.php, introduzir o montante a carregar.

Escolher um método de pagamento (Stripe/YiPay, etc.).

Concluir o pagamento; o sistema adiciona automaticamente os pontos à conta.

8.5 Definições de Segurança

Em /security_settings.php, alterar a palavra‑passe, gerir sessões, definir perguntas de segurança.

As perguntas de segurança servem como método alternativo de verificação para recuperar a palavra‑passe.

9. Extensão e Personalização

Adicionar uma nova gateway de pagamento: Adicionar configuração em config.php, implementar o encaminhamento e tratamento de callbacks em topup.php.

Adicionar novos tipos de produtos: Estender a lógica de troca em points_shop.php com novos ramos de category.

Multilíngue: Adicionar ficheiros de idioma em languages/ e herdar a classe Language.

2FA: O sistema já reserva a tabela user_2fa e esboços de interface – integrar uma biblioteca TOTP (ex. robthree/twofactorauth) para a ativar.

10. Manutenção e Monitorização

Visualização de registos: As tabelas audit_logs e login_logs fornecem um histórico detalhado; pode ser construída uma interface de administração para os mostrar.

Limpeza periódica: O sistema inclui AuditLogger::cleanOldLogs($days) para eliminar registos antigos regularmente.

Manutenção da BD: Otimizar periodicamente as tabelas de sessões e tokens, eliminando registos expirados.

Atualizações de segurança: Manter o PHP e as dependências do Composer atualizados com os últimos patches.

11. Perguntas Frequentes (FAQ)

P: O registo falha com “SOAP service unavailable”.
R: Verificar se o worldserver está em execução, se a configuração SOAP está correta e se a firewall permite a porta 7878 (preferencialmente apenas acesso local).

P: O email de ativação não é recebido.
R: Verificar a configuração SMTP e os registos de correio; os utilizadores podem usar a função “Reenviar email de ativação”.

P: Os personagens não aparecem após o início de sessão.
R: Confirmar que a configuração de characters_database está correta e que a conta Battle.net possui efetivamente personagens.

P: O item trocado com pontos não é recebido.
R: Verificar se as tabelas mail e item_instance da base de dados de personagens foram inseridas corretamente; assegurar que a caixa de correio do personagem não está cheia.

P: Falha ao redefinir a palavra‑passe.
R: Garantir que o SOAP está disponível e que a conta existe; se forem utilizadas perguntas de segurança, verificar se o hash da resposta coincide (sensível a maiúsculas/minúsculas).

12. Versão e Suporte

Versão atual: Baseada no TrinityCore 12.x (suporta 11.0 Dragonflight e versões anteriores).

Compatibilidade: Teoricamente compatível com todos os ramos do TrinityCore que utilizem o sistema de contas Battle.net (podem ser necessários ajustes menores nos nomes dos campos).

Suporte técnico: Consultar os fóruns oficiais do TrinityCore ou a documentação do sistema; utilizar os registos de erro detalhados para resolução de problemas.