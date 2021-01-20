<?php

namespace wishlist\vue;

use wishlist\modele\MessageListe;

class Liste {
    private $tabObjet;

    public function __construct(array $tab = []){
        $this->tabObjet=$tab;
    }

    /**
     * méthode qui affiche la liste des listes de souhaits
     * @return string
     */
    public function htmlListeListes(){
        $bouton1 = $this->tabObjet[1]->pathFor('creerListe');
        $res = "<section class='listesAccueil'><h1>Liste des listes de souhaits</h1><ul>";
        foreach ($this->tabObjet[0] as $l){
            $url = $this->tabObjet[1]->pathFor('getListeList',
                ['id' => $l->no, 'token' => $l->token]);
            $res .= "<li class='liste'>Titre : <a href=$url>" . $l->titre . "</a><br>Date d'expiration : " . $l->expiration . "</li><br>";
        }
        $res .= "</ul>";
        $res .= "<button onclick=\"self.location.href ='" . $bouton1 . "' \">Créer une liste</button>";
        $res .= "</section>";
        return $res;
    }

    /**
     * méthode qui affiche le détail de la liste des souhaits
     * @param $id
     * @param $token
     * @return string
     */
    public function htmlListeItems($id, $token){
        $bouton1 = $this->tabObjet[1]->pathFor('partagerListe', ['id' => $id, 'token' => $this->tabObjet[0]->token]);
        $bouton2 = $this->tabObjet[1]->pathFor('ajouterItem', ['idListe' => $id, 'token' => $this->tabObjet[0]->token]);
        $bouton3 = $this->tabObjet[1]->pathFor('modifierListe', ['id' => $id, 'token' => $this->tabObjet[0]->token]);
        $bouton4 = $this->tabObjet[1]->pathFor('ajouterMessageListe', ['id' => $id]);
        $bouton5 = $this->tabObjet[1]->pathFor('rendrePubliqueListe', ['id' => $id, 'token' => $this->tabObjet[0]->token]);
        $res = "<section class='liste'>";
        if(is_null($this->tabObjet[0]->token_partage)){
            $res .= "<button onclick=\"self.location.href ='" . $bouton1 . "' \">Partager la liste</button>";
        } else {
            $res .= "<p>Lien à partager : " . $this->tabObjet[1]
                    ->pathFor('getListeList', ['id' => $id, 'token' => $this->tabObjet[0]->token_partage])
                . "</p>";
        }

        if($this->tabObjet[0]->token == $token){
            if($this->tabObjet[0]->visibilite=='private'){
                $visibilite = "Rendre publique";
            } else {
                $visibilite = "Rendre privé";
            }
            $res .= "<button onclick=\"self.location.href ='" . $bouton5 . "' \">$visibilite</button>";
        }

        $res .= "<h1>Liste des items de la liste n°$id </h1>";
        $res .= 'Titre : ' . $this->tabObjet[0]->titre . '<br>Description : ' . $this->tabObjet[0]->description .
            '<br>Expiration : ' . $this->tabObjet[0]->expiration;

        $messages = "<br>Messages : <ol>";
        foreach (MessageListe::where('liste_id', '=', $id)->get() as $msg){
            $messages .= "<li class='message'>$msg->message</li>";
        }

        $res .= $messages . '</ol> <br><ul>';

        foreach ($this->tabObjet[0]->items()->get() as $i){
            $url1 = $this->tabObjet[1]->pathFor('getItem', ['id' => $i->id, 'token' => $this->tabObjet[0]->token]);
            $reservation = $this->etatReservation($i->id);
            if(count(explode('/', $i->img))==1){
                $img = '/MyWishListRendu/web/img/';
            } else {
                $img ='';
            }

            $res .= '<li class="item">Nom : <a href=' . $url1 . '>' . $i->nom . '</a><br> <div class="image"><img src="' . $img . $i->img .
                '" alt="image absente"></div> <br>Réservation : ' . $reservation . '</li><br>';
        }
        $res .= "</ul>";
        if(is_null($this->tabObjet[0]->token_partage)){
            $res .= "<button onclick=\"self.location.href ='" . $bouton3 . "' \">Modifer les information de la liste</button>";
            $res .= "<button onclick=\"self.location.href ='" . $bouton2 . "' \">Ajouter item</button>";
        }
        $res .= "<button onclick=\"self.location.href ='" . $bouton4 . "' \">Ajouter un message</button>";
        $res .="</section>";
        return $res;
    }

    public function etatReservation($id){
        $etat = \wishlist\modele\Item::where('id', '=', $id)->first()->util_id;
        if(is_null($etat)){
            $res = "libre";
        } else {
            $idListe = \wishlist\modele\Item::where('id', '=', $id)->first()->liste_id;
            $date = \wishlist\modele\Liste::where('no', '=', $idListe)->first()->expiration;
            //créateur une seule liste
            if(isset($_COOKIE['createur']) && $_COOKIE['createur']==$idListe && strtotime($date) > time()){
                $res = "<strong>réservé</strong>";
            } else {
                $nom = \wishlist\modele\Utilisateur::where('id', '=', $etat)->first()->pseudo;
                $res = "réservé par <strong>$nom</strong>";
            }

        }
        return $res;
    }

