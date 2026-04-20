<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Merge the standalone `employees` table into `users` so the platform has a
 * single source of truth for staff. Planning, encaissements and primes all
 * repoint their FK to `users.id`.
 *
 * Safe order:
 *   1. Add staff columns on `users`
 *   2. Add nullable `user_id` on employee_schedules, encaissements, primes
 *   3. Backfill users from employees (match by email, create missing)
 *   4. Map FKs
 *   5. Drop old employee_id columns and the employees table
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Staff fields on users --------------------------------------------------
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'site_id')) {
                $table->foreignId('site_id')->nullable()->after('location')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'staff_role')) {
                $table->string('staff_role')->nullable()->after('site_id');
            }
            if (!Schema::hasColumn('users', 'hired_at')) {
                $table->date('hired_at')->nullable()->after('staff_role');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('hired_at');
            }
            if (!Schema::hasColumn('users', 'staff_notes')) {
                $table->text('staff_notes')->nullable()->after('is_active');
            }
        });

        // 2. Nullable user_id on dependant tables -----------------------------------
        if (Schema::hasTable('employee_schedules') && !Schema::hasColumn('employee_schedules', 'user_id')) {
            Schema::table('employee_schedules', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }
        if (Schema::hasTable('encaissements') && !Schema::hasColumn('encaissements', 'user_id')) {
            Schema::table('encaissements', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('employee_id')->constrained()->nullOnDelete();
            });
        }
        if (Schema::hasTable('primes') && !Schema::hasColumn('primes', 'user_id')) {
            Schema::table('primes', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('employee_id')->constrained()->cascadeOnDelete();
            });
        }

        // 3. Backfill: for every employee, find or create a matching user -----------
        $employeeToUser = [];

        if (Schema::hasTable('employees')) {
            $employees = DB::table('employees')->get();

            foreach ($employees as $emp) {
                $userId = null;

                if (!empty($emp->email)) {
                    $userId = DB::table('users')->where('email', $emp->email)->value('id');
                }

                if (!$userId) {
                    // Generate a stable fallback email when one is missing
                    $email = $emp->email ?: Str::slug($emp->name) . '-' . $emp->id . '@staff.local';

                    $userId = DB::table('users')->insertGetId([
                        'name'              => $emp->name,
                        'email'             => $email,
                        'password'          => bcrypt(Str::random(32)),
                        'phone'             => $emp->phone,
                        'site_id'           => $emp->site_id,
                        'staff_role'        => $emp->role,
                        'hired_at'          => $emp->hired_at,
                        'is_active'         => $emp->is_active ?? 1,
                        'staff_notes'       => $emp->notes,
                        'email_verified_at' => now(),
                        'created_at'        => $emp->created_at ?? now(),
                        'updated_at'        => $emp->updated_at ?? now(),
                    ]);
                } else {
                    // Enrich existing user with staff metadata if missing
                    DB::table('users')->where('id', $userId)->update([
                        'site_id'     => DB::raw('COALESCE(site_id, ' . ((int) $emp->site_id) . ')'),
                        'staff_role'  => DB::raw("COALESCE(staff_role, " . DB::getPdo()->quote($emp->role) . ")"),
                        'phone'       => DB::raw("COALESCE(phone, " . DB::getPdo()->quote($emp->phone ?? '') . ")"),
                        'hired_at'    => DB::raw('COALESCE(hired_at, ' . ($emp->hired_at ? DB::getPdo()->quote($emp->hired_at) : 'NULL') . ')'),
                        'is_active'   => $emp->is_active ?? 1,
                        'staff_notes' => DB::raw("COALESCE(staff_notes, " . DB::getPdo()->quote($emp->notes ?? '') . ")"),
                        'updated_at'  => now(),
                    ]);
                }

                $employeeToUser[$emp->id] = $userId;
            }
        }

        // 4. Map existing FKs -------------------------------------------------------
        foreach ($employeeToUser as $employeeId => $userId) {
            if (Schema::hasTable('employee_schedules')) {
                DB::table('employee_schedules')->where('employee_id', $employeeId)->update(['user_id' => $userId]);
            }
            if (Schema::hasTable('encaissements')) {
                DB::table('encaissements')->where('employee_id', $employeeId)->update(['user_id' => $userId]);
            }
            if (Schema::hasTable('primes')) {
                DB::table('primes')->where('employee_id', $employeeId)->update(['user_id' => $userId]);
            }
        }

        // 5. Drop old employee_id columns + employees table -------------------------
        if (Schema::hasTable('employee_schedules') && Schema::hasColumn('employee_schedules', 'employee_id')) {
            Schema::table('employee_schedules', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
            // Enforce user_id NOT NULL now that backfill is done
            Schema::table('employee_schedules', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable(false)->change();
            });
        }
        if (Schema::hasTable('encaissements') && Schema::hasColumn('encaissements', 'employee_id')) {
            Schema::table('encaissements', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
        }
        if (Schema::hasTable('primes') && Schema::hasColumn('primes', 'employee_id')) {
            Schema::table('primes', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
            Schema::table('primes', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable(false)->change();
            });
        }

        if (Schema::hasTable('employees')) {
            Schema::drop('employees');
        }

        // Rename employee_schedules → user_schedules for clarity
        if (Schema::hasTable('employee_schedules') && !Schema::hasTable('user_schedules')) {
            Schema::rename('employee_schedules', 'user_schedules');
        }
    }

    public function down(): void
    {
        // One-way data migration. Restore the employees table shape but not data.
        if (Schema::hasTable('user_schedules') && !Schema::hasTable('employee_schedules')) {
            Schema::rename('user_schedules', 'employee_schedules');
        }

        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('site_id')->constrained()->cascadeOnDelete();
                $table->string('role');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->boolean('is_active')->default(true);
                $table->date('hired_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        foreach (['employee_schedules', 'encaissements', 'primes'] as $t) {
            if (Schema::hasTable($t) && !Schema::hasColumn($t, 'employee_id')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
                });
            }
        }

        Schema::table('users', function (Blueprint $table) {
            foreach (['staff_notes', 'is_active', 'hired_at', 'staff_role'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('users', 'site_id')) {
                $table->dropForeign(['site_id']);
                $table->dropColumn('site_id');
            }
        });
    }
};
