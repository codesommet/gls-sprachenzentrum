

<section class="gls-schedule-section reveal delay-1">
    <div class="gls-schedule-container reveal delay-2">

        <h2 class="gls-schedule-main-title fade-blur-title reveal delay-3">
            {{ __('sites/online.groups.title') }}
        </h2>

        @php
            $periods = [
                'morning'   => __('sites/online.groups.morning'),
                'midday'    => __('sites/online.groups.midday'),
                'afternoon' => __('sites/online.groups.afternoon'),
                'evening'   => __('sites/online.groups.evening'),
            ];

            $groupNameField = 'name_fr';
        @endphp

        @foreach ($periods as $key => $label)

            @php $collection = $groups[$key] ?? collect(); @endphp

            <div class="schedule-dropdown reveal delay-1">

                <div class="schedule-dropdown_trigger reveal delay-2">
                    <h2 class="heading-5 fade-blur-title reveal delay-3">
                        {{ $label }}
                    </h2>

                    <div class="dropdown-icon reveal delay-1">
                        <div class="dropdown-line"></div>
                        <div class="dropdown-line is-rotated"></div>
                    </div>
                </div>

                <div class="schedule-dropdown_content reveal delay-2">
                    <div class="schedule-dropdown_height reveal delay-3">

                        <div class="price-table-rich-text reveal delay-1">

                            <div class="table-rich-text reveal delay-2">
                                <p>
                                    <strong>{{ __('sites/online.groups.active') }}</strong>
                                </p>

                                @forelse ($collection->where('status', 'active') as $group)
                                    <p class="reveal delay-1">
                                        {{ data_get($group, $groupNameField) ?? $group->name }}
                                        – {{ strtoupper($group->level) }}
                                        – {{ $group->time_range }}
                                    </p>
                                @empty
                                    <p class="reveal delay-1">
                                        {{ __('Aucun groupe actif') }}
                                    </p>
                                @endforelse
                            </div>

                            <div class="table-rich-text reveal delay-3">
                                <p>
                                    <strong>{{ __('sites/online.groups.upcoming') }}</strong>
                                </p>

                                @forelse ($collection->where('status', 'upcoming') as $group)
                                    <p class="reveal delay-1">
                                        {{ data_get($group, $groupNameField) ?? $group->name }}
                                        – {{ strtoupper($group->level) }}
                                        – {{ $group->time_range }}
                                    </p>
                                @empty
                                    <p class="reveal delay-1">
                                        {{ __('Pas de nouveaux groupes prévus') }}
                                    </p>
                                @endforelse
                            </div>

                        </div>

                    </div>
                </div>

            </div>

        @endforeach

    </div>
</section>

