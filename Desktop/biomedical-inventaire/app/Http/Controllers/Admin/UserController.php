<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->orderBy('name')->paginate(20);
        $roles = Role::orderBy('name')->get();
        return view('admin.utilisateurs.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.utilisateurs.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log("Utilisateur créé : {$user->name} avec le rôle {$validated['role']}");

        return redirect()->route('admin.utilisateurs.index')
            ->with('success', "Utilisateur {$user->name} créé avec le rôle {$validated['role']}.");
    }

    public function edit(User $utilisateur)
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.utilisateurs.edit', compact('utilisateur', 'roles'));
    }

    public function update(Request $request, User $utilisateur)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $utilisateur->id,
            'role'     => 'required|exists:roles,name',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $ancienRole = $utilisateur->roles->first()?->name;

        $utilisateur->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($validated['password']) {
            $utilisateur->update(['password' => Hash::make($validated['password'])]);
        }

        $utilisateur->syncRoles([$validated['role']]);

        if ($ancienRole !== $validated['role']) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($utilisateur)
                ->log("Rôle modifié : {$utilisateur->name} — {$ancienRole} → {$validated['role']}");
        }

        return redirect()->route('admin.utilisateurs.index')
            ->with('success', "Utilisateur {$utilisateur->name} mis à jour.");
    }

    public function destroy(User $utilisateur)
    {
        if ($utilisateur->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        activity()
            ->causedBy(auth()->user())
            ->log("Utilisateur supprimé : {$utilisateur->name} ({$utilisateur->email})");

        $utilisateur->delete();

        return redirect()->route('admin.utilisateurs.index')
            ->with('success', "Utilisateur supprimé.");
    }
}
