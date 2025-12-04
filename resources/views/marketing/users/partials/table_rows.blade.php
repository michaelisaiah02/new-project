@forelse ($users as $user)
    <tr class="text-center align-middle">
        <td>{{ $loop->iteration }}</td>
        <td>{{ $user->id }}</td>
        <td class="text-start">{{ $user->name }}</td>
        <td>{{ $user->department?->name ?? '-' }}</td>
        <td>
            @if ($user->checked)
                <i class="bi bi-check-circle-fill text-success" aria-label="Checked"></i>
            @endif
        </td>
        <td>
            @if ($user->approved)
                <i class="bi bi-check-circle-fill text-success" aria-label="Approved"></i>
            @endif
        </td>
        <td>
            <button class="btn btn-sm btn-primary btn-edit-user" data-id="{{ $user->id }}"
                data-name="{{ $user->name }}" data-department="{{ $user->department_id }}"
                data-approved="{{ $user->approved }}" data-checked="{{ $user->checked }}">
                Edit
            </button>
            <button class="btn btn-sm btn-danger btn-delete-user" data-id="{{ $user->id }}"
                data-name="{{ $user->name }}" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                Delete
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center">No result.</td>
    </tr>
@endforelse
