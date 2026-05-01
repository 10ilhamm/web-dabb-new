@extends('layouts.app')

@section('breadcrumb_items')
    <span class="text-gray-400">CMS</span>
    <span class="mx-2 text-gray-300">/</span>
    <a href="{{ route('cms.pengguna.index') }}"
        class="text-gray-500 hover:text-gray-700">{{ __('cms.pengguna.breadcrumb') }}</a>
@endsection
@section('breadcrumb_active', __('cms.roles.title'))

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <style>
        #tableRoles_wrapper .dt-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
        }

        #tableRoles_wrapper .dt-top-row .dataTables_length { margin: 0; }

        #tableRoles_wrapper .dt-top-row .dt-top-right {
            display: flex;
            align-items: center;
            gap: .625rem;
            flex-wrap: wrap;
        }

        #tableRoles_wrapper .dt-top-row .dataTables_filter { margin: 0; }

        #tableRoles_wrapper .dataTables_length label,
        #tableRoles_wrapper .dataTables_filter label {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .8125rem;
            color: #6b7280;
            margin: 0;
            font-weight: 500;
        }

        #tableRoles_wrapper .dataTables_length select,
        #tableRoles_wrapper .dataTables_filter input {
            border: 1px solid #e5e7eb;
            border-radius: .5rem;
            padding: .5rem .75rem;
            font-size: .8125rem;
            background: white;
        }

        #tableRoles_wrapper .dataTables_length select {
            padding-right: 2rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right .5rem center;
            background-size: 1rem 1rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        #tableRoles_wrapper .dataTables_filter input { min-width: 220px; }

        .table-roles-filter-row {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f3f4f6;
        }

        @media (min-width: 768px) {
            .table-roles-filter-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .table-roles-filter-row label {
            display: block;
            font-size: .7rem;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: .375rem;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .table-roles-filter-row select {
            width: 100%;
            padding: .625rem .875rem;
            border: 1px solid #e5e7eb;
            border-radius: .5rem;
            font-size: .8125rem;
            background-color: white;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            transition: all .15s ease;
            color: #374151;
        }

        .table-roles-filter-row option.filter-placeholder {
            color: #9ca3af;
        }

        .table-roles-filter-row select:focus {
            outline: none;
            border-color: transparent;
            box-shadow: 0 0 0 2px #3b82f6;
        }

        div.dt-buttons {
            display: inline-flex;
            gap: .5rem;
            margin: 0;
        }

        div.dt-buttons .dt-button {
            background: #fff !important;
            border: 1px solid #e5e7eb !important;
            color: #374151 !important;
            padding: .5rem .875rem !important;
            border-radius: .5rem !important;
            font-size: .8125rem !important;
            font-weight: 500 !important;
            box-shadow: none !important;
            margin: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: .375rem !important;
            vertical-align: middle !important;
        }

        div.dt-buttons .dt-button svg {
            display: inline-block !important;
            vertical-align: middle !important;
            width: 1rem !important;
            height: 1rem !important;
            flex-shrink: 0 !important;
        }

        div.dt-buttons .dt-button:hover {
            background: #f9fafb !important;
            border-color: #d1d5db !important;
            color: #111827 !important;
        }

        div.dt-button-background,
        .dt-button-background { display: none !important; background: transparent !important; opacity: 0 !important; }

        div.dt-button-collection {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: .5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, .1);
            padding: .375rem;
            min-width: 160px;
        }

        div.dt-button-collection .dt-button {
            display: block !important;
            width: 100% !important;
            text-align: left !important;
            border: 0 !important;
            border-radius: .375rem !important;
            margin: 0 !important;
            padding: .5rem .75rem !important;
        }

        div.dt-button-collection .dt-button:hover { background: #f3f4f6 !important; }

        .btn-add-role {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            background: #174E93;
            color: white;
            font-size: .8125rem;
            font-weight: 600;
            padding: .5rem .875rem;
            border-radius: .5rem;
            transition: background-color .15s ease;
            text-decoration: none;
        }

        .btn-add-role:hover { background: #1e40af; }

        #tableRoles_wrapper .dt-bottom-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1rem 1.5rem;
            border-top: 1px solid #f3f4f6;
        }

        #tableRoles_wrapper .dataTables_info {
            font-size: .8125rem;
            color: #6b7280;
        }

        #tableRoles_wrapper .dataTables_paginate .paginate_button {
            padding: .375rem .75rem !important;
            margin: 0 .125rem !important;
            border: 1px solid #e5e7eb !important;
            border-radius: .375rem !important;
            font-size: .8125rem !important;
            background: white !important;
            color: #374151 !important;
        }

        #tableRoles_wrapper .dataTables_paginate .paginate_button.current {
            background: #174E93 !important;
            color: white !important;
            border-color: #174E93 !important;
        }

        #tableRoles_wrapper .dataTables_paginate .paginate_button:hover { background: #f3f4f6 !important; }

        #tableRoles_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #174E93 !important;
            color: white !important;
        }

        /* Child row (column details) styling */
        .role-columns-detail {
            padding: 1rem;
        }

        .role-columns-detail h4 {
            font-size: .875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: .75rem;
        }

        .role-columns-detail table {
            width: 100%;
            font-size: .8125rem;
            border-collapse: collapse;
        }

        .role-columns-detail table th {
            text-align: left;
            padding: .5rem .75rem;
            font-size: .6875rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: 1px solid #e5e7eb;
        }

        .role-columns-detail table td {
            padding: .5rem .75rem;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }

        .role-columns-detail table tr:last-child td {
            border-bottom: none;
        }

        /* Arrow indicator for expandable row */
        td.details-control {
            cursor: pointer;
            text-align: center;
            width: 40px;
        }

        td.details-control .arrow-icon {
            display: inline-flex;
            transition: transform .2s ease;
        }

        tr.shown td.details-control .arrow-icon {
            transform: rotate(90deg);
        }

        .detail-column-name {
            font-family: ui-monospace, monospace;
            font-size: .75rem;
        }

        .detail-type-badge {
            display: inline-flex;
            align-items: center;
            padding: .125rem .5rem;
            border-radius: .25rem;
            font-size: .6875rem;
            font-weight: 500;
            background-color: #f3f4f6;
            color: #374151;
        }

        .detail-attr-badge {
            display: inline-flex;
            align-items: center;
            padding: .0625rem .375rem;
            border-radius: .1875rem;
            font-size: .625rem;
            font-weight: 500;
            margin-right: .25rem;
            margin-bottom: .125rem;
        }
    </style>
