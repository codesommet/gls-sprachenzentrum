# Systeme d'Encaissement GLS

## Vue d'ensemble

Module de suivi des encaissements (recettes) pour tous les centres GLS. Permet d'importer, centraliser et analyser les paiements collectes aupres des eleves, avec support de deux formats CRM (ancien et nouveau).

---

## Objectif

- Centraliser les encaissements de **tous les centres** (Marrakech, Rabat, Casablanca, etc.)
- Importer les donnees historiques **2023-2024** (ancien CRM) et les donnees **2025+** (nouveau CRM)
- Calculer la **rentabilite** par centre (recettes - charges)
- Suivre la **performance des operateurs** (caissiers)
- Attribuer des **primes** aux employes selon les resultats

---

## Formats CRM Supportes

### Ancien CRM (2023-2024) — "Releve de guichet de recettes (CLOTURE)"

| Colonne | Description | Exemple |
|---|---|---|
| Journee | Date de la journee | 01/10/2025 |
| Caissier | Nom de l'operateur | Latifa Abouelfath |
| Guichet N° | Numero de lot journalier | 434 |
| N° | Numero de ligne sequentiel | 1, 2, 3... |
| Matricule | ID etudiant ancien CRM | 1669 |
| Montant | Montant en DH (espace = milliers) | 1 300 = 1300 DH |
| Mode de paiement | ESP / CR / VR | ESP = Especes |
| Classe | Groupe / classe | DRISS 19H A1 |
| Observations | Type de frais + mois | Frais annuel, 10 |
| Prenom et nom eleve | Nom de l'etudiant | DOUHA EL HAFDAOUI |
| Prenom et nom Payeur | Nom du payeur | EL HAFDAOUI DOUHA |
| Annee scolaire | Annee | 2025/2026 |

**Codes mode de paiement (ancien) :**
- `ESP` = Especes (cash)
- `CR` = Carte bancaire / TPE
- `VR` = Virement bancaire (suivi de N° reference)

**Parsing des observations :**
- `Frais annuel` = Frais d'inscription
- `10` = Paiement du mois d'Octobre
- `Frais annuel, 10` = Inscription + Octobre
- `09, 10` = Septembre + Octobre (paiement multiple)

### Nouveau CRM (2025+) — "Releve des Encaissements"

| Colonne | Description | Exemple |
|---|---|---|
| N° d'ordre | Numero sequentiel | 1, 2, 3... |
| Ref. | Reference unique (P-prefixe) | P1768 |
| Eleve / Payeur | Nom etudiant + payeur | HAJAR EL KIFA |
| Type | Type d'operation | Reglement |
| Montant | Montant en DH | 1300 Dh |
| Methode | Mode de paiement | Especes, TPE, Virement bancaire, Cheque |
| Frais | Description du paiement | Frais de Decembre |
| Date | Date de l'operation | 01/12/2025 |
| Operateur | Nom du caissier | mustapha |

---

## Methodes de Paiement (Normalisation)

| Ancien CRM | Nouveau CRM | Valeur BD |
|---|---|---|
| ESP | Especes | `especes` |
| CR | TPE | `tpe` |
| VR | Virement bancaire | `virement` |
| — | Cheque | `cheque` |

---

## Types de Frais (Normalisation)

| Categorie | Ancien CRM | Nouveau CRM | Valeur BD |
|---|---|---|---|
| Inscription A1/A2/B1 | Frais annuel (300 DH) | Frais d'inscription A1/A2/B1 | `inscription_a1` |
| Inscription B2 | Frais annuel (200 DH) | Frais d'inscription B2 | `inscription_b2` |
| Mensualite | Numero de mois (10, 11...) | Frais de [Mois] | `mensualite` |
| Examen OSD | examen OSD | — | `examen_osd` |

**Note :** Les etudiants peuvent payer en retard (mois passes) ou en avance (mois futurs). Le champ `fee_month` stocke le mois concerne, pas la date de paiement.

---

## Structure de la Table `encaissements`

