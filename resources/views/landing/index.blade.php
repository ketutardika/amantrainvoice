<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Create Professional Invoices Instantly</title>

    {{-- Basic SEO --}}
    <meta name="description" content="Create professional invoices instantly. Manage clients, track payments, and generate beautiful PDF invoices in seconds. Free to start.">
    <meta name="keywords" content="invoice, invoicing, invoice maker, pdf invoice, client management, payment tracking, invoice software, online invoicing">
    <meta name="robots" content="index, follow">
    <meta name="author" content="{{ config('app.name') }}">
    <link rel="canonical" href="{{ url('/') }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ config('app.name') }} — Create Professional Invoices Instantly">
    <meta property="og:description" content="Create professional invoices instantly. Manage clients, track payments, and generate beautiful PDF invoices in seconds. Free to start.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="https://instantinvoice.cloud/images/instant-invoice-demo.png">
    <meta property="og:image:alt" content="{{ config('app.name') }} dashboard preview">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name') }} — Create Professional Invoices Instantly">
    <meta name="twitter:description" content="Create professional invoices instantly. Manage clients, track payments, and generate beautiful PDF invoices in seconds. Free to start.">
    <meta name="twitter:image" content="https://instantinvoice.cloud/images/instant-invoice-demo.png">
    <meta name="twitter:image:alt" content="{{ config('app.name') }} dashboard preview">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: { DEFAULT: '#1e293b', light: '#334155', dark: '#0f172a' }
                    }
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        .gradient-text {
            background: linear-gradient(135deg, #334155 0%, #0f172a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.10);
            border-color: #cbd5e1;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }
        .float-anim { animation: float 4s ease-in-out infinite; }
    </style>
</head>
<body class="antialiased bg-[#F5F5F5] text-[#1A1A1A] font-sans">

{{-- ─── NAVIGATION ─────────────────────────────────────────────────────────── --}}
<nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-[#E8E8E8]">
    <div class="max-w-6xl mx-auto px-6 flex items-center justify-between h-16">

        {{-- Logo --}}
        <a href="/" class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-gradient-to-br from-slate-700 to-slate-900 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span class="font-bold text-slate-900 text-lg tracking-tight">{{ config('app.name') }}</span>
        </a>

        {{-- Links --}}
        <div class="hidden md:flex items-center gap-8 text-sm text-[#737373]">
            <a href="#features" class="hover:text-[#1A1A1A] transition-colors">Features</a>
            <a href="#how-it-works" class="hover:text-[#1A1A1A] transition-colors">How it works</a>
            <a href="/docs" class="hover:text-[#1A1A1A] transition-colors">Docs</a>
        </div>

        {{-- Auth CTA --}}
        <div class="flex items-center gap-3">
            <a href="/admin/login" class="hidden sm:block text-sm text-[#737373] hover:text-[#1A1A1A] transition-colors font-medium">Log in</a>
            <a href="/admin/register" class="px-4 py-2 bg-gradient-to-br from-slate-700 to-slate-900 text-white text-sm font-semibold rounded-lg hover:opacity-90 transition-all duration-200 shadow-sm">
                Get Started
            </a>
        </div>
    </div>
</nav>

{{-- ─── HERO ────────────────────────────────────────────────────────────────── --}}
<section class="pt-32 pb-24 px-6">
    <div class="max-w-5xl mx-auto">

        {{-- Badge --}}
        <div class="flex justify-center mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-[#E8E8E8] bg-white text-xs text-[#737373] font-medium shadow-sm">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full flex-shrink-0"></span>
                Free to start &mdash; No credit card required
            </div>
        </div>

        {{-- Headline --}}
        <h1 class="text-center text-5xl md:text-6xl lg:text-7xl font-extrabold text-[#1A1A1A] leading-[1.05] tracking-tight mb-6">
            Create Professional<br>
            <span class="gradient-text">Invoices Instantly</span>
        </h1>

        {{-- Subtitle --}}
        <p class="text-center text-lg md:text-xl text-[#737373] max-w-2xl mx-auto mb-10 leading-relaxed">
            Manage clients, track payments, and generate beautiful PDF invoices in seconds.
            Everything you need to get paid &mdash; fast.
        </p>

        {{-- CTAs --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-16">
            <a href="/admin/register"
               class="w-full sm:w-auto px-7 py-3.5 bg-gradient-to-br from-slate-700 to-slate-900 text-white font-semibold rounded-xl hover:opacity-90 transition-all duration-200 shadow-lg shadow-slate-900/20 hover:-translate-y-0.5 text-center">
                Start for Free &rarr;
            </a>
            <a href="/admin/login"
               class="w-full sm:w-auto px-7 py-3.5 bg-white border border-[#E8E8E8] text-[#1A1A1A] font-semibold rounded-xl hover:border-slate-300 hover:shadow-sm transition-all duration-200 text-center">
                Sign in to Dashboard
            </a>
        </div>

        {{-- Mock Invoice Card --}}
        <div class="flex justify-center">
            <div class="float-anim w-full max-w-md bg-white rounded-2xl shadow-2xl shadow-slate-900/10 border border-[#E8E8E8] overflow-hidden text-left">

                {{-- Invoice top bar --}}
                <div class="bg-gradient-to-br from-slate-700 to-slate-900 px-6 py-5 text-white">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-medium text-slate-400 uppercase tracking-widest mb-1">Invoice</p>
                            <p class="text-xl font-bold tracking-tight">#INV-2024-00042</p>
                        </div>
                        <span class="mt-1 px-2.5 py-1 bg-green-500/20 text-green-400 text-xs font-semibold rounded-full border border-green-500/30">
                            Paid
                        </span>
                    </div>
                </div>

                {{-- Invoice body --}}
                <div class="px-6 py-5">
                    <div class="flex justify-between mb-5">
                        <div>
                            <p class="text-xs text-[#737373] mb-0.5">Bill to</p>
                            <p class="text-sm font-semibold">Bali Creative Studio</p>
                            <p class="text-xs text-[#737373] mt-0.5">bali@creativestudio.id</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-[#737373] mb-0.5">Due date</p>
                            <p class="text-sm font-semibold">Mar 15, 2024</p>
                        </div>
                    </div>

                    {{-- Line items --}}
                    <div class="border-t border-[#E8E8E8] pt-4 space-y-2.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-[#737373]">Web Design Services</span>
                            <span class="font-medium">Rp 5,000,000</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-[#737373]">Monthly Maintenance</span>
                            <span class="font-medium">Rp 1,500,000</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-[#737373]">PPN 11%</span>
                            <span class="font-medium text-slate-500">Rp 715,000</span>
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="border-t border-[#E8E8E8] mt-4 pt-4 flex justify-between items-center">
                        <span class="font-semibold text-[#1A1A1A]">Total</span>
                        <span class="font-extrabold text-xl text-[#1A1A1A]">Rp 7,215,000</span>
                    </div>

                    {{-- Payment bar --}}
                    <div class="mt-4 flex gap-2">
                        <div class="flex-1 h-1.5 rounded-full bg-[#E8E8E8] overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                    <p class="text-xs text-green-600 font-medium mt-1.5">Fully paid</p>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- ─── FEATURES ────────────────────────────────────────────────────────────── --}}
<section id="features" class="py-20 px-6">
    <div class="max-w-6xl mx-auto">

        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-4xl font-bold text-[#1A1A1A] mb-3 tracking-tight">
                Everything you need to invoice like a pro
            </h2>
            <p class="text-[#737373] text-lg">Simple tools. Powerful results.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            @php
            $features = [
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>',
                    'title' => 'Instant Invoice Creation',
                    'desc'  => 'Fill in client details and line items — your invoice is ready in under 60 seconds. Auto-numbered, always organized.',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
                    'title' => 'Professional PDF Export',
                    'desc'  => 'Download or share a polished, print-ready PDF with your company logo, QR code, and itemized breakdown.',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>',
                    'title' => 'Multi-Client Management',
                    'desc'  => 'Keep all your clients in one place. Track every invoice, payment status, and outstanding balance per client.',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>',
                    'title' => 'Payment Tracking',
                    'desc'  => 'Record full or partial payments. Know instantly which invoices are paid, pending, or overdue.',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>',
                    'title' => 'Smart Invoice Numbering',
                    'desc'  => 'Auto-generated invoice numbers in your custom format (e.g., INV-2024-00001). Never duplicate or miss a number.',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/>',
                    'title' => 'Company Branding',
                    'desc'  => 'Add your logo, company name, tax ID, and bank details. Your invoice looks like you — not a template.',
                ],
            ];
            @endphp

            @foreach($features as $f)
            <div class="card-hover bg-white rounded-2xl border border-[#E8E8E8] p-6 shadow-sm cursor-default">
                <div class="w-11 h-11 bg-slate-100 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        {!! $f['icon'] !!}
                    </svg>
                </div>
                <h3 class="font-semibold text-[#1A1A1A] mb-2 text-[15px]">{{ $f['title'] }}</h3>
                <p class="text-sm text-[#737373] leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach

        </div>
    </div>
</section>

{{-- ─── HOW IT WORKS ────────────────────────────────────────────────────────── --}}
<section id="how-it-works" class="py-20 px-6 bg-white border-y border-[#E8E8E8]">
    <div class="max-w-4xl mx-auto">

        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-4xl font-bold text-[#1A1A1A] mb-3 tracking-tight">Get started in 3 steps</h2>
            <p class="text-[#737373] text-lg">From sign-up to your first invoice in minutes.</p>
        </div>

        <div class="space-y-4">

            @php
            $steps = [
                [
                    'n'     => '01',
                    'title' => 'Create your company',
                    'desc'  => 'Register and set up your company profile — name, logo, currency, and tax settings. Takes less than 2 minutes.',
                    'tag'   => 'Company Setup',
                ],
                [
                    'n'     => '02',
                    'title' => 'Add your clients',
                    'desc'  => 'Create client profiles with contact details. Reuse them across all your invoices and track their payment history.',
                    'tag'   => 'Client Management',
                ],
                [
                    'n'     => '03',
                    'title' => 'Create & send your invoice',
                    'desc'  => 'Add line items, apply taxes, and download a professional PDF. Share a public link your client can view anytime.',
                    'tag'   => 'Invoice & PDF',
                ],
            ];
            @endphp

            @foreach($steps as $step)
            <div class="flex gap-6 p-6 bg-[#F5F5F5] rounded-2xl border border-[#E8E8E8] items-start">
                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-slate-700 to-slate-900 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold text-sm tracking-tight">{{ $step['n'] }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <h3 class="font-semibold text-[#1A1A1A] text-[15px]">{{ $step['title'] }}</h3>
                        <span class="px-2 py-0.5 bg-white border border-[#E8E8E8] text-[#737373] text-xs rounded-full font-medium">{{ $step['tag'] }}</span>
                    </div>
                    <p class="text-sm text-[#737373] leading-relaxed">{{ $step['desc'] }}</p>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</section>

{{-- ─── PRICING (hidden) ────────────────────────────────────────────────────── --}}
{{-- Pricing section hidden — uncomment to restore
<section id="pricing" class="py-20 px-6">
    ...
</section>
--}}

{{-- ─── CTA BANNER ──────────────────────────────────────────────────────────── --}}
<section class="py-20 px-6 bg-white border-y border-[#E8E8E8]">
    <div class="max-w-2xl mx-auto text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-[#1A1A1A] mb-4 tracking-tight">
            Ready to get paid faster?
        </h2>
        <p class="text-[#737373] text-lg mb-8 leading-relaxed">
            Join businesses that use {{ config('app.name') }} to create and manage invoices effortlessly.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/admin/register"
               class="px-7 py-3.5 bg-gradient-to-br from-slate-700 to-slate-900 text-white font-semibold rounded-xl hover:opacity-90 transition-all hover:-translate-y-0.5 shadow-lg shadow-slate-900/20 duration-200">
                Create your free account &rarr;
            </a>
            <a href="/admin/login"
               class="px-7 py-3.5 border border-[#E8E8E8] text-[#1A1A1A] font-semibold rounded-xl hover:border-slate-300 hover:shadow-sm transition-all duration-200">
                Sign in
            </a>
        </div>
    </div>
</section>

{{-- ─── FOOTER ──────────────────────────────────────────────────────────────── --}}
<footer class="py-10 px-6 bg-[#F5F5F5]">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
        <a href="/" class="flex items-center gap-2">
            <div class="w-6 h-6 bg-gradient-to-br from-slate-700 to-slate-900 rounded-md flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span class="font-bold text-slate-700 text-sm">{{ config('app.name') }}</span>
        </a>

        <div class="flex items-center gap-6 text-xs text-[#737373]">
            <a href="#features" class="hover:text-[#1A1A1A] transition-colors">Features</a>
            <a href="#how-it-works" class="hover:text-[#1A1A1A] transition-colors">How it works</a>
            <a href="/docs" class="hover:text-[#1A1A1A] transition-colors">Docs</a>
            <a href="/admin/login" class="hover:text-[#1A1A1A] transition-colors">Log in</a>
        </div>

        <p class="text-xs text-[#737373]">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>
</footer>

</body>
</html>
