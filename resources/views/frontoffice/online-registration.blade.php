@extends('frontoffice.layouts.app')

@section('content')
    <main class="online-registration-page">
        <!-- ===========================
            HERO/BREADCRUMB SECTION
            =========================== -->
        <section class="page-header reveal delay-1" aria-label="Registration">
            <div class="page-header__bg reveal delay-2"
                style="background-image: url('{{ asset('assets/images/IMG_4399.webp') }}');">
            </div>

            <div class="page-header__inner text-center {{ app()->getLocale() == 'ar' ? 'rtl' : '' }} reveal delay-1">
                <h1 class="page-title reveal fade-blur-title delay-1">
                    Online Registration
                </h1>
                <p class="page-subtitle reveal delay-2">
                    Join our German Language Courses
                </p>
            </div>
        </section>

        <!-- ===========================
            REGISTRATION FORM SECTION
            =========================== -->
        <section class="registration-section section py-5">
            <div class="container reveal delay-1">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <!-- Introduction Card -->
                        <div class="registration-intro-card card shadow-sm rounded-4 p-5 mb-5 reveal delay-2">
                            <h2 class="h4 mb-3 reveal fade-blur-title delay-1">
                                Register for a German Course
                            </h2>
                            <p class="lead text-muted mb-0 reveal delay-2">
                                Fill out the form below to get started with GLS. Our team will contact you shortly to
                                confirm your registration and discuss the best course option for you.
                            </p>
                        </div>

                        <!-- Registration Form -->
                        <form action="{{ route('front.online-registration.store') }}" method="POST"
                            class="registration-form reveal delay-3">
                            @csrf

                            <!-- Full Name -->
                            <div class="form-group mb-4 reveal delay-1">
                                <label for="fullName" class="form-label fw-semibold">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg @error('full_name') is-invalid @enderror"
                                    id="fullName" name="full_name" placeholder="Enter your full name"
                                    value="{{ old('full_name') }}" required>
                                @error('full_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-group mb-4 reveal delay-2">
                                <label for="email" class="form-label fw-semibold">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                    class="form-control form-control-lg @error('email') is-invalid @enderror" id="email"
                                    name="email" placeholder="Enter your email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone Number -->
                            <div class="form-group mb-4 reveal delay-3">
                                <label for="phone" class="form-label fw-semibold">
                                    Phone Number <span class="text-danger">*</span>
                                </label>
                                <input type="tel"
                                    class="form-control form-control-lg @error('phone') is-invalid @enderror" id="phone"
                                    name="phone" placeholder="Enter your phone number" value="{{ old('phone') }}"
                                    required>
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Language Level -->
                            <div class="form-group mb-4 reveal delay-1">
                                <label for="level" class="form-label fw-semibold">
                                    Language Level <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg @error('level') is-invalid @enderror"
                                    id="level" name="level" required>
                                    <option value="">-- Select your level --</option>
                                    <option value="A1" {{ old('level') === 'A1' ? 'selected' : '' }}>A1 - Beginner
                                    </option>
                                    <option value="A2" {{ old('level') === 'A2' ? 'selected' : '' }}>A2 - Elementary
                                    </option>
                                    <option value="B1" {{ old('level') === 'B1' ? 'selected' : '' }}>B1 - Intermediate
                                    </option>
                                    <option value="B2" {{ old('level') === 'B2' ? 'selected' : '' }}>B2 - Upper
                                        Intermediate</option>
                                    <option value="no-idea" {{ old('level') === 'no-idea' ? 'selected' : '' }}>I'm not sure
                                    </option>
                                </select>
                                @error('level')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Course Type -->
                            <div class="form-group mb-4 reveal delay-2">
                                <label for="courseType" class="form-label fw-semibold">
                                    Course Type <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg @error('course_type') is-invalid @enderror"
                                    id="courseType" name="course_type" required>
                                    <option value="">-- Select course type --</option>
                                    <option value="intensive" {{ old('course_type') === 'intensive' ? 'selected' : '' }}>
                                        Intensive Course</option>
                                    <option value="online" {{ old('course_type') === 'online' ? 'selected' : '' }}>Online
                                        Course</option>
                                    <option value="exam-prep" {{ old('course_type') === 'exam-prep' ? 'selected' : '' }}>
                                        Exam Preparation</option>
                                </select>
                                @error('course_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Message -->
                            <div class="form-group mb-4 reveal delay-3">
                                <label for="message" class="form-label fw-semibold">
                                    Additional Message
                                </label>
                                <textarea class="form-control form-control-lg @error('message') is-invalid @enderror" id="message" name="message"
                                    rows="5" placeholder="Tell us about your language learning goals...">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Checkbox: Terms & Conditions -->
                            <div class="form-check mb-5 reveal delay-1">
                                <input class="form-check-input @error('accept_terms') is-invalid @enderror" type="checkbox"
                                    id="acceptTerms" name="accept_terms" value="1"
                                    {{ old('accept_terms') ? 'checked' : '' }}>
                                <label class="form-check-label" for="acceptTerms">
                                    I accept the <a href="{{ LaravelLocalization::localizeUrl(route('front.terms')) }}"
                                        target="_blank" class="text-decoration-none">Terms & Conditions</a>
                                    <span class="text-danger">*</span>
                                </label>
                                @error('accept_terms')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2 reveal delay-2">
                                <button type="submit" class="btn btn-success btn-lg py-3 rounded-pill fw-semibold">
                                    Register Now
                                </button>
                            </div>

                            <!-- Back Link -->
                            <div class="text-center mt-4 reveal delay-3">
                                <a href="{{ LaravelLocalization::localizeUrl(route('front.home')) }}"
                                    class="text-muted text-decoration-none">
                                    &larr; Back to Home
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===========================
            WHY CHOOSE GLS SECTION
            =========================== -->
        <section class="why-choose-gls section py-5" style="background-color: #f8f9fa;">
            <div class="container reveal delay-1">
                <h2 class="h-section-subtitle text-center mb-5 reveal fade-blur-title delay-2">
                    Why Choose GLS?
                </h2>

                <div class="row g-4">
                    <!-- Benefit 1 -->
                    <div class="col-md-6 col-lg-3 reveal delay-3">
                        <div class="benefit-card text-center p-4 rounded-4 h-100">
                            <div class="benefit-icon mb-3">
                                <i class="fas fa-chalkboard-user fa-3x text-success"></i>
                            </div>
                            <h5 class="benefit-title fw-semibold mb-2">Expert Teachers</h5>
                            <p class="benefit-text text-muted">Certified and passionate German language instructors</p>
                        </div>
                    </div>

                    <!-- Benefit 2 -->
                    <div class="col-md-6 col-lg-3 reveal delay-1">
                        <div class="benefit-card text-center p-4 rounded-4 h-100">
                            <div class="benefit-icon mb-3">
                                <i class="fas fa-laptop fa-3x text-primary"></i>
                            </div>
                            <h5 class="benefit-title fw-semibold mb-2">Flexible Learning</h5>
                            <p class="benefit-text text-muted">Online and in-person courses tailored to your schedule</p>
                        </div>
                    </div>

                    <!-- Benefit 3 -->
                    <div class="col-md-6 col-lg-3 reveal delay-2">
                        <div class="benefit-card text-center p-4 rounded-4 h-100">
                            <div class="benefit-icon mb-3">
                                <i class="fas fa-certificate fa-3x text-warning"></i>
                            </div>
                            <h5 class="benefit-title fw-semibold mb-2">Certification</h5>
                            <p class="benefit-text text-muted">Recognized certifications from GLS and Goethe</p>
                        </div>
                    </div>

                    <!-- Benefit 4 -->
                    <div class="col-md-6 col-lg-3 reveal delay-3">
                        <div class="benefit-card text-center p-4 rounded-4 h-100">
                            <div class="benefit-icon mb-3">
                                <i class="fas fa-handshake fa-3x text-info"></i>
                            </div>
                            <h5 class="benefit-title fw-semibold mb-2">Support</h5>
                            <p class="benefit-text text-muted">Personal support throughout your learning journey</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===========================
            FAQ SECTION
            =========================== -->
        <section class="registration-faq section py-5">
            <div class="container reveal delay-1">
                <h2 class="h-section-subtitle text-center mb-5 reveal fade-blur-title delay-2">
                    Frequently Asked Questions
                </h2>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="accordion accordion-flush reveal delay-3" id="registrationFAQ">
                            <!-- FAQ 1 -->
                            <div class="accordion-item reveal delay-1 mb-3 border rounded-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq1" aria-expanded="false">
                                        How long does it take to receive a response?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse"
                                    data-bs-parent="#registrationFAQ">
                                    <div class="accordion-body">
                                        We typically respond to registration inquiries within 24-48 hours during business
                                        days. Our team will contact you via email or phone with available course options.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ 2 -->
                            <div class="accordion-item reveal delay-2 mb-3 border rounded-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq2" aria-expanded="false">
                                        Can I change my course level later?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse"
                                    data-bs-parent="#registrationFAQ">
                                    <div class="accordion-body">
                                        Yes! If you find that your current level is too easy or too difficult, you can
                                        switch to a different level within the first week of your course.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ 3 -->
                            <div class="accordion-item reveal delay-3 mb-3 border rounded-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq3" aria-expanded="false">
                                        What if I'm not sure about my level?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse"
                                    data-bs-parent="#registrationFAQ">
                                    <div class="accordion-body">
                                        No problem! Select "I'm not sure" during registration, and our team will conduct a
                                        free level assessment with you to determine the perfect starting point.
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ 4 -->
                            <div class="accordion-item reveal delay-1 mb-3 border rounded-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faq4" aria-expanded="false">
                                        Are there any discounts available?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse"
                                    data-bs-parent="#registrationFAQ">
                                    <div class="accordion-body">
                                        We offer various discounts for early registration, group bookings, and returning
                                        students. Our team will discuss the best options for you during your consultation.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===========================
            CONTACT CTA SECTION
            =========================== -->
        <section class="registration-cta section py-5"
            style="background: linear-gradient(135deg, #1a472a 0%, #2d6a3e 100%);">
            <div class="container text-center reveal delay-1">
                <h2 class="h4 text-white mb-3 reveal fade-blur-title delay-2">
                    Still have questions?
                </h2>
                <p class="lead text-white mb-4 reveal delay-3">
                    Contact our team directly for personalized guidance
                </p>
                <a href="{{ LaravelLocalization::localizeUrl(route('front.contact')) }}"
                    class="btn btn-light btn-lg px-5 rounded-pill fw-semibold reveal delay-1">
                    Get in Touch
                </a>
            </div>
        </section>
    </main>
@endsection

<style>
    .benefit-card {
        background: white;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .benefit-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border-color: #28a745;
    }

    .benefit-icon {
        color: var(--light--green, #28a745);
    }

    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #1a472a;
        font-weight: 600;
    }

    .accordion-button:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
    }
</style>
