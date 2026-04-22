@extends('admin_panel.layout.app')
@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="container">
                <h3>Add New Customer</h3>
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf

                    @php
                        $branchesList = $branches ?? \App\Models\Branch::all();
                    @endphp



                    <div class="row mb-1">
                        <div class="col-md-2 mb-3">
                            <label><strong>Customer ID:</strong></label>
                            <input type="text" class="form-control" name="customer_id" readonly value="{{ $latestId }}">
                        </div>
                        <div class="col-md-2">
                            <label><strong>Customer Type :</strong></label>
                            <select class="form-control" name="customer_type">
                                <option>Credit</option>
                                <option>Cash</option>
                            </select>
                        </div>

                        @if(auth()->user() && auth()->user()->hasRole('super admin'))
                            <div class="col-md-2">
                                <label><strong>Branch:</strong></label>
                                <select class="form-control" name="branch_id" id="branch_id_select">
                                    <option value="">Select Branch</option>
                                    @foreach($branchesList as $b)
                                        <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name ?? $b->branch_name ?? 'Branch '. $b->id }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id ?? 0 }}">
                        @endif
                        <div class="col-md-2">
                            <label><strong>Customer:</strong></label>
                            <input type="text" class="form-control" name="customer_name"
                                value="{{ old('customer_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label>NTN / CNIC no:</label>
                            <input type="text" class="form-control" name="cnic" value="{{ old('cnic') }}">

                        </div>


                        <div class="row mb-2">

                            <div class="col-md-3 ">
                                <label>Filer Type:</label>
                                <select class="form-control" name="filer_type">
                                    <option value="filer">Filer</option>
                                    <option value="non filer">Non Filer</option>
                                    <option value="exempt">Exempt</option>
                                </select>
                            </div>

                            <div class="col-md-3 ">
                                <label>Mobile:</label>
                                <input type="text" class="form-control" name="mobile" value="{{ old('mobile_2') }}">
                            </div>

                             <div class="col-md-3 mb-4">
                            <label>Address:</label>
                            <textarea rows="1" class="form-control" name="address">{{ old('address') }}</textarea>
                        </div>


                        </div>





                        <div class="row mb-4">

                            {{--  <div class="col-md-6">
                <label>Credit (Cr):</label>
                <input type="number" class="form-control" name="credit" value="{{ old('credit') }}">
            </div>  --}}
                        </div>



                        <div class="text-center">
                            <button class="btn btn-success" type="button" id="saveCustomerBtn">Save Customer</button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Opening Balance Modal -->
    <div class="modal fade" id="openingBalanceModal" tabindex="-1" role="dialog" aria-labelledby="openingBalanceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="openingBalanceModalLabel">Customer Credit Setup</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="balanceForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="opening_balance"><strong>Opening Balance (Dr):</strong></label>
                            <input type="number" class="form-control" id="opening_balance" name="opening_balance"
                                   step="0.01" min="0" required>
                            <small class="form-text text-muted">Customer's initial balance</small>
                        </div>

                        <div class="form-group">
                            <label for="credit_limit"><strong>Credit Limit Amount:</strong></label>
                            <input type="number" class="form-control" id="credit_limit" name="credit_limit"
                                   step="0.01" min="0" required>
                            <small class="form-text text-muted">Maximum credit amount you can provide to the customer</small>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="no_credit_limit" name="no_credit_limit">
                            <label class="form-check-label" for="no_credit_limit">
                                No Credit Limit (Infinite)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="$('#openingBalanceModal').modal('hide')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save & Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <script>
        $(document).ready(function() {
            // Get references
            const mainForm = $('form[method="POST"]');
            const balanceForm = $('#balanceForm');
            const modal = $('#openingBalanceModal');
            const customerTypeSelect = $('select[name="customer_type"]');

            // Save Customer button click
            $('#saveCustomerBtn').click(function(e) {
                e.preventDefault();

                // Validate main form
                if (!mainForm[0].checkValidity()) {
                    e.stopPropagation();
                    mainForm.addClass('was-validated');
                    return;
                }

                // ✅ CHECK CUSTOMER TYPE
                const customerType = customerTypeSelect.val();
                console.log('Customer Type:', customerType);

                // ✅ IF Cash: Skip modal and submit directly
                if (customerType === 'Cash') {
                    console.log('✅ Cash - Skipping balance/credit modal');

                    // Add default values for walking customers
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'opening_balance',
                        value: '0'
                    }).appendTo(mainForm);

                    $('<input>').attr({
                        type: 'hidden',
                        name: 'credit_limit',
                        value: '0'
                    }).appendTo(mainForm);

                    // Submit form directly without modal
                    mainForm.submit();
                    return;
                }

                // ✅ IF MAIN CUSTOMER: Show modal for balance/credit setup
                console.log('📋 Main Customer - Showing balance modal');
                modal.modal('show');
            });

            // Close modal button functionality
            $('#openingBalanceModal').on('hidden.bs.modal', function() {
                // Modal closed
                balanceForm[0].reset();
            });

            // Handle no credit limit checkbox
            $('#no_credit_limit').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('#credit_limit').prop('disabled', isChecked);
                if (isChecked) {
                    $('#credit_limit').val('');
                }
            });

            // Balance form submission
            balanceForm.on('submit', function(e) {
                e.preventDefault();

                try {
                    // Get values
                    const opening_balance = $('#opening_balance').val();
                    const credit_limit = $('#credit_limit').val();
                    const no_credit_limit = $('#no_credit_limit').is(':checked');

                    // Validate opening balance (always required)
                    if (!opening_balance) {
                        alert('Please enter opening balance');
                        return;
                    }

                    // Validate credit limit (only if no credit limit is not checked)
                    if (!no_credit_limit && !credit_limit) {
                        alert('Please enter credit limit or check "No Credit Limit"');
                        return;
                    }

                    // Validate numeric values
                    if (isNaN(opening_balance) || (!no_credit_limit && isNaN(credit_limit))) {
                        alert('Please enter valid numbers');
                        return;
                    }

                    // Add hidden fields to main form
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'opening_balance',
                        value: opening_balance
                    }).appendTo(mainForm);

                    // Only add credit_limit if no_credit_limit is not checked
                    if (!no_credit_limit) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'credit_limit',
                            value: credit_limit
                        }).appendTo(mainForm);
                    }

                    $('<input>').attr({
                        type: 'hidden',
                        name: 'no_credit_limit',
                        value: no_credit_limit ? '1' : '0'
                    }).appendTo(mainForm);

                    // Close modal and submit
                    modal.modal('hide');

                    // Small delay to ensure modal closes
                    setTimeout(function() {
                        mainForm.submit();
                    }, 100);

                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while saving. Please try again.');
                }
            });
        });
    </script>
@endsection
