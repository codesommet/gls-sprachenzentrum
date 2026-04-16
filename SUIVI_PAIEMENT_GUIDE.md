# Suivi Paiement — Guide complet

## C'est quoi ?

Le module **Suivi Paiement** permet de suivre les paiements des étudiants par groupe et par mois, en important les fichiers Excel du CRM. Il répond aux questions :

- Combien d'étudiants ont commencé avec le prof ?
- Combien ont été ajoutés après ?
- Combien ont arrêté de payer ?
- Combien sont revenus ?
- Combien ont été annulés ou archivés ?
- Quel est le bénéfice net par mois ?

---

## Comment ça marche ?

### Etape 1 : Importer un fichier Excel du CRM

1. Aller dans **Suivi Paiement > Importer CRM**
2. Sélectionner un **groupe** (ex: Herr Abdollah 10H — B2)
3. Le **mois de début** et le **taux par étudiant** se remplissent automatiquement
4. Télécharger le fichier Excel du CRM
5. Cliquer sur **Importer**

Le système lit automatiquement le fichier Excel :
- Détecte les colonnes de mois (Frais de Septembre, Frais d'Octobre, etc.)
- Détecte les colonnes de frais (Inscription A1/A2, Inscription B2, etc.)
- Lit les montants de chaque étudiant
- Lit les **couleurs** des cellules (vert = payé, rouge = annulé, gris = archivé)

### Etape 2 : Le système classe chaque étudiant

En se basant sur la couleur de la ligne dans Excel :

| Couleur Excel | Statut dans le système |
|---|---|
| Pas de couleur / Vert | **Actif** |
| Rouge / Rose | **Annulé** |
| Gris | **Archivé** |

Vous pouvez changer le statut manuellement avec le menu déroulant sur chaque ligne.

### Etape 3 : Le système analyse le cycle de vie

Pour chaque étudiant et chaque mois, le système calcule un **statut de cycle de vie** basé sur les paiements :

| Statut | Condition | Exemple |
|---|---|---|
| **Initial** | Premier paiement = mois de début du groupe | L'étudiant a payé dès Septembre (mois de début) |
| **Nouveau** | Premier paiement après le mois de début | L'étudiant a commencé à payer en Novembre |
| **Actif** | Continue de payer (pas le premier mois) | L'étudiant paie en Octobre après avoir payé en Septembre |
| **Perdu** | Payait avant, ne paie plus ce mois | L'étudiant a payé Sep-Nov puis 0 en Décembre |
| **Retourné** | A arrêté puis recommencé à payer | L'étudiant a payé Sep-Nov, rien en Dec, puis paie en Jan |
| **Annulé** | Statut annulé + ne paie pas | Ligne rouge dans Excel, pas de paiement |
| **Archivé** | Statut archivé + ne paie pas | Ligne grise dans Excel, pas de paiement |
| **Inactif** | N'a jamais payé | Aucun montant dans aucun mois |

### Règle importante

Un étudiant **annulé** ou **archivé** qui a quand même payé certains mois garde son cycle de vie normal pour ces mois-là :

```
Exemple : Etudiant annulé (ligne rouge) mais a payé Sep et Oct

Sep → Initial (il a payé)
Oct → Actif (il a payé)
Nov → Annulé (il n'a pas payé + statut annulé)
Dec → Annulé
...
```

---

## Les pages du module

### 1. Tableau de bord (`/backoffice/payroll`)

Liste tous les groupes qui ont des imports. Pour chaque groupe :
- Nombre de versions importées
- Date du dernier import
- Taux par étudiant
- Boutons : Historique, Analyse, Etudiants

### 2. Importer CRM (`/backoffice/payroll/import/create`)

Formulaire d'import. Champs :
- **Groupe** : le groupe cible
- **Mois de début** : rempli automatiquement depuis la date de début du groupe
- **Taux par étudiant** : rempli automatiquement depuis le dernier import (ex: 500 DH)
- **Fichier Excel** : le fichier CRM à importer
- **Notes** : optionnel

### 3. Détails import (`/backoffice/payroll/group/{id}/import/{id}`)

Affiche le tableau complet des étudiants avec :
- Toutes les colonnes du fichier Excel dans le même ordre
- Les **couleurs des cellules** identiques à l'Excel (vert/rouge/gris)
- Le statut modifiable par étudiant (Actif / Annulé / Archivé)
- La ligne **Total** en bas avec les sommes par colonne

### 4. Analyse mensuelle (`/backoffice/payroll/group/{id}/analysis`)

Tableau résumé mois par mois :

| Ligne | Description |
|---|---|
| Initiaux | Nombre d'étudiants qui ont commencé dès le 1er mois |
| Nouveaux | Nombre d'étudiants ajoutés ce mois |
| Actifs | Nombre d'étudiants qui continuent de payer |
| Perdus | Nombre d'étudiants qui ont arrêté |
| Retournés | Nombre d'étudiants revenus après un arrêt |
| Annulés | Nombre d'étudiants annulés ce mois |
| Archivés | Nombre d'étudiants archivés ce mois |
| **Total payant** | Initiaux + Nouveaux + Actifs + Retournés |
| **Total montant** | Somme des paiements de tous les étudiants |
| **Paiement enseignant** | Total payant x Taux par étudiant |
| **Bénéfice net** | Total montant - Paiement enseignant |

Bouton **Recalculer** : recalcule l'analyse sans réimporter le fichier.

### 5. Comparaison (`/backoffice/payroll/group/{id}/import/{id}/compare`)

Compare deux versions d'import pour le même groupe. Deux sections :

**Section 1 — Mouvement des étudiants :**
- Cartes résumé : Initiaux, Ajoutés, Perdus, Retournés, Annulés, Archivés (comptés en étudiants uniques)
- Tableau mensuel complet avec bénéfice

**Section 2 — Différences entre fichiers :**
- Etudiants ajoutés dans le nouveau fichier
- Etudiants retirés du nouveau fichier
- Paiements modifiés (montant changé)
- Statuts changés (Actif → Annulé, etc.)

### 6. Suivi étudiants (`/backoffice/payroll/group/{id}/students`)

Timeline de chaque étudiant avec le statut par mois, coloré visuellement.

---

## Le calcul du bénéfice

```
Pour chaque mois :

  Total montant     = somme de tous les paiements étudiants ce mois
  Paiement prof     = nombre d'étudiants payants x taux par étudiant
  Bénéfice net      = Total montant - Paiement prof
```

**Exemple concret :**

```
Mois : Septembre 2025
Etudiants payants : 16
Montant total reçu : 20,600.00 DH
Taux prof : 500 DH/étudiant
Paiement prof : 16 x 500 = 8,000.00 DH
Bénéfice : 20,600 - 8,000 = 12,600.00 DH
```

Le taux par étudiant est défini :
- Soit sur la fiche de l'enseignant (taux par défaut)
- Soit sur chaque import (peut changer selon la performance du prof : 300 ou 500 DH)

---

## Le versioning (versions d'import)

Chaque import crée une **nouvelle version** (v1, v2, v3...) pour le même groupe.

- **v1** : premier import du fichier CRM
- **v2** : import du mois suivant (nouveau fichier CRM)
- **v3** : etc.

Les anciennes versions ne sont **jamais écrasées**. Vous pouvez toujours :
- Voir les détails de chaque version
- Comparer v2 avec v3 pour voir ce qui a changé
- Revenir en arrière

---

## Résumé rapide

```
Fichier Excel CRM
       ↓
  [Import dans le système]
       ↓
  Lecture automatique :
  - Colonnes de mois
  - Colonnes de frais
  - Montants
  - Couleurs (rouge/gris/vert)
       ↓
  Classification des étudiants :
  - Actif / Annulé / Archivé (depuis les couleurs)
       ↓
  Analyse du cycle de vie :
  - Initial / Nouveau / Actif / Perdu / Retourné
  (basé sur les paiements mois par mois)
       ↓
  Calcul financier :
  - Total reçu par mois
  - Paiement prof par mois
  - Bénéfice net par mois
```
