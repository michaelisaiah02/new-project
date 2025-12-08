@forelse ($customers as $customer)
    <tr class="text-center align-middle">
        <td>{{ $loop->iteration }}</td>
        <td>{{ $customer->code }}</td>
        <td class="text-start">{{ $customer->name }}</td>
        <td>{{ $customer->department?->name ?? '-' }}</td>
        <td>
            <a href="{{ route('marketing.customers.edit', ['customer' => $customer->code]) }}"
                class="btn btn-sm btn-primary">
                Edit
            </a>
            <a href="{{ route('marketing.customers.editStage', ['customer' => $customer->code, 'stageNumber' => 1]) }}"
                class="btn btn-sm btn-primary">
                Edit Stages
            </a>
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
