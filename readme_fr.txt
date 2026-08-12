Système de Gestion de Comptes Sécurisé pour TrinityCore – Documentation du Système

1. Présentation Générale
Ce système est une plateforme web de gestion et de sécurité des comptes conçue pour TrinityCore (émulateur de World of Warcraft). Il prend en charge le système de comptes Battle.net et offre des fonctionnalités complètes : inscription, activation, connexion, récupération de mot de passe, paramètres de sécurité, rechargement de points et boutique de points. Le système s’intègre en profondeur avec les bases de données auth, characters et world de TrinityCore, et communique avec le serveur de jeu via l’interface SOAP, permettant la gestion en ligne des comptes, personnages, objets, etc.

Développé en PHP 8+, il suit une architecture MVC et intègre des mécanismes de sécurité robustes (protection CSRF, prévention de la fixation de session, liste noire d’IP, limitation de débit, journalisation d’audit, exigence de mots de passe forts, etc.). Il prend également en charge plusieurs langues (chinois, anglais, français, russe, etc.) et plusieurs passerelles de paiement (Stripe, YiPay, etc.), ce qui le rend adapté aux serveurs privés WoW de petite et moyenne taille.

2. Architecture Technique et Structure des Répertoires

2.1 Pile Technologique

Composant	Choix Technologique
Langage Backend	PHP 8.0+
Base de Données	MySQL / MariaDB (partagée avec auth/characters/world de TrinityCore)
Communication	SOAP (interaction avec worldserver)
Frontend	HTML5 + CSS3 + JavaScript natif (responsive, sans framework)
Service Email	PHPMailer (SMTP)
Passerelles de Paiement	Stripe (carte de crédit), YiPay (paiements agrégés), avec interfaces réservées pour PayPal/WeChat/Alipay
Extensions Crypto	OpenSSL, GMP (ou BC Math)
Gestion des Sessions	Sessions PHP + persistance en BD (supporte l’expulsion multi‑périphériques)
2.2 Structure des Répertoires (fichiers clés)

text
/
├── config/
│   └── config.php                  # Configuration unifiée (BD, SOAP, paiements, email, paramètres de sécurité, etc.)
├── includes/
│   ├── Database.php                # Classe singleton de BD, crée automatiquement les tables supplémentaires
│   ├── Security.php                # Noyau de sécurité : CSRF, hachage de mots de passe, liste noire IP, verrouillage de connexion, etc.
│   ├── Session.php                 # Gestion des sessions : connexion, déconnexion, se souvenir de moi, contrôle multi‑session
│   ├── AuditLogger.php             # Système de journalisation d’audit (écrit dans la table audit_logs)
│   ├── RateLimiter.php             # Limitation de débit des requêtes (par IP/opération)
│   ├── Recaptcha.php               # Intégration de Google reCAPTCHA
│   ├── EmailService.php            # Service d’envoi d’emails (basé sur PHPMailer)
│   ├── SOAPClient.php              # Client SOAP pour TrinityCore (exécute les commandes GM)
│   ├── SRP6.php / TrinitySRP6.php  # Valideur SRP6 (compatible avec le système de mots de passe de TrinityCore)
│   ├── languages.php               # Classe de support multilingue
│   ├── functions.php               # Fonctions auxiliaires globales (auto‑chargement, chargement de configuration, etc.)
│   └── footer.php                  # Pied de page commun (inclut les statistiques de page)
├── vendor/                         # Dépendances Composer (PHPMailer, Stripe SDK, etc.)
├── languages/                      # Fichiers de langue (sous‑répertoires cn/en/fr/...)
├── auth.sql                        # Structures de tables supplémentaires (points, articles de boutique, jetons d’activation, sessions, etc.)
├── login.php                       # Page de connexion
├── register.php                    # Page d’inscription (appelle SOAP pour créer un compte Battle.net)
├── activate.php                    # Activation du compte (via jeton email)
├── resend_activation.php           # Renvoi de l’email d’activation
├── forgot_password.php             # Récupération de mot de passe (par email ou questions de sécurité)
├── reset_password.php              # Réinitialisation du mot de passe via jeton
├── profile.php                     # Profil utilisateur (affiche les personnages, temps en ligne, échange de points)
├── security_settings.php           # Paramètres de sécurité (changer le mot de passe, gérer les sessions, définir les questions de sécurité)
├── points_shop.php                 # Boutique de points (objets, montée de niveau, or, permissions GM)
├── topup.php                       # Rechargement de points (multiples passerelles de paiement)
└── logout.php                      # Déconnexion
3. Modules Fonctionnels Principaux

