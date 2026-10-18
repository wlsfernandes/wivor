@extends('frontend.v1.layouts.app')

@php
    $isFaceSearchEnabled = (bool) config('face_recognition.enabled');
    $supportEmail = e(config('contact.email'));
    $faqItems = array_values(array_filter([
        ['question' => 'Is WiVor really free?', 'answerHtml' => "Yes. It's free for photographers, event organizers and athletes. Athletes only pay if they like their photos and choose to buy them."],
        ['question' => 'How does WiVor find my photos?', 'answerHtml' => $isFaceSearchEnabled
            ? 'We use bib number recognition and face recognition. Enter your bib number, upload a selfie, or both.'
            : 'We use bib number recognition. Enter your bib number.'],
        ['question' => 'Do I have to buy photos?', 'answerHtml' => 'No. You can browse your photos for free and buy only the ones you want.'],
        ['question' => 'How do I receive my photos?', 'answerHtml' => 'After purchase, you can download high-resolution files right away.'],
        ['question' => "What if I can't find my photos?", 'answerHtml' => 'Photographers may still be uploading. Check back in a day or two, or contact us at <a class="wv-link" href="mailto:'.$supportEmail.'">'.$supportEmail.'</a>.'],
        $isFaceSearchEnabled
            ? ['question' => 'How is my face data used and protected?', 'answerHtml' => 'Your selfie is used only to match you with your event photos. Read our <a class="wv-link" href="'.e(route('biometric-notice')).'">Biometric Notice</a>.']
            : null,
        ['question' => 'How do photographers get paid?', 'answerHtml' => 'WiVor handles payments. When an athlete buys a photo, the photographer receives their share.'],
        ['question' => 'How can my event partner with WiVor?', 'answerHtml' => 'Fill out the form on our <a class="wv-link" href="'.e(route('organizers')).'">Organizers page</a> and we\'ll get in touch.'],
    ]));
@endphp

@section('title', 'FAQ | WiVor')
@section('meta-description', 'Answers about finding, previewing and buying your event photos on WiVor, and how photographers and event organizers work with WiVor.')

@section('structured-data')
    @php
        $faqStructuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $item): array => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answerHtml']],
            ], $faqItems),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($faqStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection

@section('content')
    <section class="wv-section wv-section--white">
        <div class="wv-container">
            <h1 class="wv-h1">Frequently Asked Questions</h1>

            <div class="wv-faq">
                @foreach ($faqItems as $faqItem)
                    <details>
                        <summary>{!! $faqItem['question'] !!}</summary>
                        <div class="wv-body">{!! $faqItem['answerHtml'] !!}</div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endsection
