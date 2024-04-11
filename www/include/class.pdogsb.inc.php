<?php
/** 
 * Classe d'accès aux données. 
 * Utilise les services de la classe PDO pour l'application GSB
 * L'attribut $monPdo matérialise la connexion avec le serveur MySql 
 * @package default
 * @author Cheri Bibi
 */
class PdoGsb {   		
    private $monPdo;
    /**
     * Crée l'instance de PDO qui sera sollicitée
     * par toutes les méthodes de la classe
     */				
    public function __construct($serveur, $bdd, $user, $mdp){
        // crée la chaîne de connexion mentionnant le type de sgbdr, l'hôte et la base
        $chaineConnexion = 'mysql:host=' . $serveur . ';dbname=' . $bdd;
        // demande que le dialogue se fasee en utilisant l'encodage utf-8
        // et le mode de gestion des erreurs soit les exceptions
        $params = array (   PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", 
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        // crée une instance de PDO (connexion avec le serveur MySql) 
        $this->monPdo = new PDO($chaineConnexion, $user, $mdp, $params); 
    }
    /**
     * Ferme la connexion avec le serveur MySQL
     */				
    public function _destruct(){
        $this->monPdo = null;
    }
  /**
   * Retourne les informations d'un visiteur
   
   * @param string  login 
   * @param string  mot de passe
   * @return array  l'id, le nom et le prénom sous la forme d'un tableau associatif 
  */
    public function getInfosVisiteur($login, $mdp){
  	    $req = "select id, nom, prenom from Visiteur where login = ? and mdp = ?";
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->bindValue(1, $login);
        $cmd->bindValue(2, $mdp);
        $cmd->execute();
  	    $ligne = $cmd->fetch();
  	    return $ligne;
    } 


    public function getInfosVisiteurHash($login){
      $req = "select mdp from Visiteur where login = ?";
      $cmd = $this->monPdo->prepare($req);
      $cmd->bindValue(1, $login);
      $cmd->execute();
      $ligne = $cmd->fetch();
      return $ligne;
  } 


  /**
   * Retourne sous forme d'un tableau associatif toutes les lignes de frais hors forfait
   * concernées par les deux arguments   
   * La boucle foreach ne peut être utilisée ici car on procède
   * à une modification de la structure itérée - transformation du champ date-   
   * @param $idVisiteur 
   * @param $mois sous la forme aaaamm
   * @return tous les champs des lignes de frais hors forfait sous la forme d'un tableau associatif 
  */
    public function getLesFraisHorsForfait($idVisiteur,$mois){      
        $req = "select * from LigneFraisHorsForfait where idVisiteur = ? and mois = ?" ;
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->bindValue(1, $idVisiteur);
        $cmd->bindValue(2, $mois);
        $cmd->execute();
  	    $lesLignes = $cmd->fetchAll();
        $cmd->closeCursor();
  	    $nbLignes = count($lesLignes);
  	    for ($i=0; $i<$nbLignes; $i++){
            $date = $lesLignes[$i]['date'];
            $lesLignes[$i]['date'] =  dateAnglaisVersFrancais($date);
  	    }
  	    return $lesLignes; 
    }
  /**
   * Retourne le nombre de justificatifs d'un visiteur pour un mois donné
   
   * @param $idVisiteur 
   * @param $mois sous la forme aaaamm
   * @return le nombre entier de justificatifs 
  */
    public function getNbjustificatifs($idVisiteur, $mois){
  	    $req = "select nbJustificatifs as nb from FicheFrais "
                . "where idVisiteur = ? and mois = ?";
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->bindValue(1, $idVisiteur);
        $cmd->bindValue(2, $mois);
        $cmd->execute();
  	    $laLigne = $cmd->fetch();
  	    return $laLigne['nb'];
    }
  /**
   * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
   * concernées par les deux arguments
   
   * @param $idVisiteur 
   * @param $mois sous la forme aaaamm
   * @return l'id, le libelle et la quantité sous la forme d'un tableau associatif 
  */
    public function getLesFraisForfait($idVisiteur, $mois){
  	    $req = "select F.id as idFrais, F.libelle as libelle, L.quantite as quantite 
                from LigneFraisForfait  L 
                inner join FraisForfait  F on F.id = L.idFraisForfait 
  		        where L.idVisiteur = ? and L.mois= ?  
  		        order by L.idFraisForfait";	
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->bindValue(1, $idVisiteur);
        $cmd->bindValue(2, $mois);
        $cmd->execute();
  	    $lesLignes = $cmd->fetchAll();
        $cmd->closeCursor();
  	    return $lesLignes; 
    }
  /**
   * Retourne tous les id de la table FraisForfait
   
   * @return un tableau associatif 
  */
    public function getLesIdFrais(){
  	    $req = "select id as idFrais from FraisForfait order by id";
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->execute();
  	    $lesLignes = $cmd->fetchAll();
        $cmd->closeCursor();
  	    return $lesLignes;
    }
  /**
   * Met à jour la table ligneFraisForfait pour un visiteur et
   * un mois donné en enregistrant les nouveaux montants
   
   * @param $idVisiteur 
   * @param $mois sous la forme aaaamm
   * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
   * @return void 
  */
    public function majFraisForfait($idVisiteur, $mois, $lesFrais){
  	    $lesCles = array_keys($lesFrais);
        $req = "update LigneFraisForfait set quantite = :qte
                 where idVisiteur = :idVisiteur
                 and mois = :mois
                 and idFraisForfait = :unIdFrais";
        $cmd = $this->monPdo->prepare($req);
  	    foreach($lesCles as $unIdFrais){
            $qte = $lesFrais[$unIdFrais];
            $cmd->bindValue("qte", $qte, PDO::PARAM_INT);
            $cmd->bindValue("idVisiteur", $idVisiteur);
            $cmd->bindValue("mois", $mois);
            $cmd->bindValue("unIdFrais", $unIdFrais);
            $cmd->execute();
        }		
    }
  /**
   * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
   
   * @param $idVisiteur 
   * @param $mois sous la forme aaaamm
   * @return vrai ou faux 
  */	
    public function estPremierFraisMois($idVisiteur,$mois) {
  	    $ok = false;      
  	    $req = "select count(*) as nbLignesFrais from FicheFrais 
  		        where idVisiteur = ? and mois = ?";
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->bindValue(1, $idVisiteur);
        $cmd->bindValue(2, $mois);
        $cmd->execute();
  	    $laLigne = $cmd->fetch();
        $cmd->closeCursor();
  	    if ($laLigne['nbLignesFrais'] == 0){
            $ok = true;
  	    }
  	    return $ok;
    }
  /**
   * Retourne le dernier mois en cours d'un visiteur  
   * @param string  idVisiteur 
   * @return string le mois sous la forme aaaamm
  */	
    public function dernierMoisSaisi($idVisiteur){
  	    $req = "select max(mois) as dernierMois from FicheFrais 
                where idVisiteur = ?";
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->bindValue(1, $idVisiteur);
        $cmd->execute();

  	    $laLigne = $cmd->fetch();
        $cmd->closeCursor();
  	    $dernierMois = $laLigne['dernierMois'];
  	    return $dernierMois;
    }  	
  /**
   * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés
   
   * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
   * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
   * @param string idVisiteur 
   * @param string mois sous la forme aaaamm
   * @return void rien
  */
    public function creeNouvellesLignesFrais($idVisiteur,$mois){
        $dernierMois = $this->dernierMoisSaisi($idVisiteur);
  	    $laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur,$dernierMois);
  	    if ($laDerniereFiche['idEtat']=='CR'){
            $this->majEtatFicheFrais($idVisiteur, $dernierMois, 'CL');			
        }        
        // préparation et exécution de la requête d'insertion de la fiche de frais
  	    $req = "insert into FicheFrais(idVisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
  		        values(?,?,0,0,now(),'CR')";
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->bindValue(1, $idVisiteur);
        $cmd->bindValue(2, $mois);
        $cmd->execute();
  	    $lesIdFrais = $this->getLesIdFrais();        
        // préparation de la requête pour insérer les lignes de frais forfait associées à la fiche de frais
        $req = "insert into LigneFraisForfait(idVisiteur,mois,idFraisForfait,quantite) 
                    values(:idVisiteur,:mois,:unIdFrais,0)";
        $cmd = $this->monPdo->prepare($req);         
        // création de lignes frais forfait autant de fois que de types de frais forfait
  	    foreach($lesIdFrais as $uneLigneIdFrais){
            $unIdFrais = $uneLigneIdFrais['idFrais'];
            $cmd->bindValue("idVisiteur", $idVisiteur);
            $cmd->bindValue("mois", $mois);
            $cmd->bindValue("unIdFrais", $unIdFrais);
            $cmd->execute();            
  	    }
    }
  /**
   * Crée un nouveau frais hors forfait pour un visiteur un mois donné
   * à partir des informations fournies en paramètre   
   * @param string  idVisiteur 
   * @param string  mois sous la forme aaaamm
   * @param string  libelle : le libelle du frais
   * @param string  la date du frais au format français jj//mm/aaaa
   * @param string  le montant
   * @return void rien
  */
    public function creeNouveauFraisHorsForfait($idVisiteur,$mois,$libelle,$date,$montant){      
  	    $dateEn = dateFrancaisVersAnglais($date);
  	    $req = "insert into LigneFraisHorsForfait 
  		        values(null,'" . $idVisiteur . "','" . $mois ."','" . 
        $libelle . "','" . $dateEn . "'," . $montant . ")";
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->execute();
    }
  /**
   * Supprime le frais hors forfait dont l'id est passé en argument
   
   * @param string  idFrais 
   * @return void rien
  */
    public function supprimerFraisHorsForfait($idFrais){
        $req = "delete from lignefraishorsforfait where id = ?" ;
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->bindValue(1, $idFrais);
        $cmd->execute();
    }
  /**
   * Retourne les mois pour lesquel un visiteur a une fiche de frais
   
   * @param string idVisiteur 
   * @return array un tableau associatif de clé un mois -aaaamm- et de valeurs l'année et le mois correspondant 
  */
    public function getLesMoisDisponibles($idVisiteur){
  	    $req = "select  mois from  FicheFrais 
              where idVisiteur = ? order by mois desc ";
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->bindValue(1, $idVisiteur);
        $cmd->execute();
        
  	    $lesMois =array();
  	    $laLigne = $cmd->fetch();
  	    while($laLigne != null)	{
            $mois = $laLigne['mois'];
            $numAnnee =substr($mois,0,4);
            $numMois =substr($mois,4,2);
            $lesMois[$mois]=
                array("mois"=> $mois, "numAnnee" => $numAnnee, "numMois" => $numMois);
            $laLigne = $cmd->fetch(); 		
  	    }
        $cmd->closeCursor();
  	    return $lesMois;
    }
  /**
   * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
   
   * @param string  idVisiteur 
   * @param string  mois sous la forme aaaamm
   * @return array  un tableau avec des champs de jointure entre une fiche de frais et la ligne d'état 
  */	
    public function getLesInfosFicheFrais($idVisiteur,$mois){
  	    $req = "select idEtat, dateModif, nbJustificatifs, montantValide, 
                Etat.libelle as libEtat 
                from  FicheFrais 
                inner join Etat on FicheFrais.idEtat = Etat.id 
  		        where idVisiteur = ? and mois = ?";
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->bindValue(1, $idVisiteur);
        $cmd->bindValue(2, $mois);
        $cmd->execute();
        
  	    $laLigne = $cmd->fetch();
        $cmd->closeCursor();
  	    return $laLigne;
    }
  /**
   * Modifie l'état et la date de modification d'une fiche de frais
   
   * Modifie le champ idEtat et met la date de modif à aujourd'hui
   * @param string  idVisiteur 
   * @param string  mois sous la forme aaaamm
   */   
    public function majEtatFicheFrais($idVisiteur,$mois,$etat){
  	    $req = "update FicheFrais 
                set idEtat = :etat, dateModif = now() 
  		        where idVisiteur = :idVisiteur and mois = :mois";
  	    $cmd = $this->monPdo->prepare($req);
        $cmd->bindValue("etat", $etat);
        $cmd->bindValue("idVisiteur", $idVisiteur);
        $cmd->bindValue("mois", $mois);
        $cmd->execute();        
    }
}