3.1 Inscription et Activation des Comptes

Flux d’inscription : L’utilisateur saisit son email et son mot de passe → le système appelle SOAP bnetaccount create pour créer un compte Battle.net → il associe automatiquement le compte de jeu (table account) et enregistre le champ email → génère un jeton d’activation (valable 24 h) → envoie un email d’activation via SMTP.

Mécanisme d’activation : L’utilisateur clique sur le lien dans l’email → la validité du jeton est vérifiée → le hachage du mot de passe temporaire est écrit dans account.passwd → le jeton est marqué comme utilisé et le compte est activé.

3.2 Connexion et Gestion des Sessions

Connexion : Supporte email + mot de passe (vérification SHA1, compatible avec sha_pass_hash ou passwd de TrinityCore).

Se souvenir de moi : Basé sur la table remember_me_tokens, renouvellement automatique pendant 30 jours.

Sécurité des sessions : Chaque connexion génère un ID de session unique, enregistre l’IP, l’User‑Agent et la dernière activité ; permet de visualiser et de révoquer les sessions sur d’autres appareils.

Verrouillage de compte : Après un nombre configurable d’échecs consécutifs (par défaut 5), le compte est verrouillé pendant 30 minutes.

3.3 Récupération et Réinitialisation du Mot de Passe

Méthode 1 : Recevoir un lien de réinitialisation par email (valable 60 minutes, usage unique).

Méthode 2 : S’authentifier via des questions de sécurité pré‑définies (au moins 3) et définir directement un nouveau mot de passe.

Les deux méthodes appellent SOAP bnetaccount set password pour mettre à jour le mot de passe et synchroniser account.passwd.

3.4 Page des Paramètres de Sécurité

Changer le mot de passe : Nécessite de vérifier le mot de passe actuel, mise à jour via SOAP.

Gérer les sessions actives : Liste tous les appareils connectés ; permet de fermer une session individuelle ou toutes sauf la session en cours.

Définir des questions de sécurité : L’utilisateur peut personnaliser de 3 à 5 questions et réponses (stockées sous forme de hachage) pour faciliter la récupération du mot de passe.

3.5 Profil Utilisateur et Informations des Personnages

Affiche les informations du compte Battle.net, le niveau GM et la liste des comptes de jeu associés.

Se connecte à la base de données characters pour afficher tous les personnages (nom, race, classe, niveau, argent, carte, statut en ligne, temps total en ligne, etc.).

Fournit une fonction « Unstuck » : téléporte le personnage au point de départ de sa race/classe (met à jour les coordonnées directement dans la BD, sans SOAP).

3.6 Système de Points (Crédits)

Obtention de points :

Échange de temps de jeu (champ totaltime) – points par heure configurables, avec un nombre minimum d’heures requis.

Achat via rechargement (voir section suivante).

Dépense des points :

Échange d’objets : Lit les articles depuis points_shop_items (ID, quantité, prix) et les envoie par courrier dans le jeu au personnage sélectionné.

Montée de niveau : Élève le personnage au niveau cible configuré (ex. 90), nécessite que le personnage soit hors ligne.

Achat d’or : Ajoute une quantité spécifiée d’or (en cuivre, avec protection contre le débordement) au personnage.

Achat de permissions GM : Octroie le niveau GM 1 au compte de jeu via account_access (RealmID = -1 pour tous les royaumes).

Toutes les transactions sont enregistrées dans points_transactions avec suivi d’état (en attente/réussi/échoué).

3.7 Rechargement de Points (Intégration des Paiements)

