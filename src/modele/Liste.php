<?php

namespace wishlist\modele;

use Illuminate\Database\Eloquent\Model;

class Liste extends Model {
    protected $table = "liste";
    protected $primaryKey = "no";
    public $timestamps=false;

    public function items(){
        return $this->hasMany('\wishlist\modele\Item', 'liste_id');
    }
    //cle externe dans la table liee (Item)

    public static function ajouterListe($userId, $titre, $desc, $date, $token){
        $liste = new Liste();
        $liste->user_id=$userId;
        $liste->titre=$titre;
        $liste->description=$desc;
        $liste->expiration=$date;
        $liste->token=$token;
        $liste->visibilite='private';
        $liste->save();
    }

    public static function modifierListe($id, $titre, $desc, $date){
        $liste = Liste::where('no', '=', $id)->first();
        $liste->titre=$titre;
        $liste->description=$desc;
        $liste->expiration=$date;
        $liste->save();
    }

    public static function modifierToken($idListe){
        $liste = Liste::where('no', '=', $idListe)->first();
        $token = bin2hex(random_bytes(32));
        $liste->token = $token;
        $liste->save();
        return $token;
    }

    public static function rendrePublique($id){
        $liste = Liste::where('no', '=', $id)->first();
        if($liste->visibilite == 'private'){
            $liste->visibilite='public';
        } else {
            $liste->visibilite='private';
        }
        $liste->save();
    }
}