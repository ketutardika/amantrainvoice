<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Documentation — {{ config('app.name') }}</title>
    <meta name="description" content="Step-by-step guide for getting started with {{ config('app.name') }}.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        .sidebar-link { transition: color 0.15s, background 0.15s; }
        .sidebar-link.active { background: #f1f5f9; color: #0f172a; font-weight: 600; }
        :target { scroll-margin-top: 80px; }
        .step-section { scroll-margin-top: 80px; }
    </style>
</head>
<body class="antialiased bg-[#F5F5F5] text-[#1A1A1A] font-sans">

{{-- ─── NAV ──────────────────────────────────────────────────────────────────── --}}
<nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-[#E8E8E8]">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between h-16">
        <a href="/" class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-gradient-to-br from-slate-700 to-slate-900 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span class="font-bold text-slate-900 text-lg tracking-tight">{{ config('app.name') }}</span>
        </a>

        <div class="hidden md:flex items-center gap-6 text-sm text-[#737373]">
            <a href="/" class="hover:text-[#1A1A1A] transition-colors">Home</a>
            <a href="/docs" class="text-slate-900 font-semibold">Docs</a>
        </div>

        <div class="flex items-center gap-3">
            <a href="/admin/login" class="hidden sm:block text-sm text-[#737373] hover:text-[#1A1A1A] transition-colors font-medium">Log in</a>
            <a href="/admin/register" class="px-4 py-2 bg-gradient-to-br from-slate-700 to-slate-900 text-white text-sm font-semibold rounded-lg hover:opacity-90 transition-all shadow-sm">
                Get Started
            </a>
        </div>
    </div>
</nav>

{{-- ─── PAGE LAYOUT ──────────────────────────────────────────────────────────── --}}
<div class="pt-16 flex min-h-screen max-w-7xl mx-auto">

    {{-- ─── SIDEBAR ──────────────────────────────────────────────────────────── --}}
    <aside class="hidden lg:block w-64 flex-shrink-0">
        <div class="sticky top-16 h-[calc(100vh-4rem)] overflow-y-auto py-8 px-4">
            <p class="text-xs font-semibold text-[#737373] uppercase tracking-widest mb-4 px-3">Getting Started</p>
            <nav class="space-y-0.5">
                @php
                $steps = [
                    ['id' => 'step-1', 'n' => '1', 'label' => 'User Registration'],
                    ['id' => 'step-2', 'n' => '2', 'label' => 'Company Registration'],
                    ['id' => 'step-3', 'n' => '3', 'label' => 'Login to Your Account'],
                    ['id' => 'step-4', 'n' => '4', 'label' => 'Invoice Settings'],
                    ['id' => 'step-5', 'n' => '5', 'label' => 'Tax Settings'],
                    ['id' => 'step-6', 'n' => '6', 'label' => 'Manage Clients'],
                    ['id' => 'step-7', 'n' => '7', 'label' => 'Manage Projects'],
                    ['id' => 'step-8', 'n' => '8', 'label' => 'Manage Invoices'],
                    ['id' => 'step-9', 'n' => '9', 'label' => 'Manage Payments'],
                ];
                @endphp

                @foreach($steps as $s)
                <a href="#{{ $s['id'] }}"
                   class="sidebar-link flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-[#737373] hover:text-[#1A1A1A] hover:bg-slate-50">
                    <span class="flex-shrink-0 w-5 h-5 bg-gradient-to-br from-slate-600 to-slate-800 text-white text-[10px] font-bold rounded flex items-center justify-center">{{ $s['n'] }}</span>
                    <span>{{ $s['label'] }}</span>
                </a>
                @endforeach
            </nav>

            <div class="mt-8 px-3">
                <div class="p-3 bg-white border border-[#E8E8E8] rounded-xl">
                    <p class="text-xs text-[#737373] mb-2">Ready to start?</p>
                    <a href="/admin/register" class="block w-full text-center py-2 bg-gradient-to-br from-slate-700 to-slate-900 text-white text-xs font-semibold rounded-lg hover:opacity-90 transition-opacity">
                        Create Account &rarr;
                    </a>
                </div>
            </div>
        </div>
    </aside>

    {{-- ─── MAIN CONTENT ─────────────────────────────────────────────────────── --}}
    <main class="flex-1 min-w-0 py-8 px-6 lg:px-10">

        {{-- Page header --}}
        <div class="mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-[#E8E8E8] rounded-full text-xs text-[#737373] font-medium mb-4 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                User Documentation Guide · Version 1.0 · February 2026
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-[#1A1A1A] tracking-tight mb-3">
                Getting Started with {{ config('app.name') }}
            </h1>
            <p class="text-[#737373] text-lg leading-relaxed max-w-2xl">
                Follow this step-by-step guide to set up your account, configure your company, and start creating professional invoices in minutes.
            </p>
        </div>

        {{-- ─── TOC (mobile) ────────────────────────────────────────────────── --}}
        <div class="lg:hidden mb-8 bg-white border border-[#E8E8E8] rounded-2xl p-5">
            <p class="text-xs font-semibold text-[#737373] uppercase tracking-widest mb-3">Table of Contents</p>
            <ol class="space-y-1.5">
                @foreach($steps as $s)
                <li>
                    <a href="#{{ $s['id'] }}" class="flex items-center gap-2.5 text-sm text-[#737373] hover:text-[#1A1A1A] transition-colors">
                        <span class="flex-shrink-0 w-5 h-5 bg-gradient-to-br from-slate-600 to-slate-800 text-white text-[10px] font-bold rounded flex items-center justify-center">{{ $s['n'] }}</span>
                        {{ $s['label'] }}
                    </a>
                </li>
                @endforeach
            </ol>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- STEP 1: USER REGISTRATION --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section id="step-1" class="step-section mb-10">
            <div class="bg-white rounded-2xl border border-[#E8E8E8] overflow-hidden shadow-sm">
                {{-- Step header --}}
                <div class="bg-gradient-to-br from-slate-700 to-slate-900 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex-shrink-0 w-9 h-9 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center text-white font-bold text-sm">1</span>
                        <div>
                            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">Step 1</p>
                            <h2 class="text-white font-bold text-xl leading-tight">User Registration</h2>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm mt-2 ml-12">Create your {{ config('app.name') }} account to get started</p>
                </div>

                <div class="p-6 space-y-6">
                    {{-- Sub-step 1 --}}
                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">1</span>
                        <div>
                            <h3 class="font-semibold text-[#1A1A1A] mb-1">Navigate to the Registration Page</h3>
                            <p class="text-sm text-[#737373] leading-relaxed">Open your browser and go to <span class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">/admin/register</span>. You will see the Sign Up form with fields for your name, email, and password.</p>
                        </div>
                    </div>

                    {{-- Sub-step 2 --}}
                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">2</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-[#1A1A1A] mb-3">Fill In Your Account Details</h3>
                            <div class="overflow-x-auto rounded-xl border border-[#E8E8E8]">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                                            <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tl-xl">Field</th>
                                            <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider">Description</th>
                                            <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tr-xl">Example</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#E8E8E8]">
                                        @foreach([
                                            ['Name *', 'Your full name', 'John Demo'],
                                            ['Email address *', 'Valid email address (used for login)', 'john@example.com'],
                                            ['Password *', 'Minimum 8 characters with letters and numbers', 'Demo@123456!'],
                                            ['Confirm password *', 'Re-enter your password to confirm', 'Demo@123456!'],
                                        ] as $row)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-3 font-medium text-[#1A1A1A] text-xs">{{ $row[0] }}</td>
                                            <td class="px-4 py-3 text-[#737373] text-xs">{{ $row[1] }}</td>
                                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $row[2] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{-- Tip box --}}
                            <div class="mt-3 flex gap-2.5 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                                <span class="text-amber-500 text-base flex-shrink-0">💡</span>
                                <p class="text-xs text-amber-800"><strong>Tip:</strong> Use a strong password with uppercase, lowercase, numbers, and special characters.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Sub-step 3 --}}
                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">3</span>
                        <div>
                            <h3 class="font-semibold text-[#1A1A1A] mb-1">Click the Sign Up Button</h3>
                            <p class="text-sm text-[#737373] leading-relaxed">Click the <strong class="text-[#1A1A1A]">"Sign up"</strong> button to create your account. Upon success, you will be automatically redirected to the Company Registration page.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- STEP 2: COMPANY REGISTRATION --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section id="step-2" class="step-section mb-10">
            <div class="bg-white rounded-2xl border border-[#E8E8E8] overflow-hidden shadow-sm">
                <div class="bg-gradient-to-br from-slate-700 to-slate-900 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex-shrink-0 w-9 h-9 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center text-white font-bold text-sm">2</span>
                        <div>
                            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">Step 2</p>
                            <h2 class="text-white font-bold text-xl leading-tight">Company Registration</h2>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm mt-2 ml-12">Register your company details to appear on invoices</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">1</span>
                        <div>
                            <h3 class="font-semibold text-[#1A1A1A] mb-1">The Register Company Form</h3>
                            <p class="text-sm text-[#737373] leading-relaxed">After successfully creating your account, you will be automatically directed to the <strong class="text-[#1A1A1A]">Register Company</strong> page. This step is required to start using {{ config('app.name') }}.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">2</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-[#1A1A1A] mb-3">Fill in Company Details</h3>
                            <div class="overflow-x-auto rounded-xl border border-[#E8E8E8]">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                                            <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tl-xl">Field</th>
                                            <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider">Description</th>
                                            <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tr-xl">Example</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#E8E8E8]">
                                        @foreach([
                                            ['Company Name *', 'Your official company/business name', 'Demo Tech Solutions'],
                                            ['Company Email', 'Business contact email address', 'contact@demotechsolutions.com'],
                                            ['Company Phone', 'Business phone number', '+1-555-987-6543'],
                                            ['Company Address', 'Full business address', '123 Business Ave, Suite 100'],
                                        ] as $row)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-3 font-medium text-[#1A1A1A] text-xs">{{ $row[0] }}</td>
                                            <td class="px-4 py-3 text-[#737373] text-xs">{{ $row[1] }}</td>
                                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $row[2] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">3</span>
                        <div>
                            <h3 class="font-semibold text-[#1A1A1A] mb-1">Click Register Company</h3>
                            <p class="text-sm text-[#737373] leading-relaxed">Click the <strong class="text-[#1A1A1A]">"Register Company"</strong> button to save your company. You will be redirected to the main Invoice Dashboard.</p>
                            <div class="mt-3 flex gap-2.5 p-3 bg-green-50 border border-green-200 rounded-xl">
                                <span class="text-green-500 text-base flex-shrink-0">✅</span>
                                <p class="text-xs text-green-800"><strong>Success:</strong> After registration, your company name will appear in the sidebar at all times.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- STEP 3: LOGIN --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section id="step-3" class="step-section mb-10">
            <div class="bg-white rounded-2xl border border-[#E8E8E8] overflow-hidden shadow-sm">
                <div class="bg-gradient-to-br from-slate-700 to-slate-900 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex-shrink-0 w-9 h-9 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center text-white font-bold text-sm">3</span>
                        <div>
                            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">Step 3</p>
                            <h2 class="text-white font-bold text-xl leading-tight">Login to Your Account</h2>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm mt-2 ml-12">Access your Invoice dashboard with your credentials</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">1</span>
                        <div>
                            <h3 class="font-semibold text-[#1A1A1A] mb-1">Go to the Login Page</h3>
                            <p class="text-sm text-[#737373]">Navigate to <span class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">/admin/login</span> or click <strong class="text-[#1A1A1A]">"Log in"</strong> from the landing page.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">2</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-[#1A1A1A] mb-3">Enter Your Credentials</h3>
                            <div class="overflow-x-auto rounded-xl border border-[#E8E8E8]">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                                            <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tl-xl">Field</th>
                                            <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tr-xl">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#E8E8E8]">
                                        <tr class="hover:bg-slate-50"><td class="px-4 py-3 font-medium text-xs">Email address *</td><td class="px-4 py-3 text-[#737373] text-xs">The email you registered with</td></tr>
                                        <tr class="hover:bg-slate-50"><td class="px-4 py-3 font-medium text-xs">Password *</td><td class="px-4 py-3 text-[#737373] text-xs">Your account password</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">3</span>
                        <div>
                            <h3 class="font-semibold text-[#1A1A1A] mb-2">Dashboard Overview</h3>
                            <p class="text-sm text-[#737373] mb-3">After login, you are directed to the <strong class="text-[#1A1A1A]">Invoice Management System Dashboard</strong> which shows:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach([
                                    ['Total Revenue', 'Cumulative revenue from all paid invoices'],
                                    ['Outstanding Amount', 'Unpaid invoice amounts'],
                                    ['Collection Rate', 'Percentage of invoices collected'],
                                    ['Total Invoice', 'Number of invoices created'],
                                    ['Total Clients', 'Active client count'],
                                    ['Overdue Invoices', 'Invoices past their due date'],
                                    ['Monthly Revenue Chart', 'Visual revenue trend'],
                                ] as $item)
                                <div class="flex items-start gap-2 p-2.5 bg-slate-50 rounded-lg border border-[#E8E8E8]">
                                    <svg class="w-3.5 h-3.5 text-slate-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-semibold text-[#1A1A1A]">{{ $item[0] }}</p>
                                        <p class="text-xs text-[#737373]">{{ $item[1] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- STEP 4: INVOICE SETTINGS --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section id="step-4" class="step-section mb-10">
            <div class="bg-white rounded-2xl border border-[#E8E8E8] overflow-hidden shadow-sm">
                <div class="bg-gradient-to-br from-slate-700 to-slate-900 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex-shrink-0 w-9 h-9 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center text-white font-bold text-sm">4</span>
                        <div>
                            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">Step 4</p>
                            <h2 class="text-white font-bold text-xl leading-tight">Invoice Settings</h2>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm mt-2 ml-12">Configure your company details, branding, and invoice defaults</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">1</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Navigate to Invoice Settings</h3>
                        <p class="text-sm text-[#737373]">In the left sidebar, click <strong class="text-[#1A1A1A]">Invoice Settings</strong> under the <em>Settings</em> section.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">2</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-[#1A1A1A] mb-3">Company Information</h3>
                            <p class="text-sm text-[#737373] mb-3">Update your company details that will appear on all generated invoices:</p>
                            <div class="overflow-x-auto rounded-xl border border-[#E8E8E8]">
                                <table class="w-full text-sm">
                                    <thead><tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tl-xl">Field</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tr-xl">Description</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-[#E8E8E8]">
                                        @foreach([
                                            ['Company Name *','Official name shown on invoices'],
                                            ['Company Email *','Contact email on invoices'],
                                            ['Company Phone','Contact phone number'],
                                            ['Company Website','Your business website URL'],
                                            ['Company Address','Full physical address'],
                                        ] as $r)
                                        <tr class="hover:bg-slate-50"><td class="px-4 py-3 font-medium text-xs">{{ $r[0] }}</td><td class="px-4 py-3 text-[#737373] text-xs">{{ $r[1] }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">3</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Branding</h3>
                        <p class="text-sm text-[#737373]">Upload your company logo (PNG or JPG recommended) and set a company tagline that will appear on all invoice PDFs.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">4</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-[#1A1A1A] mb-3">Invoice Defaults</h3>
                            <p class="text-sm text-[#737373] mb-3">Set default values for new invoices:</p>
                            <div class="overflow-x-auto rounded-xl border border-[#E8E8E8]">
                                <table class="w-full text-sm">
                                    <thead><tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tl-xl">Setting</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tr-xl">Description</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-[#E8E8E8]">
                                        @foreach([
                                            ['Invoice Number Prefix','Auto-prefix for invoice numbers (e.g., INV)'],
                                            ['Default Currency *','Currency used in all invoices'],
                                            ['Default Payment Terms *','Days until payment is due (e.g., 30)'],
                                            ['Default Tax Rate (%)','Pre-applied tax percentage'],
                                            ['Late Fee Percentage (%)','Fee applied after due date'],
                                        ] as $r)
                                        <tr class="hover:bg-slate-50"><td class="px-4 py-3 font-medium text-xs">{{ $r[0] }}</td><td class="px-4 py-3 text-[#737373] text-xs">{{ $r[1] }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">5</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Invoice Appearance</h3>
                        <p class="text-sm text-[#737373]">Select an <strong class="text-[#1A1A1A]">Invoice Template</strong> and <strong class="text-[#1A1A1A]">Date Format</strong>, and add custom <strong class="text-[#1A1A1A]">Invoice Footer Text</strong> (e.g., "Thank you for your business!").</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">6</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-2">Automation &amp; Notifications</h3>
                        <p class="text-sm text-[#737373] mb-2">Enable automated actions:</p>
                        <ul class="space-y-1.5">
                            @foreach(['Auto Send Invoice — automatically email invoices when created','Send Payment Reminders — automated reminder emails before due date','Auto Follow-up — follow-up emails for overdue invoices'] as $item)
                            <li class="flex items-start gap-2 text-xs text-[#737373]">
                                <svg class="w-3.5 h-3.5 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                {{ $item }}
                            </li>
                            @endforeach
                        </ul>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">7</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Bank Information</h3>
                        <p class="text-sm text-[#737373]">Click <strong class="text-[#1A1A1A]">"Add Bank Account"</strong> to add your payment bank account details that will appear on invoices.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">8</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Save Settings</h3>
                        <p class="text-sm text-[#737373]">Scroll to the bottom and click <strong class="text-[#1A1A1A]">"Save Settings"</strong> to apply all changes.</p></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- STEP 5: TAX SETTINGS --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section id="step-5" class="step-section mb-10">
            <div class="bg-white rounded-2xl border border-[#E8E8E8] overflow-hidden shadow-sm">
                <div class="bg-gradient-to-br from-slate-700 to-slate-900 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex-shrink-0 w-9 h-9 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center text-white font-bold text-sm">5</span>
                        <div>
                            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">Step 5</p>
                            <h2 class="text-white font-bold text-xl leading-tight">Tax Settings</h2>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm mt-2 ml-12">Manage tax rates applied to your invoices</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">1</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Navigate to Tax Settings</h3>
                        <p class="text-sm text-[#737373]">In the left sidebar, click <strong class="text-[#1A1A1A]">Tax Settings</strong> under the <em>Settings</em> section.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">2</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-[#1A1A1A] mb-3">Existing Tax Configuration</h3>
                            <p class="text-sm text-[#737373] mb-3">The system comes pre-configured with a default tax. You can edit it or add new tax rates:</p>
                            <div class="overflow-x-auto rounded-xl border border-[#E8E8E8]">
                                <table class="w-full text-sm">
                                    <thead><tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tl-xl">Field</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider">Description</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tr-xl">Example</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-[#E8E8E8]">
                                        @foreach([
                                            ['Name *','Tax name label','PPN'],
                                            ['Code *','Short tax code','PPN'],
                                            ['Rate *','Tax percentage amount','11.00'],
                                            ['Type *','Percentage or fixed amount','Percentage'],
                                            ['Description','Tax description','Pajak Pertambahan Nilai'],
                                            ['Active','Toggle to enable/disable this tax','Enabled'],
                                        ] as $r)
                                        <tr class="hover:bg-slate-50"><td class="px-4 py-3 font-medium text-xs">{{ $r[0] }}</td><td class="px-4 py-3 text-[#737373] text-xs">{{ $r[1] }}</td><td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $r[2] }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 flex gap-2.5 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                                <span class="text-blue-500 text-base flex-shrink-0">ℹ️</span>
                                <p class="text-xs text-blue-800"><strong>Note:</strong> Only active taxes will be available for selection when creating invoices.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">3</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Add a New Tax</h3>
                        <p class="text-sm text-[#737373]">Click <strong class="text-[#1A1A1A]">"Add New Tax"</strong> to add additional tax rates. Fill in the required fields and toggle "Active" to enable it.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">4</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Common Tax Rates</h3>
                        <p class="text-sm text-[#737373]">Use the <strong class="text-[#1A1A1A]">"Add Common Tax"</strong> dropdown under the <em>Common Tax Rates</em> section to quickly add commonly used tax rates from a pre-set list.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">5</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Save Tax Settings</h3>
                        <p class="text-sm text-[#737373]">Click <strong class="text-[#1A1A1A]">"Save Tax Settings"</strong> to apply your tax configuration.</p></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- STEP 6: MANAGE CLIENTS --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section id="step-6" class="step-section mb-10">
            <div class="bg-white rounded-2xl border border-[#E8E8E8] overflow-hidden shadow-sm">
                <div class="bg-gradient-to-br from-slate-700 to-slate-900 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex-shrink-0 w-9 h-9 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center text-white font-bold text-sm">6</span>
                        <div>
                            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">Step 6</p>
                            <h2 class="text-white font-bold text-xl leading-tight">Management Clients</h2>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm mt-2 ml-12">Add and manage your business clients</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">1</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Navigate to Clients</h3>
                        <p class="text-sm text-[#737373]">In the left sidebar, click <strong class="text-[#1A1A1A]">Clients</strong> under the <em>Sales</em> section. The Clients list page shows all existing clients with search and filter options.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">2</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Create a New Client</h3>
                        <p class="text-sm text-[#737373]">Click the <strong class="text-[#1A1A1A]">"New client"</strong> button in the top-right corner to open the Create Client form.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">3</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-[#1A1A1A] mb-3">Fill in Basic Information</h3>
                            <div class="overflow-x-auto rounded-xl border border-[#E8E8E8]">
                                <table class="w-full text-sm">
                                    <thead><tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tl-xl">Field</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider">Description</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tr-xl">Example</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-[#E8E8E8]">
                                        @foreach([
                                            ['Client Code *','Auto-generated unique code (editable)','CLT-2026-0001'],
                                            ['Name *','Client contact person\'s full name','Alice Johnson'],
                                            ['Company name','Client\'s company or business name','Acme Corporation'],
                                            ['Email *','Client\'s email address','alice@acmecorp.com'],
                                            ['Phone','Client\'s phone number','+1-555-123-4567'],
                                            ['Client type *','Individual or Company','Company'],
                                        ] as $r)
                                        <tr class="hover:bg-slate-50"><td class="px-4 py-3 font-medium text-xs">{{ $r[0] }}</td><td class="px-4 py-3 text-[#737373] text-xs">{{ $r[1] }}</td><td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $r[2] }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">4</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Fill in Address Information</h3>
                        <p class="text-sm text-[#737373]">Enter the client's address, city, state, postal code, and country in the <em>Address Information</em> section.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">5</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Business Information</h3>
                        <p class="text-sm text-[#737373]">Optionally fill in the client's Tax Number, Credit Limit (in your currency), and Payment Terms (in days). Ensure <strong class="text-[#1A1A1A]">Active Status</strong> is toggled on.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">6</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Save the Client</h3>
                        <p class="text-sm text-[#737373] mb-2">Click <strong class="text-[#1A1A1A]">"Create"</strong> to save the client. You can also click <strong class="text-[#1A1A1A]">"Create &amp; create another"</strong> to save and immediately add another client.</p>
                        <div class="flex gap-2.5 p-3 bg-green-50 border border-green-200 rounded-xl">
                            <span class="text-green-500 text-base flex-shrink-0">✅</span>
                            <p class="text-xs text-green-800"><strong>Success:</strong> A "Created" notification will appear, and you'll be redirected to the Edit Client page.</p>
                        </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">7</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-2">Manage Existing Clients</h3>
                        <p class="text-sm text-[#737373] mb-2">From the Clients list, you can:</p>
                        <ul class="space-y-1">
                            @foreach(['Search clients by name, email, or company','Filter clients using the filter icon','Export clients list as a file','Edit or Delete clients from the list view'] as $item)
                            <li class="flex items-center gap-2 text-xs text-[#737373]">
                                <span class="w-1.5 h-1.5 bg-slate-400 rounded-full flex-shrink-0"></span>{{ $item }}
                            </li>
                            @endforeach
                        </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- STEP 7: MANAGE PROJECTS --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section id="step-7" class="step-section mb-10">
            <div class="bg-white rounded-2xl border border-[#E8E8E8] overflow-hidden shadow-sm">
                <div class="bg-gradient-to-br from-slate-700 to-slate-900 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex-shrink-0 w-9 h-9 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center text-white font-bold text-sm">7</span>
                        <div>
                            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">Step 7</p>
                            <h2 class="text-white font-bold text-xl leading-tight">Management Projects</h2>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm mt-2 ml-12">Organize your work by creating and tracking projects</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">1</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Navigate to Projects</h3>
                        <p class="text-sm text-[#737373]">In the left sidebar, click <strong class="text-[#1A1A1A]">Projects</strong> under the <em>Sales</em> section. Projects help you organize invoices by specific work engagements.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">2</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Create a New Project</h3>
                        <p class="text-sm text-[#737373]">Click the <strong class="text-[#1A1A1A]">"New project"</strong> button to open the Create Project form.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">3</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-[#1A1A1A] mb-3">Fill in Project Information</h3>
                            <div class="overflow-x-auto rounded-xl border border-[#E8E8E8]">
                                <table class="w-full text-sm">
                                    <thead><tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tl-xl">Field</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider">Description</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tr-xl">Example</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-[#E8E8E8]">
                                        @foreach([
                                            ['Project code *','Unique auto-generated project ID','PRJ-2026-0001'],
                                            ['Client *','Select the associated client','Alice Johnson'],
                                            ['Name *','Project title/name','Website Redesign Project'],
                                            ['Description','Detailed project description','Full website redesign...'],
                                            ['Status *','Planning, Active, On Hold, Completed, Cancelled','Active'],
                                            ['Budget','Total project budget amount','5000'],
                                            ['Progress (%)','Current completion percentage','0'],
                                            ['Start date *','Project start date','01/02/2026'],
                                            ['End date','Expected project completion date','30/04/2026'],
                                        ] as $r)
                                        <tr class="hover:bg-slate-50"><td class="px-4 py-3 font-medium text-xs">{{ $r[0] }}</td><td class="px-4 py-3 text-[#737373] text-xs">{{ $r[1] }}</td><td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $r[2] }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">4</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Save the Project</h3>
                        <p class="text-sm text-[#737373] mb-2">Click <strong class="text-[#1A1A1A]">"Create"</strong> to save the project. You'll be redirected to the Edit Project page upon success.</p>
                        <div class="flex gap-2.5 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                            <span class="text-amber-500 text-base flex-shrink-0">💡</span>
                            <p class="text-xs text-amber-800"><strong>Note:</strong> Each project code must be unique. If you get an error saying "project code already taken", update the code number before saving.</p>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- STEP 8: MANAGE INVOICES --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section id="step-8" class="step-section mb-10">
            <div class="bg-white rounded-2xl border border-[#E8E8E8] overflow-hidden shadow-sm">
                <div class="bg-gradient-to-br from-slate-700 to-slate-900 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex-shrink-0 w-9 h-9 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center text-white font-bold text-sm">8</span>
                        <div>
                            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">Step 8</p>
                            <h2 class="text-white font-bold text-xl leading-tight">Management Invoices</h2>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm mt-2 ml-12">Create and manage professional invoices for your clients</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">1</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Navigate to Invoices</h3>
                        <p class="text-sm text-[#737373]">In the left sidebar, click <strong class="text-[#1A1A1A]">Invoices</strong> under the <em>Sales</em> section. The Invoices list shows all your invoices with their status and amounts.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">2</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Create a New Invoice</h3>
                        <p class="text-sm text-[#737373]">Click the <strong class="text-[#1A1A1A]">"New invoice"</strong> button in the top-right corner.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">3</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-[#1A1A1A] mb-3">Fill in Invoice Details</h3>
                            <div class="overflow-x-auto rounded-xl border border-[#E8E8E8]">
                                <table class="w-full text-sm">
                                    <thead><tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tl-xl">Field</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider">Description</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tr-xl">Example</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-[#E8E8E8]">
                                        @foreach([
                                            ['Invoice Number *','Auto-generated, format: PREFIX-YYYY-MM-00001','INV-2026-02-00001'],
                                            ['Client *','Select the client to invoice','Alice Johnson'],
                                            ['Project','Link invoice to a project (optional)','Website Redesign Project'],
                                            ['Invoice date *','Date the invoice is issued','20/02/2026'],
                                            ['Due date *','Payment deadline date','22/03/2026'],
                                            ['Status *','Draft, Sent, Viewed, Partially Paid, Paid, etc.','Draft'],
                                            ['Currency *','Invoice currency','IDR'],
                                        ] as $r)
                                        <tr class="hover:bg-slate-50"><td class="px-4 py-3 font-medium text-xs">{{ $r[0] }}</td><td class="px-4 py-3 text-[#737373] text-xs">{{ $r[1] }}</td><td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $r[2] }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">4</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-[#1A1A1A] mb-3">Add Invoice Items</h3>
                            <p class="text-sm text-[#737373] mb-3">In the <em>Invoice Items</em> section, fill in each line item:</p>
                            <div class="overflow-x-auto rounded-xl border border-[#E8E8E8]">
                                <table class="w-full text-sm">
                                    <thead><tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tl-xl">Field</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider">Description</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tr-xl">Example</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-[#E8E8E8]">
                                        @foreach([
                                            ['Name *','Item/service name','Website Design & Development'],
                                            ['Quantity *','Number of units','40'],
                                            ['Unit *','Pieces, Hours, Days, Package, Month, Year','Hours'],
                                            ['Unit price *','Price per unit','500,000'],
                                            ['Description','Detailed item description','UI/UX design work'],
                                        ] as $r)
                                        <tr class="hover:bg-slate-50"><td class="px-4 py-3 font-medium text-xs">{{ $r[0] }}</td><td class="px-4 py-3 text-[#737373] text-xs">{{ $r[1] }}</td><td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $r[2] }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-[#737373] mt-2">Click <strong class="text-[#1A1A1A]">"Add Item"</strong> to add more line items to the invoice.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">5</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-2">Totals &amp; Payment</h3>
                        <p class="text-sm text-[#737373] mb-2">The system automatically calculates totals. You can also:</p>
                        <ul class="space-y-1">
                            @foreach(['Set a Discount amount','Apply a Tax (select from your configured tax rates, e.g., PPN 11%)','The Total amount updates automatically'] as $item)
                            <li class="flex items-center gap-2 text-xs text-[#737373]"><span class="w-1.5 h-1.5 bg-slate-400 rounded-full flex-shrink-0"></span>{{ $item }}</li>
                            @endforeach
                        </ul>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">6</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Notes &amp; Terms</h3>
                        <p class="text-sm text-[#737373]">Add any <strong class="text-[#1A1A1A]">Notes</strong> (visible to client) and <strong class="text-[#1A1A1A]">Terms &amp; Conditions</strong> in the Notes &amp; Terms section.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">7</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-2">Save the Invoice</h3>
                        <p class="text-sm text-[#737373] mb-3">Click <strong class="text-[#1A1A1A]">"Create"</strong> to save. You will be redirected to the View Invoice page where you can also click <strong class="text-[#1A1A1A]">"View PDF"</strong> to see the printable version.</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach([['Draft','bg-gray-100 text-gray-700','not yet sent'],['Sent','bg-blue-100 text-blue-700','emailed to client'],['Paid','bg-green-100 text-green-700','payment confirmed']] as $s)
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 {{ $s[1] }} text-xs font-semibold rounded-full">{{ $s[0] }}</span>
                                <span class="text-xs text-[#737373]">= {{ $s[2] }}</span>
                            </div>
                            @endforeach
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- STEP 9: MANAGE PAYMENTS --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section id="step-9" class="step-section mb-10">
            <div class="bg-white rounded-2xl border border-[#E8E8E8] overflow-hidden shadow-sm">
                <div class="bg-gradient-to-br from-slate-700 to-slate-900 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex-shrink-0 w-9 h-9 bg-white/10 border border-white/20 rounded-xl flex items-center justify-center text-white font-bold text-sm">9</span>
                        <div>
                            <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">Step 9</p>
                            <h2 class="text-white font-bold text-xl leading-tight">Management Payments</h2>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm mt-2 ml-12">Record and track payments received from clients</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">1</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Navigate to Payments</h3>
                        <p class="text-sm text-[#737373]">In the left sidebar, click <strong class="text-[#1A1A1A]">Payments</strong> under the <em>Sales</em> section. This shows all recorded payments with their status.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">2</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Create a New Payment</h3>
                        <p class="text-sm text-[#737373]">Click the <strong class="text-[#1A1A1A]">"New payment"</strong> button in the top-right corner.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">3</span>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-[#1A1A1A] mb-3">Fill in Payment Information</h3>
                            <div class="overflow-x-auto rounded-xl border border-[#E8E8E8]">
                                <table class="w-full text-sm">
                                    <thead><tr class="bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tl-xl">Field</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider">Description</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs uppercase tracking-wider rounded-tr-xl">Example</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-[#E8E8E8]">
                                        @foreach([
                                            ['Payment number *','Auto-generated payment ID','PAY-2026-00001'],
                                            ['Invoice *','Select the related invoice','INV-2026-02-00001'],
                                            ['Client','Auto-populated from invoice selection','Alice Johnson'],
                                            ['Amount *','Auto-filled with invoice balance due','22,200,000'],
                                            ['Payment date *','Date payment was received','20/02/2026'],
                                            ['Payment method *','Bank Transfer, Cash, Credit Card, GoPay, OVO, etc.','Bank Transfer'],
                                            ['Reference/Transaction Number','Bank or payment gateway transaction ID','TRX-2026-0220-001'],
                                            ['Status *','Pending, Verified, Cancelled','Verified'],
                                        ] as $r)
                                        <tr class="hover:bg-slate-50"><td class="px-4 py-3 font-medium text-xs">{{ $r[0] }}</td><td class="px-4 py-3 text-[#737373] text-xs">{{ $r[1] }}</td><td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $r[2] }}</td></tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">4</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-1">Additional Information</h3>
                        <p class="text-sm text-[#737373]">Optionally upload a <strong class="text-[#1A1A1A]">Payment Proof/Receipt</strong> (image or PDF) for record keeping, and add any <strong class="text-[#1A1A1A]">Notes</strong> about the payment.</p></div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">5</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-2">Save the Payment</h3>
                        <p class="text-sm text-[#737373] mb-2">Click <strong class="text-[#1A1A1A]">"Create"</strong> to record the payment. The invoice's paid amount and balance due will update automatically.</p>
                        <div class="flex gap-2.5 p-3 bg-green-50 border border-green-200 rounded-xl">
                            <span class="text-green-500 text-base flex-shrink-0">✅</span>
                            <p class="text-xs text-green-800"><strong>Success:</strong> Once a payment is saved with Verified status, it reflects on the invoice's payment status and dashboard revenue metrics.</p>
                        </div>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <span class="flex-shrink-0 w-7 h-7 bg-slate-100 border border-[#E8E8E8] rounded-full flex items-center justify-center text-slate-700 font-bold text-xs">6</span>
                        <div><h3 class="font-semibold text-[#1A1A1A] mb-3">Payment Status Reference</h3>
                        <div class="space-y-2">
                            @foreach([
                                ['Pending','bg-yellow-100 text-yellow-800','border-yellow-200','Payment recorded but not yet confirmed'],
                                ['Verified','bg-green-100 text-green-800','border-green-200','Payment confirmed and applied to invoice'],
                                ['Cancelled','bg-red-100 text-red-800','border-red-200','Payment was cancelled or reversed'],
                            ] as $s)
                            <div class="flex items-center gap-3 p-2.5 bg-slate-50 rounded-lg border border-[#E8E8E8]">
                                <span class="px-2 py-0.5 {{ $s[1] }} border {{ $s[2] }} text-xs font-semibold rounded-full flex-shrink-0">{{ $s[0] }}</span>
                                <span class="text-xs text-[#737373]">{{ $s[3] }}</span>
                            </div>
                            @endforeach
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ─── CONGRATULATIONS ─────────────────────────────────────────────── --}}
        <div class="bg-gradient-to-br from-slate-700 to-slate-900 rounded-2xl p-8 text-center mb-10">
            <div class="w-14 h-14 bg-white/10 border border-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl">🎉</span>
            </div>
            <h2 class="text-white font-bold text-xl mb-2">You're all set!</h2>
            <p class="text-slate-300 text-sm mb-6 max-w-md mx-auto">
                You have successfully completed the full workflow of {{ config('app.name') }} — from user registration to recording payments. Your invoice management system is now fully set up and ready to use.
            </p>
            <a href="/admin/register"
               class="inline-block px-7 py-3 bg-white text-slate-900 font-bold rounded-xl hover:bg-slate-100 transition-colors text-sm">
                Go to Dashboard &rarr;
            </a>
        </div>

    </main>
</div>

{{-- ─── FOOTER ──────────────────────────────────────────────────────────────── --}}
<footer class="border-t border-[#E8E8E8] py-8 px-6 bg-white">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
        <a href="/" class="flex items-center gap-2">
            <div class="w-6 h-6 bg-gradient-to-br from-slate-700 to-slate-900 rounded-md flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span class="font-bold text-slate-700 text-sm">{{ config('app.name') }}</span>
        </a>
        <div class="flex items-center gap-6 text-xs text-[#737373]">
            <a href="/" class="hover:text-[#1A1A1A] transition-colors">Home</a>
            <a href="/docs" class="hover:text-[#1A1A1A] transition-colors font-semibold text-[#1A1A1A]">Docs</a>
            <a href="/admin/login" class="hover:text-[#1A1A1A] transition-colors">Log in</a>
        </div>
        <p class="text-xs text-[#737373]">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</footer>

<script>
// Highlight active sidebar link on scroll
const sections = document.querySelectorAll('.step-section');
const links = document.querySelectorAll('.sidebar-link');
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            links.forEach(l => l.classList.remove('active'));
            const active = document.querySelector(`.sidebar-link[href="#${entry.target.id}"]`);
            if (active) active.classList.add('active');
        }
    });
}, { rootMargin: '-20% 0px -70% 0px' });
sections.forEach(s => observer.observe(s));
</script>

</body>
</html>
