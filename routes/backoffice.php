<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Backoffice\DashboardController;
use App\Http\Controllers\Backoffice\ProfileController;
use App\Http\Controllers\Backoffice\BlogCategoryController;
use App\Http\Controllers\Backoffice\BlogPostController;
use App\Http\Controllers\Backoffice\SiteController;
use App\Http\Controllers\Backoffice\TeacherController;
use App\Http\Controllers\Backoffice\GroupController;
use App\Http\Controllers\Backoffice\LevelFollowupController;
use App\Http\Controllers\Backoffice\CertificateController;
use App\Http\Controllers\Backoffice\QuizController;
use App\Http\Controllers\Backoffice\QuizQuestionController;
use App\Http\Controllers\Backoffice\HelpController;
use App\Http\Controllers\Backoffice\UserController;
use App\Http\Controllers\Backoffice\RoleController;

use App\Http\Controllers\Backoffice\LeadController;
use App\Http\Controllers\Backoffice\GroupApplicationController as BackofficeGroupApplicationController;
use App\Http\Controllers\Frontoffice\GroupApplicationController as GroupApplicationController;

// Weekly Reports (Rapport Semaine)
use App\Http\Controllers\Backoffice\WeeklyReportController;

// RH / Planning
use App\Http\Controllers\Backoffice\EmployeeController;
use App\Http\Controllers\Backoffice\ScheduleController;
use App\Http\Controllers\Backoffice\PlanningPdfController;

// Payroll / CRM Tracking
use App\Http\Controllers\Backoffice\Payroll\GroupImportController;
use App\Http\Controllers\Backoffice\Payroll\GroupAnalysisController;
use App\Http\Controllers\Backoffice\Payroll\PresenceImportController;

/*
|--------------------------------------------------------------------------
| BACKOFFICE ROUTES
|--------------------------------------------------------------------------
| These routes are already wrapped with:
| middleware(['web', 'auth']) in web.php
|--------------------------------------------------------------------------
*/

/* Dashboard */

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('permission:dashboard.view')
    ->name('dashboard');

/* Optional dynamic pages (avoid profile conflict) */
Route::get('/dashboard/{routeName}/{name?}', [DashboardController::class, 'pageView'])->where('routeName', '^(?!profile).*$')->middleware('permission:dashboard.view');

