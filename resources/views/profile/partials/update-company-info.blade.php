<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Company Information
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's Company information and email address.") }}
        </p>
    </header>
    <div class="flex items-center gap-4">
        @if (session('status') === 'company-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600"
            >Updated successfully</p>
        @endif
    </div>
    <form method="post" action="{{ route('profile.update.company', ['id' => $user->id]) }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Company Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->company?->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->company?->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
        <div>
            <x-input-label for="Phone" value="Phone" />
            <x-text-input id="phone" name="phone" type="number" class="mt-1 block w-full" :value="old('phone', $user->company?->phone)" required autocomplete="phone" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>
        <div>
            <x-input-label for="address" value="Address" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $user->company?->address)" required autocomplete="address" />
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div>
            <x-input-label for="state" value="State" />
            <x-text-input id="state" name="state" type="text" class="mt-1 block w-full" :value="old('state', $user->company?->state)" required autocomplete="state" />
            <x-input-error class="mt-2" :messages="$errors->get('state')" />
        </div>

        <div>
            <x-input-label for="city" value="City" />
            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $user->company?->city)" required autocomplete="city" />
            <x-input-error class="mt-2" :messages="$errors->get('city')" />
        </div>

        <div>
            <x-input-label for="zipcode" value="Zipcode" />
            <x-text-input id="zipcode" name="zipcode" type="text" class="mt-1 block w-full" :value="old('zipcode', $user->company?->zipcode)" required autocomplete="zipcode" />
            <x-input-error class="mt-2" :messages="$errors->get('zipcode')" />
        </div>

        <div>
            <x-input-label for="website" value="Website URL" />
            <x-text-input id="website" name="website" type="text" class="mt-1 block w-full" :value="old('website', $user->company?->website)" required autocomplete="website" />
            <x-input-error class="mt-2" :messages="$errors->get('website')" />
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
