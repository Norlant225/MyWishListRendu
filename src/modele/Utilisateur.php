<?php

namespace wishlist\modele;

use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model {
    protected $table='utilisateur';
    protected $primaryKey='id';
    public $timestamps = false;

    public static function ajouterParticipant($pseudo){
        $existe = Utilisateur::where('pseudo', '=', $pseudo)->first();
        if(is_null($existe)){
            $util = new Utilisateur();
            $util->pseudo = $pseudo;
            $util->save();
        }
    }

    public static function ajouterUtilisateur($pseudo, $mdp){
        $hash=password_hash($mdp, PASSWORD_DEFAULT, ['cost'=> 12] );
        if(is_null(Utilisateur::where('pseudo', '=', $pseudo)->first())){
            //insertion
            $modele = new Utilisateur();
            $modele->pseudo=$pseudo;
            $modele->mdp=$hash;
            $modele->save();
        } else {
            //mise à jour
            $util = Utilisateur::where("pseudo", '=', $pseudo)->first();
            $util->mdp=$hash;
            $util->save();
        }
    }

    public static function connexion($pseudo, $mdp){
        return (!is_null(Utilisateur::where('pseudo', '=', $pseudo))
            && password_verify($mdp, Utilisateur::where('pseudo', '=', $pseudo)->first()->mdp));
    }

    public static function estAuthentifie(){
        session_start();
        $res=false;
        foreach (Utilisateur::select('id')->get() as $util){
            if(isset($_SESSION[$util->id])){
                $res=true;
                echo $util->id;
            }
        }
        return $res;
    }
}
