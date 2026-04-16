# GLS Professor Payment System

## Overview

This document specifies the **professor payment calculation system** at GLS Sprachenzentrum. It covers:

1. The **manual method** (current paper-based process done by responsables)
2. The **attendance sheet format** (paper + digital)
3. The **automation design** (how the CRM will calculate payments automatically)

The system is based on:

> **Number of paying students + how much of the course each student completed (pro-rata)**

---

## Part 1: Manual Method (Current Process)

### Core Idea

The professor is paid per student, but each student is **weighted** based on their participation during the month.

Each student is classified into one of 5 categories:

| Category | Fraction | Meaning |
|----------|----------|---------|
| Full | 1.00 | Student attended the full month (~4 weeks) |
| Three-Quarter (3/4) | 0.75 | Student attended ~3 weeks |
| Half (1/2) | 0.50 | Student attended ~2 weeks |
| Quarter (1/4) | 0.25 | Student attended ~1 week |
| Zero (0) | 0.00 | Student did not attend / did not pay |

### Base Price per Student

The base price depends on the **group level**:

| Level | Base Price |
|-------|-----------|
| A1 | 500 DH |
| B1 | 550 DH |

> **Note:** Other levels (A2, B2, C1, etc.) may have different prices. The base price is stored as `payment_per_student` on the Teacher model, with optional override per GroupImport.

### Weighted Amount per Student

The amount contributed by each student = `floor(base_price x fraction)`

**For 550 DH groups (B1):**

| Category | Calculation | Amount |
|----------|------------|--------|
| Full | floor(550 x 1.00) | 550 DH |
| 3/4 | floor(550 x 0.75) | 412 DH |
| 1/2 | floor(550 x 0.50) | 275 DH |
| 1/4 | floor(550 x 0.25) | 137 DH |
| 0 | - | 0 DH |

**For 500 DH groups (A1):**

| Category | Calculation | Amount |
|----------|------------|--------|
| Full | floor(500 x 1.00) | 500 DH |
| 3/4 | floor(500 x 0.75) | 375 DH |
| 1/2 | floor(500 x 0.50) | 250 DH |
| 1/4 | floor(500 x 0.25) | 125 DH |
| 0 | - | 0 DH |

### Calculation Steps

1. **Count students** by category (Full, 3/4, 1/2, 1/4, 0)
2. **Multiply** each count by the weighted amount
3. **Sum** all results = professor payment for that group for that month

---

## Part 2: Attendance Sheet Format

### Paper Sheets (used by responsables at each center)

From the Kenitra center photos, each paper sheet contains:

**Header:**
- GLS logo
- Teacher name (e.g., "HERR BERADA", "HERR OUAHIM", "HERR LAAZIZ")
- Center name (e.g., "German Language Center Kenitra")
- Date range (e.g., "09/02 - 06/03" = one month)
- Level and time slot (e.g., "A1 - 16h00", "B1 - 18h30")

**Body (attendance grid):**
- Rows: numbered students with NOM/PRENOM
- Columns: days organized as MO/DI/MI/DO/FR (Monday through Friday)
- Sub-header row shows actual dates (e.g., 9, 10, 11, 12, 13, 16, 17...)
- Cell values:
  - Checkmark (tick) = Present
  - X (often in red ink) = Absent
  - Empty = no class that day or student not yet enrolled

**Footer (manual calculation):**
- The responsable writes the payment calculation at the bottom
- Example: "14 x 500 = 7,000 / 2 x 375 = 750 / Total = 7,750 DH"
- Sometimes includes a signature and date of approval

### Digital Spreadsheet Format (Excel/Google Sheets)

The first image shows the digital version used in some centers:

**Structure:**
- Column "Etudiant" = student name
- Numbered date columns (1-22 etc.) with actual dates as sub-headers
- Cell values: **P** (Present, green background) or **Q** (absent, red background) or blank
- Row colors indicate status:
  - Green name = active student
  - Red name / red row = inactive/cancelled student
  - Yellow name = special status

This is the format that will be imported into the CRM.

---

## Part 3: Classification Algorithm

### How Attendance Maps to Category

The month is divided into **4 quarters** (approximately 1 week each):

| Quarter | Approximate Days | Week |
|---------|-----------------|------|
| Q1 | Days 1-5 | Week 1 |
| Q2 | Days 6-10 | Week 2 |
| Q3 | Days 11-15 | Week 3 |
| Q4 | Days 16-20+ | Week 4 |

**Classification rule:**
- A student is considered **"active" in a quarter** if they have at least 1 presence mark (P or checkmark) during that week
- Count the number of active quarters:
  - 4 active quarters = **Full**
  - 3 active quarters = **Three-Quarter**
  - 2 active quarters = **Half**
  - 1 active quarter = **Quarter**
  - 0 active quarters = **Zero**

**Important notes:**
- Individual daily attendance marks are NOT counted one by one
- They are used to determine which quarters the student was active
- A student can have absences (X) within an active quarter — what matters is whether they had ANY presence in that quarter
- Students marked as cancelled/inactive (red rows) are automatically classified as **Zero**

