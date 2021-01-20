<?php

namespace wishlist\vue;

class Vue {
    private $content;

    public function __construct(String $c){
        $this->content=$c;
}

    /**
     * méthode qui affiche le rendu HTML final
     * @param array $args
     * @return string
     */
    public function render(array $args = []){
        /*if(\wishlist\modele\Utilisateur::estAuthentifie()){
            $nav="";
        } else {
            $nav = "<a href='/MyWishListRendu/compte/creer'>S'inscrire</a>
                    <a href='/MyWishListRendu/compte/connexion'>Connexion</a>";
        }*/

        $res = <<<END
<!DOCTYPE html>
<html lang="fr">

<head>
<title>MyWishList</title>
<link href="/MyWishListRendu/src/css/Style.css" rel="stylesheet">
</head>
<header><h1>MyWishList</h1></header>
<nav>
    <a href="/MyWishListRendu/accueil">Accueil</a>
    <a href="/MyWishListRendu/compte/creer">S'inscrire</a>
    <a href='/MyWishListRendu/compte/connexion'>Connexion</a>
    <a href='/MyWishListRendu/compte/deconnexion'>Déconnexion</a>
</nav>
<body>
<div class="content">
$this->content
</div>
</body>
<footer>
    <p>&copy; 2021 - DEVILLIERS Paul - GONON Emmanuel - MONTEMURRO--CHEVRIER Maxime - S3A</p>
</footer></html>
END;
        return $res;

    }
}