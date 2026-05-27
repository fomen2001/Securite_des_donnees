<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Client;
use App\Models\Parametre;
use App\Models\Equipement;
use App\Models\Fournisseur;
use App\Models\LigneVente;
use App\Models\Maintenance;
use App\Models\MouvementStock;
use App\Models\Service;
use App\Models\User;
use App\Models\Vente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Utilisateur admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@biomedical.cm'],
            [
                'name'     => 'Administrateur',
                'password' => Hash::make('password'),
            ]
        );

        // Catégories
        $categories = [
            ['nom' => 'Monitoring',        'couleur' => '#0d6efd', 'description' => 'Moniteurs de signes vitaux, ECG'],
            ['nom' => 'Imagerie médicale',  'couleur' => '#6f42c1', 'description' => 'Radiologie, échographie, scanner'],
            ['nom' => 'Thérapeutique',      'couleur' => '#20c997', 'description' => 'Ventilateurs, pompes perfusion'],
            ['nom' => 'Diagnostic in vitro','couleur' => '#fd7e14', 'description' => 'Analyseurs biologie, hématologie'],
            ['nom' => 'Chirurgie',          'couleur' => '#dc3545', 'description' => 'Blocs opératoires, instruments'],
            ['nom' => 'Stérilisation',      'couleur' => '#6c757d', 'description' => 'Autoclaves, désinfecteurs'],
            ['nom' => 'Consommables',       'couleur' => '#0dcaf0', 'description' => 'Consommables et réactifs'],
        ];

        foreach ($categories as $cat) {
            Categorie::firstOrCreate(['nom' => $cat['nom']], $cat);
        }

        // Fournisseurs
        $fournisseurs = [
            ['nom' => 'Philips Healthcare',   'pays' => 'Pays-Bas',   'email' => 'contact@philips.com',   'statut' => 'actif'],
            ['nom' => 'Siemens Healthineers', 'pays' => 'Allemagne',  'email' => 'contact@siemens.com',   'statut' => 'actif'],
            ['nom' => 'GE Healthcare',        'pays' => 'USA',        'email' => 'contact@ge.com',        'statut' => 'actif'],
            ['nom' => 'Mindray',              'pays' => 'Chine',      'email' => 'contact@mindray.com',   'statut' => 'actif'],
            ['nom' => 'TechSanté Cameroun',   'pays' => 'Cameroun',   'email' => 'info@techsante.cm',     'statut' => 'actif'],
        ];

        foreach ($fournisseurs as $f) {
            Fournisseur::firstOrCreate(['nom' => $f['nom']], $f);
        }

        // Services
        $services = [
            ['nom' => 'Urgences',        'batiment' => 'Bâtiment A', 'etage' => 'RDC', 'responsable' => 'Dr. Mbarga'],
            ['nom' => 'Réanimation',     'batiment' => 'Bâtiment A', 'etage' => '1er', 'responsable' => 'Dr. Nkomo'],
            ['nom' => 'Cardiologie',     'batiment' => 'Bâtiment B', 'etage' => '2ème', 'responsable' => 'Dr. Tamba'],
            ['nom' => 'Radiologie',      'batiment' => 'Bâtiment C', 'etage' => 'RDC', 'responsable' => 'Dr. Essono'],
            ['nom' => 'Bloc opératoire', 'batiment' => 'Bâtiment B', 'etage' => '1er', 'responsable' => 'Dr. Fouda'],
            ['nom' => 'Pédiatrie',       'batiment' => 'Bâtiment D', 'etage' => 'RDC', 'responsable' => 'Dr. Atangana'],
            ['nom' => 'Laboratoire',     'batiment' => 'Bâtiment C', 'etage' => '1er', 'responsable' => 'Dr. Biyong'],
        ];

        foreach ($services as $svc) {
            Service::firstOrCreate(['nom' => $svc['nom']], $svc);
        }

        // Équipements de démonstration
        $equipementsData = [
            [
                'designation'   => 'Moniteur multiparamétrique',
                'marque'        => 'Philips',
                'modele'        => 'IntelliVue MX450',
                'categorie'     => 'Monitoring',
                'fournisseur'   => 'Philips Healthcare',
                'service'       => 'Réanimation',
                'etat'          => 'operationnel',
                'quantite'      => 5,
                'quantite_min'  => 2,
                'prix_achat'    => 4500000,
                'classe_risque' => 'IIb',
            ],
            [
                'designation'   => 'Ventilateur de réanimation',
                'marque'        => 'Mindray',
                'modele'        => 'SV300',
                'categorie'     => 'Thérapeutique',
                'fournisseur'   => 'Mindray',
                'service'       => 'Réanimation',
                'etat'          => 'operationnel',
                'quantite'      => 3,
                'quantite_min'  => 2,
                'prix_achat'    => 8000000,
                'classe_risque' => 'III',
            ],
            [
                'designation'   => 'Échographe portable',
                'marque'        => 'GE',
                'modele'        => 'Vscan Air',
                'categorie'     => 'Imagerie médicale',
                'fournisseur'   => 'GE Healthcare',
                'service'       => 'Urgences',
                'etat'          => 'operationnel',
                'quantite'      => 2,
                'quantite_min'  => 1,
                'prix_achat'    => 6500000,
                'classe_risque' => 'IIa',
            ],
            [
                'designation'   => 'Défibrillateur',
                'marque'        => 'Philips',
                'modele'        => 'HeartStart XL+',
                'categorie'     => 'Thérapeutique',
                'fournisseur'   => 'Philips Healthcare',
                'service'       => 'Urgences',
                'etat'          => 'operationnel',
                'quantite'      => 4,
                'quantite_min'  => 2,
                'prix_achat'    => 3200000,
                'classe_risque' => 'III',
            ],
            [
                'designation'   => 'Analyseur hématologique',
                'marque'        => 'Mindray',
                'modele'        => 'BC-6900',
                'categorie'     => 'Diagnostic in vitro',
                'fournisseur'   => 'Mindray',
                'service'       => 'Laboratoire',
                'etat'          => 'en_maintenance',
                'quantite'      => 1,
                'quantite_min'  => 1,
                'prix_achat'    => 5500000,
                'classe_risque' => 'IIb',
            ],
            [
                'designation'   => 'Table opératoire',
                'marque'        => 'Siemens',
                'modele'        => 'Dräger Alpha Classic',
                'categorie'     => 'Chirurgie',
                'fournisseur'   => 'Siemens Healthineers',
                'service'       => 'Bloc opératoire',
                'etat'          => 'operationnel',
                'quantite'      => 3,
                'quantite_min'  => 1,
                'prix_achat'    => 12000000,
                'classe_risque' => 'I',
            ],
            [
                'designation'   => 'Autoclave de stérilisation',
                'marque'        => 'Tuttnauer',
                'modele'        => '3870M',
                'categorie'     => 'Stérilisation',
                'fournisseur'   => 'TechSanté Cameroun',
                'service'       => 'Bloc opératoire',
                'etat'          => 'operationnel',
                'quantite'      => 2,
                'quantite_min'  => 1,
                'prix_achat'    => 2800000,
                'classe_risque' => 'IIa',
            ],
            [
                'designation'   => 'Electrocardiographe 12 dérivations',
                'marque'        => 'GE',
                'modele'        => 'MAC 5500',
                'categorie'     => 'Monitoring',
                'fournisseur'   => 'GE Healthcare',
                'service'       => 'Cardiologie',
                'etat'          => 'operationnel',
                'quantite'      => 2,
                'quantite_min'  => 1,
                'prix_achat'    => 1800000,
                'classe_risque' => 'IIa',
            ],
        ];

        foreach ($equipementsData as $data) {
            $categorie  = Categorie::where('nom', $data['categorie'])->first();
            $fournisseur = Fournisseur::where('nom', $data['fournisseur'])->first();
            $service    = Service::where('nom', $data['service'])->first();

            $eq = Equipement::firstOrCreate(
                ['designation' => $data['designation'], 'marque' => $data['marque']],
                [
                    'code_inventaire'        => Equipement::genererCode(),
                    'designation'            => $data['designation'],
                    'marque'                 => $data['marque'],
                    'modele'                 => $data['modele'],
                    'categorie_id'           => $categorie?->id,
                    'fournisseur_id'         => $fournisseur?->id,
                    'service_id'             => $service?->id,
                    'etat'                   => $data['etat'],
                    'quantite'               => $data['quantite'],
                    'quantite_min'           => $data['quantite_min'],
                    'prix_achat'             => $data['prix_achat'],
                    'classe_risque'          => $data['classe_risque'],
                    'date_acquisition'       => now()->subMonths(rand(6, 36)),
                    'date_fin_garantie'      => now()->addMonths(rand(-3, 24)),
                    'periodicite_maintenance'=> 365,
                    'prochaine_maintenance'  => now()->addDays(rand(-30, 90)),
                ]
            );

            // Mouvement d'entrée initial
            if ($eq->wasRecentlyCreated) {
                $eq->mouvements()->create([
                    'type'                   => 'entree',
                    'quantite'               => $data['quantite'],
                    'quantite_avant'         => 0,
                    'quantite_apres'         => $data['quantite'],
                    'service_destination_id' => $service?->id,
                    'motif'                  => 'Entrée initiale — données de démonstration',
                    'user_id'                => $admin->id,
                    'date_mouvement'         => now()->subMonths(rand(3, 12)),
                ]);
            }
        }

        // ---- Clients ----
        $clientsData = [
            ['nom' => 'Hôpital Central de Yaoundé',  'type' => 'hopital',     'ville' => 'Yaoundé',   'telephone' => '222 23 40 00'],
            ['nom' => 'Clinique La Grâce',            'type' => 'clinique',    'ville' => 'Douala',    'telephone' => '699 000 111'],
            ['nom' => 'Cabinet Dr. Mballa',           'type' => 'cabinet',     'ville' => 'Bafoussam', 'telephone' => '677 222 333'],
            ['nom' => 'Labo BioAnalysis',             'type' => 'laboratoire', 'ville' => 'Yaoundé',   'telephone' => '222 31 00 05'],
        ];

        $clients = [];
        foreach ($clientsData as $cd) {
            $clients[] = Client::firstOrCreate(['nom' => $cd['nom']], array_merge($cd, [
                'code_client' => Client::genererCode(),
                'statut'      => 'actif',
                'pays'        => 'Cameroun',
            ]));
        }

        // ---- Ventes de démonstration ----
        $equipements = Equipement::where('quantite', '>', 2)->get();

        if ($equipements->count() >= 2 && count($clients) >= 2) {
            foreach (array_slice($clients, 0, 2) as $idx => $client) {
                $eq1 = $equipements->get($idx * 2);
                $eq2 = $equipements->get($idx * 2 + 1);
                if (! $eq1 || ! $eq2) continue;

                $pu1 = $eq1->prix_achat ? $eq1->prix_achat * 1.3 : 500000;
                $pu2 = $eq2->prix_achat ? $eq2->prix_achat * 1.3 : 300000;
                $qte1 = 1; $qte2 = 1;
                $sousTotal = ($pu1 * $qte1) + ($pu2 * $qte2);
                $tva = 19.25;
                $montantTva = round($sousTotal * $tva / 100, 2);
                $totalTtc = $sousTotal + $montantTva;

                $statut = $idx === 0 ? 'payee' : 'facturee';

                $vente = Vente::create([
                    'numero_facture'   => Vente::genererNumero(),
                    'client_id'        => $client->id,
                    'user_id'          => $admin->id,
                    'statut'           => $statut,
                    'mode_paiement'    => $idx === 0 ? 'virement' : 'credit',
                    'date_vente'       => now()->subDays($idx * 15 + 5),
                    'remise_globale'   => 0,
                    'tva'              => $tva,
                    'sous_total_ht'    => $sousTotal,
                    'montant_remise'   => 0,
                    'montant_tva'      => $montantTva,
                    'total_ttc'        => $totalTtc,
                    'montant_paye'     => $idx === 0 ? $totalTtc : 0,
                ]);

                foreach ([[$eq1, $qte1, $pu1], [$eq2, $qte2, $pu2]] as [$eq, $qte, $pu]) {
                    LigneVente::create([
                        'vente_id'             => $vente->id,
                        'equipement_id'        => $eq->id,
                        'designation_snapshot' => $eq->designation,
                        'reference_snapshot'   => $eq->code_inventaire,
                        'quantite'             => $qte,
                        'prix_unitaire_ht'     => $pu,
                        'remise'               => 0,
                        'total_ht'             => $pu * $qte,
                    ]);

                    $ancienne = $eq->quantite;
                    $eq->decrement('quantite', $qte);
                    MouvementStock::create([
                        'equipement_id'      => $eq->id,
                        'type'               => 'sortie',
                        'quantite'           => $qte,
                        'quantite_avant'     => $ancienne,
                        'quantite_apres'     => $ancienne - $qte,
                        'reference_document' => $vente->numero_facture,
                        'motif'              => "Vente démo — {$vente->numero_facture}",
                        'user_id'            => $admin->id,
                        'date_mouvement'     => $vente->date_vente,
                    ]);
                }
            }
        }

        // ---- Paramètres par défaut ----
        foreach (Parametre::defauts() as $cle => $valeur) {
            Parametre::firstOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        }

        $this->command->info('Base de données initialisée avec les données de démonstration.');
        $this->command->info('Compte admin : admin@biomedical.cm / password');
    }
}
