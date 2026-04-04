<x-guest-layout>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 py-10 px-4">
    <div class="max-w-md w-full">
      <div class="bg-white rounded-2xl shadow-xl p-6 space-y-4 hover:shadow-2xl transition-shadow duration-300">
        {{-- Logo & Header (lebih ringkas) --}}
        <div class="text-center">
          <div class="flex items-center justify-center gap-2 sm:gap-3 mb-3">
              <img src="{{ asset('images/danantara.png') }}" alt="Danantara" class="h-8 sm:h-10 w-auto object-contain">
              <img src="{{ asset('images/hsse.png') }}" alt="HSSE" class="h-8 sm:h-10 w-auto object-contain">
              <img src="{{ asset('images/logoo.png') }}" alt="PLN" class="h-8 sm:h-10 w-auto object-contain">
          </div>
          <h2 class="text-2xl font-extrabold text-gray-900">Create Account</h2>
          <p class="mt-1 text-xs text-gray-600">Register to start using the system</p>
        </div>

        <form class="space-y-4" method="POST" action="{{ route('register') }}">
          @csrf

          {{-- Name --}}
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input id="name" name="name" type="text" required autocomplete="name" value="{{ old('name') }}"
                   class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                   placeholder="Your name">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Email --}}
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input id="email" name="email" type="email" required autocomplete="email" value="{{ old('email') }}"
                   class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                   placeholder="you@example.com">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Password --}}
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                   placeholder="••••••••">
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Confirm --}}
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="••••••••">
          </div>

          {{-- Submit (lebih compact) --}}
          <button type="submit"
                  class="w-full h-10 rounded-full text-sm font-semibold text-indigo-600 border-2 border-indigo-600 hover:text-white hover:bg-indigo-600 transition">
            Create Account
          </button>
        </form>

        {{-- Link back to Login (kecil & rapih) --}}
        <p class="text-center text-xs text-gray-600 pt-3 border-t border-gray-200">
          Already have an account?
          <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 transition">Sign in</a>
        </p>
      </div>
    </div>
  </div>
</x-guest-layout>
