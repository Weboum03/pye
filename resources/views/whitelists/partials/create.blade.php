<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Create New
        </h2>
    </header>
    <form method="POST" action="{{ route('ip_whitelists') }}" class="mt-6 space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('IP Address')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="ip_address" :value="old('ip_address')" required autofocus autocomplete="ip_address" />
            <x-input-error :messages="$errors->get('ip_address')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Domain')" />
            <x-text-input id="email" class="block mt-1 w-full" type="text" name="domain" :value="old('domain')" required autocomplete="domain" />
            <x-input-error :messages="$errors->get('domain')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</section>
