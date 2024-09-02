<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="flex items-center gap-4">
                    @if (session('status') === 'user-created')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">Created successfully</p>
                    @endif
                    @if (session('status') === 'profile-updated')
                    <div class="max-w-xl">
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">Updated successfully</p>

                        @endif
                    </div>
                </div>
                @endif
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="container mx-auto p-4">
                        <!-- Ticket Details -->
                        <div class="bg-white shadow-md rounded-lg mb-6 p-6">
                            <h3 class="text-2xl font-semibold mb-2">Ticket: {{ $ticket->subject }}</h3>
                            <p class="text-gray-700 mb-2"><strong>Status:</strong> <span class="text-indigo-600">{{ ucfirst($ticket->status) }}</span></p>
                            <p class="text-gray-700 mb-4">{{ $ticket->description }}</p>
                            <p class="text-gray-500 text-sm">Created by: {{ $ticket->user->name }}</p>
                            <p class="text-gray-500 text-sm">Created at: {{ $ticket->created_at->format('d M Y, H:i') }}</p>
                        </div>

                        <!-- Replies Section -->
                        <div class="bg-white shadow-md rounded-lg mb-6 p-6">
                            <h4 class="text-xl font-semibold mb-4">Replies</h4>
                            @if($ticket->replies->count())
                            @foreach($ticket->replies as $reply)
                            <div class="mb-4">
                                <p class="text-sm text-gray-500"><strong>{{ $reply->user?->name }}</strong> on {{ $reply->created_at->format('d M Y, H:i') }}</p>
                                <p class="text-gray-700">{{ $reply->message }}</p>
                            </div>
                            <hr class="my-4 border-t border-gray-200">
                            @endforeach
                            @else
                            <p class="text-gray-600">No replies yet.</p>
                            @endif
                        </div>

                        <!-- Reply Form -->
                        <div class="bg-white shadow-md rounded-lg p-6">
                            <h4 class="text-xl font-semibold mb-4">Add a Reply</h4>
                            <form action="{{ route('tickets.reply', $ticket) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="message" class="block text-gray-700 text-sm font-bold mb-2">Your Reply</label>
                                    <textarea name="message" id="message" rows="5" class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" required></textarea>
                                </div>
                                <x-primary-button>{{ __('Submit Reply') }}</x-primary-button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
</x-app-layout>