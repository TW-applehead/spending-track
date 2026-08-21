<div class="row expense-tables">
    @foreach($accounts as $account)
    <div class="col-12 col-lg-6 px-lg-3 px-0 text-center">
        <div class="position-relative d-flex justify-content-center align-items-center py-2 rounded-top">
            <div class="fw-bold">{{ $account->name }}帳戶</div>
            <button type="button" class="btn btn-dark btn-sm position-absolute end-0 me-2 btn-quick-input"
                    data-bs-toggle="modal"
                    data-bs-target="#wordsModal"
                    data-account-id="{{ $account->id }}"
                    data-account-name="{{ $account->name }}">
                快速輸入
            </button>
        </div>
        <table class="w-100 table table-striped {{ $account->id == 1 ? 'border-bottom mb-4' : 'mb-0' }}">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">
                        <input type="checkbox" class="form-check-input check-all-records">
                    </th>
                    <th class="text-start">說明</th>
                    <th style="width: 90px;" class="text-end">金額</th>
                </tr>
            </thead>
            <tbody>
                @if(count($account->expenses) > 0)
                    @foreach ($account->expenses as $expense)
                    <tr>
                        <td class="text-center align-content-center">
                            <input type="checkbox" class="form-check-input record-checkbox" value="{{ $expense->id }}">
                        </td>
                        <td class="text-start align-content-center notes-div btn-edit-record" style="cursor: pointer;"
                            data-target="#record-modal" data-toggle="modal"
                            data-account-id="{{ $account->id }}" data-id="{{ $expense->id }}"
                            data-amount="{{ $expense->amount }}" data-consumer-id="{{ $expense->consumer_id }}"
                            data-is-expense="{{ $expense->is_expense }}" data-notes="{{ $expense->notes }}">
                            <div>
                                {{ $expense->notes }}
                                @if($expense->payer_id == 2)
                                    (漂付)
                                @elseif($expense->payer_id == 1 && $expense->consumer_id == 2)
                                    (代付漂)
                                @endif
                            </div>
                        </td>
                        <td class="text-end align-content-center" style="color: {{ $expense->is_expense ? 'red' : 'green'}};">
                            ${{ number_format($expense->amount) }}
                        </td>
                    </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="3" class="text-center">尚無紀錄</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr style="border-bottom-color: transparent;">
                    <td class="text-start pt-3 pb-4" colspan="3">
                        <button type="button" class="btn btn-secondary btn-sm me-1 btn-split-records">平分紀錄</button>
                        <button type="button" class="btn btn-secondary btn-sm me-1">計算金額</button>
                        <button type="button" class="btn btn-danger btn-sm me-1 btn-batch-delete">刪除</button>
                    </td>
                </tr>
                <tr>
                    <td class="text-end pt-3 pb-4 border-0" colspan="3">
                        總花費 : <span style="color: {{ $account->quota >= 0 ? 'green' : 'red' }};">${{ number_format(abs($account->quota)) }}</span>
                        <br>
                        (漂代付 : <span style="{{ $account->sub_paid_for_main_balance > 0 ? 'color: red;' : '' }}">${{ number_format($account->sub_paid_for_main_balance) }}</span>)
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endforeach

    <div id="record-modal" class="modal fade" tabindex="-1" aria-labelledby="recordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="recordModalLabel">編輯紀錄</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        @csrf
                        <div class="mb-3">
                            <label for="editAmount" class="form-label">金額</label>
                            <input type="text" class="form-control" id="editAmount" name="amount">
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">帳戶</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="account1" name="account_id" value="1">
                                <label class="form-check-label" for="account1">飲食</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="account2" name="account_id" value="2">
                                <label class="form-check-label" for="account2">娛樂</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">應付人</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="consumerMain" name="consumer_id" value="1">
                                <label class="form-check-label" for="consumerMain">我</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="consumerSub" name="consumer_id" value="2">
                                <label class="form-check-label" for="consumerSub">漂</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">是否為費用</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="isExpenseYes" name="is_expense" value="1">
                                <label class="form-check-label" for="isExpenseYes">是</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="isExpenseNo" name="is_expense" value="0">
                                <label class="form-check-label" for="isExpenseNo">否</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="editNotes" class="form-label">說明</label>
                            <input type="text" class="form-control" id="editNotes" name="notes">
                        </div>
                        <input type="hidden" id="expenseId" name="id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="saveChanges">儲存</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.check-all-records').on('change', function() {
        let isChecked = $(this).is(':checked');

        // 只全選指定 table
        $(this).closest('table').find('.record-checkbox').prop('checked', isChecked);
    });
    $(document).on('change', '.record-checkbox', function() {
        let $table = $(this).closest('table');
        let total = $table.find('.record-checkbox').length;
        let checked = $table.find('.record-checkbox:checked').length;

        // 當勾選數量等於總數時，將該 table 的全選框打勾
        $table.find('.check-all-records').prop('checked', total === checked);
    });

    $('.btn-edit-record').on('click', function() {
        let id = $(this).data('id');
        let accountId = $(this).data('account-id');
        let amount = $(this).data('amount');
        let isExpense = $(this).data('is-expense');
        let consumerId = $(this).data('consumer-id');
        let notes = $(this).data('notes');

        // 填充表單欄位
        $('#expenseId').val(id);
        $('#editAmount').val(amount);
        $('#editNotes').val(notes);
        $('input[name="account_id"][value="' + accountId + '"]').prop('checked', true);
        $('input[name="consumer_id"][value="' + consumerId + '"]').prop('checked', true);
        $('input[name="is_expense"][value="' + isExpense + '"]').prop('checked', true);

        $('#record-modal').modal('show');
    });

    $('.btn-quick-input').on('click', function() {
        let accountId = $(this).data('account-id');
        let accountName = $(this).data('account-name');

        $('#quickAccountId').val(accountId);
        $('#targetAccountName').text(accountName + '帳戶');

        $('#wordsModal').modal('show');
    });

    $('.btn-split-records').on('click', function() {
        let selectedIds = $('.record-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert("請先勾選要平分的紀錄！");
            return;
        }

        if (confirm(`確定要將選取的 ${selectedIds.length} 筆紀錄進行平分嗎？`)) {
            $.ajax({
                url: "{{ route('expense.split') }}",
                type: 'POST',
                data: {
                    ids: selectedIds,
                    _token: $('input[name="_token"]').val(),
                },
                success: function(response) {
                    alert(response.response);

                    var time = $('#expense-tables-time').val();
                    window.location.href = "{{ url('/') }}?expense_time=" + encodeURIComponent(time);
                },
                error: function(errors) {
                    console.error(errors.responseJSON ? errors.responseJSON.message : errors);
                }
            });
        }
    });

    $('.btn-batch-delete').on('click', function() {
        let selectedIds = $('.record-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert("請先勾選要刪除的紀錄！");
            return;
        }

        if (confirm(`確定刪除${selectedIds.length}筆資料?`)) {
            $.ajax({
                url: "{{ route('expense.batch-delete') }}",
                type: 'POST',
                data: {
                    ids: selectedIds,
                    _token: $('input[name="_token"]').val(),
                },
                success: function(response) {
                    alert(response.response);

                    var time = $('#expense-tables-time').val();
                    window.location.href = "{{ url('/') }}?expense_time=" + encodeURIComponent(time);
                },
                error: function(errors) {
                    console.error(errors.responseJSON ? errors.responseJSON.message : errors);
                }
            });
        }
    });

    $('#saveChanges').on('click', function() {
        var formData = $('#editForm').serialize();

        $.ajax({
            url: "{{ route('expense.update') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                alert(response.response);

                var time = $('#expense-tables-time').val();
                window.location.href = "{{ url('/') }}?expense_time=" + encodeURIComponent(time);
            },
            error: function(errors) {
                console.error(errors.responseJSON.message);
            }
        });
    });

    // 編輯視窗的金額可以做加減乘除運算
    $('#editAmount').on('change', function() {
        let inputVal = $(this).val().trim();

        if (/[+\-*/]/.test(inputVal)) {
            try {
                let sanitizedVal = inputVal.replace(/[^0-9.+\-*/()]/g, '');

                if (sanitizedVal) {
                    let result = new Function('"use strict"; return (' + sanitizedVal + ')')();

                    if (!isNaN(result) && isFinite(result)) {
                        $(this).val(Math.round(result));
                    }
                }
            } catch (e) {
                console.warn('無效的計算公式:', inputVal);
            }
        }
    });
});
</script>
