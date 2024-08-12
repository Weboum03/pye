<header>
    <h2 class="text-lg font-medium text-gray-900">
        {{__('Transactions')}}
    </h2>
</header>

<div class="relative overflow-x-auto sm:rounded-lg mt-6 space-y-6">
    <table class="w-full text-sm text-left rtl:text-right" id="myTable" x-data="">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th class="py-3 px-6 text-center">ID</th>
                <th class="py-3 px-6 text-center">Transaction ID</th>
                <th class="py-3 px-6 text-center">Order ID</th>
                <th class="py-3 px-6 text-center">Amount</th>
                <th class="py-3 px-6 text-center">Type</th>
                <th class="py-3 px-6 text-center">Status</th>
                <th class="py-3 px-6 text-center">Date</th>
                <th class="py-3 px-6 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>

            @foreach($transactions as $transaction)
            <tr class="bg-white border-b dark:border-gray-700">
                <th scope="row" class="px-6 py-4 text-center">
                    {{ $transaction->id }}
                </th>
                <td class="px-6 py-4 text-center">
                    {{ $transaction->transaction_id }}
                </td>
                <td class="px-6 py-4 text-center">
                    #{{ $transaction->order_id }}
                </td>
                <td class="px-6 py-4 text-center">
                    {{ $transaction->amount }}
                </td>
                <td class="px-6 py-4 text-center">
                    {{ $transaction->type }}
                </td>
                <td class="px-6 py-4 text-center">
                    {{ $transaction->status }}
                </td>
                <td class="px-6 py-4 text-center">
                    {{ $transaction->created_at }}
                </td>
                <td class="px-6 py-4 text-center">
                    @if($transaction->status == 'completed' && $transaction->type != 'refund')
                        <x-primary-button onClick="confirm({{ $transaction->id }})" class="confirm-delete"
                    >{{ __('Refund') }}</x-primary-button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <x-modal name="confirm-key-reset" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form x-data="{ transactionId:''}" x-on:update-value.window="($event) => { transactionId = $event.detail; }" method="post" action="{{ route('transactions.refund') }}" class="p-6">
            @csrf
            @method('patch')
            <input type="hidden" name="id" x-model="transactionId" x-bind:value="transactionId">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Are you sure you want to refund this transaction?') }}
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