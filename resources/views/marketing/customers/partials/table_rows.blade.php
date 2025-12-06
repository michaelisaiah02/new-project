@forelse ($customers as $customer)
    <tr class="text-center align-middle">
        <td>{{ $loop->iteration }}</td>
        <td>{{ $customer->code }}</td>
        <td class="text-start">{{ $customer->name }}</td>
        <td>{{ $customer->department?->name ?? '-' }}</td>
        <td>
            <button class="btn btn-sm btn-primary" data-id="{{ $customer->code }}">
                Edit
            </button>
            <button class="btn btn-sm btn-danger btn-delete-customer" data-id="{{ $customer->code }}"
                data-name="{{ $customer->name }}" data-bs-toggle="modal" data-bs-target="#deleteCustomerModal">
                Delete
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center">No result.</td>
    </tr>
@endforelse