### Edge Cases

- **Student joins mid-month:** Only count quarters from their enrollment date
- **Student leaves mid-month:** Only count quarters until their last attendance
- **No attendance data but student paid:** Responsable can manually override the category
- **Override capability:** The system should allow manual adjustment of any student's category after auto-calculation

---

## Part 4: CRM Automation Design

### Data Flow

```
Paper attendance sheet
        |
        v
Digital spreadsheet (Excel/Google Sheets)
        |
        v
Import into CRM (like suivi paiement import)
        |
        v
Parse presence data (P/Q per student per day)
        |
        v
Auto-classify students (Full/3-4/1-2/1-4/0)
        |
        v
Calculate professor payment
        |
        v
Display results + allow manual adjustments
```

### Import Process (mirrors existing GroupImport pattern)

The presence import follows the same architecture as the existing `CrmGroupImportService`:

1. **Upload:** Responsable uploads an Excel file containing the attendance sheet
2. **Parse:** System auto-detects:
   - Header row with dates
   - Student name column
   - Date columns (from actual dates or day numbers)
   - Presence values per cell (P/Q/checkmark/X/blank)
3. **Store:** Create versioned import snapshot with:
   - Each student's daily presence records
   - Auto-detected category per student
   - Calculated payment per student
4. **Calculate:** Apply the payment formula automatically
5. **Review:** Display results for responsable to review and adjust

### Presence Excel Parser

Needs to handle both formats:

**Format A — Date-based headers (digital spreadsheet):**
```
| Etudiant | 02/03/2026 | 03/03/2026 | 04/03/2026 | ... |
|----------|-----------|-----------|-----------|-----|
| Student1 | P         | P         | Q         | ... |
```

**Format B — Day-number headers with MO/DI/MI/DO/FR (paper scan):**
```
| NOM/PRENOM | MO | DI | MI | DO | FR | MO | DI | ... |
|            | 9  | 10 | 11 | 12 | 13 | 16 | 17 | ... |
| Student1   | V  | V  | V  | V  | V  | V  | X  | ... |
```

**Detection keywords for student column:** etudiant, student, nom, prenom, stagiaire, eleve
**Detection keywords for date columns:** dates (dd/mm/yyyy), day numbers, MO/DI/MI/DO/FR headers
**Presence values:** P, V, checkmark (tick), 1 = Present | Q, X, A, 0 = Absent | blank = no data

### Payment Calculation Engine

```php
// For each student in the import:
$activeQuarters = countActiveQuarters($studentPresenceData, $totalWeeks);
$fraction = $activeQuarters / $totalQuarters; // 0.00, 0.25, 0.50, 0.75, 1.00
$studentAmount = floor($basePrice * $fraction);

// Professor total for this group:
$totalPayment = sum of all $studentAmount values
```

### Integration with Existing Payroll Module

The professor payment feature will live alongside the existing payroll module:

**Existing (suivi paiement = student payment tracking):**
- `GroupImport` → tracks what students PAY to GLS (monthly fees)
- Direction: Student → GLS
- Excel contains: student names + monthly payment amounts

**New (prof payment = professor salary calculation):**
- `PresenceImport` → tracks student attendance, calculates what GLS PAYS to the professor
- Direction: GLS → Professor
- Excel contains: student names + daily presence marks (P/Q)

Both systems share the same Group/Teacher models but track different data flows.

---

## Part 5: Data Model (New Tables)

### presence_imports

