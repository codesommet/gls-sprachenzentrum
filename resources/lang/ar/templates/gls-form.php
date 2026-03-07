<?php

return [
    'header' => [
        'title' => 'التسجيل في GLS',
        'subtitle' => 'أكملوا الخطوات لإرسال طلبكم',
    ],

    'progress' => [
        'steps' => [
            'step1' => 'المعلومات',
            'step2' => 'مركز GLS',
            'step3' => 'المجموعة',
            'step4' => 'التفضيلات',
        ],
    ],

    'fields' => [
        'name' => [
            'label' => 'الاسم الكامل',
            'placeholder' => 'اسمكم الكامل',
        ],
        'email' => [
            'label' => 'البريد الإلكتروني',
            'placeholder' => 'email@example.com',
        ],
        'phone' => [
            'label' => 'الهاتف',
            'placeholder' => '+212 650-123456',
        ],
        'adresse' => [
            'label' => 'العنوان',
            'placeholder' => 'عنوانكم الكامل',
        ],
        'type_cours' => [
            'label' => 'نوع الدورة',
            'placeholder' => 'اختاروا نوعًا',
        ],
        'type_cours_options' => [
            'presentiel' => 'دورة حضورية',
            'en_ligne' => 'دورة عبر الإنترنت',
        ],
        'centre' => [
            'label' => 'مركز GLS المفضل',
            'placeholder' => 'اختاروا مركزًا',
        ],
        'group_id' => [
            'label' => 'المجموعة',
            'placeholder' => 'اختاروا مجموعة',
        ],
        'niveau' => [
            'label' => 'مستوى اللغة الألمانية',
            'placeholder' => 'اختاروا مستوى',
        ],
        'horaire_prefere' => [
            'label' => 'جدول الدورة',
            'placeholder' => 'يُملأ تلقائيًا',
        ],
        'date_start' => [
            'label' => 'ابتداءً من...',
            'placeholder' => 'اختاروا تاريخًا',
        ],
        'accept_terms' => [
            'label' => 'أوافق على',
            'link' => 'الشروط العامة',
        ],
    ],

    'buttons' => [
        'prev' => 'رجوع',
        'next' => 'متابعة',
        'submit' => 'إرسال',
        'sending' => 'جارٍ الإرسال...',
        'cancel' => 'إلغاء',
        'back_home' => 'العودة إلى الرئيسية',
    ],

    'messages' => [
        'success_title' => 'شكرًا لكم!',
        'success_text' => 'تم إرسال طلبكم بنجاح. سيتواصل معكم فريقنا قريبًا.',
    ],

    'errors' => [
        'required_fields' => 'يرجى ملء جميع الحقول المطلوبة.',
        'duplicate' => 'لقد قدمتم طلبًا بالفعل لهذه المجموعة.',
        'server_error' => 'خطأ في الخادم. يرجى المحاولة مرة أخرى.',
        'connection_error' => 'خطأ في الاتصال. يرجى المحاولة مرة أخرى.',
        'generic' => 'حدث خطأ.',
        'session_expired' => 'انتهت الجلسة. يرجى إعادة تحميل الصفحة.',
        'check_fields' => 'يرجى التحقق من حقول النموذج.',
    ],

    'js' => [
        'loading' => 'جارٍ التحميل...',
        'error_loading' => 'خطأ في التحميل',
        'select_level' => 'اختاروا مستوى',
        'select_center' => 'اختاروا مركزًا',
        'select_group' => 'اختاروا مجموعة',
        'select_date' => 'اختاروا تاريخًا',
        'group_label' => 'مجموعة',
        'group_night_label' => 'مجموعة ليلية',
    ],
];
