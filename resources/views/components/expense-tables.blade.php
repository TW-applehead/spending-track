<div class="row">
    @foreach($accounts as $account)
    <div class="col-md-6 text-center">
        <table class="w-100 table shadow-sm">
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
                            <button class="btn btn-dark btn-sm btn-edit-record" data-target="#record-modal" data-toggle="modal" data-account-id="{{ $account->id }}"
                                    data-id="{{ $expense->id }}" data-amount="{{ $expense->amount }}" data-other-account="{{ $expense->other_account }}" data-is-expense="{{ $expense->is_expense }}" data-notes="{{ $expense->notes }}">
                                編輯
                            </button>
                            <button class="btn btn-danger btn-sm btn-del-record" data-id="{{ $expense->id }}">
                                刪除
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

    <div id="record-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">編輯紀錄</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        @csrf
                        <div class="mb-3">
                            <label for="editAmount" class="form-label">金額</label>
                            <input type="number" class="form-control" id="editAmount" name="amount">
                        </div>
                        <div class="mb-3 d-flex">
                            <label class="form-label">是否為代付</label>
                            <div class="mx-3 otherAccountNo">
                                <input type="radio" id="otherAccountNo" name="other_account" value="0">
                                <label for="otherAccountNo">否</label>
                            </div>
                            <div class="mx-3 otherAccountYes1">
                                <input type="radio" id="otherAccountYes1" name="other_account" value="1">
                                <label for="otherAccountYes1">是 (飲食代付)</label>
                            </div>
                            <div class="mx-3 otherAccountYes2">
                                <input type="radio" id="otherAccountYes2" name="other_account" value="2">
                                <label for="otherAccountYes2">是 (娛樂代付)</label>
                            </div>
                        </div>
                        <div class="mb-3 d-flex">
                            <label class="form-label">是否為費用</label>
                            <div class="mx-3">
                                <input type="radio" id="isExpenseYes" name="is_expense" value="1">
                                <label for="isExpenseYes">是</label>
                            </div>
                            <div class="mx-3">
                                <input type="radio" id="isExpenseNo" name="is_expense" value="0">
                                <label for="isExpenseNo">否</label>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
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
        $('input[name="other_account"][value="' + otherAccount + '"]').prop('checked', true);
        $('input[name="is_expense"][value="' + isExpense + '"]').prop('checked', true);
        $('div[class*="otherAccount"]').show();
        $('.otherAccountYes' + accountId).hide();

        $('#editModal').modal('show');
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
