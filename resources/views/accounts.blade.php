<x-app-layouts>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Monthly Allowance</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($accounts as $account)
                <tr>
                    <form action="{{ route('accounts.update', $account->id) }}" method="POST">
                        @csrf
                        @method('POST')
                        <td><input type="text" name="name" value="{{ $account->name }}"></td>
                        <td><input type="text" name="monthly_allowance" value="{{ $account->monthly_allowance }}"></td>
                        <td><button type="submit">Update</button></td>
                    </form>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-success">
            {{ session('error') }}
        </div>
    @endif
</x-app-layouts>
