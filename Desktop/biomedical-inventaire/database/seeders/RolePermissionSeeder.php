<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Vider le cache Spatie avant de recréer
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── PERMISSIONS ──────────────────────────────────────────
        $permissions = [
            // Tableau de bord
            'dashboard.voir',

            // Équipements
            'equipements.voir',
            'equipements.creer',
            'equipements.modifier',
            'equipements.supprimer',
            'equipements.exporter',

            // Mouvements de stock
            'mouvements.voir',
            'mouvements.creer',

            // Maintenances
            'maintenances.voir',
            'maintenances.creer',
            'maintenances.modifier',
            'maintenances.supprimer',

            // Ventes & Facturation
            'ventes.voir',
            'ventes.creer',
            'ventes.paiement',
            'ventes.annuler',
            'ventes.livrer',

            // Clients
            'clients.voir',
            'clients.creer',
            'clients.modifier',
            'clients.supprimer',

            // Fournisseurs
            'fournisseurs.voir',
            'fournisseurs.gerer',

            // Référentiels (catégories, services)
            'referentiels.voir',
            'referentiels.gerer',

            // Paramètres
            'parametres.gerer',

            // Administration
            'admin.utilisateurs',
            'admin.logs',

            // Module RH
            'rh.voir',
            'rh.employes.voir',
            'rh.employes.creer',
            'rh.employes.modifier',
            'rh.employes.supprimer',
            'rh.bulletins.voir',
            'rh.bulletins.creer',
            'rh.bulletins.valider',
            'rh.bulletins.payer',
            'rh.conges.gerer',

            // Module Finance
            'finance.voir',
            'finance.depenses.creer',
            'finance.depenses.modifier',
            'finance.depenses.approuver',
            'finance.rapports.voir',

            // Module GED (Documents)
            'documents.voir',
            'documents.creer',
            'documents.modifier',
            'documents.supprimer',
            'documents.gerer',           // gestion catégories
            'documents.confidentiels',   // accès aux docs confidentiels

            // Module Achats
            'achats.voir',
            'achats.commandes.creer',
            'achats.commandes.modifier',
            'achats.commandes.annuler',
            'achats.receptions.creer',
            'achats.receptions.valider',
            'achats.livraisons.creer',
            'achats.livraisons.expedier',

            // Module Secrétariat
            'secretariat.voir',
            'secretariat.visiteurs.gerer',
            'secretariat.messages.envoyer',
            'secretariat.reunions.gerer',

            // Module Impôts
            'impots.voir',
            'impots.tva.voir',
            'impots.tva.gerer',
            'impots.is.voir',
            'impots.is.gerer',
            'impots.bilan.voir',
            'impots.bilan.gerer',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ── RÔLES ───────────────────────────────────────────────

        // 1. Administrateur — accès total
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // 2. Responsable des ventes
        $vente = Role::firstOrCreate(['name' => 'responsable_vente']);
        $vente->syncPermissions([
            'dashboard.voir',
            'equipements.voir',
            'ventes.voir', 'ventes.creer', 'ventes.paiement', 'ventes.annuler', 'ventes.livrer',
            'clients.voir', 'clients.creer', 'clients.modifier',
            'fournisseurs.voir',
            'mouvements.voir',
        ]);

        // 3. Technicien de maintenance
        $maintenance = Role::firstOrCreate(['name' => 'technicien_maintenance']);
        $maintenance->syncPermissions([
            'dashboard.voir',
            'equipements.voir', 'equipements.modifier',
            'maintenances.voir', 'maintenances.creer', 'maintenances.modifier',
            'mouvements.voir',
            'fournisseurs.voir',
        ]);

        // 4. Gestionnaire de stock
        $stock = Role::firstOrCreate(['name' => 'gestionnaire_stock']);
        $stock->syncPermissions([
            'dashboard.voir',
            'equipements.voir', 'equipements.creer', 'equipements.modifier', 'equipements.exporter',
            'mouvements.voir', 'mouvements.creer',
            'fournisseurs.voir', 'fournisseurs.gerer',
            'referentiels.voir', 'referentiels.gerer',
            'maintenances.voir',
        ]);

        // 5. Lecteur — consultation uniquement
        $lecteur = Role::firstOrCreate(['name' => 'lecteur']);
        $lecteur->syncPermissions([
            'dashboard.voir',
            'equipements.voir',
            'mouvements.voir',
            'maintenances.voir',
            'ventes.voir',
            'clients.voir',
            'fournisseurs.voir',
            'referentiels.voir',
        ]);

        // 6. Responsable RH — module RH complet
        $rh = Role::firstOrCreate(['name' => 'responsable_rh']);
        $rh->syncPermissions([
            'dashboard.voir',
            'rh.voir',
            'rh.employes.voir', 'rh.employes.creer', 'rh.employes.modifier', 'rh.employes.supprimer',
            'rh.bulletins.voir', 'rh.bulletins.creer', 'rh.bulletins.valider', 'rh.bulletins.payer',
            'rh.conges.gerer',
        ]);

        // 7. Responsable Finance — module Finance complet
        $finance = Role::firstOrCreate(['name' => 'responsable_finance']);
        $finance->syncPermissions([
            'dashboard.voir',
            'finance.voir',
            'finance.depenses.creer', 'finance.depenses.modifier', 'finance.depenses.approuver',
            'finance.rapports.voir',
            'ventes.voir',
            'fournisseurs.voir',
        ]);

        // Ajouter documents.voir à tous les rôles de base
        foreach ([$vente, $maintenance, $stock] as $role) {
            $role->givePermissionTo(['documents.voir', 'documents.creer']);
        }

        // Ajouter achats.voir au gestionnaire_stock
        $stock->givePermissionTo([
            'achats.voir',
            'achats.commandes.creer', 'achats.commandes.modifier',
            'achats.receptions.creer', 'achats.receptions.valider',
        ]);

        // 8.5. Responsable Achats — module Achats complet
        $achats = Role::firstOrCreate(['name' => 'responsable_achats']);
        $achats->syncPermissions([
            'dashboard.voir',
            'achats.voir',
            'achats.commandes.creer', 'achats.commandes.modifier', 'achats.commandes.annuler',
            'achats.receptions.creer', 'achats.receptions.valider',
            'achats.livraisons.creer', 'achats.livraisons.expedier',
            'fournisseurs.voir', 'fournisseurs.gerer',
            'equipements.voir',
            'mouvements.voir',
            'ventes.voir',
            'clients.voir',
        ]);

        // 9. Secrétaire — module secrétariat complet
        $secretaire = Role::firstOrCreate(['name' => 'secretaire']);
        $secretaire->syncPermissions([
            'dashboard.voir',
            'secretariat.voir',
            'secretariat.visiteurs.gerer',
            'secretariat.messages.envoyer',
            'secretariat.reunions.gerer',
            'clients.voir',
            'documents.voir',
            'documents.creer',
        ]);

        // 8. Responsable Fiscal — module Impôts complet
        $fiscal = Role::firstOrCreate(['name' => 'responsable_fiscal']);
        $fiscal->syncPermissions([
            'dashboard.voir',
            'finance.voir', 'finance.rapports.voir',
            'impots.voir',
            'impots.tva.voir', 'impots.tva.gerer',
            'impots.is.voir', 'impots.is.gerer',
            'impots.bilan.voir', 'impots.bilan.gerer',
            'ventes.voir',
        ]);

        // ── UTILISATEURS DE DÉMONSTRATION ───────────────────────

        $users = [
            [
                'name'     => 'Administrateur',
                'email'    => 'admin@biomedical.cm',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ],
            [
                'name'     => 'Alice Vente',
                'email'    => 'vente@biomedical.cm',
                'password' => Hash::make('password'),
                'role'     => 'responsable_vente',
            ],
            [
                'name'     => 'Bob Technicien',
                'email'    => 'maintenance@biomedical.cm',
                'password' => Hash::make('password'),
                'role'     => 'technicien_maintenance',
            ],
            [
                'name'     => 'Claire Stock',
                'email'    => 'stock@biomedical.cm',
                'password' => Hash::make('password'),
                'role'     => 'gestionnaire_stock',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => $data['password']]
            );
            $user->syncRoles([$data['role']]);
        }

        $this->command->info('Rôles, permissions et utilisateurs créés.');
        $this->command->table(
            ['Rôle', 'Email', 'Mot de passe'],
            array_map(fn ($u) => [$u['role'], $u['email'], 'password'], $users)
        );
    }
}
