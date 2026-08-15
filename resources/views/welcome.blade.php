<x-app-layouts>
    @if(session('success'))
        <div class="alert alert-success my-3">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger my-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="text-center fs-3 fw-bold mt-3">記帳系統 2.0</div>
    <div class="text-center mt-1 mb-3">
        只手動記<span class="fw-bold">現金交易</span>、<span class="fw-bold">非台銀匯款</span>與 漂代付
    </div>
    <div class="text-end mb-3">
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
            匯入信用卡帳單
        </button>
    </div>

    <div class="bg-white shadow-sm rounded px-3 mt-5">
        <div class="pt-4">
            <input
                type="text"
                class="form-control w-auto"
                id="expense-tables-time"
                name="expense-tables-time"
                value="{{ request('expense-tables-time', now()->format('Ym')) }}"
                placeholder="YYYYMM"
                maxlength="6"
            >
        </div>
        <div class="expense-tables">
            <x-expense-tables :time="now()->format('Ym')" />
        </div>
    </div>

    <div class="row my-5 d-none">
        <a href="{{ route('accounts.index') }}">修改帳戶扣打</a>
    </div>

    <form action="{{ route('expense.store') }}" method="POST" class=" my-5">
        @csrf
        <div class="row">
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
                    <input class="form-check-input" type="radio" name="other_account" id="is-not-other-account" value="0" checked>
                    <label class="form-check-label" for="is_not_other_account">否</label>
                </div>
                <div class="form-check form-check-inline is-food-account" style="display: none">
                    <input class="form-check-input" type="radio" name="other_account" id="is-food-account" value="1">
                    <label class="form-check-label" for="is_food_account">是，飲食代付</label>
                </div>
                <div class="form-check form-check-inline is-entertain-account">
                    <input class="form-check-input" type="radio" name="other_account" id="is-entertain-account" value="2">
                    <label class="form-check-label" for="is_entertain_account">是，娛樂代付</label>
                </div>
            </div>

            <!-- 時間 -->
            <div class="form-group col-md-6">
                <label for="expense_time">時間</label>
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

            <div class="text-center my-3">
                <button type="submit" class="btn btn-primary mx-auto">儲存</button>
            </div>
        </div>
    </form>

    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('expense.import') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">匯入信用卡帳單</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="billText" class="form-label">請將帳單內容貼於下方文字方塊：</label>
                            <textarea class="form-control" id="billText" name="text" rows="12" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">匯入</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layouts>

<script>
    $(document).ready(function() {
        $('#account').on('change', function() {
            let account = $(this).val();
            if (account == 1) {
                $('.is-food-account').hide();
                $('.is-entertain-account').show();
            } else if (account == 2)  {
                $('.is-food-account').show();
                $('.is-entertain-account').hide();
            }
        });

        $('#expense-tables-time').on('change', function() {
            let selectedTime = $(this).val();

            $.ajax({
                url: "{{ route('expense.tables') }}",
                method: 'GET',
                data: {
                    time: selectedTime
                },
                beforeSend : function(){
                    $('.expense-tables').html('');
                },
                success: function(response) {
                    $('.expense-tables').append(response);
                },
                error: function(errors) {
                    console.error(errors.responseJSON.message);
                }
            });
        });
    });
</script>
