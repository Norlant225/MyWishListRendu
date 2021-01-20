<?php

namespace wishlist\vue;

class Utilisateur
{
    private $tabObjet;

    public function __construct(array $tab = [])
    {
        $this->tabObjet = $tab;
    }

    /**
     * méthode qui affiche le formulaire de création d'un compte
     * @return string
     */
    public function creationCompte(){
        $url = $this->tabObjet[0]->pathFor('creerCompte');
        $res = <<<END
<section class="gestionCompte">
<h1>Création d'un compte</h1>
<form id="r1" method="post" action=$url>
    <label for="r1">Pseudo : </label>
    <input type="text" id="r1" name="pseudo" placeholder="<pseudo>" required>
    <label for="c1">Mot de passe : </label>
    <input type="password" id="c1" name="mdp" placeholder="<mot de passe>" required>
    <button type="submit">Valider</button>
</form>
</section>
END;
        return $res;
    }

    public function compteCree($n){
        switch ($n){
            case 0:
                $url = $this->tabObjet[0]->pathFor('connexionCompte');
                $res = '<section class="gestionCompte">';
                $res .= "<h1>Compte créé !</h1>";
                $res .= "<a href=$url>Connexion</a>";
                $res .= '</section>';
                break;
            case 1:
                $url = $this->tabObjet[0]->pathFor('creerCompte');
                $res = '<section class="gestionCompte">';
                $res .= "<h1>Erreur : utilisateur déjà existant</h1>";
                $res .= "<a href=$url>Retour</a>";
                $res .= '</section>';
        }
        return $res;
    }

    /**
     * méthode qui affiche le formulaire de connexion
     * @return string
     */
    public function connexionCompte(){
        $url = $this->tabObjet[0]->pathFor('connexionCompte');
        $res = <<<END
<section class="gestionCompte">
<h1>Connexion</h1>
<form id="r1" method="post" action=$url>
    <label for="r1">Pseudo : </label>
    <input type="text" id="r1" name="pseudo" placeholder="<pseudo>" required>
    <label for="c1">Mot de passe : </label>
    <input type="password" id="c1" name="mdp" placeholder="<mot de passe>" required>
    <button type="submit">Valider</button>
</form>
</section>
END;
        return $res;
    }

    public function compteConnecte($n){
        switch ($n){
            case 0:
                $url = $this->tabObjet[0]->pathFor('accueil');
                $res = '<section class="gestionCompte">';
                $res .= "<h1>Connexion réussie !</h1>";
                $res .= "<a href=$url>Accueil</a>";
                $res .= '</section>';
                break;
            case 1:
                $url = $this->tabObjet[0]->pathFor('connexionCompte');
                $res = '<section class="gestionCompte">';
                $res .= "<h1>Erreur : utilisateur inexistant ou mot de passe incorrect</h1>";
                $res .= "<a href=$url>Retour</a>";
                $res .= '</section>';
        }
        return $res;
    }

    public function compteDeconnecte(){
        $url = $this->tabObjet[0]->pathFor('accueil');
        $res = '<section class="gestionCompte">';
        $res .= "<h1>Déconnexion réussie</h1>";
        $res .= "<a href=$url>Retour</a>";
        $res .= '</section>';
        return $res;
    }

}