    /**
     * méthode qui affiche le formulaire de création d'une liste
     * @return string
     */
    public function creationListe(){
        $url = $this->tabObjet[0]->pathFor('creerListe');
        $res = <<<END
<section class="liste">
<h1>Création d'une liste</h1>
<form id="r1" method="post" action=$url>
    <label for="c1">Titre : </label>
    <input type="text" id="c1" name="titre" placeholder="<titre>" required>
    
    <label for="c2">Description : </label>
    <input type="text" id="c2" name="description" placeholder="<description>" required>
    
    <label for="c3">Date expiration : </label>
    <input type="date" id="c3" name="date" placeholder="<date>" required>
    
    <label for="c4">Pseudo du créateur : </label>
    <input type="text" id="c4" name="pseudo" placeholder="<pseudo>" required>
    
    <button type="submit">Valider</button>
</form>
</section>
END;
        return $res;
    }

    /**
     * méthode qui affiche le formulaire de modification d'une liste
     * @param $titre
     * @param $desc
     * @param $date
     * @param $id
     * @param $token
     * @return string
     */
    public function modificationListe($titre, $desc, $date,$id,$token){
        $url = $this->tabObjet[0]->pathFor('modifierListe',['id' => $id, 'token' => $token]);
        $res = <<<END
<section class="liste">
<h1>Modification de la liste n°$id</h1>
<form id="r1" method="post" action=$url>
    <label for="c1">Titre : </label>
    <input type="text" id="c1" name="titre" placeholder="<titre>" value="$titre" required>
    
    <label for="c2">Description : </label>
    <input type="text" id="c2" name="description" placeholder='<description>' value= "$desc" required>
    
    <label for="c3">Date expiration : </label>
    <input type="date" id="c3" name="date" placeholder='<date>' value="$date" required>
    
    <button type="submit">Valider</button>
</form>
</section>
END;
        return $res;
    }

    public function listeModifiee($id, $token){
        $url = $this->tabObjet[0]->pathFor('getListeList', ['id' => $id, 'token' => $token]);
        $res = "<section class='liste'><h1>Informations de la liste n°$id modifiées</h1>";
        $res .=  "<a href=$url>Retour</a></section>";
        return $res;
    }

    public function listeCreee($id, $token){
        $url = $this->tabObjet[1]->pathFor('getListeList', ['id' => $id, 'token' => $token]);
        $res = "<section class='liste'><p>Lien de la liste : <a href=$url>$url</a></p></section>";
        return $res;
    }

    /**
     * méthode qui affiche le formulaire de création d'un item pour l'ajouter à une liste
     * @param $id
     * @param $token
     * @return string
     */
    public function ajouterItem($id, $token){
        $url = $this->tabObjet[0]->pathFor('ajouterItem', ['idListe' => $id, 'token' => $token]);
        $liste = "<select name='liste_items' size=1>";
        foreach ($this->tabObjet[1] as $i){
            $liste .= "<option>" . $i->nom . "</option>";
        }
        $liste .= "</select>";

        $res = <<<END
<section class="liste">
<h1>Ajout d'un item dans la liste n°$id</h1>
<h4>Item dans la base de données</h4>
<form id="r1" method="post" action=$url>
    $liste
    <button name='bouton' value=1 type="submit">Valider</button>
</form>
<br>
<h4>Nouvel item</h4>
<form id="r2" method="post" action=$url>
    <label for="c1">Nom : </label>
    <input type="text" id="c1" name="nom" placeholder="<nom>" required>
    
    <label for="c2">Description : </label>
    <input type="text" id="c2" name="description" placeholder="<description>" required>
    
    <label for="c3">Prix : </label>
    <input type="text" id="c3" name="prix" placeholder="<prix>" required>
    
    <label for="c4">URL : </label>
    <input type="url" id="c4" name="url" placeholder="<url>">
    <button name='bouton' value=2 type="submit">Valider</button>
</form>
</section>
END;
        return $res;
    }

    public function itemAjoute($id, $token, $nomItem){
        $url = $this->tabObjet[0]->pathFor('getListeList', ['id' => $id, 'token' => $token]);
        $res = "<section class='liste'><h1>Item '$nomItem' ajouté à la liste n°$id</h1>";
        $res .= "<a href=$url>Retour</a></section>";
        return $res;
    }

    public function partageListe($id, $tokenPartage, $token){
        $url1 = $this->tabObjet[0]->pathFor('getListeList', ['id' => $id, 'token' => $tokenPartage]);
        $url2 = $this->tabObjet[0]->pathFor('getListeList', ['id' => $id, 'token' => $token]);
        $res = "<section class='liste'><p>L'URL à partager : $url1</p>";
        $res .= "<a href=$url2>Retour</a></section>";
        return $res;
    }

    /**
     * méthode qui affiche le formulaire d'ajout d'un message à une liste
     * @param $id
     * @return string
     */
    public function ajouterMessage($id){
        $url = $this->tabObjet[0]->pathFor('ajouterMessageListe',['id' => $id]);
        $res = <<<END
<section class="liste">
<h1>Ajout d'un message dans la liste n°$id</h1>
<form id="r1" method="post" action=$url>
    <label for="c1">Message : </label>
    <input type="text" id="c1" name="message" placeholder="<message>" required>
    
    <button type="submit">Valider</button>
</form>
</section>
END;
        return $res;
    }

    public function messageAjoute($id, $token){
        $url = $this->tabObjet[0]->pathFor('getListeList', ['id' => $id, 'token' => $token]);
        $res = "<section class='liste'><h1>Message ajouté dans la liste n°$id</h1>";
        $res .= "<a href=$url>Retour</a></section>";
        return $res;
    }

    public function listePartagee($id, $token){
        $url = $this->tabObjet[0]->pathFor('getListeList', ['id' => $id, 'token' => $token]);
        $res = "<section class='liste'><h1>Visibilité de la liste n°$id modifiée</h1>";
        $res .= "<a href=$url>Retour</a></section>";
        return $res;
    }
}