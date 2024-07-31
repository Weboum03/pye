<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Update IP Address
        </h2>
    </header>
    <div class="flex items-center gap-4">
        @if (session('status') === 'user-created')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600"
            >Created successfully</p>
        @endif
    </div>
    <form method="post" action="{{ route('ip_whitelists.update', ['id' => $user->id]) }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="Address" :value="__('IP Address')" />
            <x-text-input id="name" name="ip_address" type="text" class="mt-1 block w-full" :value="old('ip_address', $user->ip_address)" required autofocus autocomplete="ip_address" />
            <x-input-error class="mt-2" :messages="$errors->get('ip_address')" />
        </div>

        <div>
            <x-input-label for="Domain" value="Domain" />
            <x-text-input id="Domain" name="domain" type="text" class="mt-1 block w-full" :value="old('domain', $user->domain)" required autocomplete="domain" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