Configuration : Activer et configurer chaque passerelle dans config.php.

Passerelles supportées :

Stripe : Utilise le flux PaymentIntent, le frontend affiche Stripe Elements, le backend confirme le paiement et ajoute les points automatiquement.

YiPay (paiement agrégé) : Génère une signature, redirige vers la plateforme de paiement et gère les notifications asynchrones (notify) et les retours synchrones (return).

Contrôle de taux : Chaque passerelle peut avoir son propre taux de change (1 CNY = X points), avec un taux global par défaut de 100.

Sécurité : Tous les retours de paiement vérifient les signatures et valident que la commande correspond bien à l’utilisateur, empêchant toute falsification.

3.8 Journalisation et Audit

Journal d’audit : Enregistre les opérations critiques (connexion, inscription, changement de mot de passe, échange d’objets, révocation de session, etc.) dans la table audit_logs, avec l’IP, l’User‑Agent et les détails en JSON.

Journaux de connexion : Enregistre séparément chaque tentative (réussite/échec) pour l’analyse de sécurité.

Limitation de débit : Basée sur l’IP et le type d’opération (ex. inscription, réinitialisation du mot de passe) pour prévenir les attaques par force brute.

4. Mécanismes de Sécurité Détaillés

Couche de Protection	Mesures Spécifiques
Couche Transport	Force HTTPS (configurable) pour éviter les attaques MITM.
Authentification	Mots de passe hachés avec SHA1 (compatible avec TrinityCore natif) ou SRP6 ; interface réservée pour la 2FA.
Sécurité des Sessions	ID de session régénéré périodiquement ; lié à l’IP et à l’User‑Agent ; cookies HttpOnly, SameSite=Strict ; sessions persistantes en BD, avec expiration et expulsion forcée.
Protection CSRF	Chaque formulaire intègre un jeton aléatoire (Security::generateCSRFToken) vérifié à l’envoi.
Filtrage des Entrées	Sorties échappées avec htmlspecialchars ; requêtes SQL avec instructions préparées (mysqli).
Robustesse des Mots de Passe	Exige au moins 8 caractères, avec majuscules, minuscules, chiffres et caractères spéciaux ; liste noire de mots de passe faibles intégrée.
Limitation de Débit	RateLimiter utilise Redis ou des enregistrements en BD pour limiter les inscriptions, réinitialisations, etc. (par défaut 5 par heure).
Liste Noire d’IP	Ajoute automatiquement les IP qui violent de manière répétée (ex. >10 erreurs de mot de passe), avec une expiration configurable.
Protection de Connexion	Verrouillage du compte après trop d’échecs (30 minutes) pour éviter la force brute.
Mécanisme d’Activation	Les comptes doivent être activés par email avant d’accéder au panel web ; le jeton est à usage unique et valable 24 h.
Contrôle d’Accès	Toutes les pages restreintes (profil, boutique, etc.) vérifient la session et redirigent les utilisateurs non authentifiés.
Journal d’Audit	Toutes les opérations sensibles sont enregistrées dans audit_logs pour une investigation ultérieure.
Communication SOAP	Utilise des identifiants distincts (nom d’utilisateur/mot de passe) pour communiquer avec worldserver ; TLS recommandé.
5. Conception de la Base de Données (Tables Supplémentaires)
En plus des tables natives de TrinityCore, le système ajoute les tables suivantes (voir auth.sql) :

Nom de la Table	Objectif
account_activation_tokens	Stocke les jetons d’activation d’inscription (avec hachage du mot de passe temporaire)
password_reset_tokens	Stocke les jetons de réinitialisation de mot de passe (usage unique, 60 min)
password_reset_limits	Enregistre les compteurs de demandes de réinitialisation par IP/utilisateur (pour la limitation de débit)
user_security_questions	Stocke les questions de sécurité de l’utilisateur (ID de question et hachage de la réponse)
user_2fa	Stocke les clés secrètes TOTP (réservé)
remember_me_tokens	Jetons « Se souvenir de moi » (connexion persistante)
account_sessions	Enregistrements des sessions actives (pour la gestion multi‑appareils)
login_logs	Journaux des tentatives de connexion
audit_logs	Journaux d’audit (détails en JSON)
rate_limits	Enregistrements génériques de limitation de débit
ip_blacklist	Liste noire d’IP (avec expiration)
user_points	Solde et statistiques des points de l’utilisateur
points_shop_items	Configuration des articles de la boutique (ID, prix, stock, catégorie, etc.)
points_transactions	Enregistrement des transactions de points (échanges, rechargements, échanges de temps)
6. Instructions de Configuration (config.php)
Le fichier de configuration contient les sections principales suivantes :