@endpush

@section('content')
    <div class="space-y-6">

        {{-- Page Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ __('cms.roles.title') }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ __('cms.roles.subtitle') }}</p>
            </div>
            <a href="{{ route('cms.pengguna.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                {{ __('cms.pengguna.back') }}
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-start justify-between gap-2">
                <div>
                    <p class="text-sm text-gray-500">{{ __('cms.roles.stats_total') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-lg bg-blue-50 text-[#174E93] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-start justify-between gap-2">
                <div>
                    <p class="text-sm text-gray-500">{{ __('cms.roles.stats_system') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['system'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-start justify-between gap-2">
                <div>
                    <p class="text-sm text-gray-500">{{ __('cms.roles.stats_custom') }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['custom'] }}</p>
                </div>
                <div class="w-11 h-11 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="table-roles-filter-row">
                <div>
                    <label>Filter</label>
                    <select id="filter-type">
                        <option value="" class="filter-placeholder">{{ __('cms.roles.filter_type') }}</option>
                        <option value="system">{{ __('cms.roles.type_system') }}</option>
                        <option value="custom">{{ __('cms.roles.type_custom') }}</option>
                    </select>
                </div>
                <div>
                    <label>Filter</label>
                    <select id="filter-columns">
                        <option value="" class="filter-placeholder">{{ __('cms.roles.filter_columns') }}</option>
                        <option value="0">{{ __('cms.roles.filter_columns_none') }}</option>
                        <option value="1+">{{ __('cms.roles.filter_columns_has') }}</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="tableRoles" class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="w-10"></th>
                            <th class="w-12">No</th>
                            <th>{{ __('cms.roles.col_name') }}</th>
                            <th>{{ __('cms.roles.col_label') }}</th>
                            <th>{{ __('cms.roles.col_table') }}</th>
                            <th>{{ __('cms.roles.col_columns') }}</th>
                            <th>{{ __('cms.roles.col_type') }}</th>
                            <th>{{ __('cms.roles.col_users') }}</th>
                            <th class="text-center">{{ __('cms.pengguna.col_action') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        const i18n = {
            btnExport: '{{ __('cms.pengguna.btn_export') }}',
            btnCopy: '{{ __('cms.pengguna.btn_copy') }}',
            btnCsv: '{{ __('cms.pengguna.btn_csv') }}',
            btnExcel: '{{ __('cms.pengguna.btn_excel') }}',
            btnPdf: '{{ __('cms.pengguna.btn_pdf') }}',
            btnPrint: '{{ __('cms.pengguna.btn_print') }}',
            btnAddRole: '{{ __('cms.roles.add_button') }}',
            typeSystem: '{{ __('cms.roles.type_system') }}',
            typeCustom: '{{ __('cms.roles.type_custom') }}',
            columnsCount: '{{ __('cms.roles.columns_count') }}',
            filterColumnsNone: '{{ __('cms.roles.filter_columns_none') }}',
            filterColumnsHas: '{{ __('cms.roles.filter_columns_has') }}',
            urlCreate: '{{ route('cms.pengguna.roles.create') }}',
            searchPlaceholder: '{{ __('cms.roles.search_placeholder') }}',
            tableStructure: '{{ __('cms.roles.table_structure') }}',
            noColumns: '{{ __('cms.roles.no_columns') }}',
        };

        function formatColumns(columnsData) {
            if (!columnsData || columnsData.length === 0) {
                return `<div class="role-columns-detail"><p class="text-sm text-gray-400 italic">${i18n.noColumns}</p></div>`;
            }

            let html = `<div class="role-columns-detail">
                <h4>${i18n.tableStructure}</h4>
                <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Column</th>
                            <th>Type</th>
                            <th>Label</th>
                            <th>Attributes</th>
                            <th>Foreign Key</th>
                            <th>Options</th>
                        </tr>
                    </thead>
                    <tbody>`;

            columnsData.forEach(function(col) {
                let attrs = '';
                if (col.is_primary) attrs += `<span class="detail-attr-badge" style="background:#fef3c7;color:#92400e;">Primary</span>`;
                if (col.is_unique) attrs += `<span class="detail-attr-badge" style="background:#dbeafe;color:#1e40af;">Unique</span>`;
                if (col.is_nullable) attrs += `<span class="detail-attr-badge" style="background:#d1fae5;color:#065f46;">Nullable</span>`;
                if (col.is_unsigned) attrs += `<span class="detail-attr-badge" style="background:#ede9fe;color:#5b21b6;">Unsigned</span>`;
                if (col.is_auto_increment) attrs += `<span class="detail-attr-badge" style="background:#fed7aa;color:#9a3412;">Auto Inc</span>`;
                if (!attrs) attrs = '<span class="text-gray-400 text-xs">—</span>';

                let foreignHtml = '—';
                if (col.is_foreign) {
                    foreignHtml = `<div class="text-xs"><span class="font-mono text-gray-700">${col.references_table}.${col.references_column}</span>`;
                    if (col.on_delete || col.on_update) {
                        foreignHtml += `<div class="text-gray-400">`;
                        if (col.on_delete) foreignHtml += `<span class="mr-1">on delete: <span class="font-mono text-gray-600">${col.on_delete}</span></span>`;
                        if (col.on_update) foreignHtml += `<span>on update: <span class="font-mono text-gray-600">${col.on_update}</span></span>`;
                        foreignHtml += `</div>`;
                    }
                    foreignHtml += `</div>`;
                }

                let optionsHtml = '—';
                if (col.options) {
                    optionsHtml = `<span class="text-xs font-mono text-gray-600">${col.options}</span>`;
                }

                html += `<tr>
                    <td><span class="detail-column-name">${col.column_name}</span></td>
                    <td><span class="detail-type-badge">${col.column_type}${col.column_length ? '(' + col.column_length + ')' : ''}</span></td>
                    <td class="text-gray-600">${col.column_label}</td>
                    <td><div style="display:flex;flex-wrap:wrap;gap:2px;">${attrs}</div></td>
                    <td>${foreignHtml}</td>
                    <td>${optionsHtml}</td>
                </tr>`;
            });

            html += `</tbody></table></div></div>`;
            return html;
        }

        $(document).ready(function() {
            const table = $('#tableRoles').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: window.location.href,
                    dataSrc: 'data',
                },
                dom: `
                    <"dt-top-row"l<"dt-top-right"Bf>>
                    rt
                    <"dt-bottom-row"ip>
                `,
                buttons: [{
                    extend: 'collection',
                    text: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> ${i18n.btnExport}`,
                    buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                    className: 'btn-export-group',
                }],
                columnDefs: [
                    { orderable: false, targets: [0, 8] },
                    { className: 'details-control', targets: 0 },
                    { targets: 1, orderSequence: ['asc', 'desc'] },
                ],
                columns: [
                    {
                        data: null,
                        defaultContent: `<span class="arrow-icon"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></span>`,
                    },
                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'name',
                        render: function(data) {
                            return `<code class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">${data}</code>`;
                        }
                    },
                    { data: 'label' },
                    {
                        data: 'table_name',
                        render: function(data) {
                            if (!data) return '<span class="text-gray-400">—</span>';
                            return `<code class="font-mono bg-gray-50 px-1.5 py-0.5 rounded text-xs">${data}</code>`;
                        }
                    },
                    {
                        data: 'columns_count',
                        render: function(data) {
                            return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-600 border border-blue-100">${data} ${i18n.columnsCount}</span>`;
                        }
                    },
                    {
                        data: 'is_system',
                        render: function(data) {
                            if (data) {
                                return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-600 border border-red-100">${i18n.typeSystem}</span>`;
                            }
                            return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-600 border border-green-100">${i18n.typeCustom}</span>`;
                        }
                    },
                    { data: 'users_count', defaultContent: '0' },
                    {
                        data: null,
                        className: 'text-center',
                        orderable: false,
                        render: function(data) {
                            const editUrl = `{{ route('cms.pengguna.roles.edit', ':id') }}`.replace(':id', data.id);
                            const deleteUrl = `{{ route('cms.pengguna.roles.destroy', ':id') }}`.replace(':id', data.id);
                            let html = `<div class="flex items-center justify-center gap-2">
                                <a href="${editUrl}" class="inline-flex items-center justify-center w-8 h-8 bg-yellow-400 hover:bg-yellow-500 text-white rounded-md transition-colors" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>`;
                            if (!data.is_system) {
                                html += `<form action="${deleteUrl}" method="POST" class="inline" onsubmit="return confirm('Hapus role ${data.label}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-md transition-colors" title="Hapus">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>`;
                            }
                            html += `</div>`;
                            return html;
                        }
                    }
                ],
                order: [[1, 'asc']],
                language: {
                    search: '',
                    searchPlaceholder: i18n.searchPlaceholder,
                    lengthMenu: '_MENU_',
                    info: '{{ __('cms.roles.datatable_info') }}',
                    infoEmpty: '{{ __('cms.roles.datatable_info_empty') }}',
                    infoFiltered: '{{ __('cms.roles.datatable_info_filtered') }}',
                    zeroRecords: '{{ __('cms.roles.datatable_zero_records') }}',
                    paginate: {
                        first: '&laquo;',
                        previous: '&lsaquo;',
                        next: '&rsaquo;',
                        last: '&raquo;',
                    },
                },
                initComplete: function() {
                    const topRight = $('#tableRoles_wrapper .dt-top-right');
                    topRight.append(
                        `<a href="${i18n.urlCreate}" class="btn-add-role">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            ${i18n.btnAddRole}
                        </a>`
                    );
                },
            });

            // Expandable row details (child row)
            $('#tableRoles tbody').on('click', 'td.details-control', function() {
                const tr = $(this).closest('tr');
                const row = table.row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    const data = row.data();
                    row.child(formatColumns(data.columns_data)).show();
                    tr.addClass('shown');
                }
            });

            // Filter by type
            $('#filter-type').on('change', function() {
                const val = this.value;
                if (val === 'system') {
                    table.column(6).search('System').draw();
                } else if (val === 'custom') {
                    table.column(6).search('Custom').draw();
                } else {
                    table.column(6).search('').draw();
                }
            });

            // Filter by columns count
            $('#filter-columns').on('change', function() {
                const val = this.value;
                if (!val) {
                    table.column(5).search('').draw();
                } else if (val === '0') {
                    table.column(5).search('0 ' + i18n.columnsCount).draw();
                } else {
                    table.column(5).search('').draw();
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            const count = parseInt($(table.row(dataIndex).node()).find('td:eq(5)').text()) || 0;
                            return count > 0;
                        }
                    );
                    table.draw();
                    $.fn.dataTable.ext.search.pop();
                }
            });
        });
    </script>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: @json(session('success')),
                        toast: true,
                        position: 'top-end',
                        timer: 2500,
                        showConfirmButton: false
                    });
                }
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: @json(session('error')),
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            });
        </script>
    @endif
@endpush