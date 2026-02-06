<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_options', function (Blueprint $table) {

            if (Schema::hasColumn('quiz_options', 'option_text')) {
                $table->string('option_text')->nullable()->change();
            }

            if (!Schema::hasColumn('quiz_options', 'option_type')) {
                $table->string('option_type', 20)->default('text')->after('question_id'); // text|image
            }

            if (!Schema::hasColumn('quiz_options', 'option_media_path')) {
                $table->string('option_media_path')->nullable()->after('option_type'); // image path
            }

            if (!Schema::hasColumn('quiz_options', 'option_caption')) {
                $table->string('option_caption')->nullable()->after('option_media_path'); // optional label under image
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_options', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_options', 'option_caption')) $table->dropColumn('option_caption');
            if (Schema::hasColumn('quiz_options', 'option_media_path')) $table->dropColumn('option_media_path');
            if (Schema::hasColumn('quiz_options', 'option_type')) $table->dropColumn('option_type');

            // option_text back to not-null (attention si tu as des rows null déjà)
            // $table->string('option_text')->nullable(false)->change();
        });
    }
};
