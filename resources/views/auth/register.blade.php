<x-guest-layout title="Register">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Create Athlete Account</h1>
        <p class="mt-1 text-sm text-slate-500">New accounts require manual administrator verification before dashboard access.</p>
    </div>

    @if ($errors->any())
        <div class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.submit') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                Full Name
            </label>
            <input id="name"
                   type="text"
                   name="name"
                   value="{{ old('name') }}"
                   required
                   autofocus
                   placeholder="Rafif Muchsin"
                   class="input input-bordered w-full bg-slate-50/70 border-slate-300 focus:border-forest-800 focus:outline-hidden text-sm rounded-xl" />
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                Email Address
            </label>
            <input id="email"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   placeholder="athlete@club.id"
                   class="input input-bordered w-full bg-slate-50/70 border-slate-300 focus:border-forest-800 focus:outline-hidden text-sm rounded-xl" />
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                Password
            </label>
            <input id="password"
                   type="password"
                   name="password"
                   required
                   class="input input-bordered w-full bg-slate-50/70 border-slate-300 focus:border-forest-800 focus:outline-hidden text-sm rounded-xl" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                Confirm Password
            </label>
            <input id="password_confirmation"
                   type="password"
                   name="password_confirmation"
                   required
                   class="input input-bordered w-full bg-slate-50/70 border-slate-300 focus:border-forest-800 focus:outline-hidden text-sm rounded-xl" />
        </div>

        <button type="submit"
                class="btn w-full bg-forest-900 hover:bg-forest-800 text-white border-0 rounded-xl font-semibold text-sm h-11 shadow-xs">
            Register Account
        </button>
    </form>

    <div class="mt-6 pt-5 border-t border-slate-100 text-center text-sm text-slate-500">
        Already registered?
        <a href="{{ route('login') }}" class="font-semibold text-forest-800 hover:text-forest-700">
            Sign in
        </a>
    </div>
</x-guest-layout>

