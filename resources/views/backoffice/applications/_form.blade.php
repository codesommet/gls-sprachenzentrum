{{-- Applicant Info --}}
<div class="row mb-4">
    <h6 class="mb-3">Informations du candidat</h6>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="full_name">Nom complet <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('full_name') is-invalid @enderror"
            id="full_name" name="full_name"
            value="{{ old('full_name', $application->full_name ?? '') }}" required>
        @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
        <input type="email" class="form-control @error('email') is-invalid @enderror"
            id="email" name="email"
            value="{{ old('email', $application->email ?? '') }}" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="whatsapp_number">WhatsApp <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('whatsapp_number') is-invalid @enderror"
            id="whatsapp_number" name="whatsapp_number"
            value="{{ old('whatsapp_number', $application->whatsapp_number ?? '') }}" required>
        @error('whatsapp_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="birthday">Date de naissance</label>
        <input type="date" class="form-control @error('birthday') is-invalid @enderror"
            id="birthday" name="birthday"
            value="{{ old('birthday', isset($application) && $application->birthday ? $application->birthday->format('Y-m-d') : '') }}">
        @error('birthday') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Group Selection --}}
<div class="row mb-4">
    <h6 class="mb-3">Groupe</h6>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="group_id">Groupe <span class="text-danger">*</span></label>
        <select class="form-select @error('group_id') is-invalid @enderror" id="group_id" name="group_id" required>
            <option value="">-- Sélectionner un groupe --</option>
            @foreach($groups as $group)
                <option value="{{ $group->id }}" @selected(old('group_id', $application->group_id ?? '') == $group->id)>
                    {{ $group->name ?? $group->name_fr }} — {{ $group->site?->name ?? 'N/A' }} ({{ $group->level ?? 'N/A' }})
                </option>
            @endforeach
        </select>
        @error('group_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="status">Statut</label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
            <option value="pending" @selected(old('status', $application->status ?? 'pending') === 'pending')>En attente</option>
            <option value="approved" @selected(old('status', $application->status ?? '') === 'approved')>Approuvé</option>
            <option value="rejected" @selected(old('status', $application->status ?? '') === 'rejected')>Rejeté</option>
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Notes --}}
<div class="row mb-3">
    <div class="col-12">
        <label class="form-label" for="note">Notes</label>
        <textarea class="form-control @error('note') is-invalid @enderror"
            id="note" name="note" rows="3">{{ old('note', $application->note ?? '') }}</textarea>
        @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