6.1 Connexions à la Base de Données (database / characters_database / world_database)

Connecte respectivement aux bases auth, characters et world, avec support d’hôtes et de ports indépendants.

6.2 Configuration SOAP

php
$config['soap'] = [
    'host' => '127.0.0.1',      // Adresse SOAP du worldserver
    'port' => 7878,             // Port par défaut
    'username' => '3#1',        // Format `account_id#realm_id`
    'password' => '...',        // Mot de passe SOAP (doit correspondre à worldserver.conf)
    'timeout' => 30,
    'debug' => false,
];
6.3 Passerelles de Paiement (stripe / yipay / paypal / wechat / alipay)

Chaque passerelle dispose de son propre interrupteur d’activation, clés, taux de change et environnement (sandbox).

YiPay prend en charge la signature MD5.

6.4 Service d’Email

Utilise SMTP pour envoyer les emails d’activation, de réinitialisation de mot de passe, d’alerte de sécurité, etc.

Supporte Gmail, QQ Mail, etc. (nécessite des mots de passe spécifiques à l’application).

6.5 Paramètres de Sécurité

min_password_length, max_login_attempts, lockout_duration_minutes, session_lifetime, remember_me_lifetime, etc.

Activer/désactiver reCAPTCHA, 2FA (réservé).

6.6 Points et Boutique

points_per_hour (taux d’échange du temps de jeu), min_exchange_hours.

level_boost_target (niveau cible pour les montées de niveau).

Catégories d’articles : level_boost, gold, gm_level, articles normaux.

7. Exigences de l’Environnement de Déploiement

7.1 Environnement Serveur

PHP : Version 8.2 (la version gratuite requiert exactement 8.2)

MySQL : 8.0+ / MariaDB 12+

Serveur Web : Apache / Nginx

Extensions PHP requises : mysqli, session, curl, soap (obligatoire), gd, json, mbstring, gmp, sg11, Imagick

Composer : 2.0+

Installation des dépendances :

bash
composer require phpmailer/phpmailer
composer require stripe/stripe-php   # si Stripe est activé
7.2 Configuration de TrinityCore

worldserver.conf doit activer SOAP :

text
SOAP.Enabled = 1
SOAP.Port = 7878
SOAP.Redirect = 0
La base auth doit contenir la table battlenet_accounts (fournie par TrinityCore).

La table account doit inclure un champ email (le système l’ajoutera automatiquement s’il manque).

7.3 Installation des Dépendances
Utiliser Composer comme indiqué ci‑dessus.

7.4 Permissions des Fichiers

config/config.php doit avoir les permissions 600 ou 640 (lecture seule).

Les répertoires de journaux (si l’audit n’utilise pas la BD) doivent être accessibles en écriture.

Les répertoires de téléchargement (le cas échéant) nécessitent des contrôles d’accès appropriés.

7.5 Réseau et Sécurité

Il est fortement recommandé d’activer HTTPS (définir require_https = true dans la configuration).

Configurer le pare‑feu pour n’ouvrir que les ports 80/443 ; restreindre le port SOAP (7878) à localhost.

Mettre à jour régulièrement PHP et les extensions.

8. Exemples de Flux d’Utilisation

8.1 Inscription d’un Nouvel Utilisateur

Visiter /register.php, saisir l’email et le mot de passe.

Le système appelle SOAP pour créer un compte Battle.net, génère un compte de jeu et envoie un email d’activation.

