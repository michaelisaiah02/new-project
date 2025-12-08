@php
    function formatPhone($phone)
    {
        $digits = preg_replace('/\D/', '', $phone);

        // fallback: treat as Indonesia
        return '+62-' . chunkNumber(ltrim($digits, '0'));
    }

    // Helper: chunk phone number for display (btw you can refine this rule)
    function chunkNumber($number)
    {
        return implode('-', str_split($number, 4));
    }
@endphp
@forelse ($users as $user)
    <tr class="text-center align-middle">
        <td>{{ $loop->iteration }}</td>
        <td>{{ $user->id }}</td>
        <td class="text-start">{{ $user->name }}</td>
        <td>{{ $user->department?->name ?? '-' }}</td>
        <td>{{ formatPhone($user->whatsapp) }}</td>
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
                data-whatsapp="{{ $user->whatsapp }}" data-approved="{{ $user->approved }}"
                data-checked="{{ $user->checked }}">
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
