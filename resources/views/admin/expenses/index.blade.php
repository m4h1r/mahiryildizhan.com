@extends('admin.layout', ['title' => 'Expenses', 'heading' => 'Expenses'])

@section('content')
    <div class="space-y-6">
        <section class="card-admin p-4 md:p-6">
            <form method="GET" class="grid gap-3 md:grid-cols-4">
                <input class="form-input-admin" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                <input class="form-input-admin" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                <input class="form-input-admin" type="number" name="year" placeholder="Year" value="{{ $filters['year'] ?? '' }}">
                <input class="form-input-admin" type="number" min="1" max="12" name="month" placeholder="Month" value="{{ $filters['month'] ?? '' }}">

                <select name="expense_type_id" class="form-input-admin">
                    <option value="">All Types</option>
                    @foreach ($expenseTypes as $expenseType)
                        <option value="{{ $expenseType->id }}" @selected((string) ($filters['expense_type_id'] ?? '') === (string) $expenseType->id)>{{ $expenseType->name }}</option>
                    @endforeach
                </select>

                <select name="currency_id" class="form-input-admin">
                    <option value="">All Currencies</option>
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency->id }}" @selected((string) ($filters['currency_id'] ?? '') === (string) $currency->id)>{{ $currency->code }}</option>
                    @endforeach
                </select>

                <select name="stakeholder_id" class="form-input-admin">
                    <option value="">All Stakeholders</option>
                    @foreach ($stakeholders as $stakeholder)
                        <option value="{{ $stakeholder->id }}" @selected((string) ($filters['stakeholder_id'] ?? '') === (string) $stakeholder->id)>
                            {{ $stakeholder->title ?: trim(($stakeholder->name ?? '').' '.($stakeholder->surname ?? '')) }}
                        </option>
                    @endforeach
                </select>

                <input class="form-input-admin" name="stakeholder_query" placeholder="Search stakeholder name or VKN" value="{{ $filters['stakeholder_query'] ?? '' }}">

                <select name="company_expense" class="form-input-admin">
                    <option value="">All Company Flags</option>
                    <option value="1" @selected(($filters['company_expense'] ?? '') === '1')>Company</option>
                    <option value="0" @selected(($filters['company_expense'] ?? '') === '0')>Personal</option>
                </select>

                <select name="paid_by_others" class="form-input-admin">
                    <option value="">All Payer Flags</option>
                    <option value="1" @selected(($filters['paid_by_others'] ?? '') === '1')>Paid by others</option>
                    <option value="0" @selected(($filters['paid_by_others'] ?? '') === '0')>Paid by me</option>
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="admin-btn admin-btn-primary">Filter</button>
                    <a href="{{ route('admin.expenses.index') }}" class="admin-btn admin-btn-ghost">Reset</a>
                </div>
            </form>
        </section>

        <section class="admin-table-shell">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <h2 class="text-sm font-semibold">Expense List</h2>
                <a href="{{ route('admin.expenses.create') }}" class="admin-btn admin-btn-primary">New Expense</a>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Stakeholder</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Total</th>
                            <th>Flags</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td>{{ optional($expense->date)->toDateString() }}</td>
                                <td>{{ optional($expense->stakeholder)->title ?: '-' }}</td>
                                <td>{{ optional($expense->expenseType)->name ?: '-' }}</td>
                                <td>{{ number_format((float) $expense->price, 2) }} x {{ number_format((float) $expense->quantity, 3) }}</td>
                                <td>{{ number_format((float) $expense->total, 2) }} {{ optional($expense->currency)->code }}</td>
                                <td class="text-xs">
                                    {{ $expense->company_expense ? 'Company' : 'Personal' }} /
                                    {{ $expense->paid_by_others ? 'Others' : 'Self' }}
                                </td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.expenses.edit', $expense) }}" class="admin-btn admin-btn-ghost !rounded-lg !px-2.5 !py-1.5 !text-xs">Edit</a>

                                        <form method="POST" action="{{ route('admin.expenses.duplicate', $expense) }}">
                                            @csrf
                                            <button type="submit" class="admin-btn admin-btn-ghost !rounded-lg !px-2.5 !py-1.5 !text-xs">Duplicate</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn !rounded-lg !border !border-red-300 !px-2.5 !py-1.5 !text-xs !font-semibold !text-red-700 transition hover:!border-red-400 hover:!text-red-800 dark:!border-red-900 dark:!text-red-300">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No expenses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                {{ $expenses->links() }}
            </div>
        </section>
    </div>
@endsection
