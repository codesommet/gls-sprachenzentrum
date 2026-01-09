<p>Nouvelle demande de consultation reçue :</p>

<ul>
    <li><strong>Nom :</strong> {{ $consultation->name }}</li>
    <li><strong>Ville :</strong> {{ $consultation->city }}</li>
    <li><strong>Téléphone :</strong> {{ $consultation->phone }}</li>
    <li><strong>Email :</strong> {{ $consultation->email }}</li>
    <li><strong>Date :</strong> {{ $consultation->created_at->format('d/m/Y H:i') }}</li>
</ul>
