<header>
    <h2 class="text-lg font-medium text-gray-900">
        {{__('Tickets')}}
    </h2>
</header>

<form method="get" action="" class="mt-6 space-y-6">
    <div class="flex flex-row rounded">
        <div class="">
            <x-input-label for="from" :value="__('From')" />
            <x-text-input name="from" type="date" class="mt-1 block" :value="old('from', request()->from)" required autocomplete="from" />
            <x-input-error class="mt-2" :messages="$errors->get('from')" />
        </div>

        <div class="px-6">
            <x-input-label for="to" :value="__('To')" />
            <x-text-input name="to" type="date" class="mt-1 block" :value="old('to', request()->to)" required autocomplete="to" />
            <x-input-error class="mt-2" :messages="$errors->get('to')" />
        </div>


        <div class="flex items-center gap-4 mt-4">
            <x-primary-button>{{ __('Filter') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </div>
</form>

<div class="relative overflow-x-auto sm:rounded-lg mt-6 space-y-6">
    <table class="w-full text-sm text-left rtl:text-right" id="myTable" x-data="">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th class="py-3 px-6 text-center">ID</th>
                <th class="py-3 px-6 text-center">subject</th>
                <th class="py-3 px-6 text-center">description</th>
                <th class="py-3 px-6 text-center">Status</th>
                <th class="py-3 px-6 text-center">Date</th>
                <th class="py-3 px-6 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>

            @foreach($tickets as $ticket)
            <tr class="bg-white border-b dark:border-gray-700">
                <th scope="row" class="px-6 py-4 text-center">
                    #{{ $ticket->id }}
                </th>
                <td class="px-6 py-4 text-center">
                    {{ $ticket->subject }}
                </td>
                <td class="px-6 py-4 text-center">
                    {{ $ticket->description }}
                </td>
                <td class="px-6 py-4 text-center">
                    {{ $ticket->status }}
                </td>
                <td class="px-6 py-4 text-center">
                    {{ $ticket->created_at }}
                </td>
                <td class="px-6 py-4 text-center">
                    @if($ticket->status != 'close')
                        <x-anchor-button href="tickets/{{ $ticket->id }}/reply">{{ __('Reply') }}</x-anchor-button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@section('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">

<style>
    .dataTables_wrapper .dataTables_length select {
        padding-right: 2.5rem
    }
</style>
@endsection

@section('scripts')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('#myTable').DataTable();
    });
</script>

<script>

const elements = document.getElementsByClassName('confirm-delete');

const event = new CustomEvent('open-modal', {
    detail: 'confirm-key-reset',
    bubbles:true,
    cancelable:true,
    composed:true
});

function confirm(value) {
    const eventSecond = new CustomEvent('update-value', {
        detail: value,
    });
    this.dispatchEvent(event);
    this.dispatchEvent(eventSecond);
}

</script>
@endsection