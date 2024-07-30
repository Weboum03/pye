<header>
    <h2 class="text-lg font-medium text-gray-900">
        Transactions
    </h2>
</header>

<div class="relative overflow-x-auto sm:rounded-lg mt-6 space-y-6">
    <table class="w-full text-sm text-left rtl:text-right" id="myTable">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th class="py-3 px-6 text-center">ID</th>
                <th class="py-3 px-6 text-center">Name</th>
                <th class="py-3 px-6 text-center">Email</th>
                <th class="py-3 px-6 text-center">Phone</th>
                <th class="py-3 px-6 text-center">DOB</th>
                <th class="py-3 px-6 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>

            @foreach($users as $user)
            <tr class="bg-white border-b dark:border-gray-700">
                <th scope="row" class="px-6 py-4 text-center">
                    {{ $user->id }}
                </th>
                <td class="px-6 py-4 text-center">
                    {{ $user->name }}
                </td>
                <td class="px-6 py-4 text-center">
                    {{ $user->email }}
                </td>
                <td class="px-6 py-4 text-center">
                    {{ $user->phone }}
                </td>
                <td class="px-6 py-4 text-center">
                    {{ $user->dob }}
                </td>
                <td class="px-6 py-4 text-center">
                    <a href="/users/{{ $user->id }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
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
@endsection