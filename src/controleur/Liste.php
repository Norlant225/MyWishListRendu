<?php

namespace wishlist\controleur;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use wishlist\modele\MessageListe;
use \wishlist\vue\Liste as VListe;
use wishlist\vue\Vue;

class Liste {
    //conteneur de dépendances
    private $c;

    public function __construct(\Slim\Container $container) {
        $this->c=$container;
    }

    //fonctionnalité 0
    public function getAccueil (Request $rq, Response $rs, array $args) : Response {
        $listes = \wishlist\modele\Liste::all();
        $router = $this->c->router;
        $v = new VListe([$listes, $router]);
        $vue = new Vue($v->htmlListeListes());
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 1
    function getListeList (Request $rq, Response $rs, array $args) : Response {
        $liste1 = \wishlist\modele\Liste::where('no', "=", $args['id'])
            ->where('token', '=', $args['token'])->first();
        $liste2 = \wishlist\modele\Liste::where('no', "=", $args['id'])
            ->where('token_partage', '=', $args['token'])->first();
        $router = $this->c->router;
        if(!is_null($liste1)){  //vérification du 1er token
            $v = new VListe([$liste1, $router]);
            $vue = new Vue($v->htmlListeItems($args['id'], $args['token']));
        } else {
            if(!is_null($liste2)){  //vérification du 2nd token
                $v = new VListe([$liste2, $router]);
                $vue = new Vue($v->htmlListeItems($args['id'], $args['token']));
            } else {
                $vue = new Vue("<h1>Erreur : liste introuvable</h1>");
            }
        }
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 6.1
    public function creerListe (Request $rq, Response $rs, array $args) : Response {
        $router = $this->c->router;
        $v = new VListe([$router]);
        $vue = new Vue($v->creationListe());
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 6.2
    public function postCreerListe (Request $rq, Response $rs, array $args) : Response {
        //récupération des données
        $pseudo = $rq->getParams()['pseudo'];
        $pseudo = filter_var($pseudo, FILTER_SANITIZE_STRING);

        \wishlist\modele\Utilisateur::ajouterParticipant($pseudo);

        $titre = $rq->getParams()['titre'];
        $titre = filter_var($titre, FILTER_SANITIZE_STRING);

        $desc = $rq->getParams()['description'];
        $desc = filter_var($desc, FILTER_SANITIZE_STRING);

        $date = $rq->getParams()['date'];

        //récuppération de l'id de l'utilisateur
        $idUtil = \wishlist\modele\Utilisateur::where('pseudo', '=', $pseudo)->first()->id;

        //génération du token
        $token = random_bytes(32);
        $token = bin2hex($token);

        //ajout dans la BDD
        \wishlist\modele\Liste::ajouterListe($idUtil, $titre, $desc, $date, $token);

        //création du cookie
        $liste = \wishlist\modele\Liste::where('titre', '=', $titre)->first();
        setcookie('createur', $liste->no, time()+60*60*24*31*12, '/');

        //affichage de la vue
        $router = $this->c->router;
        $v = new VListe([$liste, $router]);
        $vue = new Vue($v->listeCreee($liste->no, $liste->token));
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionalité 7.1
    public function modifierListe (Request $rq, Response $rs, array $args) : Response {
        $liste = \wishlist\modele\Liste::where('no', "=", $args['id'])
            ->where('token', '=', $args['token'])->first();
        if(!is_null($liste)){
            $router = $this->c->router;
            $v = new VListe([$router]);
            $titre = \wishlist\modele\Liste::where('no','=',$args['id'])->first()->titre;
            $desc = \wishlist\modele\Liste::where('no','=',$args['id'])->first()->description;
            $date = \wishlist\modele\Liste::where('no','=',$args['id'])->first()->expiration;
            $vue = new Vue($v->modificationListe($titre, $desc, $date,$args['id'],$args['token']));
        } else {
            $liste = \wishlist\modele\Liste::where('no', "=", $args['id'])
                ->where('token_partage', '=', $args['token'])->first();
            if(!is_null($liste)){
                $vue = new Vue("<h1>Erreur : modifications non autorisées</h1>");
            } else {
                $vue = new Vue("<h1>Erreur : liste introuvable</h1>");
            }
        }
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 7.2
    public function postModifierListe (Request $rq, Response $rs, array $args) : Response
    {
        $titre = $rq->getParams()['titre'];
        $titre = filter_var($titre, FILTER_SANITIZE_STRING);

        $desc = $rq->getParams()['description'];
        $desc = filter_var($desc, FILTER_SANITIZE_STRING);

        $date = $rq->getParams()['date'];

        //modification dans la BDD
        \wishlist\modele\Liste::modifierListe($args['id'], $titre, $desc, $date);

        //affichage de la vue
        $liste = \wishlist\modele\Liste::where('no', '=', $args['id'])->first();
        $router = $this->c->router;
        $v = new VListe([$router]);
        $vue = new Vue($v->listeModifiee($liste->no, $liste->token));
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 8.1
    public function ajouterItem (Request $rq, Response $rs, array $args) : Response {
        $liste = \wishlist\modele\Liste::where('no', "=", $args['idListe'])
            ->where('token', '=', $args['token'])->first();
        $router = $this->c->router;
        if(!is_null($liste)){
            $listeItemLibre = \wishlist\modele\Item::where('liste_id', '=', 0)->get();

            $v = new VListe([$router, $listeItemLibre]);
            $vue = new Vue($v->ajouterItem($args['idListe'], $args['token']));
        } else {
            $liste = \wishlist\modele\Liste::where('no', "=", $args['idListe'])
                ->where('token_partage', '=', $args['token'])->first();
            if(!is_null($liste)) {
                $vue = new Vue("<h1>Erreur : ajout d'item non autorisées</h1>");
            } else {
                $vue = new Vue("<h1>Erreur : liste introuvable</h1>");
            }
        }
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 8.2
    public function postAjouterItem (Request $rq, Response $rs, array $args) : Response {
        if($rq->getParams()['bouton'] == '1'){
            //récupération des données
            $nom = $rq->getParams()['liste_items'];
            $idItem = \wishlist\modele\Item::where('nom' ,'=', $nom)->first()->id;
            //ajout la BDD
            \wishlist\modele\Item::ajouterItemListe($idItem, $args['idListe']);
        } else {
            //récupération des données
            $nom = $rq->getParams()['nom'];
            $nom = filter_var($nom, FILTER_SANITIZE_STRING);

            $desc = $rq->getParams()['description'];
            $desc = filter_var($desc, FILTER_SANITIZE_STRING);

            $prix = $rq->getParams()['prix'];
            $prix = filter_var($prix, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

            if($rq->getParams()['url'] != ""){
                $url = $rq->getParams()['url'];
                $url = filter_var($url, FILTER_SANITIZE_URL);
            } else {
                $url = "";
            }
            \wishlist\modele\Item::ajouterItem($args['idListe'], $nom, $desc, $prix, $url);
        }

        //affichage de la vue
        $router = $this->c->router;
        $token = \wishlist\modele\Liste::where('no', '=', $args['idListe'])->first()->token;
        $v = new VListe([$router]);
        $vue = new Vue($v->itemAjoute($args['idListe'], $token, $nom));
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 14
    public function partagerListe (Request $rq, Response $rs, array $args) : Response {
        $liste = \wishlist\modele\Liste::where('no', "=", $args['id'])->first();

        if(!is_null($liste->token) && is_null($liste->token_partage)) {
            //génération du token
            $token = random_bytes(32);
            $token = bin2hex($token);

            $liste->token_partage=$token;
            $liste->save();

            $router = $this->c->router;
            $v = new VListe([$router]);
            $vue = new Vue($v->partageListe($args['id'], $token, $args['token']));
        } else {
            if(!is_null($liste->token_partage)) {
                $vue = new Vue("<h1>Erreur : liste déjà partagée</h1>");
            } else {
                $vue = new Vue("<h1>Erreur : liste introuvable</h1>");
            }
        }
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 5.1
    public function ajouterMessageListe (Request $rq, Response $rs, array $args) : Response {
        $router = $this->c->router;
        $v = new VListe([$router]);
        $vue = new Vue($v->ajouterMessage($args['id']));
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 5.2
    public function postAjouterMessageListe (Request $rq, Response $rs, array $args) : Response {
        //récupération des données
        $msg = $rq->getParsedBody()['message'];
        $msg = filter_var($msg, FILTER_SANITIZE_STRING);

        MessageListe::ajouterMessage($args['id'], $msg);

        $router = $this->c->router;
        $token = \wishlist\modele\Liste::where('no', '=', $args['id'])->first()->token;
        $v = new VListe([$router]);
        $vue = new Vue($v->messageAjoute($args['id'], $token));
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 20
    public function rendrePubliqueListe (Request $rq, Response $rs, array $args) : Response {
        $liste = \wishlist\modele\Liste::where('no', "=", $args['id'])->first();
        if($liste->token == $args['token']){
            \wishlist\modele\Liste::rendrePublique($args['id']);

            $router = $this->c->router;
            $v = new VListe([$router]);
            $vue = new Vue($v->listePartagee($args['id'], $args['token']));
        } else {
            $vue = new Vue("<h1>Erreur : liste introuvable</h1>");
        }
        $rs->getBody()->write($vue->render());
        return $rs;
    }
}