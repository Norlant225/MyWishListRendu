<?php

namespace wishlist\controleur;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \wishlist\vue\Utilisateur as VUtil;
use wishlist\vue\Vue;

class Utilisateur
{
    //conteneur de dépendances
    private $c;

    public function __construct(\Slim\Container $container)
    {
        $this->c = $container;
    }

    //fonctionnalité 17.1
    public function creerCompte (Request $rq, Response $rs, array $args) : Response {
        $router = $this->c->router;
        $v = new VUtil([$router]);
        $vue = new Vue($v->creationCompte());
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 17.2
    public function postCreerCompte (Request $rq, Response $rs, array $args) : Response {
        $pseudo = $rq->getParsedBody()['pseudo'];
        $pseudo = filter_var($pseudo, FILTER_SANITIZE_STRING);

        $router = $this->c->router;
        $v = new VUtil([$router]);
        //compte deja cree
        if(!is_null(\wishlist\modele\Utilisateur::where('pseudo', '=', $pseudo)->first()) &&
            \wishlist\modele\Utilisateur::where('pseudo', '=', $pseudo)->first()->mdp != null){
            $vue = new Vue($v->compteCree(1));
        } else {
            //contrainte de mot de passe : 8 caractères minimum, 1 majuscule, 1 chiffres, 1 caractère spécial
            //https://github.com/martinssipenko/password-policy TODO
            $mdp = $rq->getParsedBody()['mdp'];

            \wishlist\modele\Utilisateur::ajouterUtilisateur($pseudo, $mdp);

            $vue = new Vue($v->compteCree(0));
        }
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    public function verifierMDP($mdp){
        //preg_match
    }

    //fonctionnalité 18.1
    public function connexionCompte (Request $rq, Response $rs, array $args) : Response {
        $router = $this->c->router;
        $v = new VUtil([$router]);
        $vue = new Vue($v->connexionCompte());
        $rs->getBody()->write($vue->render());
        return $rs;
    }

    //fonctionnalité 18.2
    public function postConnexionCompte (Request $rq, Response $rs, array $args) : Response {
        //recuperation des donnees
        $pseudo = $rq->getParsedBody()['pseudo'];
        $pseudo = filter_var($pseudo, FILTER_SANITIZE_STRING);
        $mdp=$rq->getParsedBody()['mdp'];
        $router = $this->c->router;
        $v=new VUtil([$router]);
        //authentification
        if(self::authentification($pseudo, $mdp)){
            $vue=new Vue($v->compteConnecte(0));
        }else {
            $vue=new Vue($v->compteConnecte(1));
        }

        $rs->getBody()->write($vue->render());
        return $rs;
    }

    public static function authentification($pseudo, $mdp){
        if(\wishlist\modele\Utilisateur::connexion($pseudo, $mdp)){
            self::chargerProfil(\wishlist\modele\Utilisateur::where('pseudo', '=', $pseudo)->first()->id);
            return true;
        } else {
            return false;
        }
    }

    private static function chargerProfil($uid){
        session_start();
        unset($_SESSION[$uid]);
        $_SESSION[$uid] = array('role' => 'utilisateur');
    }

    public function deconnexionCompte (Request $rq, Response $rs, array $args) : Response {
        session_destroy();

        $router = $this->c->router;
        $v = new VUtil([$router]);
        $vue = new Vue($v->compteDeconnecte());
        $rs->getBody()->write($vue->render());
        return $rs;
    }
}