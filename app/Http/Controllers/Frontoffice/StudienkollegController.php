<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Studienkolleg;
use Illuminate\View\View;

class StudienkollegController extends Controller
{
    /**
     * Studienkollegs listing with filters
     */
    public function index(): View
{
    // Get filter inputs from query string
    $filters = request()->only([
        'q', 'city', 'lang', 'course',
        'uni_assist', 'entrance_exam',
        'certification_required', 'translation_required',
        'featured',
    ]);

    // Clean up empty/null filters
    $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

    // Featured only if no filters applied
    $featured = null;

    if (empty($filters)) {
        $featured = Studienkolleg::query()
            ->where('public', true)
            ->where('featured', true)
            ->orderByDesc('updated_at')   // ou name asc si tu veux
            ->first();
    }

    // Build query with filters
    $query = Studienkolleg::query()
        ->where('public', true)
        ->filter($filters)
        ->orderBy('featured', 'desc')
        ->orderBy('name', 'asc');

    // Remove featured from the listing to avoid duplicates
    if ($featured) {
        $query->where('id', '!=', $featured->id);
    }

    // Paginate results
    $studienkollegs = $query->get();

    return view('frontoffice.studienkollegs.index', [
        'featured'       => $featured,
        'studienkollegs' => $studienkollegs,
    ]);
}


    /**
     * Studienkolleg detail page
     */
    public function show(string $slug): View
    {
        $studienkolleg = Studienkolleg::query()
            ->where('slug', $slug)
            ->where('public', true)
            ->firstOrFail();

        return view('frontoffice.studienkollegs.show', [
            'studienkolleg' => $studienkolleg,
        ]);
    }
}
