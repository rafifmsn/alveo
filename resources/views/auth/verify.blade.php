<x-guest-layout title="Verification Required">
    <!-- Modal-style Verification Required Card -->
    <div class="text-center">
        <div class="mx-auto w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700 mb-4">
            <x-icon name="shield-alert" class="w-6 h-6" />
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
            Pending Administrator Clearance
        </span>

        <h1 class="text-xl font-bold text-slate-900">
            Verification Required
        </h1>

        <p class="mt-2.5 text-sm text-slate-600 leading-relaxed">
            Your athlete account (<span class="font-semibold text-slate-900">{{ $user?->email }}</span>) is currently unverified.
            To prevent automated API quota abuse, new accounts are activated manually by an administrator.
        </p>

        <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80 text-left text-xs text-slate-600 space-y-1.5 leading-relaxed">
            <p class="font-semibold text-slate-800">Already sent your request?</p>
            <p>
                Please allow <span class="font-semibold text-slate-900 tabular-nums">24-48 hours</span> for administrator review.
                Once your account is marked as verified in the database, refreshing this page will immediately unlock your dashboard.
            </p>
        </div>

        <div class="mt-6 space-y-2.5">
            <a href="{{ $mailtoUrl }}"
               class="btn w-full bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-sm h-11 shadow-xs inline-flex items-center justify-center gap-2">
                <x-icon name="mail" class="w-4 h-4" />
                <span>Send Verification Email Manually</span>
            </a>

            <a href="{{ route('finder') }}"
               class="btn btn-outline w-full border-slate-300 hover:bg-slate-100 hover:text-slate-900 text-slate-700 rounded-xl font-semibold text-sm h-10 inline-flex items-center justify-center gap-2">
                <x-icon name="refresh-cw" class="w-4 h-4" />
                <span>Check Verification Status</span>
            </a>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-5 pt-4 border-t border-slate-100">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <x-icon name="log-out" class="w-3.5 h-3.5" />
                <span>Sign out and switch account</span>
            </button>
        </form>
    </div>
</x-guest-layout>

