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
                                <h1 class="form-title">{{ __('templates/group-apply-modals.header.title') }}</h1>
                                <p class="form-subtitle">{{ __('templates/group-apply-modals.header.subtitle') }}</p>
                            </div>

                            {{-- ✅ ONE message box only (used for errors + duplicate) --}}
                            <div class="error-message" id="applyGroupErrorMessage"></div>

                            <form id="applyGroupForm" method="POST" action="{{ route('front.groups.apply') }}">
                                @csrf

                                <input type="hidden" name="group_id" id="applyGroupId" value="">

                                <div class="form-group">
                                    <label>{{ __('templates/group-apply-modals.fields.group.label') }}</label>
                                    <input type="text" id="applyGroupLabel" readonly
                                        placeholder="{{ __('templates/group-apply-modals.fields.group.placeholder') }}">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('templates/group-apply-modals.fields.schedule.label') }}</label>
                                    <input type="text" id="applyGroupSchedule" readonly
                                        placeholder="{{ __('templates/group-apply-modals.fields.schedule.placeholder') }}">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('templates/group-apply-modals.fields.level.label') }}</label>
                                    <input type="text" id="applyGroupLevel" readonly
                                        placeholder="{{ __('templates/group-apply-modals.fields.level.placeholder') }}">
                                </div>

                                <div class="form-group">
                                    <label
                                        for="applyFullName">{{ __('templates/group-apply-modals.fields.full_name.label') }}
                                        <span class="required">*</span></label>
                                    <input type="text" id="applyFullName" name="full_name"
                                        placeholder="{{ __('templates/group-apply-modals.fields.full_name.placeholder') }}"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label
                                        for="apply_email">{{ __('templates/group-apply-modals.fields.email.label') }}
                                        <span class="required">*</span></label>
                                    <input type="email" id="apply_email" name="email"
                                        placeholder="{{ __('templates/group-apply-modals.fields.email.placeholder') }}"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label for="applyPhone">{{ __('templates/group-apply-modals.fields.phone.label') }}
                                        <span class="required">*</span></label>
                                    <input type="tel" id="applyPhone" name="phone"
                                        placeholder="{{ __('templates/group-apply-modals.fields.phone.placeholder') }}"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label
                                        for="applyAddress">{{ __('templates/group-apply-modals.fields.address.label') }}</label>
                                    <input type="text" id="applyAddress" name="address"
                                        placeholder="{{ __('templates/group-apply-modals.fields.address.placeholder') }}">
                                </div>

                                <div class="form-group">
                                    <label
                                        for="applyBirthday">{{ __('templates/group-apply-modals.fields.birthday.label') }}</label>
                                    <input type="date" id="applyBirthday" name="birthday"
                                        max="{{ now()->subYears(10)->format('Y-m-d') }}">
                                </div>

                                <div class="form-group">
                                    <label
                                        for="applyNote">{{ __('templates/group-apply-modals.fields.note.label') }}</label>
                                    <textarea id="applyNote" name="note" placeholder="{{ __('templates/group-apply-modals.fields.note.placeholder') }}"></textarea>
                                </div>

                                <div class="button-group">
                                    <button type="button" class="button"
                                        data-bs-dismiss="modal">{{ __('templates/group-apply-modals.buttons.cancel') }}</button>
                                    <button type="submit" class="button"
                                        id="applyGroupSubmitBtn">{{ __('templates/group-apply-modals.buttons.submit') }}</button>
                                </div>
                            </form>

                            {{-- ✅ Success UI --}}
                            <div class="success-message" id="applyGroupSuccessMessage">
                                <div class="success-icon"></div>
                                <h3>{{ __('templates/group-apply-modals.messages.success_title') }}</h3>
                                <p id="applyGroupSuccessText">
                                    {{ __('templates/group-apply-modals.messages.success_text') }}</p>
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
