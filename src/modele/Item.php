<?php

namespace wishlist\modele;

use Illuminate\Database\Eloquent\Model;

class Item extends Model {
    protected $table='item';
    protected $primaryKey='id';
    public $timestamps = false;

    public function liste(){
        return $this->belongsTo('\wishlist\modele\Liste', 'liste_id');
    }
    //cle externe dans la table source (this)

    public static function ajouterItem($listeId, $nom, $desc, $prix, $url){
        $item = new Item();
        $item->liste_id=$listeId;
        $item->nom=$nom;
        $item->descr=$desc;
        $item->url=$url;
        $item->tarif=$prix;
        $item->save();
    }

    public static function ajouterItemListe($idItem, $idListe){
        $item = Item::where('id', '=', $idItem)->first();
        $item->liste_id=$idListe;
        $item->save();
    }

    public static function modifierItem($id, $nom, $desc, $img, $prix, $url){
        $item = Item::where('id', '=', $id)->first();
        $item->nom=$nom;
        $item->descr=$desc;
        $item->img=$img;
        $item->url=$url;
        $item->tarif=$prix;
        $item->save();
    }
}
