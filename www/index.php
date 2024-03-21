<?php
session_start();

$config = include ('../config.php');

error_reporting($config['debug']);
ini_set("display_errors", $config['debug']);

require_once ("include/fct.inc.php");
require_once ("include/class.pdogsb.inc.php");

include ("vues/v_entete.php");

$pdo = new PdoGsb(
  $config["host"],
  $config["database"],
  $config["user"],
  $config["password"]
);
$pdoo = new PDO("mysql:host=" . $config['host'] . "; dbname=" . $config['database'] . "", $config["user"], $config["password"]);
$pdoo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$request = "SELECT mdp, id FROM Visiteur ";
$stmt = $pdoo->prepare($request);
$stmt->execute();
$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

foreach ($result as $visiteur) {
  if (!str_contains($visiteur['mdp'], '$argon2i')) {
    try {
      $hash_mdp = password_hash($visiteur['mdp'], PASSWORD_ARGON2I);
      $sql = "UPDATE Visiteur SET mdp='$hash_mdp' WHERE id='" . $visiteur['id'] . "'";
      $stmt = $pdoo->prepare($sql);
      $stmt->execute();
      echo $stmt->rowCount() . " records UPDATED successfully";
    } catch (PDOException $e) {
      echo $sql . "<br>" . $e->getMessage();
    }
    echo $visiteur['mdp'] . "<br>" . PHP_EOL;
  }
  
}








$tabErreurs = array();
$estConnecte = estConnecte();
$uc = lireDonneeUrl('uc');
switch ($uc) {
  case 'connexion':
    include ("controleurs/c_connexion.php");
    break;

  case 'gererFrais':
    include ("controleurs/c_gererFrais.php");
    break;

  default:
    if ($estConnecte) {
      include ("controleurs/c_etatFrais.php");
    } else {
      include ("controleurs/c_connexion.php");
    }
    break;

}
include ("vues/v_finContenu.php");
include ("vues/v_pied.php");
