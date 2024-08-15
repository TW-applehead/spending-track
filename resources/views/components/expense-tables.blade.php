<div class="row">
    @foreach($accounts as $account)
    <div class="col-md-6 text-center">
        <table class="w-75">
            <thead>
                <tr>
                    <th>{{ $account->name }}帳戶</th>
                    <th>代付</th>
                    <th>說明</th>
                    <th>動作</th>
                </tr>
            </thead>
            <tbody>
                @if(count($account->expenses) > 0)
                @foreach ($account->expenses as $expense)
                <tr>
                    <td style="color: {{ $expense->is_expense ? 'red' : 'green'}};">{{ $expense->amount }}</td>
                    <td>{{ $expense->other_account == 0 ? '否' : '是' }}</td>
                    <td>{{ $expense->notes }}</td>
                    <td>
                        <button class="btn btn-secondary">編輯</button>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="4" class="text-center">尚無紀錄</td>
                </tr>
                @endif
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