```
encaissements
├── id                     (bigint, PK)
├── site_id                (FK -> sites) — centre GLS
├── reference              (string) — Matricule (ancien) ou Ref P-xxx (nouveau)
├── source_system          (enum: 'old_crm', 'new_crm', 'manual')
├── student_name           (string) — Nom de l'etudiant
├── payer_name             (string, nullable) — Nom du payeur si different
├── amount                 (decimal 10,2) — Montant en DH
├── payment_method         (enum: 'especes', 'tpe', 'virement', 'cheque')
├── fee_type               (enum: 'inscription_a1', 'inscription_b2', 'mensualite', 'examen_osd', 'autre')
├── fee_month              (date, nullable) — Mois concerne (ex: 2025-10-01 pour Octobre)
├── fee_description        (string) — Texte brut original
├── group_name             (string, nullable) — Nom du groupe/classe
├── school_year            (string) — Annee scolaire (ex: 2025/2026)
├── collected_at           (date) — Date de l'encaissement
├── operator_name          (string) — Nom du caissier
├── employee_id            (FK -> employees, nullable) — Lien employe si disponible
├── guichet_number         (integer, nullable) — N° guichet (ancien CRM seulement)
├── order_number           (integer, nullable) — N° d'ordre / N° ligne
├── notes                  (text, nullable) — Observations supplementaires
├── imported_at            (timestamp) — Date d'import
├── imported_by            (FK -> users, nullable) — Utilisateur ayant importe
├── created_at             (timestamp)
├── updated_at             (timestamp)
```

---

## Tables Complementaires (Rentabilite & Primes)

### `site_expenses` — Charges fixes par centre

```
site_expenses
├── id
├── site_id                (FK -> sites)
├── type                   (enum: 'loyer', 'electricite', 'eau', 'internet', 'fournitures', 'autre')
├── label                  (string) — Description
├── amount                 (decimal 10,2) — Montant mensuel
├── month                  (date) — Mois concerne
├── timestamps
```

### `primes` — Primes employes

```
primes
├── id
├── employee_id            (FK -> employees)
├── site_id                (FK -> sites)
├── amount                 (decimal 10,2)
├── month                  (date) — Periode de la prime
├── type                   (enum: 'performance', 'collection', 'assiduite', 'autre')
├── reason                 (text) — Justification
├── approved_by            (FK -> users, nullable)
├── approved_at            (timestamp, nullable)
├── timestamps
```

---

## Calcul de Rentabilite par Centre

```
Rentabilite = Recettes - Charges

Recettes (par mois, par centre) :
  = SUM(encaissements.amount) WHERE site_id = X AND collected_at BETWEEN debut AND fin

Charges (par mois, par centre) :
  = Salaires employes (employees.salary ou table dediee)
  + Paiements profs (presence_payment_summaries.total_payment)
  + Charges fixes (site_expenses.amount)
  + Primes (primes.amount)

Marge = Recettes - Charges
Taux de rentabilite = (Marge / Recettes) * 100
```

---

## Donnees Reelles — Exemple Decembre 2025 (GLS Marrakech)

### Totaux par methode de paiement
| Methode | Total |
|---|---|
| Especes | 372 600 Dh |
| TPE | 63 000 Dh |
| Virement bancaire | 14 100 Dh |
| Cheque | 1 300 Dh |
| **Total** | **451 000 Dh** |

### Totaux par type de frais
| Type de frais | Total |
|---|---|
| Frais de Decembre (mensualite) | 409 700 Dh |
| Frais d'inscription A1/A2/B1 | 15 200 Dh |
| Frais de Janvier (avance) | 9 900 Dh |
| Frais de Novembre (retard) | 9 200 Dh |
| Frais de Fevrier (avance) | 3 300 Dh |
| Frais d'inscription B2 | 3 200 Dh |
| Frais d'Octobre (retard) | 500 Dh |

### Statistiques
- **439 operations** sur la periode du 01/12/2025 au 30/12/2025
- **Operateur principal :** mustapha
- **82.6%** des paiements en especes

---

## Plan d'Implementation

### Phase 1 — Fondation
1. Migration `encaissements` table
2. Model `Encaissement` + relations
3. Migration `site_expenses` table
4. Migration `primes` table

### Phase 2 — Import des Donnees
5. Parseur ancien CRM (2023-2024) — import Excel/PDF
6. Parseur nouveau CRM (2025+) — import Excel/PDF
7. Interface d'import dans le backoffice

### Phase 3 — Backoffice & Rapports
8. CRUD encaissements (liste, filtres, saisie manuelle)
9. Dashboard encaissements par centre / operateur / periode
10. Totaux par methode de paiement et type de frais
11. Export PDF du releve (reproduction du format original)

### Phase 4 — Rentabilite
12. Dashboard rentabilite par centre
13. Comparaison multi-centres
14. Graphiques d'evolution mensuelle

### Phase 5 — Primes
15. CRUD primes employes
16. Regles de calcul automatique (objectifs de collection, assiduite)
17. Workflow de validation (approbation manager/directeur)

---

## Contexte Technique

- **Framework :** Laravel 11, PHP 8.2+
- **Base de donnees :** MySQL
- **Frontend :** Blade + Bootstrap 5 + Tailwind CSS
- **Existant :** Systeme de suivi des paiements etudiants (group_imports), presence (presence_imports), employes (employees + schedules) deja en place
