<?php

namespace wishlist\controleur;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \wishlist\vue\Item as VItem;
use wishlist\vue\Vue;

class Item {
    //conteneur de dépendances
    private $c;

    public function __construct(\Slim\Container $container) {
        $this->c=$container;
    }

    //fonctionnalité 2
    public function getItem (Request $rq, Response $rs, array $args) : Response {
        $item = \wishlist\modele\Item::where('id', "=", $args['id'])->first();
        if($item->liste_id == 0){
            $vue = new Vue("<h1>Erreur : item n'appartient à aucune liste</h1>");
        } else {
            $token = $item->liste()->first()->token;
            if($token == $args['token']){
                $router = $this->c->router;
                $v = new VItem([$item, $router]);
                $vue = new Vue($v->htmlItem($args['id']));
            } else {
                $vue = new Vue("<h1>Erreur : item introuvable</h1>");
            }
        }
        $rs->getBody()->write($vue->render());
        return $rs;
    }
    //fonctionnalité 3.1 / 4.1    #affiche le formulaire#
    public function reserverItem (Request $rq, Response $rs, array $args) : Response {
        //vérification de l'état de réservation de l'item
        $util = \wishlist\modele\Item::where('id', '=', $args['id'])->first()->util_id;
        if(is_null($util)){
            $router = $this->c->router;
            $v = new VItem([$router]);
            $vue = new Vue($v->reservationItem($args['id']));
            $rs->getBody()->write($vue->render());
        } else {
            $vue = new Vue("<h1>Erreur : item déjà réservé</h1>");
            $rs->getBody()->write($vue->render());
        }
        return $rs;
    }

    //fonctionnalité 3.2 / 4.2   #traite les données du formulaire#
    public function postReserverItem (Request $rq, Response $rs, array $args) : Response {
        //ajout du pseudo dans la BDD
        $pseudo = $rq->getParams()['pseudo'];
        $pseudo = filter_var($pseudo, FILTER_SANITIZE_STRING);

        $mes = $rq->getParams()['message'];
        $mes = filter_var($mes, FILTER_SANITIZE_STRING);

        \wishlist\modele\Utilisateur::ajouterParticipant($pseudo);

        //récuppération de l'id de l'utilisateur
        $util = \wishlist\modele\Utilisateur::where('pseudo', '=', $pseudo)->first()->id;


        //réservation de l'item dans la BDD
        $item = \wishlist\modele\Item::where('id', '=', $args['id'])->first();
        $item->util_id=$util;
        $item->message=$mes;
        $item->save();

        $router = $this->c->router;
        $v = new VItem([$router]);
        $vue = new Vue($v->itemReserve($args['id']));
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 9.1
    public function modifierItem(Request $rq, Response $rs, array $args) : Response {
        $item = \wishlist\modele\Item::where('id', '=', $args['id'])->first();
        $liste = \wishlist\modele\Liste::where('no', '=', $item->liste_id)->first();
        if(is_null($liste->token_partage)){
            $reservation = $item->util_id;
            if(is_null($reservation)){
                $router = $this->c->router;
                $v = new VItem([$router]);
                $vue = new Vue($v->modifierItem($args['id'], $item->nom, $item->descr, $item->img, $item->url, $item->tarif));
            } else {
                $vue = new Vue("<h1>Erreur : item réservé</h1>");
            }
        } else {
            $vue = new Vue("<h1>Erreur : modifications non autorisées</h1>");
        }
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 9.2
    public function postModifierItem (Request $rq, Response $rs, array $args) : Response {
        //récupération des données
        $nom = $rq->getParsedBody()['nom'];
        $nom = filter_var($nom, FILTER_SANITIZE_STRING);

        $desc = $rq->getParsedBody()['description'];
        $desc = filter_var($desc, FILTER_SANITIZE_STRING);

        $img = $rq->getParsedBody()['image'];
        $img = filter_var($img, FILTER_SANITIZE_URL);

        $url = $rq->getParsedBody()['url'];
        $url = filter_var($url, FILTER_SANITIZE_URL);

        $prix = $rq->getParsedBody()['prix'];
        $prix = filter_var($prix, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        \wishlist\modele\Item::modifierItem($args['id'], $nom, $desc, $img, $prix, $url);

        $router = $this->c->router;
        $v = new VItem([$router]);
        $vue = new Vue($v->itemModifie($args['id']));
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 10
    public function supprimerItem (Request $rq, Response $rs, array $args) : Response {
        $item = \wishlist\modele\Item::where('id', '=', $args['id'])->first();
        $liste = \wishlist\modele\Liste::where('no', '=', $item->liste_id)->first();
        if(is_null($liste->token_partage)){
            $reservation = $item->util_id;
            if(is_null($reservation)) {
                $item = \wishlist\modele\Item::where('id', '=', $args['id'])->first();
                $liste = \wishlist\modele\Liste::where('no', '=', $item->liste_id)->first();
                $item->delete();

                $router = $this->c->router;
                $v = new VItem([$router]);
                $vue = new Vue($v->itemSupprime($liste->no, $liste->token));
            } else {
                $vue = new Vue("<h1>Erreur : item réservé</h1>");
            }
        } else {
            $vue = new Vue("<h1>Erreur : suppression non autorisée</h1>");
        }

        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //foctionnalité 13
    public function supprimerImageItem (Request $rq, Response $rs, array $args) : Response {
        $item = \wishlist\modele\Item::where('id', '=', $args['id'])->first();
        $liste = \wishlist\modele\Liste::where('no', '=', $item->liste_id)->first();
        if(is_null($liste->token_partage && $item->img != "")){
            $reservation = $item->util_id;
            if(is_null($reservation)) {
                $item = \wishlist\modele\Item::where('id', '=', $args['id'])->first();
                $liste = \wishlist\modele\Liste::where('no', '=', $item->liste_id)->first();
                $item->img="";
                $item->save();

                $router = $this->c->router;
                $v = new VItem([$router]);
                $vue = new Vue($v->imageItemSupprime($liste->no, $liste->token));
            } else {
                $vue = new Vue("<h1>Erreur : item réservé</h1>");
            }
        } else {
            $vue = new Vue("<h1>Erreur : suppression non autorisée</h1>");
        }

        $rs->getBody()->write($vue->render());
        return $rs;
    }
}