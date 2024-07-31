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
            <x-input-label for="name" :value="__('API Key')" />
            <x-text-input id="name" readonly name="name" type="text" class="mt-1 block w-full bg-grey" :value="old('name', $user->apiKeys()->latest()->first()?->key)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="flex items-center gap-4">
            @if($user->apiKeys()->latest()->first())
            <x-primary-button x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-key-reset')"
        >{{ __('Generate') }}</x-primary-button>
            @else
            <x-primary-button >{{ __('Generate') }}</x-primary-button>
            @endif
            

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

    <x-modal name="confirm-key-reset" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('key.generate') }}" class="p-6">
            @csrf
            @method('post')

            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Are you sure you want to reset your API Key?') }}
            </h2>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Confirm') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
