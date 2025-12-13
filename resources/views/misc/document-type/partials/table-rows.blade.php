@forelse ($documentTypes as $documentType)
    <tr class="text-center align-middle">
        <td>{{ $loop->iteration }}</td>
        <td class="text-start">{{ $documentType->name }}</td>
        <td>
            <button class="btn btn-sm btn-primary btn-edit" data-code="{{ $documentType->code }}"
                data-name="{{ $documentType->name }}">
                Edit
            </button>
            <button class="btn btn-sm btn-danger btn-delete" data-code="{{ $documentType->code }}"
                data-name="{{ $documentType->name }}" data-bs-toggle="modal" data-bs-target="#deleteDocumentTypeModal">
                Delete
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center">No result.</td>
    </tr>
@endforelse
