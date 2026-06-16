<?php

namespace App\Http\Controllers\Secretariat;

use App\Http\Controllers\Controller;
use App\Mail\MessageClientMail;
use App\Models\Client;
use App\Models\MessageClient;
use App\Models\MessageClientDestinataire;
use App\Models\MessageClientPieceJointe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    // Extensions autorisées pour les pièces jointes
    const EXTENSIONS = ['pdf','doc','docx','xls','xlsx','ppt','pptx','jpg','jpeg','png','gif','zip','txt'];
    const TAILLE_MAX_MB = 10; // par fichier

    public function index(Request $request)
    {
        $query = MessageClient::with('user')->withCount(['destinataires', 'piecesJointes'])->latest();

        if ($request->filled('q')) {
            $query->where(fn($b) => $b->where('objet', 'like', '%' . $request->q . '%')
                ->orWhere('reference', 'like', '%' . $request->q . '%'));
        }
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('canal'))  $query->where('canal',  $request->canal);

        $messages = $query->paginate(20)->withQueryString();
        return view('secretariat.messages.index', compact('messages'));
    }

    public function create()
    {
        $clients = Client::orderBy('nom')->get();
        return view('secretariat.messages.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $maxKo = self::TAILLE_MAX_MB * 1024;
        $exts  = implode(',', self::EXTENSIONS);

        $request->validate([
            'objet'              => 'required|string|max:255',
            'corps'              => 'required|string',
            'canal'              => 'required|in:email,sms,email_sms',
            'client_ids'         => 'required|array|min:1',
            'client_ids.*'       => 'exists:clients,id',
            'action'             => 'required|in:brouillon,envoyer',
            'pieces_jointes'     => 'nullable|array',
            'pieces_jointes.*'   => "file|max:{$maxKo}|mimes:{$exts}",
        ]);

        $message = MessageClient::create([
            'reference' => MessageClient::genererReference(),
            'objet'     => $request->objet,
            'corps'     => $request->corps,
            'canal'     => $request->canal,
            'statut'    => 'brouillon',
            'user_id'   => auth()->id(),
        ]);

        // Pièces jointes
        foreach ($request->file('pieces_jointes', []) as $fichier) {
            $chemin = $fichier->store("messages/{$message->id}", 'local');
            MessageClientPieceJointe::create([
                'message_client_id' => $message->id,
                'nom_original'      => $fichier->getClientOriginalName(),
                'chemin'            => $chemin,
                'mime'              => $fichier->getMimeType(),
                'taille'            => $fichier->getSize(),
            ]);
        }

        // Destinataires
        $clients = Client::whereIn('id', $request->client_ids)->get();
        foreach ($clients as $client) {
            MessageClientDestinataire::create([
                'message_client_id' => $message->id,
                'client_id'         => $client->id,
                'email_copie'       => $client->email,
                'telephone_copie'   => $client->telephone,
                'statut_email'      => 'en_attente',
                'statut_sms'        => 'en_attente',
            ]);
        }

        if ($request->action === 'envoyer') {
            $this->envoyerMessage($message);
        }

        return redirect()->route('secretariat.messages.show', $message)
            ->with('success', $request->action === 'envoyer' ? 'Message envoyé.' : 'Brouillon sauvegardé.');
    }

    public function show(MessageClient $message)
    {
        $message->load('destinataires.client', 'user', 'piecesJointes');
        return view('secretariat.messages.show', compact('message'));
    }

    public function envoyer(MessageClient $message)
    {
        if ($message->statut === 'envoye') {
            return back()->with('error', 'Message déjà envoyé.');
        }
        $this->envoyerMessage($message);
        return back()->with('success', 'Message envoyé.');
    }

    public function telechargerPieceJointe(MessageClientPieceJointe $piece)
    {
        // Vérifier que le fichier existe
        if (!Storage::disk('local')->exists($piece->chemin)) {
            abort(404, 'Fichier introuvable.');
        }
        return Storage::disk('local')->download($piece->chemin, $piece->nom_original);
    }

    public function supprimerPieceJointe(MessageClientPieceJointe $piece)
    {
        $message = $piece->message;

        // Ne peut supprimer que si le message est encore en brouillon
        abort_if($message->statut !== 'brouillon', 403, 'Impossible de modifier un message déjà envoyé.');

        Storage::disk('local')->delete($piece->chemin);
        $piece->delete();

        return back()->with('success', 'Pièce jointe supprimée.');
    }

    public function destroy(MessageClient $message)
    {
        // Supprimer les fichiers physiques
        foreach ($message->piecesJointes as $pj) {
            Storage::disk('local')->delete($pj->chemin);
        }
        // Supprimer le dossier s'il est vide
        Storage::disk('local')->deleteDirectory("messages/{$message->id}");

        $message->delete();
        return redirect()->route('secretariat.messages.index')->with('success', 'Message supprimé.');
    }

    private function envoyerMessage(MessageClient $message): void
    {
        $message->load('destinataires.client', 'piecesJointes');
        $succes = 0;
        $echecs = 0;

        foreach ($message->destinataires as $dest) {
            // Envoi email
            if (in_array($message->canal, ['email', 'email_sms']) && $dest->email_copie) {
                try {
                    Mail::to($dest->email_copie)->send(new MessageClientMail($message, $dest->client->nom));
                    $dest->update(['statut_email' => 'envoye']);
                    $succes++;
                } catch (\Exception $e) {
                    $dest->update(['statut_email' => 'echoue']);
                    $echecs++;
                }
            } else {
                $dest->update(['statut_email' => 'non_concerne']);
            }

            // SMS — nécessite API externe
            if (in_array($message->canal, ['sms', 'email_sms']) && $dest->telephone_copie) {
                $dest->update(['statut_sms' => 'echoue']); // TODO: brancher API SMS
            } else {
                $dest->update(['statut_sms' => 'non_concerne']);
            }
        }

        $statutFinal = match(true) {
            $echecs === 0 && $succes > 0 => 'envoye',
            $succes > 0 && $echecs > 0   => 'partiellement_envoye',
            default                       => 'echoue',
        };

        $message->update(['statut' => $statutFinal, 'envoye_le' => now()]);
    }
}
