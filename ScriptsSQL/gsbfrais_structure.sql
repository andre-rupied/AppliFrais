--
-- Base de données: gsbfrais
-- Nom de table commence par une maj., puis min., puis maj. à chaque début de mot dans le nom
-- Nom de colonne commence par une min., puis min., puis maj. à chaque début de mot dans le nom
-- L'option ENGINE=InnoDB en fin de création de table a été supprimée, 
-- cette option étant celle par défaut à partir de MySql 5
--
 use gsbfrais;
-- --------------------------------------------------------

--
-- Structure de la table FraisForfait
--

CREATE TABLE FraisForfait (
  id char(3) NOT NULL,
  libelle char(20) DEFAULT NULL,
  montant decimal(5,2) DEFAULT NULL,
  CONSTRAINT pkFraisForfait PRIMARY KEY (id)
);

-- --------------------------------------------------------

--
-- Structure de la table Etat
--

CREATE TABLE  Etat (
  id char(2) NOT NULL,
  libelle varchar(30) DEFAULT NULL,
  CONSTRAINT pkEtat PRIMARY KEY (id)
);

-- --------------------------------------------------------

--
-- Structure de la table visiteur
--

CREATE TABLE  Visiteur (
  id char(4) NOT NULL,
  nom char(30) DEFAULT NULL,
  prenom char(30)  DEFAULT NULL, 
  login char(20) DEFAULT NULL,
  mdp char(255) DEFAULT NULL,
  adresse char(30) DEFAULT NULL,
  cp char(5) DEFAULT NULL,
  ville char(30) DEFAULT NULL,
  dateEmbauche date DEFAULT NULL,
  CONSTRAINT pkVisiteur PRIMARY KEY (id)
);


-- --------------------------------------------------------

--
-- Structure de la table fichefrais
--

CREATE TABLE  FicheFrais (
  idVisiteur char(4) NOT NULL,
  mois char(6) NOT NULL,
  nbJustificatifs int(11) DEFAULT NULL,
  montantValide decimal(10,2) DEFAULT NULL,
  dateModif date DEFAULT NULL,
  idEtat char(2) DEFAULT 'CR',
  CONSTRAINT pkFicheFrais PRIMARY KEY (idVisiteur, mois)
);


-- --------------------------------------------------------

--
-- Structure de la table lignefraisforfait
--

CREATE TABLE LigneFraisForfait (
  idVisiteur char(4) NOT NULL,
  mois char(6) NOT NULL,
  idFraisForfait char(3) NOT NULL,
  quantite int(11) DEFAULT NULL,
  constraint pkLigneFraisForfait PRIMARY KEY (idVisiteur, mois, idFraisForfait)
);

-- --------------------------------------------------------

--
-- Structure de la table lignefraishorsforfait
--

CREATE TABLE  LigneFraisHorsForfait (
  id int(11) NOT NULL auto_increment,
  idVisiteur char(4) NOT NULL,
  mois char(6) NOT NULL,
  libelle varchar(100) DEFAULT NULL,
  date date DEFAULT NULL,
  montant decimal(10,2) DEFAULT NULL,
  constraint pkLigneFraisHorsForfait PRIMARY KEY (id)
);

--
-- Déclaration des contraintes d'intégrité référentielle
--



ALTER TABLE FicheFrais add 
CONSTRAINT fk1FicheFrais FOREIGN KEY (idVisiteur) references Visiteur(id);
ALTER TABLE FicheFrais add 
CONSTRAINT fk2FicheFrais FOREIGN KEY (idEtat) references Etat(id);


ALTER TABLE LigneFraisForfait add 
CONSTRAINT fk1LigneFraisForfait FOREIGN KEY (idVisiteur, mois) REFERENCES FicheFrais(idVisiteur, mois);
ALTER TABLE LigneFraisForfait add 
CONSTRAINT fk2LigneFraisForfait FOREIGN KEY (idFraisForfait) references FraisForfait(id);

ALTER TABLE LigneFraisHorsForfait add 
CONSTRAINT fkLigneFraisHorsForfait FOREIGN KEY (idVisiteur, mois) REFERENCES FicheFrais(idVisiteur, mois);
