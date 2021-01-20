<?php

namespace wishlist\modele;

use Illuminate\Database\Eloquent\Model;

class MessageListe extends Model
{
    protected $table = 'messageliste';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function liste()
    {
        return $this->belongsTo('\wishlist\modele\Liste', 'liste_id');
    }
    //cle externe dans la table source (this)

    public static function ajouterMessage($idListe, $message){
        $msg = new MessageListe();
        $msg->liste_id=$idListe;
        $msg->message=$message;
        $msg->save();
    }
}