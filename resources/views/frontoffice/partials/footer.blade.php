<footer class="site-footer mt-5 {{ app()->getLocale() == 'ar' ? 'rtl' : '' }}">
    <div class="container footer-inner py-5">

        {{-- ===== Footer Intro (with GLS Logo) ===== --}}
        <div class="footer-intro mb-4 {{ app()->getLocale() == 'ar' ? 'text-end' : '' }}">
            <p>
                {{ __('footer.intro_text') }}
            </p>
            <img src="{{ asset('assets/images/logo/gls-blanc.webp') }}" alt="GLS Sprachenzentrum Logo">
        </div>

        {{-- ===== Newsletter ===== --}}
        <div class="col align-items-center mb-4">
            <div class="col-12 col-md-6 {{ app()->getLocale() == 'ar' ? 'text-end' : '' }}">
                <h6 class="footer-title mb-2">{{ __('footer.newsletter.title') }}</h6>
                <p class="mb-0 mb-4 mt-4 small">{{ __('footer.newsletter.text') }}</p>
            </div>

            <div class="col-12 col-md-6 mt-3 mt-md-0">
                {{-- wrapper pour limiter la largeur comme sur ton screenshot --}}
                <div style="max-width: 520px; {{ app()->getLocale() == 'ar' ? 'margin-left:auto;' : '' }}">
                    <form id="newsletterForm" class="d-flex gap-2" action="{{ route('newsletter.subscribe') }}"
                        method="POST">
                        @csrf
                        <input type="hidden" name="source" value="footer">

                        <input id="newsletterEmail" type="email" name="email" class="form-control"
                            placeholder="{{ __('footer.newsletter.placeholder') }}" required autocomplete="email">

                        <button id="newsletterBtn" type="submit" class="btn btn-light">
                            {{ __('footer.newsletter.button') }}
                        </button>
                    </form>

                    <div id="newsletterMsg" class="small mt-2"></div>
                </div>
            </div>
        </div>

        {{-- ===== Footer Columns ===== --}}
        <div class="row footer-columns pt-4 border-top border-dark">

            {{-- Column 1 – About --}}
            <div class="col-6 col-md-3 mb-4">
                <h6 class="footer-title">{{ __('footer.about') }}</h6>
                <ul class="list-unstyled footer-links">
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.about')) }}">
                            {{ __('footer.about_us') }}
                        </a>
                    </li>

                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.faq')) }}">
                            {{ __('footer.faq') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.contact')) }}">
                            {{ __('footer.contact') }}
                        </a>
                    </li>

                    {{-- <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.partners.fc_marokko')) }}">
                            {{ __('footer.partner_fc_marokko') }}
                        </a>
                    </li> --}}

                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.discover-your-level')) }}">
                            {{ __('footer.discover_your_level') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 2 – Our Sites (All cities) --}}
            <div class="col-6 col-md-3 mb-4">
                <h6 class="footer-title">{{ __('footer.our_sites') }}</h6>
                <ul class="list-unstyled footer-links">
                    {{-- IMPORTANT: /sites/{slug} --}}
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.sites.show', 'casablanca')) }}">
                            {{ __('footer.sites.casablanca') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.sites.show', 'marrakech')) }}">
                            {{ __('footer.sites.marrakech') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.sites.show', 'rabat')) }}">
                            {{ __('footer.sites.rabat') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.sites.show', 'kenitra')) }}">
                            {{ __('footer.sites.kenitra') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.sites.show', 'sale')) }}">
                            {{ __('footer.sites.sale') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.sites.show', 'agadir')) }}">
                            {{ __('footer.sites.agadir') }}
                        </a>
                    </li>

                    {{-- Online (redirects already exist) --}}
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.online-courses')) }}">
                            {{ __('footer.sites.online') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 3 – Courses + Levels A1-B2 --}}
            <div class="col-6 col-md-3 mb-4">
                <h6 class="footer-title">{{ __('footer.german_courses') }}</h6>
                <ul class="list-unstyled footer-links">
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.intensive-courses')) }}">
                            {{ __('footer.intensive_courses') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.online-courses')) }}">
                            {{ __('footer.online_courses') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.pricing')) }}">
                            {{ __('footer.pricing') }}
                        </a>
                    </li>

                    <li class="mt-2">
                        <span class="footer-title d-block mb-1" style="font-size: 0.95rem;">
                            {{ __('footer.levels') }}
                        </span>
                    </li>

                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.a1')) }}">A1</a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.a2')) }}">A2</a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.b1')) }}">B1</a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.niveaux.b2')) }}">B2</a>
                    </li>
                </ul>
            </div>

            {{-- Column 4 – Exams + Resources --}}
            <div class="col-6 col-md-3 mb-4">
                <h6 class="footer-title">{{ __('footer.resources') }}</h6>
                <ul class="list-unstyled footer-links">
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.exams.gls')) }}">
                            {{ __('footer.exams_gls') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.exams.osd')) }}">
                            {{ __('footer.exams_osd') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.exams.goethe')) }}">
                            {{ __('footer.exams_goethe') }}
                        </a>
                    </li>

                    <li class="mt-2">
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.studienkollegs')) }}">
                            {{ __('footer.studienkollegs') }}
                        </a>
                    </li>

                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('blog.index')) }}">
                            {{ __('footer.blog') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.student-stories')) }}">
                            {{ __('footer.student_stories') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ LaravelLocalization::localizeUrl(route('front.certificate.check')) }}">
                            {{ __('footer.certificate_check') }}
                        </a>
                    </li>
                </ul>
            </div>


        </div>
    </div>

    {{-- ===== Footer Bottom ===== --}}
    <div class="footer-bottom">
        <div
            class="container d-flex flex-column flex-md-row justify-content-between align-items-center py-3 small text-center text-md-start">
            <div class="footer-legal {{ app()->getLocale() == 'ar' ? 'text-end' : '' }}">
                <a href="{{ LaravelLocalization::localizeUrl(route('front.terms')) }}">
                    {{ __('footer.terms') }}
                </a>

                <a href="{{ LaravelLocalization::localizeUrl(route('front.privacy')) }}">
                    {{ __('footer.privacy') }}
                </a>

                <a href="#" data-open-cookies>{{ __('footer.cookies') }}</a>

            </div>

            <div class="footer-brand mt-2 mt-md-0">
                {{ __('footer.copyright') }}
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/newsletter.js') }}"></script>
    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {},
            Tawk_LoadStart = new Date();
        (function() {
            var s1 = document.createElement("script"),
                s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = 'https://embed.tawk.to/69666fd7d7f0511983c59b92/1jes29osr';
            s1.charset = 'UTF-8';
            s1.setAttribute('crossorigin', '*');
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
    <!--End of Tawk.to Script-->

</footer>
