{{-- resources/views/frontoffice/templates/group-apply-modals.blade.php --}}

<link rel="stylesheet" href="{{ asset('assets/css/gls-form.css') }}">

@php
    $applyGroups = $applyGroups ?? collect();
@endphp

<div class="modal fade" id="glsApplyGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: transparent; border: 0;">
            <div class="modal-body p-0">

                <div class="container" id="glsApplyRoot">

                    <div class="form-card">

                        <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close">✕</button>

                        <div class="decorative-element"></div>

                        <div class="form-content">

                            <div class="form-header" id="applyGroupFormHeader">
                                <h1 class="form-title">Apply – Groupe GLS</h1>
                                <p class="form-subtitle">Votre demande sera envoyée avec le groupe sélectionné</p>
                            </div>

                            {{-- ✅ ONE message box only (used for errors + duplicate) --}}
                            <div class="error-message" id="applyGroupErrorMessage"></div>

                            <form id="applyGroupForm" method="POST" action="{{ route('front.groups.apply') }}">
                                @csrf

                                <input type="hidden" name="group_id" id="applyGroupId" value="">

                                <div class="form-group">
                                    <label>Selected group</label>
                                    <input type="text" id="applyGroupLabel" readonly placeholder="Auto rempli">
                                </div>

                                <div class="form-group">
                                    <label>Schedule</label>
                                    <input type="text" id="applyGroupSchedule" readonly placeholder="Auto rempli">
                                </div>

                                <div class="form-group">
                                    <label>Level</label>
                                    <input type="text" id="applyGroupLevel" readonly placeholder="Auto rempli">
                                </div>

                                <div class="form-group">
                                    <label for="applyFullName">Full name <span class="required">*</span></label>
                                    <input type="text" id="applyFullName" name="full_name"
                                        placeholder="Your full name" required>
                                </div>

                                <div class="form-group">
                                    <label for="apply_email">Email <span class="required">*</span></label>
                                    <input type="email" id="apply_email" name="email"
                                        placeholder="email@example.com" required>
                                </div>

                                <div class="form-group">
                                    <label for="applyPhone">Phone <span class="required">*</span></label>
                                    <input type="tel" id="applyPhone" name="phone" placeholder="+212 6xx-xxxxxx"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label for="applyAddress">Address</label>
                                    <input type="text" id="applyAddress" name="address"
                                        placeholder="City, street...">
                                </div>

                                <div class="form-group">
                                    <label for="applyBirthday">Date de naissance</label>
                                    <input type="date" id="applyBirthday" name="birthday"
                                        max="{{ now()->subYears(10)->format('Y-m-d') }}">
                                </div>

                                <div class="form-group">
                                    <label for="applyNote">Note</label>
                                    <textarea id="applyNote" name="note" placeholder="Optional message..."></textarea>
                                </div>

                                <div class="button-group">
                                    <button type="button" class="button" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="button" id="applyGroupSubmitBtn">Send</button>
                                </div>
                            </form>

                            {{-- ✅ Success UI --}}
                            <div class="success-message" id="applyGroupSuccessMessage">
                                <div class="success-icon"></div>
                                <h3>Merci !</h3>
                                <p id="applyGroupSuccessText">Votre demande a bien été envoyée. Notre équipe vous
                                    contactera
                                    sous peu.</p>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


{{-- Fallback data (si tu veux ouvrir via ?group=ID) --}}
<select id="applyGroupsData" class="d-none">
    @foreach ($applyGroups as $g)
        <option value="{{ $g->id }}" data-label="{{ $g->name_fr ?? ($g->name ?? 'Groupe #' . $g->id) }}"
            data-schedule="{{ $g->period_label ?? ($g->period ?? '') }}"
            data-level="{{ $g->level ?? ($g->niveau ?? '') }}">
        </option>
    @endforeach
</select>

<script src="{{ asset('assets/js/apply-group.js') }}" defer></script>
