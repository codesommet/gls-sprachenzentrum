<p>Bonjour {{ $consultation->name }},</p>

<p>Nous avons bien reçu votre demande de consultation.</p>

<p><strong>Récapitulatif :</strong></p>
<ul>
    <li>Ville : {{ $consultation->city }}</li>
    <li>Téléphone : {{ $consultation->phone }}</li>
    <li>Email : {{ $consultation->email }}</li>
    <li>Date : {{ $consultation->created_at->format('d/m/Y H:i') }}</li>
</ul>

<p>Notre équipe vous contactera très bientôt.</p>

<p>Cordialement,<br>GLS</p>