L’utilisateur clique sur le lien d’activation → le compte est activé et il peut se connecter au panel web.

8.2 Connexion et Obtention de Points

Visiter /login.php, saisir l’email et le mot de passe, éventuellement cocher « Se souvenir de moi ».

Après connexion, aller dans /profile.php pour voir la liste des personnages et le solde de points.

Dans la section « Centre de points », saisir le nombre d’heures de jeu à échanger contre des points (consomme le totaltime du personnage).

8.3 Dépense des Points

Cliquer sur « Boutique de points » pour aller dans /points_shop.php.

Parcourir les articles (objets, montée de niveau, or, permissions GM).

Sélectionner un article et un personnage cible, cliquer sur « Échanger ».

Le système déduit les points, effectue l’action correspondante (envoie le courrier avec l’objet, met à jour le niveau, ajoute l’or, etc.) et enregistre la transaction.

8.4 Rechargement de Points

Visiter /topup.php, saisir le montant à recharger.

Choisir un moyen de paiement (Stripe/YiPay, etc.).

Finaliser le paiement ; le système ajoute automatiquement les points au compte.

8.5 Paramètres de Sécurité

Dans /security_settings.php, changer le mot de passe, gérer les sessions, définir les questions de sécurité.

Les questions de sécurité servent de méthode de vérification alternative pour la récupération du mot de passe.

9. Extension et Personnalisation

Ajouter une nouvelle passerelle de paiement : Ajouter la configuration dans config.php, implémenter le routage et la gestion des callbacks dans topup.php.

Ajouter de nouveaux types de produits : Étendre la logique d’échange dans points_shop.php avec de nouvelles branches de category.

Multilingue : Ajouter des fichiers de langue dans languages/ et hériter de la classe Language.

2FA : Le système réserve déjà la table user_2fa et des ébauches d’interface – intégrer une bibliothèque TOTP (ex. robthree/twofactorauth) pour l’activer.

10. Maintenance et Supervision

Visualisation des journaux : Les tables audit_logs et login_logs fournissent un historique détaillé ; on peut construire une interface d’administration pour les afficher.

Nettoyage périodique : Le système inclut AuditLogger::cleanOldLogs($days) pour supprimer régulièrement les anciens enregistrements.

Maintenance de la BD : Optimiser périodiquement les tables de sessions et de jetons en supprimant les enregistrements expirés.

Mises à jour de sécurité : Maintenir PHP et les dépendances Composer à jour avec les derniers correctifs.

11. Foire Aux Questions (FAQ)

Q : L’inscription échoue avec le message « SOAP service unavailable ».
R : Vérifier que worldserver est en cours d’exécution, que la configuration SOAP est correcte et que le pare‑feu autorise le port 7878 (de préférence en accès local uniquement).

Q : L’email d’activation n’est pas reçu.
R : Vérifier la configuration SMTP et les journaux de messagerie ; les utilisateurs peuvent utiliser la fonction « Renvoyer l’email d’activation ».

Q : Les personnages n’apparaissent pas après la connexion.
R : Confirmer que la configuration de characters_database est correcte et que le compte Battle.net possède effectivement des personnages.

Q : L’objet échangé avec des points n’est pas reçu.
R : Vérifier que les tables mail et item_instance de la base de données des personnages ont été correctement insérées ; s’assurer que la boîte aux lettres du personnage n’est pas pleine.

Q : Échec de la réinitialisation du mot de passe.
R : S’assurer que SOAP est disponible et que le compte existe ; si des questions de sécurité sont utilisées, vérifier que le hachage de la réponse correspond (respect de la casse).

12. Version et Support

Version actuelle : Basée sur TrinityCore 12.x (support de 11.0 Dragonflight et versions antérieures).

Compatibilité : Théoriquement compatible avec toutes les branches de TrinityCore utilisant le système de comptes Battle.net (des ajustements mineurs sur les noms de champs peuvent être nécessaires).

Support technique : Consulter les forums officiels de TrinityCore ou la documentation du système ; utiliser les journaux d’erreur détaillés pour le dépannage.