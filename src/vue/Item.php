<?php

namespace wishlist\vue;

class Item {
    private $tabObjet;

    public function __construct(array $tab = []){
        $this->tabObjet=$tab;
    }

    /**
     * methode qui affiche le détail d'un item
     * @param $id
     * @return string
     */
    public function htmlItem($id){
        $reservation = $this->etatReservation($id);
        if(count(explode('/', $this->tabObjet[0]->img))==1){
            $img = '/MyWishListRendu/web/img/';
        } else {
            $img ='';
        }

        $res = "<section class='item'>";
        $res .= "<h1>Item n°$id</h1>";
        $res .= "Nom : " . $this->tabObjet[0]->nom . "<br>Description : " . $this->tabObjet[0]->descr . '<br> <div class="image"><img src="'
          . $img . $this->tabObjet[0]->img . '" alt="image absente"></div><br> URL : <a href='. $this->tabObjet[0]->url . ">" . $this->tabObjet[0]->url
            . "</a><br> Tarif : "
            . $this->tabObjet[0]->tarif;
        $res .= '€<br>' . $reservation;
        $res .= '</section>';
        return $res;
    }

    public function etatReservation($id){
        $item = \wishlist\modele\Item::where('id', '=', $id)->first();
        if(is_null($item->util_id)){
            $url1 = $this->tabObjet[1]->pathFor('modifierItem', ['id' =>$id]);
            $url2 = $this->tabObjet[1]->pathFor('reserverItem', ['id' => $id]);
            $url3 = $this->tabObjet[1]->pathFor('supprimerItem', ['id' => $id]);
            $url4 = $this->tabObjet[1]->pathFor('supprimerImageItem', ['id' => $id]);

            $res = "Réservation : <a href=$url2>libre</a>";

            $liste = \wishlist\modele\Liste::where('no', '=', $item->liste_id)->first();
            if(is_null($liste->token_partage)){
                $res .= "<br><br><button onclick=\"self.location.href ='" . $url1 . "' \">Modifier l'item</button>";
                $res .= "<button onclick=\"self.location.href ='" . $url3 . "' \">Supprimer l'item</button>";
            }
            if($item->img != ""){
                $res .= "<button onclick=\"self.location.href ='" . $url4 . "' \">Supprimer l'image</button>";
            }
        } else {
            $idListe = \wishlist\modele\Item::where('id', '=', $id)->first()->liste_id;
            $date = \wishlist\modele\Liste::where('no', '=', $idListe)->first()->expiration;
            //créateur une seule liste
            if(isset($_COOKIE['createur']) && $_COOKIE['createur']==$idListe && strtotime($date) > time()){
                $res = "Réservation : <strong>réservé</strong>";
            } else {
                $nom = \wishlist\modele\Utilisateur::where('id', '=', $item->util_id)->first()->pseudo;
                $res = "Réservation : réservé par <strong>$nom</strong>";
                $mes = \wishlist\modele\Item::where('id', '=', $id)->first()->message;
                $res .= "</br> Message : $mes";
            }
        }
        return $res;
    }

    /**
     * méthode qui affiche le formulaire de réservation d'item
     * @param $id
     * @return string
     */
    public function reservationItem($id){
        $url = $this->tabObjet[0]->pathFor('reserverItem', ['id' => $id]);
        $res = <<<END
<section class="item">
<h1>Réservation de l'item $id</h1>
<form id="r1" method="post" action=$url>
    <label for="r1">Pseudo du participant : </label>
    <input type="text" id="r1" name="pseudo" placeholder="<pseudo>" required>
    <label for="c1">Message : </label>
    <input type="text" id="c1" name="message" placeholder="<message>">
    <button type="submit">Valider</button>
</form>
</section>
END;
        return $res;
    }

    public function itemReserve($id){
        $idListe = \wishlist\modele\Item::where('id', '=', $id)->first()->liste_id;
        $token = \wishlist\modele\Liste::where('no', '=', $idListe)->first()->token;

        $url = $this->tabObjet[0]->pathFor('getListeList', ['id' => $idListe, 'token' => $token]);

        $res = '<section class="item">';
        $res .= "<h1>Item n°$id réservé !</h1>";
        $res .= "<a href=$url>Retour</a>";
        $res .= '</section>';
        return $res;
    }

    /**
     * méthode qui affiche le formulaire de modification d'item
     * @param $id
     * @param $nom
     * @param $desc
     * @param $img
     * @param $lien
     * @param $prix
     * @return string
     */
    public function modifierItem($id, $nom, $desc, $img, $lien, $prix){
        $url = $this->tabObjet[0]->pathFor('modifierItem', ['id' => $id]);
        $res = <<<END
<section class="item">
<h1>Modification de l'item n°$id</h1>
<form id="r1" method="post" action=$url>
    <label for="c1">Nom : </label>
    <input type="text" id="c1" name="nom" placeholder="<nom>" value="$nom" required>
    
    <label for="c2">Description : </label>
    <input type="text" id="c2" name="description" placeholder="<description>" value="$desc" required>
    
    <label for="c3">Image : </label>
    <input type="text" id="c3" name="image" placeholder="<image>" value="$img">
    
    <label for="c4">URL : </label>
    <input type="url" id="c4" name="url" placeholder="<url>" value="$lien">
    
    <label for="c5">Prix : </label>
    <input type="text" id="c5" name="prix" placeholder="<prix>" value="$prix" required>
    <button type="submit">Valider</button>
</form>
</section>
END;
        return $res;
    }

    public function itemModifie($id){
        $idListe = \wishlist\modele\Item::where('id', '=', $id)->first()->liste_id;
        $token = \wishlist\modele\Liste::where('no', '=', $idListe)->first()->token;
        $url = $this->tabObjet[0]->pathFor('getListeList', ['id' => $idListe, 'token' => $token]);

        $res = '<section class="item">';
        $res .= "<h1>Informations de l'item n°$id modifiées</h1>";
        $res .= "<a href=$url>Retour</a>";
        $res .= '</section>';
        return $res;
    }

    public function itemSupprime($id, $token){
        $url = $this->tabObjet[0]->pathFor('getListeList', ['id' => $id, 'token' => $token]);

        $res = '<section class="item">';
        $res .= "<h1>Item n°$id supprimé</h1>";
        $res .= "<a href=$url>Retour</a>";
        $res .= '</section>';
        return $res;
    }

    public function imageItemSupprime($id, $token){
        $url = $this->tabObjet[0]->pathFor('getListeList', ['id' => $id, 'token' => $token]);

        $res = '<section class="item">';
        $res .= "<h1>Image de l'item n°$id supprimée</h1>";
        $res .= "<a href=$url>Retour</a>";
        $res .= '</section>';
        return $res;
    }
}