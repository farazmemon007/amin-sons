@extends('admin_panel.layout.app')

@section('content')
<style>
    .find-wrapper { max-width: 960px; margin: 30px auto; padding: 0 16px; }

    .find-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        border-radius: 16px;
        padding: 36px 32px;
        margin-bottom: 28px;
        box-shadow: 0 12px 40px rgba(0,0,0,.25);
        position: relative;
        overflow: hidden;
    }
    .find-header::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 200px; height: 200px;
        background: radial-gradient(circle, rgba(99,102,241,.15), transparent 70%);
        border-radius: 50%;
    }
    .find-header h2 {
        color: #fff;
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0 0 6px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .find-header h2 i { color: #818cf8; font-size: 1.3rem; }
    .find-header p { color: #94a3b8; font-size: 13px; margin: 0; }

    /* Search Card */
    .search-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(0,0,0,.06);
        border: 1px solid #e2e8f0;
        padding: 28px;
        margin-bottom: 28px;
    }

    .search-row {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    .search-field { flex: 1; min-width: 180px; }
    .search-field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 6px;
    }
    .search-field select,
    .search-field input {
        width: 100%;
        padding: 10px 14px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        background: #f8fafc;
        transition: all .2s;
        outline: none;
    }
    .search-field select:focus,
    .search-field input:focus {
        border-color: #6366f1;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(99,102,241,.1);
    }

    .btn-find {
        padding: 10px 28px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
    .btn-find:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.35); }
    .btn-find:disabled { opacity: .5; cursor: not-allowed; transform: none; }

    /* Branch selector bar */
    .branch-bar {
        background: linear-gradient(135deg, #1a3a6e, #1e40af);
        border-radius: 10px;
        padding: 14px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }
    .branch-bar label { color: #93c5fd; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; }
    .branch-bar select {
        border: 2px solid #3b82f6;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        background: #eff6ff;
        min-width: 200px;
    }

    /* Loading */
    .find-loading {
        text-align: center;
        padding: 40px;
        color: #94a3b8;
        display: none;
    }
    .find-loading .spinner {
        width: 36px; height: 36px;
        border: 3px solid #e2e8f0;
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: spin .7s linear infinite;
        margin: 0 auto 12px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* No result */
    .find-empty {
        text-align: center;
        padding: 50px 20px;
        color: #94a3b8;
        display: none;
    }
    .find-empty i { font-size: 3rem; margin-bottom: 14px; opacity: .4; display: block; }
    .find-empty p { font-size: 14px; margin: 0; }

    /* Results */
    .results-section { display: none; }
    .results-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }
    .results-header .rh-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: #fff;
    }
    .results-header h4 { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0; }
    .results-header .rh-count {
        background: #f1f5f9;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        margin-left: auto;
    }

    /* Result Card */
    .result-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 22px;
        margin-bottom: 12px;
        transition: all .2s;
        box-shadow: 0 2px 8px rgba(0,0,0,.03);
    }
    .result-card:hover {
        border-color: #c7d2fe;
        box-shadow: 0 4px 16px rgba(99,102,241,.1);
        transform: translateY(-1px);
    }
    .rc-top {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }
    .rc-number {
        font-size: 1.05rem;
        font-weight: 800;
        color: #1e293b;
    }
    .rc-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
    }
    .rc-badge.posted { background: #dcfce7; color: #166534; }
    .rc-badge.pending { background: #fef9c3; color: #854d0e; }
    .rc-badge.draft { background: #e0e7ff; color: #4338ca; }
    .rc-badge.default { background: #f1f5f9; color: #475569; }

    .rc-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }
    .rc-meta-item label {
        font-size: 10px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .3px;
        margin-bottom: 2px;
        display: block;
    }
    .rc-meta-item span {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
    }

    .rc-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        border-top: 1px solid #f1f5f9;
        padding-top: 12px;
    }
    .rc-btn {
        padding: 6px 16px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all .15s;
        border: none;
        cursor: pointer;
    }
    .rc-btn-view { background: #eef2ff; color: #4f46e5; }
    .rc-btn-view:hover { background: #c7d2fe; color: #3730a3; }
    .rc-btn-edit { background: #fef9c3; color: #854d0e; }
    .rc-btn-edit:hover { background: #fde68a; color: #713f12; }
    .rc-btn-delete { background: #fee2e2; color: #dc2626; }
    .rc-btn-delete:hover { background: #fca5a5; color: #991b1b; }
    .rc-btn-pdf { background: #dcfce7; color: #166534; }
    .rc-btn-pdf:hover { background: #bbf7d0; color: #14532d; }
    .rc-btn-receipt { background: #e0f2fe; color: #0369a1; }
    .rc-btn-receipt:hover { background: #bae6fd; color: #075985; }
    .rc-btn-dc { background: #f3e8ff; color: #7c3aed; }
    .rc-btn-dc:hover { background: #e9d5ff; color: #6d28d9; }
    .rc-btn-gatepass { background: #fef3c7; color: #92400e; }
    .rc-btn-gatepass:hover { background: #fde68a; color: #78350f; }
    .rc-btn-pending {
        background: #f1f5f9;
        color: #94a3b8;
        border: 1.5px dashed #cbd5e1;
        cursor: pointer;
    }
    .rc-btn-pending:hover { background: #e2e8f0; color: #64748b; }
</style>

<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<div class="find-wrapper">

    {{-- Header --}}
    <div class="find-header">
        <h2><i class="fas fa-search"></i> Find Document</h2>
        <p>Search any document by type and number — Sale Invoices, Purchase Invoices, Outward & Inward Gatepasses</p>
    </div>

    {{-- Super Admin: Branch Selector --}}
    @if($isSuperAdmin)
    <div class="branch-bar">
        <span style="font-size:18px;">&#127968;</span>
        <label>Branch:</label>
        <select id="find_branch_id">
            <option value="0">🌐 All Branches</option>
            @foreach($branches as $br)
                <option value="{{ $br->id }}">{{ $br->name }}</option>
            @endforeach
        </select>
        <span style="color:#60a5fa;font-size:11px;">Super admin can search across any branch</span>
    </div>
    @endif

    {{-- Search Card --}}
    <div class="search-card">
        <div class="search-row">
            <div class="search-field" style="max-width:220px;">
                <label><i class="fas fa-folder"></i> Find By</label>
                <select id="find_type">
                    <option value="">— Select Type —</option>
                    <option value="sale_invoice">📄 Sale Invoice</option>
                    <option value="purchase_invoice">🛒 Purchase Invoice</option>
                    <option value="delivery_challan">🚚 Delivery Challan</option>
                    <option value="outward_gatepass">🚚 Outward Gatepass</option>
                    <option value="inward_gatepass">📥 Inward Gatepass</option>
                </select>
            </div>
            <div class="search-field" style="max-width:180px;">
                <label><i class="fas fa-hashtag"></i> Document No.</label>
                <input type="text" id="find_number" placeholder="Optional if dates used" autocomplete="off">
            </div>
            <div class="search-field" style="max-width:160px;">
                <label><i class="fas fa-calendar-alt"></i> Start Date</label>
                <input type="date" id="find_start_date">
            </div>
            <div class="search-field" style="max-width:160px;">
                <label><i class="fas fa-calendar-check"></i> End Date</label>
                <input type="date" id="find_end_date">
            </div>
            <button class="btn-find" id="btn_find" onclick="doFind()">
                <i class="fas fa-search"></i> Find
            </button>
        </div>
    </div>

    {{-- Loading --}}
    <div class="find-loading" id="find_loading">
        <div class="spinner"></div>
        <p>Searching...</p>
    </div>

    {{-- Empty State --}}
    <div class="find-empty" id="find_empty">
        <i class="fas fa-inbox"></i>
        <p id="find_empty_msg">No documents found.</p>
    </div>

    {{-- Results --}}
    <div class="results-section" id="find_results">
        <div class="results-header">
            <div class="rh-icon" id="rh_icon"><i class="fas fa-file"></i></div>
            <h4 id="rh_title">Results</h4>
            <span class="rh-count" id="rh_count">0</span>
        </div>
        <div id="results_container"></div>
    </div>

</div>

<script>
function doFind() {
    var type      = document.getElementById('find_type').value;
    var number    = document.getElementById('find_number').value.trim();
    var startDate = document.getElementById('find_start_date').value;
    var endDate   = document.getElementById('find_end_date').value;
    var branchEl  = document.getElementById('find_branch_id');
    var branchId  = branchEl ? branchEl.value : 0;

    if (!type) { alert('Please select a document type.'); return; }
    
    // Allow search if either number is provided OR both dates are provided
    if (!number && (!startDate || !endDate)) { 
        alert('Please enter a document number OR select both Start and End Dates.'); 
        return; 
    }

    // Show loading
    document.getElementById('find_loading').style.display = 'block';
    document.getElementById('find_empty').style.display = 'none';
    document.getElementById('find_results').style.display = 'none';
    document.getElementById('btn_find').disabled = true;

    $.ajax({
        url: '{{ route("find.search") }}',
        type: 'GET',
        data: { 
            type: type, 
            number: number, 
            start_date: startDate, 
            end_date: endDate, 
            branch_id: branchId 
        },
        dataType: 'json',
        success: function(resp) {
            document.getElementById('find_loading').style.display = 'none';
            document.getElementById('btn_find').disabled = false;

            if (!resp.found) {
                document.getElementById('find_empty_msg').textContent = resp.message || 'No documents found.';
                document.getElementById('find_empty').style.display = 'block';
                return;
            }

            // Render results
            renderResults(resp);
        },
        error: function(xhr) {
            document.getElementById('find_loading').style.display = 'none';
            document.getElementById('btn_find').disabled = false;
            alert('Error searching: ' + (xhr.responseJSON?.message || 'Server error'));
        }
    });
}

// Enter key triggers search
document.getElementById('find_number').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); doFind(); }
});

function renderResults(resp) {
    var section = document.getElementById('find_results');
    var container = document.getElementById('results_container');

    // Header
    document.getElementById('rh_icon').innerHTML = '<i class="' + resp.icon + '"></i>';
    document.getElementById('rh_icon').style.background = resp.color;
    document.getElementById('rh_title').textContent = resp.type + ' Results';
    document.getElementById('rh_count').textContent = resp.records.length + ' found';

    // Cards
    var html = '';
    resp.records.forEach(function(rec) {
        var statusClass = 'default';
        var st = (rec.status || '').toLowerCase();
        if (st === 'posted' || st === 'received' || st === 'delivered') statusClass = 'posted';
        else if (st === 'pending' || st === 'partial') statusClass = 'pending';
        else if (st === 'draft' || st === 'draft_posted') statusClass = 'draft';

        html += '<div class="result-card">';
        html += '<div class="rc-top">';
        html += '<span class="rc-number">' + escHtml(rec.doc_number) + '</span>';
        if (rec.manual_number) html += '<span style="color:#94a3b8;font-size:12px;">(Manual: ' + escHtml(rec.manual_number) + ')</span>';
        html += '<span class="rc-badge ' + statusClass + '">' + escHtml(rec.status) + '</span>';
        html += '</div>';

        html += '<div class="rc-meta">';
        html += metaItem('Date', rec.date);
        html += metaItem('Party', rec.party);
        html += metaItem('Branch', rec.branch);
        if (rec.amount !== '—') html += metaItem('Amount', 'PKR ' + rec.amount);
        if (rec.items_count > 0) html += metaItem('Items', rec.items_count);
        html += '</div>';

        html += '<div class="rc-actions">';

        // View — always show if available
        if (rec.urls.view)
            html += '<a href="' + rec.urls.view + '" class="rc-btn rc-btn-view"><i class="fas fa-eye"></i> View</a>';

        // DC Logic (Dual Functionality)
        if (typeof rec.urls.dc !== 'undefined') {
            if (rec.urls.dc) {
                // DC exists -> View DC
                html += '<a href="' + rec.urls.dc + '" class="rc-btn rc-btn-dc"><i class="fas fa-file-alt"></i> View DC</a>';
            } else if (rec.urls.dc_create) {
                // DC does not exist but can be created -> Create DC
                html += '<a href="' + rec.urls.dc_create + '" class="rc-btn" style="background:#dbeafe;color:#1e40af;"><i class="fas fa-plus"></i> Create DC</a>';
            } else {
                html += '<a href="javascript:void(0)" class="rc-btn rc-btn-pending" onclick="showPending(\'DC\', \'Cannot create DC yet. The invoice must be finalized or in Draft Posted state.\');"><i class="fas fa-file-alt"></i> DC ⏳</a>';
            }
        }

        // Gatepass Logic (Dual Functionality)
        if (typeof rec.urls.gatepass !== 'undefined') {
            if (rec.urls.gatepass) {
                // Gatepass exists -> View Gatepass
                html += '<a href="' + rec.urls.gatepass + '" class="rc-btn rc-btn-gatepass"><i class="fas fa-truck"></i> View Gatepass</a>';
            } else if (rec.urls.gatepass_create) {
                // Gatepass does not exist but can be created -> Create Gatepass
                html += '<a href="' + rec.urls.gatepass_create + '" class="rc-btn" style="background:#ffedd5;color:#9a3412;"><i class="fas fa-plus"></i> Create Gatepass</a>';
            } else {
                html += '<a href="javascript:void(0)" class="rc-btn rc-btn-pending" onclick="showPending(\'Gatepass\', \'Cannot create gatepass yet. A Delivery Challan must be generated first.\');"><i class="fas fa-truck"></i> Gatepass ⏳</a>';
            }
        }

        // PDF
        if (rec.urls.pdf)
            html += '<a href="' + rec.urls.pdf + '" class="rc-btn rc-btn-pdf"><i class="fas fa-file-pdf"></i> PDF</a>';

        html += '</div>';
        html += '</div>';
    });

    container.innerHTML = html;
    section.style.display = 'block';
}

function metaItem(label, value) {
    return '<div class="rc-meta-item"><label>' + label + '</label><span>' + escHtml(value || '—') + '</span></div>';
}

function escHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function showPending(title, message) {
    Swal.fire({
        icon: 'info',
        title: title + ' Pending',
        text: message,
        confirmButtonColor: '#6366f1',
        confirmButtonText: 'OK'
    });
}

function deleteDoc(url) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This document will be permanently deleted. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (!result.isConfirmed) return;

    // Create and submit a hidden form for DELETE
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';

    var csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    var method = document.createElement('input');
    method.type = 'hidden';
    method.name = '_method';
    method.value = 'DELETE';
    form.appendChild(method);

        document.body.appendChild(form);
        form.submit();
    });
}
</script>

@endsection
