<?php

require_once __DIR__ . '/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Illuminate\Database\Capsule\Manager as DB;

$db = new DB();

$config = require_once __DIR__ . '/src/conf/settings.php';
//conteneur de dependances
$c =new \Slim\Container($config);

$db->addConnection(parse_ini_file($c['settings']['dbfile']));
$db->setAsGlobal();
$db->bootEloquent();

$app = new \Slim\App($c);

//fonctionnalité 7.1
$app->get('/liste/modifier/{id}/{token}[/]', \wishlist\controleur\Liste::class . ':modifierListe')
    ->setName("modifierListe");

//fonctionnalité 7.2
$app->post('/liste/modifier/{id}/{token}[/]', \wishlist\controleur\Liste::class . ':postModifierListe');

//fonctionnalité 1
$app->get('/liste/souhait/{id}/{token}[/]', \wishlist\controleur\Liste::class . ':getListeList')
    ->setName("getListeList");

//fonctionnalité 8.1
$app->get('/liste/ajouter/{idListe}/{token}[/]', \wishlist\controleur\Liste::class . ':ajouterItem')
    ->setName("ajouterItem");

//fonctionnalité 8.2
$app->post('/liste/ajouter/{idListe}/{token}[/]', \wishlist\controleur\Liste::class . ':postAjouterItem');

//fonctionnalité 14
$app->get('/liste/partager/{id}/{token}[/]', \wishlist\controleur\Liste::class . ':partagerListe')
    ->setName("partagerListe");

//fonctionnalité 20
$app->get('/liste/publique/{id}/{token}[/]', \wishlist\controleur\Liste::class . ':rendrePubliqueListe')
    ->setName("rendrePubliqueListe");

//fonctionnalité 5.1
$app->get('/liste/message/{id}[/]', \wishlist\controleur\Liste::class . ':ajouterMessageListe')
    ->setName("ajouterMessageListe");

//fonctionnalité 5.2
$app->post('/liste/message/{id}[/]', \wishlist\controleur\Liste::class . ':postAjouterMessageListe');

//fonctionnalité 9.1
$app->get('/item/modifier/{id}[/]', \wishlist\controleur\Item::class . ':modifierItem')
    ->setName("modifierItem");

//fonctionnalité 9.2
$app->post('/item/modifier/{id}[/]', \wishlist\controleur\Item::class . ':postModifierItem');

//fonctionnalité 10
$app->get('/item/supprimer/{id}[/]', \wishlist\controleur\Item::class . ':supprimerItem')
    ->setName("supprimerItem");

//fonctionnalité 13
$app->get('/item/supprimer-image/{id}[/]', \wishlist\controleur\Item::class . ':supprimerImageItem')
    ->setName("supprimerImageItem");

//fonctionnalité 17.1
$app->get('/compte/creer[/]', \wishlist\controleur\Utilisateur::class . ':creerCompte')
    ->setName("creerCompte");

//fonctionnalité 17.2
$app->post('/compte/creer[/]', \wishlist\controleur\Utilisateur::class . ':postCreerCompte');

//fonctionnalité 18.1
$app->get('/compte/connexion[/]', \wishlist\controleur\Utilisateur::class . ':connexionCompte')
    ->setName("connexionCompte");

//fonctionnalité 18.2
$app->post('/compte/connexion[/]', \wishlist\controleur\Utilisateur::class . ':postConnexionCompte');

$app->get('/compte/deconnexion[/]', \wishlist\controleur\Utilisateur::class . ':deconnexionCompte');

//fonctionnalité 6.1
$app->get('/liste/creer[/]', \wishlist\controleur\Liste::class . ':creerListe')
    ->setName("creerListe");

//fonctionnalité 6.2
$app->post('/liste/creer[/]', \wishlist\controleur\Liste::class . ':postCreerListe');

//fonctionnalité 3.1 / 4.1
$app->get('/item/reserver/{id}[/]', \wishlist\controleur\Item::class . ':reserverItem')
    ->setName("reserverItem");

//fonctionnalité 3.2 / 4.2
$app->post('/item/reserver/{id}[/]', \wishlist\controleur\Item::class . ':postReserverItem');

//fonctionnalité 2
$app->get('/item/{id}/{token}[/]', \wishlist\controleur\Item::class . ':getItem')
    ->setName("getItem");

//fonctionnalité 0
$app->get('/accueil[/]', \wishlist\controleur\Liste::class . ':getAccueil')->setName('accueil');

$app->post('compte/creer[/]', \wishlist\controleur\Utilisateur::class.':creerCompte');

$app->run();