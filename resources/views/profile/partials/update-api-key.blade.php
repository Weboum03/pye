<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update API Key') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's API Key.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('key.generate') }}" class="mt-6 space-y-6">
        @csrf
        @method('post')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" readonly name="name" type="text" class="mt-1 block w-full bg-grey" :value="old('name', $user->apiKeys()->latest()->first()?->key)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Generate') }}</x-primary-button>

            @if (session('status') === 'key-updated')
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
