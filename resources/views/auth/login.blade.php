<x-guest-layout title="Sign In">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Sign in to your account</h1>
        <p class="mt-1 text-sm text-slate-500">Access your aerobic exposure engine and saved corridors.</p>
    </div>

    @if ($errors->any())
        <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                Email Address
            </label>
            <input id="email"
                   type="email"
                   name="email"
                   value="{{ old('email', 'rafif@alveo.id') }}"
                   required
                   autofocus
                   class="input input-bordered w-full bg-slate-50/70 border-slate-300 focus:border-forest-800 focus:outline-hidden text-sm rounded-xl" />
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                Password
            </label>
            <input id="password"
                   type="password"
                   name="password"
                   value="password"
                   required
                   class="input input-bordered w-full bg-slate-50/70 border-slate-300 focus:border-forest-800 focus:outline-hidden text-sm rounded-xl" />
        </div>

        <div class="flex items-center justify-between pt-1">
            <label class="inline-flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                <input type="checkbox" name="remember" class="checkbox checkbox-xs rounded-sm border-slate-300 checked:bg-forest-800" />
                <span>Remember session</span>
            </label>
        </div>

        <button type="submit"
                class="btn w-full bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-sm h-11 shadow-xs">
            Sign In
        </button>
    </form>

    <div class="mt-6 pt-5 border-t border-slate-100 text-center text-sm text-slate-500">
        New athlete?
        <a href="{{ route('register') }}" class="font-semibold text-forest-800 hover:text-forest-700">
            Create an account
        </a>
    </div>
</x-guest-layout>

