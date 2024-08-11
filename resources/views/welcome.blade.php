<x-app-layouts>
    <form action="{{ route('expense.store') }}" method="POST">
        @csrf
        <!-- 金額輸入框 -->
        <div class="form-group">
            <label for="amount">金額</label>
            <input type="number" class="form-control" id="amount" name="amount" required>
        </div>

        <!-- 帳戶選擇 -->
        <div class="form-group">
            <label for="account">選擇帳戶</label>
            <select class="form-control" id="account" name="account_id" required>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- 收入或支出選擇 -->
        <div class="form-group">
            <label>類型</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="expense" value="1" checked>
                <label class="form-check-label" for="expense">花費</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="income" value="2">
                <label class="form-check-label" for="income">收入</label>
            </div>
        </div>

        <!-- 其他帳戶代付 -->
        <div class="form-group">
            <label>是否為其他帳戶代付？</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="is_other_account" id="is_other_account_yes" value="1">
                <label class="form-check-label" for="is_other_account_yes">是</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="is_other_account" id="is_other_account_no" value="0" checked>
                <label class="form-check-label" for="is_other_account_no">否</label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">儲存</button>
    </form>
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

    <a href="{{ route('accounts.index') }}">帳戶</a>
</x-app-layouts>
