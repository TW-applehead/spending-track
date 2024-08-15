<x-app-layouts>
    <form action="{{ route('expense.store') }}" method="POST">
        @csrf
        <div class="row mb-3">
            <div class="col-12 text-center">刷卡 (當月不會扣 所以要記)</div>
            <div class="col-12 text-center mb-3">台新代付 (直接算在該帳戶)</div>
            <!-- 金額輸入框 -->
            <div class="form-group col-md-6">
                <label for="amount">金額</label>
                <input type="number" class="form-control" id="amount" name="amount" required>
            </div>

            <!-- 帳戶選擇 -->
            <div class="form-group col-md-6">
                <label for="account">選擇帳戶</label>
                <select class="form-control" id="account" name="account_id" required>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 收入或支出選擇 -->
            <div class="form-group col-md-6">
                <label>類型</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="is_expense" id="expense" value="1" checked>
                    <label class="form-check-label" for="expense">花費</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="is_expense" id="income" value="0">
                    <label class="form-check-label" for="income">收入</label>
                </div>
            </div>

            <!-- 其他帳戶代付 -->
            <div class="form-group col-md-6">
                <label>是否為其他帳戶代付？</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="other_account" id="is_not_other_account" value="0" checked>
                    <label class="form-check-label" for="is_not_other_account">否</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="other_account" id="is_food_account" value="1">
                    <label class="form-check-label" for="is_food_account">是，飲食代付</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="other_account" id="is_entertain_account" value="2">
                    <label class="form-check-label" for="is_entertain_account">是，娛樂代付</label>
                </div>
            </div>

            <!-- 時間 -->
            <div class="form-group col-md-6">
                <label for="account">時間</label>
                <select class="form-control" id="time" name="expense_time" required>
                    @foreach($months as $month)
                        @if($month == now()->format('Ym'))
                            <option value="{{ $month }}" selected>{{ $month }}</option>
                        @else
                            <option value="{{ $month }}">{{ $month }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- 說明 -->
            <div class="form-group col-md-6">
                <label for="amount">說明</label>
                <input type="text" class="form-control" id="notes" name="notes">
            </div>

            <button type="submit" class="btn btn-primary mx-auto">儲存</button>
        </div>
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

    <div class="row">
        @foreach($accounts as $account)
        <div class="col-md-6">
            <table class="w-100">
                <thead>
                    <tr>
                        <th>{{ $account->name }}帳戶</th>
                        <th>代付</th>
                        <th>說明</th>
                        <th>動作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($account->expenses as $expense)
                    <tr>
                        <td style="color: {{ $expense->is_expense ? 'red' : 'green'}};">{{ $expense->amount }}</td>
                        <td>{{ $expense->other_account == 0 ? '是' : '否' }}</td>
                        <td>{{ $expense->notes }}</td>
                        <td>
                            <button class="btn btn-secondary">編輯</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
        @foreach($accounts as $account)
        <div class="col-md-6 mt-3">
            {{ $account->name }} : <span style="color: {{ $account->quota >= 0 ? 'green' : 'red' }};">{{ abs($account->quota) }}</span>
            {{ $account->balance_difference ? '' : ' (尚無下個月餘額)' }}
        </div>
        @endforeach
    </div>

    <div class="row my-5">
        <a href="{{ route('accounts.index') }}">修改帳戶扣打</a>
    </div>
</x-app-layouts>