Versioned attendance import snapshots (mirrors `group_imports` pattern):

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| group_id | FK → groups | Target group |
| version | int | Auto-incremented per group |
| month | date | The month this attendance covers |
| date_start | date | First day of attendance period |
| date_end | date | Last day of attendance period |
| total_days | int | Total class days in the period |
| payment_per_student | decimal(10,2) nullable | Override base price (defaults to teacher's rate) |
| file_name | string | Original file name |
| file_path | string | Stored file path |
| notes | text nullable | Import notes |
| imported_by | FK → users nullable | Who imported |
| created_at, updated_at | timestamps | |

### presence_import_students

One row per student per import:

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| presence_import_id | FK → presence_imports | |
| row_number | int | Excel row number |
| student_name | string | |
| total_present | int | Count of present days |
| total_absent | int | Count of absent days |
| active_quarters | int | Number of active quarters (0-4) |
| category | enum | full, three_quarter, half, quarter, zero |
| category_override | enum nullable | Manual override by responsable |
| weighted_amount | decimal(10,2) | Calculated payment amount for this student |
| status | enum | active, cancelled, transferred, unknown |
| row_color | string(7) nullable | Hex color from Excel row |
| raw_data | JSON nullable | Raw Excel row data |
| created_at, updated_at | timestamps | |

### presence_records

Daily presence data per student:

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| presence_import_student_id | FK → presence_import_students | |
| date | date | The class day |
| status | enum | present, absent, no_data |
| raw_value | string nullable | Original cell value (P, Q, V, X, etc.) |
| created_at, updated_at | timestamps | |

### presence_payment_summaries

Final calculation per import (one row per import = one month for one group):

| Column | Type | Description |
|--------|------|-------------|
| id | bigint PK | |
| presence_import_id | FK → presence_imports | |
| base_price | decimal(10,2) | Base price per student used |
| count_full | int | Students at Full |
| count_three_quarter | int | Students at 3/4 |
| count_half | int | Students at 1/2 |
| count_quarter | int | Students at 1/4 |
| count_zero | int | Students at 0 |
| total_students | int | Total active students |
| total_payment | decimal(10,2) | Final professor payment |
| approved_by | FK → users nullable | Who approved the payment |
| approved_at | timestamp nullable | When approved |
| created_at, updated_at | timestamps | |

---

## Part 6: Real Examples (from Kenitra Center)

### Example 1: Herr Ouahim - B1 - 10h00

```
25 x 550 = 13,750
 3 x 412 =  1,236
─────────────────
Total    = 14,986 DH
```
- 25 full students, 3 three-quarter

### Example 2: Herr Ouahim - B1 - 16h00

```
21 x 550 = 11,550
 2 x 412 =    824
 1 x   0 =      0
─────────────────
Total    = 12,374 DH
```
- 21 full, 2 three-quarter, 1 inactive

### Example 3: Herr Berada - A1 - 18h30

```
10 x 500 = 5,000
 2 x 250 =   500
 1 x 375 =   375
─────────────────
Total    =  5,875 DH
```
- 10 full, 2 half, 1 three-quarter

### Example 4: Herr Laaziz - A1 - 18h30

```
 9 x 500 = 4,500
 2 x 375 =   750
 2 x 250 =   500
─────────────────
Total    =  5,750 DH
```
- 9 full, 2 three-quarter, 2 half

### Example 5: Herr Ouahim - B1 - 18h30

```
19 x 550 = 10,450
 1 x 275 =    275
 1 x 137 =    137
─────────────────
Total    = 10,862 DH
```
- 19 full, 1 half, 1 quarter

### Example 6: Herr Berada - A1 - 16h00

```
14 x 500 = 7,000
 2 x 375 =   750
─────────────────
Total    =  7,750 DH
```
- 14 full, 2 three-quarter

---

## Part 7: Key Rules

1. **Pro-rata system:** Payment = percentage of full price based on attendance quarter
2. **Floor rounding:** `floor(base_price x fraction)` — e.g., 550 x 0.75 = 412.5 → 412 DH
3. **Monthly recalculation:** Categories and totals are recalculated each month
4. **Attendance is indirect:** Individual checkmarks determine the category, not the payment directly
5. **Manual override:** Responsable can always adjust a student's category before finalizing payment
6. **Per-group calculation:** Each group generates a separate payment for the professor. A professor teaching 3 groups gets 3 separate calculations summed
7. **Zero students still listed:** Students who didn't attend/pay are listed with 0 contribution (for tracking)

---

## Part 8: Services Architecture (to build)

Following the existing payroll service pattern:

```
app/Services/Payroll/
├── CrmGroupImportService.php          (existing - student payments)
├── CrmExcelParserService.php          (existing - payment Excel parser)
├── ImportComparisonService.php        (existing - version comparison)
├── StudentLifecycleAnalysisService.php (existing - lifecycle statuses)
├── PresenceImportService.php          (NEW - orchestrate presence import)
├── PresenceExcelParserService.php     (NEW - parse attendance Excel)
└── ProfPaymentCalculationService.php  (NEW - calculate professor payment)
```

**PresenceImportService** — orchestrates:
1. Store uploaded file
2. Auto-increment version
3. Create PresenceImport record
4. Parse Excel with PresenceExcelParserService
5. Persist students + daily records
6. Trigger ProfPaymentCalculationService

**PresenceExcelParserService** — handles:
1. Auto-detect header row (looks for date patterns or day abbreviations)
2. Detect student name column
3. Detect date columns
4. Parse presence values (P/Q/V/X/blank)
5. Return structured data

**ProfPaymentCalculationService** — handles:
1. Group daily records into quarters (weeks)
2. Classify each student into a category
3. Calculate weighted amount per student
4. Sum totals and create payment summary

---

## Part 9: Routes (to add)

Under the existing payroll route prefix:

```
GET    /payroll/presence                              → PresenceImportController@dashboard
GET    /payroll/presence/import/create                → PresenceImportController@create
POST   /payroll/presence/import                       → PresenceImportController@store
GET    /payroll/presence/group/{group}/imports         → PresenceImportController@index
GET    /payroll/presence/group/{group}/import/{import} → PresenceImportController@show
PATCH  /payroll/presence/student/{student}/category    → PresenceImportController@updateCategory
DELETE /payroll/presence/import/{import}               → PresenceImportController@destroy
GET    /payroll/presence/group/{group}/payment-summary → ProfPaymentController@summary
POST   /payroll/presence/import/{import}/approve       → ProfPaymentController@approve
```
