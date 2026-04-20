<?php

namespace App\Http\Controllers\Backoffice\Encaissement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Encaissement\StoreSiteExpenseRequest;
use App\Models\SiteExpense;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = SiteExpense::with('site')->orderByDesc('month');

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month . '-01');
        }

        $expenses = $query->paginate(50)->withQueryString();
        $sites = Site::where('is_active', true)->orderBy('name')->get();

        return view('backoffice.encaissements.expenses.index', compact('expenses', 'sites'));
    }

    public function create()
    {
        $sites = Site::where('is_active', true)->orderBy('name')->get();

        return view('backoffice.encaissements.expenses.create', compact('sites'));
    }

    public function store(StoreSiteExpenseRequest $request)
    {
        SiteExpense::create($request->validated());

        return redirect()
            ->route('backoffice.encaissements.expenses.index')
            ->with('success', 'Charge ajoutée avec succès.');
    }

    public function edit(SiteExpense $expense)
    {
        $sites = Site::where('is_active', true)->orderBy('name')->get();

        return view('backoffice.encaissements.expenses.edit', compact('expense', 'sites'));
    }

    public function update(StoreSiteExpenseRequest $request, SiteExpense $expense)
    {
        $expense->update($request->validated());

        return redirect()
            ->route('backoffice.encaissements.expenses.index')
            ->with('success', 'Charge mise à jour.');
    }

    public function destroy(SiteExpense $expense)
    {
        $expense->delete();

        return redirect()
            ->route('backoffice.encaissements.expenses.index')
            ->with('success', 'Charge supprimée.');
    }
}
