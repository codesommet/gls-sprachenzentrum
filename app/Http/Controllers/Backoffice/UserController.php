<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->latest()->get();

        return view('backoffice.users.index', compact('users'));
    }

    public function create()
    {
        $roles = $this->availableRoles();

        return view('backoffice.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|string|exists:roles,name',
        ]);

        $validated['email_verified_at'] = now();

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => $validated['password'],
            'email_verified_at' => now(),
        ]);

        // Only Super Admin can assign the Super Admin role
        if ($validated['role'] === 'Super Admin' && ! auth()->user()->hasRole('Super Admin')) {
            return back()->with('error', 'Seul un Super Admin peut attribuer le rôle Super Admin.');
        }

        $user->assignRole($validated['role']);

        return redirect()
            ->route('backoffice.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(string $id)
    {
        $user  = User::findOrFail($id);
        $roles = $this->availableRoles();

        return view('backoffice.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role'     => 'required|string|exists:roles,name',
        ]);

        $userData = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = $validated['password'];
        }

        // Only Super Admin can assign the Super Admin role
        if ($validated['role'] === 'Super Admin' && ! auth()->user()->hasRole('Super Admin')) {
            return back()->with('error', 'Seul un Super Admin peut attribuer le rôle Super Admin.');
        }

        $user->update($userData);
        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('backoffice.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Only Super Admin users see the "Super Admin" role in the dropdown.
     */
    private function availableRoles()
    {
        $query = Role::orderBy('name');

        if (! auth()->user()->hasRole('Super Admin')) {
            $query->where('name', '!=', 'Super Admin');
        }

        return $query->get();
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()
                ->route('backoffice.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()
            ->route('backoffice.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }
}
