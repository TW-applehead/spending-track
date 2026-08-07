<div class="row expense-tables">
    @foreach($accounts as $account)
    <div class="col-12 col-lg-6 p-0 text-center">
        {{ $account->name }}帳戶
        <table class="w-100 table table-striped">
            <thead>
                <tr>
                    <th style="width: 85px;" class="text-start">金額</th>
                    <th class="text-start notes-div">說明</th>
                    <th>動作</th>
                </tr>
            </thead>
            <tbody>
                @if(count($account->expenses) > 0)
                    @foreach ($account->expenses as $expense)
                    <tr>
                        <td class="text-start align-content-center" style="color: {{ $expense->is_expense ? 'red' : 'green'}};">${{ number_format($expense->amount) }}</td>
                        <td class="text-start notes-div">
                            <div>{{ $expense->notes }}</div>
                        </td>
                        <td class="align-content-center">
                            <button class="btn btn-dark btn-sm btn-edit-record" data-target="#record-modal" data-toggle="modal" data-account-id="{{ $account->id }}"
                                    data-id="{{ $expense->id }}" data-amount="{{ $expense->amount }}" data-other-account="{{ $expense->other_account }}" data-is-expense="{{ $expense->is_expense }}" data-notes="{{ $expense->notes }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                                </svg>
                            </button>
                            <button class="btn btn-danger btn-sm btn-del-record" data-id="{{ $expense->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                    <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                                </svg>
                            </button>
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
                            <input type="number" class="form-control" id="editAmount" name="amount">
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
                            <label class="form-label d-block">是否為代付</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="otherAccountNo" name="other_account" value="0">
                                <label class="form-check-label" for="otherAccountNo">否</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="otherAccountYes1" name="other_account" value="1">
                                <label class="form-check-label" for="otherAccountYes1">是 (飲食代付)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="otherAccountYes2" name="other_account" value="2">
                                <label class="form-check-label" for="otherAccountYes2">是 (娛樂代付)</label>
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
    $('.btn-edit-record').on('click', function() {
        let id = $(this).data('id');
        let accountId = $(this).data('account-id');
        let amount = $(this).data('amount');
        let isExpense = $(this).data('is-expense');
        let otherAccount = $(this).data('other-account');
        let notes = $(this).data('notes');

        // 填充表單欄位
        $('#expenseId').val(id);
        $('#editAmount').val(amount);
        $('#editNotes').val(notes);
        $('input[name="account_id"][value="' + accountId + '"]').prop('checked', true);
        $('input[name="other_account"][value="' + otherAccount + '"]').prop('checked', true);
        $('input[name="is_expense"][value="' + isExpense + '"]').prop('checked', true);
        $('div[class*="otherAccount"]').show();
        $('.otherAccountYes' + accountId).hide();

        $('#record-modal').modal('show');
    });

    $('.btn-del-record').on('click', function() {
        if (confirm("確定刪除?")) {
            $.ajax({
            url: "{{ route('expense.delete') }}",
            type: 'POST',
            data: {
                id: $(this).data('id'),
                _token: $('input[name="_token"]').val(),
            },
            success: function(response) {
                alert(response.response);
                location.reload();
            },
            error: function(errors) {
                console.error(errors.responseJSON.message);
            }
        });
        } else {
            return;
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
                location.reload();
            },
            error: function(errors) {
                console.error(errors.responseJSON.message);
            }
        });
    });
});
</script>