/*
|--------------------------------------------------------------------------
| BACKOFFICE MODULES
|--------------------------------------------------------------------------
*/
Route::prefix('backoffice')
    ->name('backoffice.')
    ->group(function () {
        /*
        |----------------------------------------------------------------------
        | BLOG → Catégories
        |----------------------------------------------------------------------
        */
        Route::prefix('blog/categories')
            ->name('blog.categories.')
            ->group(function () {
                Route::get('/', [BlogCategoryController::class, 'index'])->middleware('permission:blog_categories.view')->name('index');
                Route::get('/create', [BlogCategoryController::class, 'create'])->middleware('permission:blog_categories.create')->name('create');
                Route::post('/', [BlogCategoryController::class, 'store'])->middleware('permission:blog_categories.create')->name('store');
                Route::get('/{category}/edit', [BlogCategoryController::class, 'edit'])->middleware('permission:blog_categories.edit')->name('edit');
                Route::put('/{category}', [BlogCategoryController::class, 'update'])->middleware('permission:blog_categories.edit')->name('update');
                Route::delete('/{category}', [BlogCategoryController::class, 'destroy'])->middleware('permission:blog_categories.delete')->name('destroy');
            });

        /*
        |----------------------------------------------------------------------
        | BLOG → Articles
        |----------------------------------------------------------------------
        */
        Route::prefix('blog/posts')
            ->name('blog.posts.')
            ->group(function () {
                Route::get('/', [BlogPostController::class, 'index'])->middleware('permission:blog_posts.view')->name('index');
                Route::get('/create', [BlogPostController::class, 'create'])->middleware('permission:blog_posts.create')->name('create');
                Route::post('/', [BlogPostController::class, 'store'])->middleware('permission:blog_posts.create')->name('store');
                Route::get('/{post}/edit', [BlogPostController::class, 'edit'])->middleware('permission:blog_posts.edit')->name('edit');
                Route::put('/{post}', [BlogPostController::class, 'update'])->middleware('permission:blog_posts.edit')->name('update');
                Route::delete('/{post}', [BlogPostController::class, 'destroy'])->middleware('permission:blog_posts.delete')->name('destroy');
            });

        /*
        |----------------------------------------------------------------------
        | SITES GLS
        |----------------------------------------------------------------------
        */
        Route::prefix('sites')
            ->name('sites.')
            ->group(function () {
                Route::get('/', [SiteController::class, 'index'])->middleware('permission:sites.view')->name('index');
                Route::get('/create', [SiteController::class, 'create'])->middleware('permission:sites.create')->name('create');
                Route::post('/', [SiteController::class, 'store'])->middleware('permission:sites.create')->name('store');
                Route::get('/{site}/edit', [SiteController::class, 'edit'])->middleware('permission:sites.edit')->name('edit');
                Route::put('/{site}', [SiteController::class, 'update'])->middleware('permission:sites.edit')->name('update');
                Route::delete('/{site}', [SiteController::class, 'destroy'])->middleware('permission:sites.delete')->name('destroy');
            });

        /*
        |----------------------------------------------------------------------
        | ENSEIGNANTS
        |----------------------------------------------------------------------
        */
        Route::prefix('teachers')
            ->name('teachers.')
            ->group(function () {
                Route::get('/', [TeacherController::class, 'index'])->middleware('permission:teachers.view')->name('index');
                Route::get('/create', [TeacherController::class, 'create'])->middleware('permission:teachers.create')->name('create');
                Route::post('/', [TeacherController::class, 'store'])->middleware('permission:teachers.create')->name('store');
                Route::get('/{teacher}/edit', [TeacherController::class, 'edit'])->middleware('permission:teachers.edit')->name('edit');
                Route::put('/{teacher}', [TeacherController::class, 'update'])->middleware('permission:teachers.edit')->name('update');
                Route::delete('/{teacher}', [TeacherController::class, 'destroy'])->middleware('permission:teachers.delete')->name('destroy');
            });

        /*
        |----------------------------------------------------------------------
        | GROUPES
        |----------------------------------------------------------------------
        */
        Route::prefix('groups')
            ->name('groups.')
            ->group(function () {
                Route::get('/', [GroupController::class, 'index'])->middleware('permission:groups.view')->name('index');
                Route::get('/create', [GroupController::class, 'create'])->middleware('permission:groups.create')->name('create');
                Route::post('/', [GroupController::class, 'store'])->middleware('permission:groups.create')->name('store');
                Route::get('/{group}/edit', [GroupController::class, 'edit'])->middleware('permission:groups.edit')->name('edit');
                Route::put('/{group}', [GroupController::class, 'update'])->middleware('permission:groups.edit')->name('update');
                Route::delete('/{group}', [GroupController::class, 'destroy'])->middleware('permission:groups.delete')->name('destroy');

                // Applications (uses groups.view permission)
                Route::get('/{group}/applications', [GroupController::class, 'applications'])->middleware('permission:groups.view')->name('applications');
                Route::patch('/{group}/applications/{application}/approve', [GroupApplicationController::class, 'approve'])->middleware('permission:groups.edit')->name('applications.approve');
                Route::patch('/{group}/applications/{application}/reject', [GroupApplicationController::class, 'reject'])->middleware('permission:groups.edit')->name('applications.reject');
            });

        /*
        |----------------------------------------------------------------------
        | CERTIFICATS
        |----------------------------------------------------------------------
        */
        Route::prefix('certificates')
            ->name('certificates.')
            ->group(function () {
                Route::get('/', [CertificateController::class, 'index'])->middleware('permission:certificates.view')->name('index');
                Route::get('/create', [CertificateController::class, 'create'])->middleware('permission:certificates.create')->name('create');
                Route::post('/', [CertificateController::class, 'store'])->middleware('permission:certificates.create')->name('store');

                Route::get('/{certificate}', [CertificateController::class, 'show'])->middleware('permission:certificates.view')->name('show');
                Route::get('/{certificate}/edit', [CertificateController::class, 'edit'])->middleware('permission:certificates.edit')->name('edit');
                Route::put('/{certificate}', [CertificateController::class, 'update'])->middleware('permission:certificates.edit')->name('update');
                Route::delete('/{certificate}', [CertificateController::class, 'destroy'])->middleware('permission:certificates.delete')->name('destroy');

                // PDF EXPORT
                Route::get('/{certificate}/pdf', [CertificateController::class, 'pdf'])->middleware('permission:certificates.view')->name('pdf');
            });

        /*
        |----------------------------------------------------------------------
        | STUDIENKOLLEGS
        |----------------------------------------------------------------------
        */
        Route::prefix('studienkollegs')
            ->name('studienkollegs.')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Backoffice\StudienkollegController::class, 'index'])->middleware('permission:studienkollegs.view')->name('index');
                Route::get('/create', [\App\Http\Controllers\Backoffice\StudienkollegController::class, 'create'])->middleware('permission:studienkollegs.create')->name('create');
                Route::post('/', [\App\Http\Controllers\Backoffice\StudienkollegController::class, 'store'])->middleware('permission:studienkollegs.create')->name('store');
                Route::get('/{studienkolleg}/edit', [\App\Http\Controllers\Backoffice\StudienkollegController::class, 'edit'])->middleware('permission:studienkollegs.edit')->name('edit');
                Route::put('/{studienkolleg}', [\App\Http\Controllers\Backoffice\StudienkollegController::class, 'update'])->middleware('permission:studienkollegs.edit')->name('update');
                Route::delete('/{studienkolleg}', [\App\Http\Controllers\Backoffice\StudienkollegController::class, 'destroy'])->middleware('permission:studienkollegs.delete')->name('destroy');
            });

        Route::prefix('quizzes')
            ->name('quizzes.')
            ->group(function () {
                Route::get('/', [QuizController::class, 'index'])->middleware('permission:quizzes.view')->name('index');
                Route::get('/create', [QuizController::class, 'create'])->middleware('permission:quizzes.create')->name('create');
                Route::post('/', [QuizController::class, 'store'])->middleware('permission:quizzes.create')->name('store');
                Route::get('/{quiz}/edit', [QuizController::class, 'edit'])->middleware('permission:quizzes.edit')->name('edit');
                Route::put('/{quiz}', [QuizController::class, 'update'])->middleware('permission:quizzes.edit')->name('update');
                Route::delete('/{quiz}', [QuizController::class, 'destroy'])->middleware('permission:quizzes.delete')->name('destroy');

                // Questions (nested — uses quizzes permission)
                Route::get('/{quiz}/questions', [QuizQuestionController::class, 'index'])->middleware('permission:quizzes.view')->name('questions.index');
                Route::get('/{quiz}/questions/create', [QuizQuestionController::class, 'create'])->middleware('permission:quizzes.create')->name('questions.create');
                Route::post('/{quiz}/questions', [QuizQuestionController::class, 'store'])->middleware('permission:quizzes.create')->name('questions.store');
                Route::get('/{quiz}/questions/{question}/edit', [QuizQuestionController::class, 'edit'])->middleware('permission:quizzes.edit')->name('questions.edit');
                Route::put('/{quiz}/questions/{question}', [QuizQuestionController::class, 'update'])->middleware('permission:quizzes.edit')->name('questions.update');
                Route::delete('/{quiz}/questions/{question}', [QuizQuestionController::class, 'destroy'])->middleware('permission:quizzes.delete')->name('questions.destroy');
            });

        /*
        |----------------------------------------------------------------------
        | LEADS (Consultations, Inscriptions, Applications)
        |----------------------------------------------------------------------
        */
        Route::prefix('leads')
            ->name('leads.')
            ->group(function () {
                Route::get('/', [LeadController::class, 'index'])->middleware('permission:leads.view')->name('index');
                Route::get('/statistiques', [LeadController::class, 'stats'])->middleware('permission:lead_stats.view')->name('stats');
                Route::delete('/consultation/{consultation}', [LeadController::class, 'destroyConsultation'])->middleware('permission:leads.delete')->name('consultation.destroy');
                Route::delete('/inscription/{inscription}', [LeadController::class, 'destroyInscription'])->middleware('permission:leads.delete')->name('inscription.destroy');
                Route::delete('/application/{application}', [LeadController::class, 'destroyApplication'])->middleware('permission:leads.delete')->name('application.destroy');
            });

        /*
        |----------------------------------------------------------------------
        | APPLICATIONS (standalone CRUD)
        |----------------------------------------------------------------------
        */
        Route::prefix('applications')
            ->name('applications.')
            ->group(function () {
                Route::get('/', [BackofficeGroupApplicationController::class, 'index'])->middleware('permission:applications.view')->name('index');
                Route::get('/create', [BackofficeGroupApplicationController::class, 'create'])->middleware('permission:applications.create')->name('create');
                Route::post('/', [BackofficeGroupApplicationController::class, 'store'])->middleware('permission:applications.create')->name('store');
                Route::get('/{application}', [BackofficeGroupApplicationController::class, 'show'])->middleware('permission:applications.view')->name('show');
                Route::get('/{application}/edit', [BackofficeGroupApplicationController::class, 'edit'])->middleware('permission:applications.edit')->name('edit');
                Route::put('/{application}', [BackofficeGroupApplicationController::class, 'update'])->middleware('permission:applications.edit')->name('update');
                Route::delete('/{application}', [BackofficeGroupApplicationController::class, 'destroy'])->middleware('permission:applications.delete')->name('destroy');
                Route::post('/{application}/resync', [BackofficeGroupApplicationController::class, 'resync'])->middleware('permission:applications.edit')->name('resync');
            });

        /*
        |----------------------------------------------------------------------
        | UTILISATEURS
        |----------------------------------------------------------------------
        */
        Route::prefix('users')
            ->name('users.')
            ->group(function () {
                Route::get('/', [UserController::class, 'index'])->middleware('permission:users.view')->name('index');
                Route::get('/create', [UserController::class, 'create'])->middleware('permission:users.create')->name('create');
                Route::post('/', [UserController::class, 'store'])->middleware('permission:users.create')->name('store');
                Route::get('/{user}/edit', [UserController::class, 'edit'])->middleware('permission:users.edit')->name('edit');
                Route::put('/{user}', [UserController::class, 'update'])->middleware('permission:users.edit')->name('update');
                Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('destroy');
            });

        /*
        |----------------------------------------------------------------------
        | ROLES & PERMISSIONS
        |----------------------------------------------------------------------
        */
        Route::prefix('roles')
            ->name('roles.')
            ->group(function () {
                Route::get('/', [RoleController::class, 'index'])->middleware('permission:roles.view')->name('index');
                Route::get('/create', [RoleController::class, 'create'])->middleware('permission:roles.create')->name('create');
                Route::post('/', [RoleController::class, 'store'])->middleware('permission:roles.create')->name('store');
                Route::get('/{role}/edit', [RoleController::class, 'edit'])->middleware('permission:roles.edit')->name('edit');
                Route::put('/{role}', [RoleController::class, 'update'])->middleware('permission:roles.edit')->name('update');
                Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete')->name('destroy');
            });

        /*
        |----------------------------------------------------------------------
        | PAYROLL — Suivi paiement / CRM Import
        |----------------------------------------------------------------------
        */
        Route::prefix('payroll')
            ->name('payroll.')
            ->group(function () {
                // Dashboard — all groups with imports
                Route::get('/', [GroupImportController::class, 'dashboard'])->middleware('permission:payroll.view')->name('dashboard');

                // Upload new import
                Route::get('/import/create', [GroupImportController::class, 'create'])->middleware('permission:payroll.create')->name('import.create');
                Route::post('/import', [GroupImportController::class, 'store'])->middleware('permission:payroll.create')->name('import.store');

                // Import history for a group
                Route::get('/group/{group}/imports', [GroupImportController::class, 'index'])->middleware('permission:payroll.view')->name('group.imports');

                // Import detail & comparison
                Route::get('/group/{group}/import/{import}', [GroupImportController::class, 'show'])->middleware('permission:payroll.view')->name('import.show');
                Route::get('/group/{group}/import/{import}/compare', [GroupImportController::class, 'compare'])->middleware('permission:payroll.view')->name('import.compare');
                Route::delete('/import/{import}', [GroupImportController::class, 'destroy'])->middleware('permission:payroll.delete')->name('import.destroy');

                // Update student status (cancelled, transferred, etc.)
                Route::patch('/student/{student}/status', [GroupImportController::class, 'updateStudentStatus'])->middleware('permission:payroll.edit')->name('student.status');

                // Monthly analysis & student lifecycle
                Route::get('/group/{group}/analysis', [GroupAnalysisController::class, 'monthly'])->middleware('permission:payroll.view')->name('group.analysis');
                Route::post('/group/{group}/recalculate', [GroupAnalysisController::class, 'recalculate'])->middleware('permission:payroll.edit')->name('group.recalculate');
                Route::get('/group/{group}/students', [GroupAnalysisController::class, 'students'])->middleware('permission:payroll.view')->name('group.students');

                /*
                |--------------------------------------------------------------
                | PRESENCE — Attendance import & professor payment calculation
                |--------------------------------------------------------------
                */
                Route::prefix('presence')
                    ->name('presence.')
                    ->group(function () {
                        // Dashboard — all groups with presence imports
                        Route::get('/', [PresenceImportController::class, 'dashboard'])->middleware('permission:presence.view')->name('dashboard');

                        // Upload new presence import
                        Route::get('/import/create', [PresenceImportController::class, 'create'])->middleware('permission:presence.create')->name('import.create');
                        Route::post('/import', [PresenceImportController::class, 'store'])->middleware('permission:presence.create')->name('import.store');

                        // Debug: dump raw Excel data (temporary)
                        Route::post('/debug', [PresenceImportController::class, 'debug'])->middleware('permission:presence.create')->name('debug');

                        // Import history for a group
                        Route::get('/group/{group}/imports', [PresenceImportController::class, 'index'])->middleware('permission:presence.view')->name('group.imports');

                        // Import detail
                        Route::get('/group/{group}/import/{import}', [PresenceImportController::class, 'show'])->middleware('permission:presence.view')->name('import.show');

                        // Delete import
                        Route::delete('/import/{import}', [PresenceImportController::class, 'destroy'])->middleware('permission:presence.delete')->name('import.destroy');

                        // Update student category override
                        Route::patch('/student/{student}/category', [PresenceImportController::class, 'updateCategory'])->middleware('permission:presence.edit')->name('student.category');

                        // Approve payment
                        Route::post('/import/{import}/approve', [PresenceImportController::class, 'approve'])->middleware('permission:presence.edit')->name('import.approve');

                        // Recalculate payment
                        Route::post('/import/{import}/recalculate', [PresenceImportController::class, 'recalculate'])->middleware('permission:presence.edit')->name('import.recalculate');
                    });
            });

        /*
        |----------------------------------------------------------------------
        | RAPPORT SEMAINE (Weekly Reports)
        |----------------------------------------------------------------------
        */
        Route::prefix('weekly-reports')
            ->name('weekly_reports.')
            ->group(function () {
                Route::get('/', [WeeklyReportController::class, 'index'])->middleware('permission:weekly_reports.view')->name('index');
                Route::post('/', [WeeklyReportController::class, 'store'])->middleware('permission:weekly_reports.create')->name('store');
                Route::delete('/{weeklyReport}', [WeeklyReportController::class, 'destroy'])->middleware('permission:weekly_reports.delete')->name('destroy');
                Route::get('/events', [WeeklyReportController::class, 'events'])->middleware('permission:weekly_reports.view')->name('events');
            });

        /*
        |----------------------------------------------------------------------
        | RH / PLANNING — Employés & Horaires
        |----------------------------------------------------------------------
        */
        Route::prefix('employees')
            ->name('employees.')
            ->group(function () {
                Route::get('/', [EmployeeController::class, 'index'])->middleware('permission:employees.view')->name('index');
                Route::get('/create', [EmployeeController::class, 'create'])->middleware('permission:employees.create')->name('create');
                Route::post('/', [EmployeeController::class, 'store'])->middleware('permission:employees.create')->name('store');
                Route::get('/{employee}', [EmployeeController::class, 'show'])->middleware('permission:employees.view')->name('show');
                Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->middleware('permission:employees.edit')->name('edit');
                Route::put('/{employee}', [EmployeeController::class, 'update'])->middleware('permission:employees.edit')->name('update');
                Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->middleware('permission:employees.delete')->name('destroy');
            });

        Route::prefix('schedules')
            ->name('schedules.')
            ->group(function () {
                Route::get('/', [ScheduleController::class, 'index'])->middleware('permission:schedules.view')->name('index');
                Route::get('/create', [ScheduleController::class, 'create'])->middleware('permission:schedules.create')->name('create');
                Route::post('/', [ScheduleController::class, 'store'])->middleware('permission:schedules.create')->name('store');
                Route::get('/{schedule}/edit', [ScheduleController::class, 'edit'])->middleware('permission:schedules.edit')->name('edit');
                Route::put('/{schedule}', [ScheduleController::class, 'update'])->middleware('permission:schedules.edit')->name('update');
                Route::delete('/{schedule}', [ScheduleController::class, 'destroy'])->middleware('permission:schedules.delete')->name('destroy');
            });

        Route::prefix('planning')
            ->name('planning.')
            ->group(function () {
                Route::get('/export', [PlanningPdfController::class, 'exportForm'])->middleware('permission:schedules.view')->name('export-form');
                Route::get('/pdf/employee/{employee}', [PlanningPdfController::class, 'employee'])->middleware('permission:schedules.view')->name('pdf.employee');
                Route::get('/pdf/site/{site}', [PlanningPdfController::class, 'site'])->middleware('permission:schedules.view')->name('pdf.site');
            });

        /*
        |----------------------------------------------------------------------
        | AIDE & DOCUMENTATION
        |----------------------------------------------------------------------
        */
        Route::prefix('help')
            ->name('help.')
            ->group(function () {
                Route::get('/documentation', [HelpController::class, 'documentation'])->name('documentation');
            });

        /*
        |----------------------------------------------------------------------
        | LEVEL FOLLOWUPS (Suivi niveau)
        |----------------------------------------------------------------------
        */
        Route::get('/level-followups', [LevelFollowupController::class, 'index'])
            ->middleware('permission:level_followups.view')
            ->name('level_followups.index');

        Route::get('/level-followups/pdf', [LevelFollowupController::class, 'pdf'])
            ->middleware('permission:level_followups.view')
            ->name('level_followups.pdf');

        Route::get('/level-followups/group/{group}/pdf', [LevelFollowupController::class, 'pdfByGroup'])
            ->middleware('permission:level_followups.view')
            ->name('level_followups.group_pdf');

        Route::get('/level-followups/group/{group}', [LevelFollowupController::class, 'showGroup'])
            ->middleware('permission:level_followups.view')
            ->name('level_followups.group_show');

        Route::post('/level-followups/{followup}/complete', [LevelFollowupController::class, 'complete'])
            ->middleware('permission:level_followups.edit')
            ->name('level_followups.complete');

        Route::patch('/level-followups/{followup}/notes', [LevelFollowupController::class, 'updateNotes'])
            ->middleware('permission:level_followups.edit')
            ->name('level_followups.update_notes');

        Route::delete('/level-followups/{followup}', [LevelFollowupController::class, 'destroy'])
            ->middleware('permission:level_followups.delete')
            ->name('level_followups.destroy');
    });

/*
|--------------------------------------------------------------------------
| PROFILE ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
});
